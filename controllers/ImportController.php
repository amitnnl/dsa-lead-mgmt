<?php
/**
 * Import Controller - Excel/CSV file import with column mapping
 */
class ImportController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'upload': $this->upload(); break;
            case 'process': $this->process(); break;
            case 'history': $this->history(); break;
            default: $this->index(); break;
        }
    }

    private function index(): void {
        $data = ['page' => 'import'];
        $data['batches'] = $this->db->fetchAll(
            "SELECT ib.*, u.name as user_name FROM import_batches ib LEFT JOIN users u ON ib.user_id = u.id ORDER BY ib.created_at DESC LIMIT 20"
        );
        require __DIR__ . '/../views/layout.php';
    }

    private function upload(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=import'); return;
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'File upload failed.'];
            header('Location: index.php?page=import'); return;
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Only CSV and Excel files are supported.'];
            header('Location: index.php?page=import'); return;
        }

        // Save uploaded file
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $savedName = 'import_' . time() . '.' . $ext;
        $savedPath = UPLOAD_DIR . $savedName;
        move_uploaded_file($file['tmp_name'], $savedPath);

        // Read headers from the file
        $headers = $this->readHeaders($savedPath, $ext);
        if (empty($headers)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not read file headers.'];
            header('Location: index.php?page=import'); return;
        }

        // Store in session for mapping step
        $_SESSION['import_file'] = $savedPath;
        $_SESSION['import_filename'] = $file['name'];
        $_SESSION['import_headers'] = $headers;
        $_SESSION['import_ext'] = $ext;

        // Show mapping page
        $data = [
            'page' => 'import_map',
            'headers' => $headers,
            'filename' => $file['name'],
            'db_columns' => $this->getDbColumns(),
        ];
        require __DIR__ . '/../views/layout.php';
    }

    private function process(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=import'); return;
        }

        $filePath = $_SESSION['import_file'] ?? '';
        $ext = $_SESSION['import_ext'] ?? 'csv';
        $filename = $_SESSION['import_filename'] ?? 'unknown';

        if (!file_exists($filePath)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Import file not found.'];
            header('Location: index.php?page=import'); return;
        }

        // Get column mapping from POST
        $mapping = $_POST['mapping'] ?? [];
        $defaultSource = Security::sanitize($_POST['default_source'] ?? 'Excel Import');
        $defaultStatus = Security::sanitize($_POST['default_status'] ?? 'New');

        // Create import batch
        $batchId = $this->db->insert('import_batches', [
            'filename' => $filename,
            'column_mapping' => json_encode($mapping),
            'user_id' => Security::userId(),
            'status' => 'processing',
        ]);

        // Read all rows
        $rows = $this->readAllRows($filePath, $ext);
        $headers = $_SESSION['import_headers'] ?? [];

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($rows as $row) {
            try {
                $leadData = $this->mapRowToLead($row, $headers, $mapping, $defaultSource, $defaultStatus);
                if (empty($leadData['customer_name'])) { $skipped++; continue; }

                $leadData['import_batch_id'] = $batchId;
                $leadData['lead_score'] = LeadScorer::calculate($leadData);
                $leadData['lead_grade'] = LeadScorer::getLabel($leadData['lead_score']);

                $this->db->insert('leads', $leadData);
                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        // Update batch
        $this->db->update('import_batches', [
            'total_rows' => count($rows),
            'imported_rows' => $imported,
            'skipped_rows' => $skipped,
            'error_rows' => $errors,
            'status' => 'completed',
        ], 'id = ?', [$batchId]);

        // Cleanup session
        unset($_SESSION['import_file'], $_SESSION['import_filename'], $_SESSION['import_headers'], $_SESSION['import_ext']);

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Import complete! {$imported} leads imported, {$skipped} skipped, {$errors} errors."];
        header('Location: index.php?page=import');
    }

    private function readHeaders(string $path, string $ext): array {
        if ($ext === 'csv') {
            $handle = fopen($path, 'r');
            $headers = fgetcsv($handle);
            fclose($handle);
            return $headers ?: [];
        }
        // For xlsx - use simple XML parsing or PhpSpreadsheet if available
        return $this->readXlsxHeaders($path);
    }

    private function readAllRows(string $path, string $ext): array {
        if ($ext === 'csv') {
            $rows = [];
            $handle = fopen($path, 'r');
            fgetcsv($handle); // skip headers
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
            return $rows;
        }
        return $this->readXlsxRows($path);
    }

    private function readXlsxHeaders(string $path): array {
        // Try PhpSpreadsheet first
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [];
            foreach ($sheet->getRowIterator(1, 1) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $val = $cell->getValue();
                    if ($val !== null) $headers[] = trim($val);
                }
            }
            return $headers;
        }

        // Fallback: Parse xlsx as ZIP/XML
        return $this->parseXlsxFallback($path, true);
    }

    private function readXlsxRows(string $path): array {
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = [];
            $firstRow = true;
            foreach ($sheet->getRowIterator() as $row) {
                if ($firstRow) { $firstRow = false; continue; }
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $rows[] = $rowData;
            }
            return $rows;
        }

        return $this->parseXlsxFallback($path, false);
    }

    private function parseXlsxFallback(string $path, bool $headersOnly): array {
        // Basic xlsx parser using ZipArchive
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        // Read shared strings
        $strings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $xml = simplexml_load_string($ssXml);
            foreach ($xml->si as $si) {
                $strings[] = (string) $si->t;
            }
        }

        // Read sheet1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (!$sheetXml) return [];

        $xml = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $t = (string) ($cell['t'] ?? '');
                $v = (string) ($cell->v ?? '');
                if ($t === 's' && isset($strings[(int)$v])) {
                    $rowData[] = $strings[(int)$v];
                } else {
                    $rowData[] = $v;
                }
            }
            if ($headersOnly) return $rowData;
            $rows[] = $rowData;
        }

        // Remove first row (headers)
        if (!empty($rows)) array_shift($rows);
        return $rows;
    }

    private function mapRowToLead(array $row, array $headers, array $mapping, string $defaultSource, string $defaultStatus): array {
        $lead = [
            'customer_name' => '', 'phone_number' => '', 'alt_phone' => '',
            'email_address' => '', 'city' => '', 'state' => '', 'pincode' => '',
            'loan_type' => '', 'loan_amount' => 0, 'monthly_income' => 0,
            'employer' => '', 'employment_type' => null, 'address' => '',
            'lead_source' => $defaultSource, 'status' => $defaultStatus,
            'bank_name' => '', 'credit_score' => null, 'gender' => null,
            'dob' => null, 'remarks' => '',
        ];

        foreach ($mapping as $fileCol => $dbCol) {
            if (empty($dbCol) || $dbCol === 'skip') continue;
            $colIndex = intval($fileCol);
            $value = $row[$colIndex] ?? '';
            if (is_string($value)) $value = trim($value);

            switch ($dbCol) {
                case 'loan_amount':
                case 'monthly_income':
                    $lead[$dbCol] = floatval(preg_replace('/[^0-9.]/', '', $value));
                    break;
                case 'credit_score':
                    $lead[$dbCol] = !empty($value) ? intval($value) : null;
                    break;
                default:
                    $lead[$dbCol] = Security::sanitize((string) $value);
                    break;
            }
        }
        return $lead;
    }

    private function getDbColumns(): array {
        return [
            'skip' => '-- Skip this column --',
            'customer_name' => 'Customer Name',
            'phone_number' => 'Phone Number',
            'alt_phone' => 'Alt Phone',
            'email_address' => 'Email Address',
            'dob' => 'Date of Birth',
            'gender' => 'Gender',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'pincode' => 'Pincode',
            'loan_type' => 'Loan Type',
            'loan_amount' => 'Loan Amount',
            'monthly_income' => 'Monthly Income',
            'employer' => 'Employer',
            'employment_type' => 'Employment Type',
            'credit_score' => 'Credit Score',
            'bank_name' => 'Bank Name',
            'remarks' => 'Remarks',
        ];
    }
}
