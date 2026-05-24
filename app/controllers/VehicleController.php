<?php
// VehicleController
class VehicleController extends Controller {

    private Vehicle $model;
    private Driver  $driverModel;

    public function __construct() {
        $this->model = new Vehicle();
        $this->driverModel = new Driver();
    }

    public function index(): void {
        $this->requireRole('admin');
        $this->view('admin.vehicles', [
            'vehicles' => $this->model->all(),
            'flash'    => $this->getFlash('success'),
        ]);
    }

    public function create(): void {
        $this->requireRole('admin');
        $this->view('admin.vehicle_form', [
            'vehicle' => null,
            'drivers' => $this->driverModel->all(),
            'error'   => $this->getFlash('error'),
        ]);
    }

    public function store(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $assignedDriverId = (int)($_POST['assigned_driver_id'] ?? 0);
        if ($assignedDriverId > 0 && !$this->driverModel->findById($assignedDriverId)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Selected driver is invalid.'], 422);
            }
            $this->flash('error', 'Selected driver is invalid.');
            $this->redirect('admin/vehicles/create');
        }

        $data = [
            'plate_number' => $this->postInput('plate_number'),
            'make_model'   => $this->postInput('make_model'),
            'vehicle_type' => $this->postInput('vehicle_type'),
            'capacity'     => (int)($_POST['capacity'] ?? 0),
            'year'         => $_POST['year']  ?? null,
            'color'        => $this->postInput('color'),
            'assigned_driver_id' => $assignedDriverId > 0 ? $assignedDriverId : null,
            'notes'        => $this->postInput('notes'),
        ];

        if (empty($data['plate_number']) || empty($data['make_model']) || $data['capacity'] < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Plate number, model, and capacity are required.'], 422);
            }
            $this->flash('error', 'Plate number, model, and capacity are required.');
            $this->redirect('admin/vehicles/create');
        }

        try {
            $this->model->create($data);
        } catch (\PDOException $e) {
            $msg = 'Failed to add vehicle.';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $msg = 'A vehicle with this plate number already exists.';
            }
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 422);
            }
            $this->flash('error', $msg);
            $this->redirect('admin/vehicles/create');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Vehicle added successfully.', 'redirect' => BASE_URL . 'admin/vehicles']);
        }
        $this->flash('success', 'Vehicle added successfully.');
        $this->redirect('admin/vehicles');
    }

    public function edit(): void {
        $this->requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $vehicle = $this->model->findById($id);
        if (!$vehicle) { http_response_code(404); die('Vehicle not found.'); }
        $this->view('admin.vehicle_form', [
            'vehicle' => $vehicle,
            'drivers' => $this->driverModel->all(),
            'error'   => null,
        ]);
    }

    public function update(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $id   = (int)($_POST['vehicle_id'] ?? 0);
        $assignedDriverId = (int)($_POST['assigned_driver_id'] ?? 0);
        if ($assignedDriverId > 0 && !$this->driverModel->findById($assignedDriverId)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Selected driver is invalid.'], 422);
            }
            $this->flash('error', 'Selected driver is invalid.');
            $this->redirect('admin/vehicles');
        }
        $data = [
            'plate_number' => $this->postInput('plate_number'),
            'make_model'   => $this->postInput('make_model'),
            'vehicle_type' => $this->postInput('vehicle_type'),
            'capacity'     => (int)($_POST['capacity'] ?? 0),
            'year'         => $_POST['year']   ?? null,
            'color'        => $this->postInput('color'),
            'status'       => $_POST['status'] ?? 'available',
            'assigned_driver_id' => $assignedDriverId > 0 ? $assignedDriverId : null,
            'notes'        => $this->postInput('notes'),
        ];


        $validStatuses = ['available', 'in_use', 'maintenance', 'retired'];
        if (!in_array($data['status'], $validStatuses, true)) {
            $data['status'] = 'available';
        }

        try {
            $this->model->update($id, $data);
        } catch (\PDOException $e) {
            $msg = 'Failed to update vehicle.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 422);
            }
            $this->flash('error', $msg);
            $this->redirect('admin/vehicles');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Vehicle updated successfully.', 'redirect' => BASE_URL . 'admin/vehicles']);
        }
        $this->flash('success', 'Vehicle updated successfully.');
        $this->redirect('admin/vehicles');
    }

    /** API: Return available vehicles filtered by type and capacity (JSON). */
    public function availableApi(): void {
        $type     = $_GET['type']     ?? null;
        $capacity = max(1, (int)($_GET['capacity'] ?? 1));

        $vehicles = $this->model->availableByFilter($type, $capacity);

        $result = array_map(function ($v) {
            return [
                'vehicle_id'   => (int)$v['vehicle_id'],
                'plate_number' => $v['plate_number'],
                'make_model'   => $v['make_model'],
                'vehicle_type' => $v['vehicle_type'] ?? '—',
                'capacity'     => (int)$v['capacity'],
                'year'         => $v['year'] ?? '—',
                'color'        => $v['color'] ?? '—',
                'driver_name'  => $v['assigned_driver_name'] ?? null,
            ];
        }, $vehicles);

        $this->json(['success' => true, 'vehicles' => $result]);
    }
}
