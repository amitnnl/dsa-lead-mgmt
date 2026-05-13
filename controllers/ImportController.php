<?php
/**
 * Import Controller - Excel/CSV file import with column mapping
 * Supports both synchronous (small files) and background queue (large files)
 */
class ImportController {
    private Database $db;
    /** Threshold: files with more rows than this go to background queue */
    private int $asyncThreshold = 500;

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

        if (isset($_POST['reselect_file']) && file_exists($_POST['reselect_file'])) {
            $savedPath = $_POST['reselect_file'];
            $ext = strtolower(pathinfo($savedPath, PATHINFO_EXTENSION));
            $filename = $_SESSION['import_filename'] ?? 'uploaded_file';
        } else {
            if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'File upload failed.'];
                header('Location: index.php?page=import'); return;
            }

            $file = $_FILES['import_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $file['name'];

            if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Only CSV and Excel files are supported.'];
                header('Location: index.php?page=import'); return;
            }

            // Save uploaded file
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            $savedName = 'import_' . time() . '.' . $ext;
            $savedPath = UPLOAD_DIR . $savedName;
            move_uploaded_file($file['tmp_name'], $savedPath);
        }

        // Read sheets from the file
        $sheets = $this->getSheets($savedPath, $ext);
        $selectedSheet = $_POST['sheet'] ?? array_keys($sheets)[0] ?? 1;
        $importType = $_POST['import_type'] ?? 'leads';

        // Read headers from the selected sheet
        $headers = $this->readHeaders($savedPath, $ext, $selectedSheet);
        if (empty($headers)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Could not read file headers from the selected sheet.'];
            header('Location: index.php?page=import'); return;
        }

        // Count rows for async threshold decision
        $rowCount = $this->countRows($savedPath, $ext, $selectedSheet);

        // Store in session for mapping step
        $_SESSION['import_file'] = $savedPath;
        $_SESSION['import_filename'] = $filename;
        $_SESSION['import_headers'] = $headers;
        $_SESSION['import_ext'] = $ext;
        $_SESSION['import_row_count'] = $rowCount;
        $_SESSION['import_sheet'] = $selectedSheet;
        $_SESSION['import_type'] = $importType;

        // Show mapping page
        $data = [
            'page' => 'import_map',
            'headers' => $headers,
            'filename' => $filename,
            'sheets' => $sheets,
            'current_sheet' => $selectedSheet,
            'import_type' => $importType,
            'db_columns' => $this->getDbColumns($importType),
            'row_count' => $rowCount,
            'is_large' => $rowCount > $this->asyncThreshold,
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
        $rowCount = $_SESSION['import_row_count'] ?? 0;
        $headers = $_SESSION['import_headers'] ?? [];
        $importType = $_SESSION['import_type'] ?? 'leads';

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
            'import_type' => $importType,
        ]);

        // Decision: sync or async
        if ($rowCount > $this->asyncThreshold) {
            // === ASYNC: Queue for background processing ===
            $processor = new JobProcessor();
            $jobId = $processor->queueImport($batchId, $filePath, $ext, $mapping, $defaultSource, $defaultStatus, $headers, $importType);

            // Try to process immediately (inline async simulation)
            try {
                set_time_limit(300);
                $processor->processNext();
            } catch (\Exception $e) {}

            // Cleanup session
            unset($_SESSION['import_file'], $_SESSION['import_filename'], $_SESSION['import_headers'], $_SESSION['import_ext'], $_SESSION['import_row_count'], $_SESSION['import_type'], $_SESSION['import_sheet']);

            $jobStatus = JobProcessor::getJobStatus($jobId);
            if ($jobStatus && $jobStatus['status'] === 'completed') {
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Import complete! {$jobStatus['imported_rows']} items imported."];
            } else {
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Large file queued for processing ({$rowCount} rows)."];
            }
        } else {
            // === SYNC: Process immediately (small files) ===
            $sheet = $_SESSION['import_sheet'] ?? 1;
            $rows = $this->readAllRows($filePath, $ext, $sheet);
            $imported = 0;
            $skipped = 0;
            $errors = 0;

            $targetTable = ($importType === 'payouts') ? 'client_payouts' : 'leads';

            foreach ($rows as $row) {
                try {
                    $itemData = $this->mapRowToItem($row, $headers, $mapping, $defaultSource, $defaultStatus, $importType);
                    
                    if ($importType === 'leads') {
                        if (empty($itemData['customer_name'])) { $skipped++; continue; }
                        $itemData['import_batch_id'] = $batchId;
                        $itemData['lead_score'] = LeadScorer::calculate($itemData);
                        $itemData['lead_grade'] = LeadScorer::getLabel($itemData['lead_score']);
                    } else {
                        if (empty($itemData['client_name']) && empty($itemData['payout_amount'])) { $skipped++; continue; }
                        $itemData['import_batch_id'] = $batchId;
                        
                        // Try to link to existing lead by phone number
                        if (!empty($itemData['phone_number'])) {
                            $lead = $this->db->fetch("SELECT id FROM leads WHERE phone_number = ? LIMIT 1", [$itemData['phone_number']]);
                            if ($lead) {
                                $itemData['lead_id'] = $lead['id'];
                            }
                        }
                    }

                    $this->db->insert($targetTable, $itemData);
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
            unset($_SESSION['import_file'], $_SESSION['import_filename'], $_SESSION['import_headers'], $_SESSION['import_ext'], $_SESSION['import_row_count']);

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Import complete! {$imported} leads imported, {$skipped} skipped, {$errors} errors."];
        }

        header('Location: index.php?page=import');
    }

    /**
     * Count rows in a file (fast scan without loading everything into memory)
     */
    private function countRows(string $path, string $ext, $sheet = 1): int {
        if ($ext === 'csv') {
            $count = 0;
            $handle = fopen($path, 'r');
            while (fgetcsv($handle) !== false) $count++;
            fclose($handle);
            return max(0, $count - 1); // Exclude header
        }
        // For xlsx - just count rows from XML
        $rows = $this->readAllRows($path, $ext, $sheet);
        return count($rows);
    }

    // ===== Public wrappers for JobProcessor access =====
    public function readAllRowsPublic(string $path, string $ext, $sheet = 1): array {
        return $this->readAllRows($path, $ext, $sheet);
    }
    public function mapRowToItemPublic(array $row, array $headers, array $mapping, string $defaultSource, string $defaultStatus, string $type = 'leads'): array {
        return $this->mapRowToItem($row, $headers, $mapping, $defaultSource, $defaultStatus, $type);
    }

    private function readHeaders(string $path, string $ext, $sheet = 1): array {
        if ($ext === 'csv') {
            $handle = fopen($path, 'r');
            $headers = fgetcsv($handle);
            fclose($handle);
            return $headers ?: [];
        }
        // For xlsx - use simple XML parsing or PhpSpreadsheet if available
        return $this->readXlsxHeaders($path, $sheet);
    }

    private function readAllRows(string $path, string $ext, $sheet = 1): array {
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
        return $this->readXlsxRows($path, $sheet);
    }

    private function readXlsxHeaders(string $path, $sheet = 1): array {
        // Try PhpSpreadsheet first
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheetObj = is_numeric($sheet) ? $spreadsheet->getSheet($sheet - 1) : $spreadsheet->getSheetByName($sheet);
            if (!$sheetObj) $sheetObj = $spreadsheet->getActiveSheet();
            
            $headers = [];
            foreach ($sheetObj->getRowIterator(1, 1) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $val = $cell->getValue();
                    if ($val !== null) $headers[] = trim($val);
                }
            }
            return $headers;
        }

        // Fallback: Parse xlsx as ZIP/XML
        return $this->parseXlsxFallback($path, true, $sheet);
    }

    private function readXlsxRows(string $path, $sheet = 1): array {
        if (class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheetObj = is_numeric($sheet) ? $spreadsheet->getSheet($sheet - 1) : $spreadsheet->getSheetByName($sheet);
            if (!$sheetObj) $sheetObj = $spreadsheet->getActiveSheet();
            
            $rows = [];
            $firstRow = true;
            foreach ($sheetObj->getRowIterator() as $row) {
                if ($firstRow) { $firstRow = false; continue; }
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $rows[] = $rowData;
            }
            return $rows;
        }

        return $this->parseXlsxFallback($path, false, $sheet);
    }

    private function parseXlsxFallback(string $path, bool $headersOnly, $sheet = 1): array {
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

        // Determine sheet file
        $sheetFile = "xl/worksheets/sheet{$sheet}.xml";
        if (!$zip->locateName($sheetFile)) {
            // If sheetN.xml doesn't exist, try to find mapping in workbook.xml
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            if ($workbookXml) {
                $xml = simplexml_load_string($workbookXml);
                $idx = 1;
                foreach ($xml->sheets->sheet as $s) {
                    if ($idx == $sheet || (string)$s['name'] == $sheet) {
                        // Usually they are sequential in files too, but let's be careful
                        // A truly robust parser would read .rels but for fallback we assume sequential
                        $sheetFile = "xl/worksheets/sheet{$idx}.xml";
                        break;
                    }
                    $idx++;
                }
            }
        }

        $sheetXml = $zip->getFromName($sheetFile);
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

    /**
     * Get list of sheets in an Excel file
     */
    private function getSheets(string $path, string $ext): array {
        if ($ext !== 'xlsx') return ['Default' => 1];

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return ['Sheet 1' => 1];

        $sheets = [];
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        if ($workbookXml) {
            $xml = simplexml_load_string($workbookXml);
            $idx = 1;
            foreach ($xml->sheets->sheet as $sheet) {
                $name = (string)$sheet['name'];
                $sheets[$name] = $idx++;
            }
        }
        $zip->close();

        return !empty($sheets) ? $sheets : ['Sheet 1' => 1];
    }

    private function mapRowToItem(array $row, array $headers, array $mapping, string $defaultSource, string $defaultStatus, string $type = 'leads'): array {
        if ($type === 'payouts') {
            $item = [
                'client_name' => '', 'phone_number' => '', 'payout_amount' => 0, 
                'payout_date' => date('Y-m-d'), 'bank_name' => '', 'account_number' => '',
                'transaction_id' => '', 'remarks' => ''
            ];
        } else {
            $item = [
                'customer_name' => '', 'phone_number' => '', 'alt_phone' => '',
                'email_address' => '', 'city' => '', 'state' => '', 'pincode' => '',
                'loan_type' => '', 'loan_amount' => 0, 'monthly_income' => 0,
                'employer' => '', 'employment_type' => null, 'address' => '',
                'lead_source' => $defaultSource, 'status' => $defaultStatus,
                'bank_name' => '', 'credit_score' => null, 'gender' => null,
                'dob' => null, 'remarks' => '',
            ];
        }

        foreach ($mapping as $fileCol => $dbCol) {
            if (empty($dbCol) || $dbCol === 'skip') continue;
            $colIndex = intval($fileCol);
            $value = $row[$colIndex] ?? '';
            if (is_string($value)) $value = trim($value);

            if ($dbCol === 'loan_amount' || $dbCol === 'monthly_income' || $dbCol === 'payout_amount') {
                $item[$dbCol] = floatval(preg_replace('/[^0-9.]/', '', (string)$value));
            } elseif ($dbCol === 'credit_score') {
                $item[$dbCol] = !empty($value) ? intval($value) : null;
            } else {
                $item[$dbCol] = Security::sanitize((string) $value);
            }
        }
        return $item;
    }

    private function getDbColumns(string $type = 'leads'): array {
        if ($type === 'payouts') {
            return [
                'skip' => '-- Skip this column --',
                'client_name' => 'Client Name',
                'phone_number' => 'Phone Number (to match lead)',
                'payout_amount' => 'Payout Amount',
                'payout_date' => 'Payout Date',
                'bank_name' => 'Bank Name',
                'account_number' => 'Account Number',
                'transaction_id' => 'Transaction ID',
                'remarks' => 'Remarks',
            ];
        }
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
