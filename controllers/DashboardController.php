<?php
/**
 * Dashboard Controller
 */
class DashboardController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        $data = $this->getDashboardData();
        require __DIR__ . '/../views/layout.php';
    }

    private function getDashboardData(): array {
        $data = [];
        $userId = Security::userId();
        $role = $_SESSION['user_role'] ?? 'agent';
        $isAdmin = Security::isAdmin();

        // 1. Role-based filtering logic
        $where = '1=1';
        $params = [];
        if (!$isAdmin) {
            if ($role === 'manager') {
                $subAgentIds = array_column($this->db->fetchAll("SELECT id FROM users WHERE parent_id = ?", [$userId]), 'id');
                $allowedIds = array_merge([$userId], $subAgentIds);
                $placeholders = implode(',', array_fill(0, count($allowedIds), '?'));
                $where .= " AND assigned_to IN ($placeholders)";
                $params = array_merge($params, $allowedIds);
            } else {
                $where .= ' AND assigned_to = ?';
                $params[] = $userId;
            }
        }

        // 2. Core Stats
        $data['total_leads'] = $this->db->count('leads', $where, $params);
        $data['today_leads'] = $this->db->count('leads', "$where AND DATE(created_at) = CURDATE()", $params);
        $data['month_leads'] = $this->db->count('leads', "$where AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())", $params);

        // 3. Financial Metrics
        $pipeline = $this->db->fetch("SELECT SUM(loan_amount) as total FROM leads WHERE $where AND status NOT IN ('Rejected','Disbursed')", $params);
        $data['pipeline_value'] = $pipeline['total'] ?? 0;

        $disbursed = $this->db->fetch("SELECT SUM(loan_amount) as total FROM leads WHERE $where AND status = 'Disbursed'", $params);
        $data['disbursed_value'] = $disbursed['total'] ?? 0;

        // Commission Pipeline Calculation (Assuming average 1.5% commission for estimates)
        $avgRate = 0.015;
        $data['estimated_commissions'] = $data['pipeline_value'] * $avgRate;
        $data['earned_commissions'] = $data['disbursed_value'] * $avgRate;

        // Target progress (Hardcoded target for now)
        $data['monthly_target'] = 10000000; // 1 Crore target
        $data['target_progress'] = min(100, round(($data['disbursed_value'] / $data['monthly_target']) * 100));

        // Status counts
        $data['status_counts'] = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM leads WHERE $where GROUP BY status ORDER BY FIELD(status, 'New','Contacted','Documentation','Submitted','Approved','Disbursed','Rejected')",
            $params
        );

        // Recent leads
        $data['recent_leads'] = $this->db->fetchAll(
            "SELECT l.*, u.name as agent_name FROM leads l LEFT JOIN users u ON l.assigned_to = u.id WHERE $where ORDER BY l.created_at DESC LIMIT 8",
            $params
        );

        // Agent performance (Only for Managers/Admins)
        if ($isAdmin || $role === 'manager') {
            $perfWhere = $isAdmin ? "1=1" : "parent_id = $userId OR id = $userId";
            $data['agent_performance'] = $this->db->fetchAll(
                "SELECT u.name, COUNT(l.id) as total_leads, 
                        SUM(CASE WHEN l.status = 'Disbursed' THEN 1 ELSE 0 END) as converted,
                        SUM(CASE WHEN l.status = 'Disbursed' THEN l.loan_amount ELSE 0 END) as revenue
                 FROM users u 
                 LEFT JOIN leads l ON l.assigned_to = u.id 
                 WHERE $perfWhere AND u.role IN ('agent','manager')
                 GROUP BY u.id, u.name
                 ORDER BY revenue DESC LIMIT 5"
            );
        }

        // Recent activities (Lead-specific activities based on access)
        $actJoin = "LEFT JOIN leads l ON a.lead_id = l.id";
        $actWhere = "1=1";
        if (!$isAdmin) {
            if ($role === 'manager') {
                $subAgentIds = array_column($this->db->fetchAll("SELECT id FROM users WHERE parent_id = ?", [$userId]), 'id');
                $allowedIds = array_merge([$userId], $subAgentIds);
                $placeholders = implode(',', array_fill(0, count($allowedIds), '?'));
                $actWhere = "l.assigned_to IN ($placeholders)";
                // params for this would need a separate array
            } else {
                $actWhere = "l.assigned_to = $userId";
            }
        }
        $data['recent_activities'] = $this->db->fetchAll(
            "SELECT a.*, l.customer_name, u.name as user_name 
             FROM activity_log a 
             $actJoin
             LEFT JOIN users u ON a.user_id = u.id 
             WHERE $actWhere
             ORDER BY a.created_at DESC LIMIT 10"
        );

        $data['page'] = 'dashboard';
        return $data;
    }
}
