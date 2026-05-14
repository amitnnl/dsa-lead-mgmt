<?php
/**
 * Vehicle Controller - Inventory CRUD + Admin Management
 */
class VehicleController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        // Only admin and manager can manage vehicle inventory
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, ['admin', 'manager'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Access denied.'];
            header('Location: index.php?page=dashboard'); exit;
        }
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'add': $this->addForm(); break;
            case 'store': $this->store(); break;
            case 'edit': $this->editForm(); break;
            case 'update': $this->update(); break;
            case 'toggle': $this->toggleStatus(); break;
            default: $this->index(); break;
        }
    }

    private function index(): void {
        $data = ['page' => 'vehicles'];
        $status = $_GET['status'] ?? '';
        $where = '1=1';
        $params = [];

        if ($status) { $where .= ' AND v.status = ?'; $params[] = $status; }

        $data['vehicles'] = $this->db->fetchAll(
            "SELECT v.*, u.name as added_by_name FROM vehicles v LEFT JOIN users u ON v.added_by = u.id WHERE $where ORDER BY v.created_at DESC",
            $params
        );
        $data['stats'] = [
            'total' => $this->db->count('vehicles', '1=1'),
            'available' => $this->db->count('vehicles', "status = 'Available'"),
            'reserved' => $this->db->count('vehicles', "status = 'Reserved'"),
            'sold' => $this->db->count('vehicles', "status = 'Sold'"),
            'total_value' => $this->db->fetch("SELECT SUM(asking_price) as v FROM vehicles WHERE status = 'Available'")['v'] ?? 0,
        ];
        require __DIR__ . '/../views/layout.php';
    }

    private function addForm(): void {
        $data = ['page' => 'vehicle_form', 'vehicle' => null, 'mode' => 'create'];
        require __DIR__ . '/../views/layout.php';
    }

    private function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=vehicles'); return;
        }
        $vData = $this->extractData($_POST);
        $vData['added_by'] = Security::userId();
        $id = $this->db->insert('vehicles', $vData);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Vehicle added to inventory!'];
        header("Location: index.php?page=vehicles");
    }

    private function editForm(): void {
        $id = intval($_GET['id'] ?? 0);
        $vehicle = $this->db->fetch("SELECT * FROM vehicles WHERE id = ?", [$id]);
        if (!$vehicle) { header('Location: index.php?page=vehicles'); return; }
        $data = ['page' => 'vehicle_form', 'vehicle' => $vehicle, 'mode' => 'edit'];
        require __DIR__ . '/../views/layout.php';
    }

    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=vehicles'); return;
        }
        $id = intval($_POST['id'] ?? 0);
        $vData = $this->extractData($_POST);
        $this->db->update('vehicles', $vData, 'id = ?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Vehicle updated!'];
        header("Location: index.php?page=vehicles");
    }

    private function toggleStatus(): void {
        $id = intval($_GET['id'] ?? 0);
        $newStatus = Security::sanitize($_GET['to'] ?? 'Available');
        if (in_array($newStatus, ['Available', 'Reserved', 'Sold', 'Delisted'])) {
            $this->db->update('vehicles', ['status' => $newStatus], 'id = ?', [$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Vehicle marked as {$newStatus}."];
        }
        header('Location: index.php?page=vehicles');
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
            'status'         => in_array($post['status'] ?? '', ['Available','Reserved','Sold','Delisted']) ? $post['status'] : 'Available',
        ];
    }
}
