<?php
/**
 * API Controller - JSON endpoints for AJAX operations + RESTful API v1
 * 
 * Internal endpoints (session auth):
 *   - quick_status, quick_assign, search_leads, dashboard_stats, export_csv, job_status
 * 
 * External REST API v1 (API key auth):
 *   - v1_leads    GET/POST   — List/Create leads
 *   - v1_lead     GET/PUT/DELETE — Get/Update/Delete single lead
 *   - v1_enrich   POST       — Bulk enrich leads with external data
 *   - v1_stats    GET        — Dashboard statistics
 */
class ApiController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        // V1 API routes use API key auth
        if (str_starts_with($action, 'v1_')) {
            $this->handleV1($action);
            return;
        }

        // Internal routes use session auth
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']); return;
        }

        switch ($action) {
            case 'quick_status': $this->quickStatus(); break;
            case 'quick_assign': $this->quickAssign(); break;
            case 'search_leads': $this->searchLeads(); break;
            case 'dashboard_stats': $this->dashboardStats(); break;
            case 'export_csv': $this->exportCsv(); break;
            case 'job_status': $this->jobStatus(); break;
            default: echo json_encode(['error' => 'Unknown action']); break;
        }
    }

    // ==========================================
    //  INTERNAL ENDPOINTS (Session Auth)
    // ==========================================

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

    private function jobStatus(): void {
        $jobId = intval($_GET['id'] ?? 0);
        if (!$jobId) { echo json_encode(['error' => 'No job ID']); return; }
        $status = JobProcessor::getJobStatus($jobId);
        echo json_encode($status ?: ['error' => 'Job not found']);
    }

    // ==========================================
    //  REST API V1 (API Key Auth)
    // ==========================================

    private function handleV1(string $action): void {
        // Authenticate via API key
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
        if (empty($apiKey)) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing API key. Include X-API-Key header.']);
            return;
        }

        $keyRecord = $this->db->fetch(
            "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1", [$apiKey]
        );
        if (!$keyRecord) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or revoked API key.']);
            return;
        }

        // Update usage stats
        $this->db->update('api_keys', [
            'last_used_at' => date('Y-m-d H:i:s'),
            'request_count' => $keyRecord['request_count'] + 1,
        ], 'id = ?', [$keyRecord['id']]);

        $perms = json_decode($keyRecord['permissions'], true) ?? [];
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($action) {
            case 'v1_leads':
                if ($method === 'GET') {
                    $this->requirePerm($perms, 'read');
                    $this->v1ListLeads();
                } elseif ($method === 'POST') {
                    $this->requirePerm($perms, 'write');
                    $this->v1CreateLead();
                } else {
                    http_response_code(405);
                    echo json_encode(['error' => 'Method not allowed']);
                }
                break;
            case 'v1_lead':
                $id = intval($_GET['id'] ?? 0);
                if ($method === 'GET') {
                    $this->requirePerm($perms, 'read');
                    $this->v1GetLead($id);
                } elseif ($method === 'PUT' || $method === 'PATCH') {
                    $this->requirePerm($perms, 'write');
                    $this->v1UpdateLead($id);
                } elseif ($method === 'DELETE') {
                    $this->requirePerm($perms, 'delete');
                    $this->v1DeleteLead($id);
                } else {
                    http_response_code(405);
                    echo json_encode(['error' => 'Method not allowed']);
                }
                break;
            case 'v1_enrich':
                $this->requirePerm($perms, 'enrich');
                $this->v1EnrichLeads();
                break;
            case 'v1_stats':
                $this->requirePerm($perms, 'read');
                $this->v1Stats();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Unknown API endpoint']);
                break;
        }
    }

    private function requirePerm(array $perms, string $perm): void {
        if (!in_array($perm, $perms)) {
            http_response_code(403);
            echo json_encode(['error' => "API key lacks '{$perm}' permission."]);
            exit;
        }
    }

    /**
     * GET /api/v1_leads — List leads with pagination and filters
     */
    private function v1ListLeads(): void {
        $page = max(1, intval($_GET['p'] ?? 1));
        $perPage = min(100, max(1, intval($_GET['per_page'] ?? 50)));
        $offset = ($page - 1) * $perPage;
        $where = '1=1';
        $params = [];

        // Filters
        if (!empty($_GET['status'])) { $where .= ' AND status = ?'; $params[] = $_GET['status']; }
        if (!empty($_GET['grade'])) { $where .= ' AND lead_grade = ?'; $params[] = $_GET['grade']; }
        if (!empty($_GET['city'])) { $where .= ' AND city = ?'; $params[] = $_GET['city']; }
        if (!empty($_GET['loan_type'])) { $where .= ' AND loan_type = ?'; $params[] = $_GET['loan_type']; }
        if (!empty($_GET['source'])) { $where .= ' AND lead_source = ?'; $params[] = $_GET['source']; }
        if (!empty($_GET['search'])) {
            $s = '%' . $_GET['search'] . '%';
            $where .= ' AND (customer_name LIKE ? OR phone_number LIKE ? OR email_address LIKE ?)';
            $params = array_merge($params, [$s, $s, $s]);
        }
        if (!empty($_GET['since'])) { $where .= ' AND created_at >= ?'; $params[] = $_GET['since']; }

        $total = $this->db->fetch("SELECT COUNT(*) as cnt FROM leads WHERE {$where}", $params)['cnt'];
        $leads = $this->db->fetchAll(
            "SELECT * FROM leads WHERE {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}", $params
        );

        echo json_encode([
            'success' => true,
            'data' => $leads,
            'pagination' => [
                'total' => (int) $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * POST /api/v1_leads — Create a new lead
     */
    private function v1CreateLead(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        if (empty($input['customer_name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'customer_name is required']); return;
        }

        $leadData = $this->sanitizeLeadInput($input);
        $leadData['lead_score'] = LeadScorer::calculate($leadData);
        $leadData['lead_grade'] = LeadScorer::getLabel($leadData['lead_score']);

        $id = $this->db->insert('leads', $leadData);
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);

        http_response_code(201);
        echo json_encode(['success' => true, 'data' => $lead]);
    }

    /**
     * GET /api/v1_lead?id=123 — Get a single lead
     */
    private function v1GetLead(int $id): void {
        $lead = $this->db->fetch(
            "SELECT l.*, u.name as agent_name FROM leads l LEFT JOIN users u ON l.assigned_to = u.id WHERE l.id = ?", [$id]
        );
        if (!$lead) {
            http_response_code(404);
            echo json_encode(['error' => 'Lead not found']); return;
        }
        $activities = $this->db->fetchAll(
            "SELECT a.*, u.name as user_name FROM activity_log a LEFT JOIN users u ON a.user_id = u.id WHERE a.lead_id = ? ORDER BY a.created_at DESC LIMIT 20", [$id]
        );
        $lead['activities'] = $activities;
        echo json_encode(['success' => true, 'data' => $lead]);
    }

    /**
     * PUT /api/v1_lead?id=123 — Update a lead
     */
    private function v1UpdateLead(int $id): void {
        $existing = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Lead not found']); return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $leadData = $this->sanitizeLeadInput($input, $existing);
        $leadData['lead_score'] = LeadScorer::calculate($leadData);
        $leadData['lead_grade'] = LeadScorer::getLabel($leadData['lead_score']);

        $this->db->update('leads', $leadData, 'id = ?', [$id]);

        // Log status change
        if (($existing['status'] ?? '') !== $leadData['status']) {
            $this->db->insert('activity_log', [
                'lead_id' => $id, 'user_id' => null,
                'action' => 'Status Changed (API)', 'old_value' => $existing['status'], 'new_value' => $leadData['status'],
            ]);
        }

        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);
        echo json_encode(['success' => true, 'data' => $lead]);
    }

    /**
     * DELETE /api/v1_lead?id=123 — Delete a lead
     */
    private function v1DeleteLead(int $id): void {
        $existing = $this->db->fetch("SELECT id FROM leads WHERE id = ?", [$id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Lead not found']); return;
        }
        $this->db->delete('leads', 'id = ?', [$id]);
        echo json_encode(['success' => true, 'message' => 'Lead deleted']);
    }

    /**
     * POST /api/v1_enrich — Bulk enrich leads with external data
     * Expects JSON: { "leads": [{ "phone_number": "...", "credit_score": 750, ... }] }
     * Matches by phone_number and updates specified fields
     */
    private function v1EnrichLeads(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['leads']) || !is_array($input['leads'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Expected JSON with "leads" array']); return;
        }

        $enrichableFields = ['credit_score', 'employer', 'employment_type', 'monthly_income', 
                             'bank_name', 'city', 'state', 'pincode', 'address', 'email_address', 
                             'dob', 'gender', 'remarks', 'loan_amount', 'loan_type'];

        $updated = 0;
        $notFound = 0;
        $errors = 0;
        $results = [];

        foreach ($input['leads'] as $enrichData) {
            $phone = $enrichData['phone_number'] ?? '';
            $email = $enrichData['email_address'] ?? '';
            $leadId = intval($enrichData['id'] ?? 0);

            // Find lead by ID, phone, or email
            $lead = null;
            if ($leadId) {
                $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
            } elseif ($phone) {
                $lead = $this->db->fetch("SELECT * FROM leads WHERE phone_number = ?", [$phone]);
            } elseif ($email) {
                $lead = $this->db->fetch("SELECT * FROM leads WHERE email_address = ?", [$email]);
            }

            if (!$lead) { $notFound++; $results[] = ['match' => $phone ?: $email ?: $leadId, 'status' => 'not_found']; continue; }

            try {
                $updates = [];
                foreach ($enrichableFields as $field) {
                    if (isset($enrichData[$field]) && $enrichData[$field] !== '' && $enrichData[$field] !== null) {
                        $updates[$field] = $enrichData[$field];
                    }
                }

                if (!empty($updates)) {
                    // Recalculate score with enriched data
                    $merged = array_merge($lead, $updates);
                    $updates['lead_score'] = LeadScorer::calculate($merged);
                    $updates['lead_grade'] = LeadScorer::getLabel($updates['lead_score']);

                    $this->db->update('leads', $updates, 'id = ?', [$lead['id']]);

                    // Log enrichment
                    $this->db->insert('activity_log', [
                        'lead_id' => $lead['id'], 'user_id' => null,
                        'action' => 'Data Enriched (API)',
                        'notes' => 'Fields updated: ' . implode(', ', array_keys($updates)),
                    ]);

                    $updated++;
                    $results[] = ['id' => $lead['id'], 'match' => $phone ?: $email, 'status' => 'enriched', 'fields' => array_keys($updates)];
                }
            } catch (\Exception $e) {
                $errors++;
                $results[] = ['match' => $phone ?: $email, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        echo json_encode([
            'success' => true,
            'summary' => ['updated' => $updated, 'not_found' => $notFound, 'errors' => $errors],
            'results' => $results,
        ]);
    }

    /**
     * GET /api/v1_stats — Dashboard statistics
     */
    private function v1Stats(): void {
        $total = $this->db->count('leads');
        $pipeline = $this->db->fetch("SELECT SUM(loan_amount) as v FROM leads WHERE status NOT IN ('Rejected','Disbursed')")['v'] ?? 0;
        $disbursed = $this->db->fetch("SELECT SUM(loan_amount) as v FROM leads WHERE status = 'Disbursed'")['v'] ?? 0;
        $conversionRate = $total > 0 ? round(($this->db->count('leads', "status = 'Disbursed'") / $total) * 100, 1) : 0;

        $statusBreakdown = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
        $gradeBreakdown = $this->db->fetchAll("SELECT lead_grade, COUNT(*) as count FROM leads GROUP BY lead_grade");
        $topCities = $this->db->fetchAll("SELECT city, COUNT(*) as count FROM leads WHERE city IS NOT NULL AND city != '' GROUP BY city ORDER BY count DESC LIMIT 10");
        $loanTypes = $this->db->fetchAll("SELECT loan_type, COUNT(*) as count, SUM(loan_amount) as total_value FROM leads WHERE loan_type IS NOT NULL GROUP BY loan_type ORDER BY count DESC");

        echo json_encode([
            'success' => true,
            'data' => [
                'total_leads' => $total,
                'pipeline_value' => (float) $pipeline,
                'disbursed_value' => (float) $disbursed,
                'conversion_rate' => $conversionRate,
                'today_leads' => $this->db->count('leads', "DATE(created_at) = CURDATE()"),
                'status_breakdown' => $statusBreakdown,
                'grade_breakdown' => $gradeBreakdown,
                'top_cities' => $topCities,
                'loan_types' => $loanTypes,
            ],
        ]);
    }

    /**
     * Sanitize lead input from API
     */
    private function sanitizeLeadInput(array $input, ?array $existing = null): array {
        $defaults = $existing ?? [
            'customer_name' => '', 'phone_number' => '', 'alt_phone' => '',
            'email_address' => '', 'city' => '', 'state' => '', 'pincode' => '',
            'loan_type' => '', 'loan_amount' => 0, 'monthly_income' => 0,
            'employer' => '', 'employment_type' => null, 'address' => '',
            'lead_source' => 'API', 'status' => 'New',
            'bank_name' => '', 'credit_score' => null, 'gender' => null,
            'dob' => null, 'remarks' => '', 'assigned_to' => null, 'follow_up_date' => null,
        ];

        return [
            'customer_name'   => $input['customer_name'] ?? $defaults['customer_name'],
            'phone_number'    => $input['phone_number'] ?? $defaults['phone_number'],
            'alt_phone'       => $input['alt_phone'] ?? $defaults['alt_phone'],
            'email_address'   => $input['email_address'] ?? $defaults['email_address'],
            'dob'             => !empty($input['dob']) ? $input['dob'] : ($defaults['dob'] ?? null),
            'gender'          => in_array($input['gender'] ?? '', ['Male','Female','Other']) ? $input['gender'] : ($defaults['gender'] ?? null),
            'address'         => $input['address'] ?? $defaults['address'],
            'city'            => $input['city'] ?? $defaults['city'],
            'state'           => $input['state'] ?? $defaults['state'],
            'pincode'         => $input['pincode'] ?? $defaults['pincode'],
            'loan_type'       => $input['loan_type'] ?? $defaults['loan_type'],
            'loan_amount'     => floatval($input['loan_amount'] ?? $defaults['loan_amount']),
            'monthly_income'  => floatval($input['monthly_income'] ?? $defaults['monthly_income']),
            'employer'        => $input['employer'] ?? $defaults['employer'],
            'employment_type' => in_array($input['employment_type'] ?? '', ['Salaried','Self-Employed','Business','Retired','Other']) ? $input['employment_type'] : ($defaults['employment_type'] ?? null),
            'credit_score'    => !empty($input['credit_score']) ? intval($input['credit_score']) : ($defaults['credit_score'] ?? null),
            'bank_name'       => $input['bank_name'] ?? $defaults['bank_name'],
            'lead_source'     => $input['lead_source'] ?? $defaults['lead_source'],
            'status'          => $input['status'] ?? $defaults['status'],
            'assigned_to'     => !empty($input['assigned_to']) ? intval($input['assigned_to']) : ($defaults['assigned_to'] ?? null),
            'remarks'         => $input['remarks'] ?? $defaults['remarks'],
            'follow_up_date'  => !empty($input['follow_up_date']) ? $input['follow_up_date'] : ($defaults['follow_up_date'] ?? null),
        ];
    }
}
