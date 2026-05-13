<?php
/**
 * Document Controller - Handles file uploads and management for leads
 */
class DocumentController {
    private Database $db;
    private string $uploadDir;

    public function __construct() {
        $this->db = new Database();
        $this->uploadDir = __DIR__ . '/../storage/documents/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'upload': $this->upload(); break;
            case 'delete': $this->delete(); break;
            case 'download': $this->download(); break;
            case 'verify': $this->verify(); break;
            default: header('Location: index.php?page=leads'); break;
        }
    }

    private function upload(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=leads'); return;
        }

        $leadId = intval($_POST['lead_id'] ?? 0);
        $docType = Security::sanitize($_POST['document_type'] ?? 'Other');
        $file = $_FILES['document_file'] ?? null;

        if (!$leadId || !$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid file upload.'];
            header("Location: index.php?page=leads&action=view&id={$leadId}"); return;
        }

        // Security check
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid file type. Allowed: PDF, JPG, PNG, DOCX'];
            header("Location: index.php?page=leads&action=view&id={$leadId}"); return;
        }

        // Save file
        $newName = $leadId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $newName)) {
            $this->db->insert('lead_documents', [
                'lead_id' => $leadId,
                'document_type' => $docType,
                'file_name' => $file['name'],
                'file_path' => $newName,
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'uploaded_by' => Security::userId()
            ]);

            $this->db->insert('activity_log', [
                'lead_id' => $leadId, 'user_id' => Security::userId(),
                'action' => 'Document Uploaded', 'new_value' => $docType
            ]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Document uploaded successfully!'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to save file. Check directory permissions.'];
        }

        header("Location: index.php?page=leads&action=view&id={$leadId}");
    }

    private function download(): void {
        $id = intval($_GET['id'] ?? 0);
        $doc = $this->db->fetch("SELECT * FROM lead_documents WHERE id = ?", [$id]);
        
        if (!$doc || !file_exists($this->uploadDir . $doc['file_path'])) {
            die("File not found.");
        }

        header('Content-Type: ' . ($doc['file_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $doc['file_name'] . '"');
        readfile($this->uploadDir . $doc['file_path']);
        exit;
    }

    private function delete(): void {
        $id = intval($_GET['id'] ?? 0);
        $doc = $this->db->fetch("SELECT * FROM lead_documents WHERE id = ?", [$id]);
        
        if ($doc) {
            @unlink($this->uploadDir . $doc['file_path']);
            $this->db->delete('lead_documents', 'id = ?', [$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Document deleted.'];
            header("Location: index.php?page=leads&action=view&id={$doc['lead_id']}");
        } else {
            header('Location: index.php?page=leads');
        }
    }

    private function verify(): void {
        if (!Security::isAdmin() && $_SESSION['user_role'] !== 'manager') return;
        $id = intval($_GET['id'] ?? 0);
        $status = $_GET['status'] === 'Verified' ? 'Verified' : 'Rejected';
        
        $doc = $this->db->fetch("SELECT lead_id FROM lead_documents WHERE id = ?", [$id]);
        if ($doc) {
            $this->db->update('lead_documents', ['status' => $status], 'id = ?', [$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Document marked as {$status}."];
            header("Location: index.php?page=leads&action=view&id={$doc['lead_id']}");
        }
    }
}
