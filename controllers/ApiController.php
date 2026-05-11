<?php
/**
 * API Controller - JSON endpoints for AJAX operations
 */
class ApiController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        // Check auth for API
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Unauthorized']); return;
        }

        switch ($action) {
            case 'quick_status': $this->quickStatus(); break;
            case 'quick_assign': $this->quickAssign(); break;
            case 'search_leads': $this->searchLeads(); break;
            case 'dashboard_stats': $this->dashboardStats(); break;
            case 'export_csv': $this->exportCsv(); break;
            default: echo json_encode(['error' => 'Unknown action']); break;
        }
    }

    private function quickStatus(): void {
        $id = intval($_POST['lead_id'] ?? 0);
        $status = Security::sanitize($_POST['status'] ?? '');
        $validStatuses = array_keys(LEAD_STATUSES);
        if (!$id || !in_array($status, $validStatuses)) {
            echo json_encode(['error' => 'Invalid data']); return;
        }

        $old = $this->db->fetch("SELECT status FROM leads WHERE id = ?", [$id]);
        $this->db->update('leads', ['status' => $status], 'id = ?', [$id]);
        $this->db->insert('activity_log', [
            'lead_id' => $id, 'user_id' => Security::userId(),
            'action' => 'Status Changed', 'old_value' => $old['status'], 'new_value' => $status,
        ]);
        echo json_encode(['success' => true, 'status' => $status]);
    }

    private function quickAssign(): void {
        $id = intval($_POST['lead_id'] ?? 0);
        $agentId = intval($_POST['agent_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Invalid data']); return; }

        $this->db->update('leads', ['assigned_to' => $agentId ?: null], 'id = ?', [$id]);
        $agent = $agentId ? $this->db->fetch("SELECT name FROM users WHERE id = ?", [$agentId]) : null;
        $this->db->insert('activity_log', [
            'lead_id' => $id, 'user_id' => Security::userId(),
            'action' => 'Reassigned', 'new_value' => $agent['name'] ?? 'Unassigned',
        ]);
        echo json_encode(['success' => true, 'agent_name' => $agent['name'] ?? 'Unassigned']);
    }

    private function searchLeads(): void {
        $q = '%' . Security::sanitize($_GET['q'] ?? '') . '%';
        $results = $this->db->fetchAll(
            "SELECT id, customer_name, phone_number, status, lead_grade FROM leads 
             WHERE customer_name LIKE ? OR phone_number LIKE ? LIMIT 10", [$q, $q]
        );
        echo json_encode($results);
    }

    private function dashboardStats(): void {
        $total = $this->db->count('leads');
        $new = $this->db->count('leads', "status = 'New'");
        $pipeline = $this->db->fetch("SELECT SUM(loan_amount) as v FROM leads WHERE status NOT IN ('Rejected','Disbursed')")['v'] ?? 0;
        echo json_encode(compact('total', 'new', 'pipeline'));
    }

    private function exportCsv(): void {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID','Name','Phone','Email','City','Loan Type','Loan Amount','Status','Score','Grade','Source','Created']);

        $leads = $this->db->fetchAll("SELECT * FROM leads ORDER BY created_at DESC");
        foreach ($leads as $l) {
            fputcsv($output, [
                $l['id'], $l['customer_name'], $l['phone_number'], $l['email_address'],
                $l['city'], $l['loan_type'], $l['loan_amount'], $l['status'],
                $l['lead_score'], $l['lead_grade'], $l['lead_source'], $l['created_at'],
            ]);
        }
        fclose($output);
        exit;
    }
}
