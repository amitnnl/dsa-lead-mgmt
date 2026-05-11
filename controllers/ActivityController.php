<?php
/**
 * Activity Controller - View activity logs
 */
class ActivityController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'add': $this->add(); break;
            default: $this->index(); break;
        }
    }

    private function index(): void {
        $data = ['page' => 'activity'];
        $page = max(1, intval($_GET['p'] ?? 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $data['total'] = $this->db->count('activity_log');
        $data['totalPages'] = ceil($data['total'] / $perPage);
        $data['currentPage'] = $page;

        $data['activities'] = $this->db->fetchAll(
            "SELECT a.*, l.customer_name, l.phone_number, u.name as user_name 
             FROM activity_log a 
             LEFT JOIN leads l ON a.lead_id = l.id 
             LEFT JOIN users u ON a.user_id = u.id 
             ORDER BY a.created_at DESC 
             LIMIT {$perPage} OFFSET {$offset}"
        );
        require __DIR__ . '/../views/layout.php';
    }

    private function add(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=leads'); return;
        }
        $leadId = intval($_POST['lead_id'] ?? 0);
        $action = Security::sanitize($_POST['action_type'] ?? '');
        $notes = Security::sanitize($_POST['notes'] ?? '');

        if ($leadId && $action) {
            $this->db->insert('activity_log', [
                'lead_id' => $leadId,
                'user_id' => Security::userId(),
                'action'  => $action,
                'notes'   => $notes,
            ]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Activity logged.'];
        }
        header("Location: index.php?page=leads&action=view&id={$leadId}");
    }
}
