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

        // Total leads
        $data['total_leads'] = $this->db->count('leads');

        // Status breakdown
        $data['status_counts'] = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM leads GROUP BY status ORDER BY FIELD(status, 'New','Contacted','Documentation','Submitted','Approved','Disbursed','Rejected')"
        );

        // Lead grade breakdown
        $data['grade_counts'] = $this->db->fetchAll(
            "SELECT lead_grade, COUNT(*) as count FROM leads GROUP BY lead_grade"
        );

        // Total pipeline value
        $pipeline = $this->db->fetch(
            "SELECT SUM(loan_amount) as total FROM leads WHERE status NOT IN ('Rejected','Disbursed')"
        );
        $data['pipeline_value'] = $pipeline['total'] ?? 0;

        // Disbursed value
        $disbursed = $this->db->fetch("SELECT SUM(loan_amount) as total FROM leads WHERE status = 'Disbursed'");
        $data['disbursed_value'] = $disbursed['total'] ?? 0;

        // Conversion rate
        $data['conversion_rate'] = $data['total_leads'] > 0
            ? round(($this->db->count('leads', "status = 'Disbursed'") / $data['total_leads']) * 100, 1)
            : 0;

        // Today's leads
        $data['today_leads'] = $this->db->count('leads', "DATE(created_at) = CURDATE()");

        // This month leads
        $data['month_leads'] = $this->db->count('leads', "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");

        // Follow-ups due today
        $data['followups_today'] = $this->db->count('leads', "follow_up_date = CURDATE() AND status NOT IN ('Disbursed','Rejected')");

        // Recent leads
        $data['recent_leads'] = $this->db->fetchAll(
            "SELECT l.*, u.name as agent_name FROM leads l LEFT JOIN users u ON l.assigned_to = u.id ORDER BY l.created_at DESC LIMIT 8"
        );

        // Monthly trend (last 6 months)
        $data['monthly_trend'] = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(loan_amount) as value 
             FROM leads 
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
             GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
             ORDER BY month"
        );

        // Top cities
        $data['top_cities'] = $this->db->fetchAll(
            "SELECT city, COUNT(*) as count FROM leads WHERE city IS NOT NULL AND city != '' GROUP BY city ORDER BY count DESC LIMIT 5"
        );

        // Loan type distribution
        $data['loan_types'] = $this->db->fetchAll(
            "SELECT loan_type, COUNT(*) as count, SUM(loan_amount) as value FROM leads WHERE loan_type IS NOT NULL GROUP BY loan_type ORDER BY count DESC"
        );

        // Agent performance
        $data['agent_performance'] = $this->db->fetchAll(
            "SELECT u.name, COUNT(l.id) as total_leads, 
                    SUM(CASE WHEN l.status = 'Disbursed' THEN 1 ELSE 0 END) as converted,
                    SUM(CASE WHEN l.status = 'Disbursed' THEN l.loan_amount ELSE 0 END) as revenue
             FROM users u 
             LEFT JOIN leads l ON l.assigned_to = u.id 
             WHERE u.role IN ('agent','manager')
             GROUP BY u.id, u.name
             ORDER BY converted DESC"
        );

        // Recent activities
        $data['recent_activities'] = $this->db->fetchAll(
            "SELECT a.*, l.customer_name, u.name as user_name 
             FROM activity_log a 
             LEFT JOIN leads l ON a.lead_id = l.id 
             LEFT JOIN users u ON a.user_id = u.id 
             ORDER BY a.created_at DESC LIMIT 10"
        );

        $data['page'] = 'dashboard';
        return $data;
    }
}
