<?php
/**
 * Dealer Controller - Dealer Partner Portal
 * Dealers can list vehicles, track inquiries, and view earnings.
 */
class DealerController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        if ($_SESSION['user_role'] !== 'dealer') {
            header('Location: index.php?page=dashboard'); exit;
        }
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'add_vehicle': $this->addVehicle(); break;
            case 'store_vehicle': $this->storeVehicle(); break;
            case 'edit_vehicle': $this->editVehicle(); break;
            case 'update_vehicle': $this->updateVehicle(); break;
            case 'my_vehicles': $this->myVehicles(); break;
            case 'inquiries': $this->inquiries(); break;
            default: $this->dashboard(); break;
        }
    }

    private function dashboard(): void {
        $dealerId = Security::userId();
        $data = ['page' => 'dealer_dashboard'];
        
        $data['total_vehicles'] = $this->db->count('vehicles', 'dealer_id = ?', [$dealerId]);
        $data['available'] = $this->db->count('vehicles', "dealer_id = ? AND status = 'Available'", [$dealerId]);
        $data['sold'] = $this->db->count('vehicles', "dealer_id = ? AND status = 'Sold'", [$dealerId]);
        
        $inv = $this->db->fetch("SELECT SUM(asking_price) as v FROM vehicles WHERE dealer_id = ? AND status = 'Available'", [$dealerId]);
        $data['inventory_value'] = $inv['v'] ?? 0;

        $soldVal = $this->db->fetch("SELECT SUM(asking_price) as v FROM vehicles WHERE dealer_id = ? AND status = 'Sold'", [$dealerId]);
        $data['sold_value'] = $soldVal['v'] ?? 0;

        $data['total_inquiries'] = $this->db->fetch(
            "SELECT SUM(inquiries_count) as cnt FROM vehicles WHERE dealer_id = ?", [$dealerId]
        )['cnt'] ?? 0;

        $data['recent_vehicles'] = $this->db->fetchAll(
            "SELECT * FROM vehicles WHERE dealer_id = ? ORDER BY created_at DESC LIMIT 6",
            [$dealerId]
        );

        require __DIR__ . '/../views/layout.php';
    }

    private function myVehicles(): void {
        $dealerId = Security::userId();
        $data = ['page' => 'dealer_vehicles'];
        $data['vehicles'] = $this->db->fetchAll(
            "SELECT * FROM vehicles WHERE dealer_id = ? ORDER BY created_at DESC", [$dealerId]
        );
        require __DIR__ . '/../views/layout.php';
    }

    private function addVehicle(): void {
        $data = ['page' => 'dealer_vehicle_form', 'vehicle' => null, 'mode' => 'create'];
        require __DIR__ . '/../views/layout.php';
    }

    private function storeVehicle(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=dealer&action=my_vehicles'); return;
        }
        $vData = $this->extractData($_POST);
        $vData['dealer_id'] = Security::userId();
        $vData['added_by'] = Security::userId();
        $this->db->insert('vehicles', $vData);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Vehicle listed successfully!'];
        header('Location: index.php?page=dealer&action=my_vehicles');
    }

    private function editVehicle(): void {
        $id = intval($_GET['id'] ?? 0);
        $vehicle = $this->db->fetch("SELECT * FROM vehicles WHERE id = ? AND dealer_id = ?", [$id, Security::userId()]);
        if (!$vehicle) { header('Location: index.php?page=dealer&action=my_vehicles'); return; }
        $data = ['page' => 'dealer_vehicle_form', 'vehicle' => $vehicle, 'mode' => 'edit'];
        require __DIR__ . '/../views/layout.php';
    }

    private function updateVehicle(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=dealer&action=my_vehicles'); return;
        }
        $id = intval($_POST['id'] ?? 0);
        $existing = $this->db->fetch("SELECT id FROM vehicles WHERE id = ? AND dealer_id = ?", [$id, Security::userId()]);
        if (!$existing) { header('Location: index.php?page=dealer&action=my_vehicles'); return; }
        
        $vData = $this->extractData($_POST);
        $this->db->update('vehicles', $vData, 'id = ?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Vehicle updated!'];
        header('Location: index.php?page=dealer&action=my_vehicles');
    }

    private function inquiries(): void {
        $dealerId = Security::userId();
        $data = ['page' => 'dealer_inquiries'];
        $data['leads'] = $this->db->fetchAll(
            "SELECT l.*, v.make as v_make, v.model as v_model, v.year as v_year 
             FROM leads l 
             LEFT JOIN vehicles v ON l.vehicle_make = v.make AND l.vehicle_model LIKE CONCAT('%', v.model, '%')
             WHERE v.dealer_id = ? AND l.lead_source IN ('EMI Calculator', 'Vehicle Inquiry')
             ORDER BY l.created_at DESC LIMIT 50",
            [$dealerId]
        );
        require __DIR__ . '/../views/layout.php';
    }

    private function extractData(array $post): array {
        return [
            'make'           => Security::sanitize($post['make'] ?? ''),
            'model'          => Security::sanitize($post['model'] ?? ''),
            'variant'        => Security::sanitize($post['variant'] ?? ''),
            'year'           => intval($post['year'] ?? date('Y')),
            'registration_no'=> strtoupper(Security::sanitize($post['registration_no'] ?? '')),
            'color'          => Security::sanitize($post['color'] ?? ''),
            'fuel_type'      => Security::sanitize($post['fuel_type'] ?? 'Petrol'),
            'transmission'   => Security::sanitize($post['transmission'] ?? 'Manual'),
            'km_driven'      => intval($post['km_driven'] ?? 0),
            'owner_count'    => intval($post['owner_count'] ?? 1),
            'asking_price'   => floatval($post['asking_price'] ?? 0),
            'market_value'   => floatval($post['market_value'] ?? 0),
            'body_type'      => Security::sanitize($post['body_type'] ?? ''),
            'insurance_valid'=> !empty($post['insurance_valid']) ? $post['insurance_valid'] : null,
            'hypothecated'   => isset($post['hypothecated']) ? 1 : 0,
            'description'    => Security::sanitize($post['description'] ?? ''),
            'photo_url'      => filter_var($post['photo_url'] ?? '', FILTER_SANITIZE_URL),
            'status'         => 'Available',
        ];
    }
}
