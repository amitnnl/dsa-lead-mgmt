<?php
/**
 * Background Job Processor
 * Processes queued import jobs in chunks to handle large files without timeouts
 */
class JobProcessor {
    private Database $db;
    private int $chunkSize;

    public function __construct(int $chunkSize = 100) {
        $this->db = new Database();
        $this->chunkSize = $chunkSize;
    }

    /**
     * Queue an import job for background processing
     */
    public function queueImport(int $batchId, string $filePath, string $ext, array $mapping, string $defaultSource, string $defaultStatus, array $headers): int {
        return $this->db->insert('job_queue', [
            'type' => 'import',
            'batch_id' => $batchId,
            'payload' => json_encode([
                'file_path' => $filePath,
                'ext' => $ext,
                'mapping' => $mapping,
                'default_source' => $defaultSource,
                'default_status' => $defaultStatus,
                'headers' => $headers,
            ]),
            'status' => 'pending',
            'progress' => 0,
            'total_items' => 0,
            'processed_items' => 0,
        ]);
    }

    /**
     * Process the next pending job (called by worker.php or inline)
     */
    public function processNext(): bool {
        // Grab the next pending job
        $job = $this->db->fetch(
            "SELECT * FROM job_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 1"
        );
        if (!$job) return false;

        // Mark as running
        $this->db->update('job_queue', ['status' => 'running', 'started_at' => date('Y-m-d H:i:s')], 'id = ?', [$job['id']]);

        try {
            $payload = json_decode($job['payload'], true);
            $this->runImportJob($job['id'], $job['batch_id'], $payload);
            return true;
        } catch (\Exception $e) {
            $this->db->update('job_queue', [
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$job['id']]);
            $this->db->update('import_batches', ['status' => 'failed'], 'id = ?', [$job['batch_id']]);
            return false;
        }
    }

    /**
     * Run import job in chunks
     */
    private function runImportJob(int $jobId, int $batchId, array $payload): void {
        $filePath = $payload['file_path'];
        $ext = $payload['ext'];
        $mapping = $payload['mapping'];
        $defaultSource = $payload['default_source'];
        $defaultStatus = $payload['default_status'];
        $headers = $payload['headers'];

        // Read all rows
        $importer = new ImportController();
        $rows = $importer->readAllRowsPublic($filePath, $ext);
        $totalRows = count($rows);

        // Update totals
        $this->db->update('job_queue', ['total_items' => $totalRows], 'id = ?', [$jobId]);
        $this->db->update('import_batches', ['total_rows' => $totalRows], 'id = ?', [$batchId]);

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // Process in chunks
        $chunks = array_chunk($rows, $this->chunkSize);
        $processedSoFar = 0;

        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                try {
                    $leadData = $importer->mapRowToLeadPublic($row, $headers, $mapping, $defaultSource, $defaultStatus);
                    if (empty($leadData['customer_name'])) { $skipped++; $processedSoFar++; continue; }

                    $leadData['import_batch_id'] = $batchId;
                    $leadData['lead_score'] = LeadScorer::calculate($leadData);
                    $leadData['lead_grade'] = LeadScorer::getLabel($leadData['lead_score']);

                    $this->db->insert('leads', $leadData);
                    $imported++;
                } catch (\Exception $e) {
                    $errors++;
                }
                $processedSoFar++;
            }

            // Update progress after each chunk
            $progress = $totalRows > 0 ? round(($processedSoFar / $totalRows) * 100) : 100;
            $this->db->update('job_queue', [
                'processed_items' => $processedSoFar,
                'progress' => $progress,
            ], 'id = ?', [$jobId]);

            // Update batch with running totals
            $this->db->update('import_batches', [
                'imported_rows' => $imported,
                'skipped_rows' => $skipped,
                'error_rows' => $errors,
            ], 'id = ?', [$batchId]);
        }

        // Mark complete
        $this->db->update('job_queue', [
            'status' => 'completed',
            'progress' => 100,
            'processed_items' => $processedSoFar,
            'completed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$jobId]);

        $this->db->update('import_batches', [
            'total_rows' => $totalRows,
            'imported_rows' => $imported,
            'skipped_rows' => $skipped,
            'error_rows' => $errors,
            'status' => 'completed',
        ], 'id = ?', [$batchId]);
    }

    /**
     * Get job status (for API polling)
     */
    public static function getJobStatus(int $jobId): ?array {
        $db = new Database();
        $job = $db->fetch("SELECT j.*, b.imported_rows, b.skipped_rows, b.error_rows, b.total_rows, b.status as batch_status 
                           FROM job_queue j 
                           LEFT JOIN import_batches b ON j.batch_id = b.id 
                           WHERE j.id = ?", [$jobId]);
        return $job;
    }
}
