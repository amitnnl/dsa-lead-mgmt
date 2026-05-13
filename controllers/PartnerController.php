<?php
/**
 * Partner Controller - Handles the Connector/Partner Portal experience
 */
class PartnerController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        // Ensure user is a partner
        if ($_SESSION['user_role'] !== 'partner') {
            header('Location: index.php?page=dashboard'); exit;
        }
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'submit_lead': $this->submitLead(); break;
            case 'payouts': $this->payouts(); break;
            default: $this->dashboard(); break;
        }
    }

    private function dashboard(): void {
        $userId = Security::userId();
        $data = ['page' => 'partner_dashboard'];
        
        // Stats for partner
        $data['total_submitted'] = $this->db->count('leads', "assigned_to = ?", [$userId]);
        $data['disbursed_count'] = $this->db->count('leads', "assigned_to = ? AND status = 'Disbursed'", [$userId]);
        
        $payout = $this->db->fetch(
            "SELECT SUM(payout_amount) as total FROM client_payouts WHERE lead_id IN (SELECT id FROM leads WHERE assigned_to = ?)",
            [$userId]
        );
        $data['total_payouts'] = $payout['total'] ?? 0;

        $data['recent_leads'] = $this->db->fetchAll(
            "SELECT * FROM leads WHERE assigned_to = ? ORDER BY created_at DESC LIMIT 5",
            [$userId]
        );

        require __DIR__ . '/../views/layout.php';
    }

    private function submitLead(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::validateCsrf()) {
            $leadData = [
                'customer_name' => Security::sanitize($_POST['customer_name'] ?? ''),
                'phone_number' => Security::sanitizePhone($_POST['phone_number'] ?? ''),
                'loan_type' => Security::sanitize($_POST['loan_type'] ?? ''),
                'loan_amount' => floatval($_POST['loan_amount'] ?? 0),
                'assigned_to' => Security::userId(),
                'status' => 'New',
                'lead_source' => 'Partner Portal'
            ];
            
            $id = $this->db->insert('leads', $leadData);
            $this->db->insert('activity_log', [
                'lead_id' => $id, 'user_id' => Security::userId(),
                'action' => 'Submitted by Partner'
            ]);
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lead submitted successfully! Our team will review it soon.'];
            header('Location: index.php?page=partner'); return;
        }
        
        $data = ['page' => 'partner_submit'];
        require __DIR__ . '/../views/layout.php';
    }

    private function payouts(): void {
        $userId = Security::userId();
        $data = ['page' => 'partner_payouts'];
        $data['payouts'] = $this->db->fetchAll(
            "SELECT p.*, l.customer_name 
             FROM client_payouts p 
             JOIN leads l ON p.lead_id = l.id 
             WHERE l.assigned_to = ? 
             ORDER BY p.payout_date DESC",
            [$userId]
        );
        require __DIR__ . '/../views/layout.php';
    }
}
