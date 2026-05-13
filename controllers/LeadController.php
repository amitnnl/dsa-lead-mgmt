<?php
/**
 * Lead Controller - CRUD operations for leads
 */
class LeadController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'create': $this->create(); break;
            case 'store': $this->store(); break;
            case 'edit': $this->edit(); break;
            case 'update': $this->update(); break;
            case 'delete': $this->delete(); break;
            case 'view': $this->view(); break;
            default: $this->index(); break;
        }
    }

    private function index(): void {
        $data = ['page' => 'leads'];
        $status = $_GET['status'] ?? '';
        $grade = $_GET['grade'] ?? '';
        $search = $_GET['search'] ?? '';
        $loanType = $_GET['loan_type'] ?? '';
        $where = '1=1';
        $params = [];

        if ($status) { $where .= ' AND l.status = ?'; $params[] = $status; }
        if ($grade) { $where .= ' AND l.lead_grade = ?'; $params[] = $grade; }
        if ($loanType) { $where .= ' AND l.loan_type = ?'; $params[] = $loanType; }
        
        // Role-based filtering
        if (!Security::isAdmin()) {
            if ($_SESSION['user_role'] === 'manager') {
                // Manager sees their own + sub-agents' leads
                $subAgentIds = array_column($this->db->fetchAll("SELECT id FROM users WHERE parent_id = ?", [Security::userId()]), 'id');
                $allowedIds = array_merge([Security::userId()], $subAgentIds);
                $placeholders = implode(',', array_fill(0, count($allowedIds), '?'));
                $where .= " AND l.assigned_to IN ($placeholders)";
                $params = array_merge($params, $allowedIds);
            } else {
                // Agent sees only their own
                $where .= ' AND l.assigned_to = ?';
                $params[] = Security::userId();
            }
        }

        if ($search) {
            $where .= ' AND (l.customer_name LIKE ? OR l.phone_number LIKE ? OR l.email_address LIKE ? OR l.city LIKE ?)';
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        $page = max(1, intval($_GET['p'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $total = $this->db->fetch("SELECT COUNT(*) as cnt FROM leads l WHERE {$where}", $params)['cnt'];
        $totalPages = ceil($total / $perPage);

        $data['leads'] = $this->db->fetchAll(
            "SELECT l.*, u.name as agent_name FROM leads l LEFT JOIN users u ON l.assigned_to = u.id WHERE {$where} ORDER BY l.created_at DESC LIMIT {$perPage} OFFSET {$offset}", $params
        );
        $data['total'] = $total;
        $data['totalPages'] = $totalPages;
        $data['currentPage'] = $page;
        $data['filters'] = compact('status', 'grade', 'search', 'loanType');
        $data['agents'] = $this->db->fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
        require __DIR__ . '/../views/layout.php';
    }

    private function create(): void {
        $data = ['page' => 'lead_form', 'lead' => null, 'mode' => 'create'];
        $data['agents'] = $this->db->fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
        require __DIR__ . '/../views/layout.php';
    }

    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=leads'); return;
        }
        $leadData = $this->extractLeadData($_POST);
        $leadData['lead_score'] = LeadScorer::calculate($leadData);
        $leadData['lead_grade'] = LeadScorer::getLabel($leadData['lead_score']);
        $leadId = $this->db->insert('leads', $leadData);
        $this->logActivity($leadId, 'Lead Created', null, $leadData['status']);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lead created successfully!'];
        header("Location: index.php?page=leads&action=view&id={$leadId}");
    }

    private function edit(): void {
        $id = intval($_GET['id'] ?? 0);
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);
        if (!$lead) { $_SESSION['flash'] = ['type'=>'error','message'=>'Lead not found.']; header('Location: index.php?page=leads'); return; }
        $data = ['page' => 'lead_form', 'lead' => $lead, 'mode' => 'edit'];
        $data['agents'] = $this->db->fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
        require __DIR__ . '/../views/layout.php';
    }

    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=leads'); return;
        }
        $id = intval($_POST['id'] ?? 0);
        $oldLead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);
        if (!$oldLead) { $_SESSION['flash'] = ['type'=>'error','message'=>'Lead not found.']; header('Location: index.php?page=leads'); return; }
        $leadData = $this->extractLeadData($_POST);
        $leadData['lead_score'] = LeadScorer::calculate($leadData);
        $leadData['lead_grade'] = LeadScorer::getLabel($leadData['lead_score']);
        $this->db->update('leads', $leadData, 'id = ?', [$id]);
        if ($oldLead['status'] !== $leadData['status']) {
            $this->logActivity($id, 'Status Changed', $oldLead['status'], $leadData['status']);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lead updated successfully!'];
        header("Location: index.php?page=leads&action=view&id={$id}");
    }

    private function delete(): void {
        if (!Security::isAdmin()) { $_SESSION['flash'] = ['type'=>'error','message'=>'Unauthorized.']; header('Location: index.php?page=leads'); return; }
        $id = intval($_GET['id'] ?? 0);
        $this->db->delete('leads', 'id = ?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lead deleted.'];
        header('Location: index.php?page=leads');
    }

    private function view(): void {
        $id = intval($_GET['id'] ?? 0);
        $lead = $this->db->fetch("SELECT l.*, u.name as agent_name FROM leads l LEFT JOIN users u ON l.assigned_to = u.id WHERE l.id = ?", [$id]);
        if (!$lead) { $_SESSION['flash'] = ['type'=>'error','message'=>'Lead not found.']; header('Location: index.php?page=leads'); return; }
        $data = ['page' => 'lead_view', 'lead' => $lead];
        $data['activities'] = $this->db->fetchAll("SELECT a.*, u.name as user_name FROM activity_log a LEFT JOIN users u ON a.user_id = u.id WHERE a.lead_id = ? ORDER BY a.created_at DESC", [$id]);
        $data['payouts'] = $this->db->fetchAll("SELECT * FROM client_payouts WHERE lead_id = ? OR (phone_number = ? AND phone_number IS NOT NULL AND phone_number != '') ORDER BY payout_date DESC", [$id, $lead['phone_number']]);
        $data['documents'] = $this->db->fetchAll("SELECT d.*, u.name as uploader_name FROM lead_documents d LEFT JOIN users u ON d.uploaded_by = u.id WHERE d.lead_id = ? ORDER BY d.created_at DESC", [$id]);
        $data['agents'] = $this->db->fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        // Anti-Theft: Log every lead view
        $this->db->insert('activity_log', [
            'lead_id' => $id,
            'user_id' => Security::userId(),
            'action' => 'Lead Viewed',
            'notes' => 'Viewed full lead profile'
        ]);

        // Vehicle Finance: Bank Rates, Insurance, RC Transfer
        $vehicleTypes = ['Used Car Loan','Used Bike Loan','Used Commercial Vehicle Loan','New Car Loan','New Bike Loan'];
        if (in_array($lead['loan_type'] ?? '', $vehicleTypes)) {
            try {
                $data['bank_rates'] = $this->db->fetchAll(
                    "SELECT * FROM bank_rates WHERE loan_type = ? AND is_active = 1 ORDER BY interest_rate ASC",
                    [$lead['loan_type']]
                );
            } catch (Exception $e) { $data['bank_rates'] = []; }

            try {
                $data['insurance'] = $this->db->fetchAll(
                    "SELECT * FROM insurance_policies WHERE lead_id = ? ORDER BY created_at DESC", [$id]
                );
            } catch (Exception $e) { $data['insurance'] = []; }

            try {
                $data['rc_transfer'] = $this->db->fetch(
                    "SELECT * FROM rc_transfers WHERE lead_id = ? ORDER BY id DESC LIMIT 1", [$id]
                );
            } catch (Exception $e) { $data['rc_transfer'] = null; }

            try {
                $data['inspection'] = $this->db->fetch(
                    "SELECT * FROM vehicle_inspections WHERE lead_id = ? ORDER BY id DESC LIMIT 1", [$id]
                );
            } catch (Exception $e) { $data['inspection'] = null; }
        }

        require __DIR__ . '/../views/layout.php';
    }

    private function extractLeadData(array $post): array {
        return [
            'customer_name'   => Security::sanitize($post['customer_name'] ?? ''),
            'phone_number'    => Security::sanitizePhone($post['phone_number'] ?? ''),
            'alt_phone'       => Security::sanitizePhone($post['alt_phone'] ?? ''),
            'email_address'   => filter_var($post['email_address'] ?? '', FILTER_SANITIZE_EMAIL),
            'dob'             => !empty($post['dob']) ? $post['dob'] : null,
            'gender'          => in_array($post['gender'] ?? '', ['Male','Female','Other']) ? $post['gender'] : null,
            'address'         => Security::sanitize($post['address'] ?? ''),
            'city'            => Security::sanitize($post['city'] ?? ''),
            'state'           => Security::sanitize($post['state'] ?? ''),
            'pincode'         => Security::sanitize($post['pincode'] ?? ''),
            'loan_type'       => Security::sanitize($post['loan_type'] ?? ''),
            'loan_amount'     => floatval($post['loan_amount'] ?? 0),
            'monthly_income'  => floatval($post['monthly_income'] ?? 0),
            'employer'        => Security::sanitize($post['employer'] ?? ''),
            'employment_type' => in_array($post['employment_type'] ?? '', ['Salaried','Self-Employed','Business','Retired','Other']) ? $post['employment_type'] : null,
            'credit_score'    => !empty($post['credit_score']) ? intval($post['credit_score']) : null,
            'bank_name'       => Security::sanitize($post['bank_name'] ?? ''),
            'lead_source'     => Security::sanitize($post['lead_source'] ?? 'Other'),
            'status'          => Security::sanitize($post['status'] ?? 'New'),
            'assigned_to'     => !empty($post['assigned_to']) ? intval($post['assigned_to']) : null,
            'remarks'         => Security::sanitize($post['remarks'] ?? ''),
            'follow_up_date'  => !empty($post['follow_up_date']) ? $post['follow_up_date'] : null,
            // Vehicle fields
            'vehicle_make'         => Security::sanitize($post['vehicle_make'] ?? ''),
            'vehicle_model'        => Security::sanitize($post['vehicle_model'] ?? ''),
            'vehicle_year'         => !empty($post['vehicle_year']) ? intval($post['vehicle_year']) : null,
            'vehicle_reg_no'       => strtoupper(Security::sanitize($post['vehicle_reg_no'] ?? '')),
            'vehicle_km'           => !empty($post['vehicle_km']) ? intval($post['vehicle_km']) : null,
            'vehicle_fuel'         => Security::sanitize($post['vehicle_fuel'] ?? ''),
            'vehicle_owner'        => !empty($post['vehicle_owner']) ? intval($post['vehicle_owner']) : null,
            'vehicle_price'        => floatval($post['vehicle_price'] ?? 0),
            'vehicle_hypothecated' => in_array($post['vehicle_hypothecated'] ?? '', ['Yes','No']) ? $post['vehicle_hypothecated'] : 'No',
        ];
    }

    private function logActivity(int $leadId, string $action, ?string $oldVal, ?string $newVal): void {
        $this->db->insert('activity_log', [
            'lead_id' => $leadId, 'user_id' => Security::userId(),
            'action' => $action, 'old_value' => $oldVal, 'new_value' => $newVal,
        ]);
    }
}
