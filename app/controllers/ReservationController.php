<?php
class ReservationController extends Controller {

    private Reservation $model;
    private Approval    $approvalModel;
    private Vehicle     $vehicleModel;
    private Driver      $driverModel;

    public function __construct() {
        $this->model         = new Reservation();
        $this->approvalModel = new Approval();
        $this->vehicleModel  = new Vehicle();
        $this->driverModel   = new Driver();
    }



    public function adminIndex(): void {
        $this->requireRole('admin');
        $this->view('admin.reservations', [
            'reservations' => $this->model->all(),
            'flash'        => $this->getFlash('success'),
        ]);
    }

    public function adminView(): void {
        $this->requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $reservation = $this->model->findById($id);
        if (!$reservation) { http_response_code(404); die('Not found.'); }

        $logModel    = new DispatchLog();
        $dispatchLog = $logModel->findByReservation($id);

        $this->view('admin.reservation_view', [
            'reservation' => $reservation,
            'approvals'   => $this->approvalModel->forReservation($id),
            'dispatchLog' => $dispatchLog,
            'flash'       => $this->getFlash('success'),
        ]);
    }

    /** Assign a vehicle to an approved reservation (Admin). Driver is auto-resolved from fleet management. */
    public function assign(): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $id        = (int)($_POST['reservation_id'] ?? 0);
        $vehicleId = (int)($_POST['vehicle_id']     ?? 0);

        if ($vehicleId < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please select a vehicle.'], 422);
            }
            $this->flash('error', 'Please select a vehicle.');
            $this->redirect("admin/reservations/view?id={$id}");
        }

        $reservation = $this->model->findById($id);
        if (!$reservation) { http_response_code(404); die('Not found.'); }
        if ($reservation['status'] !== 'approved') {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Reservation must be approved before assignment.'], 422);
            }
            $this->flash('error', 'Reservation must be approved before assignment.');
            $this->redirect("admin/reservations/view?id={$id}");
        }

        $vehicle = $this->vehicleModel->findById($vehicleId);
        if (!$vehicle) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Vehicle not found.'], 422);
            }
            $this->flash('error', 'Vehicle not found.');
            $this->redirect("admin/reservations/view?id={$id}");
        }

        $driverId = $vehicle['assigned_driver_id'] ? (int)$vehicle['assigned_driver_id'] : 0;
        if ($driverId < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'This vehicle has no assigned driver. Please assign a driver in Fleet Management first.'], 422);
            }
            $this->flash('error', 'This vehicle has no assigned driver. Please assign a driver in Fleet Management first.');
            $this->redirect("admin/reservations/view?id={$id}");
        }

        $this->model->assignVehicle($id, $vehicleId);
        $this->vehicleModel->setStatus($vehicleId, 'in_use');

        $logModel = new DispatchLog();
        $logModel->create([
            'reservation_id' => $id,
            'driver_id'      => $driverId,
            'vehicle_id'     => $vehicleId,
            'start_mileage'  => 0,
        ]);

        $this->driverModel->setAvailability($driverId, false);

        if ($this->isAjax()) {
            $this->json([
                'success'  => true,
                'message'  => 'Vehicle and driver assigned successfully.',
                'redirect' => BASE_URL . "admin/reservations/view?id={$id}",
            ]);
        }
        $this->flash('success', 'Vehicle and driver assigned successfully.');
        $this->redirect("admin/reservations/view?id={$id}");
    }



    public function pendingApprovals(): void {
        $this->requireRole('admin', 'staff');
        $reservations = $this->model->pending();

        $this->view('admin.approvals', [
            'reservations' => $reservations,
            'flash'        => $this->getFlash('success'),
            'error'        => $this->getFlash('error'),
        ]);
    }

    public function decide(): void {
        $this->requireRole('admin', 'staff');
        $this->verifyCsrf();

        $id       = (int)($_POST['reservation_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $remarks  = trim($_POST['remarks'] ?? '');
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid decision.'], 422);
            }
            $this->redirect('approvals');
        }

        $oldestId = $this->model->oldestPendingId();
        if ($oldestId && $oldestId !== $id) {
            $msg = 'Please review requests in order. The oldest pending request must be decided first.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 409);
            }
            $this->flash('error', $msg);
            $this->redirect('approvals');
        }

        $newStatus = $decision === 'approved' ? 'approved' : 'rejected';

        if ($decision === 'approved') {
            $reservation = $this->model->findById($id);
            $vehicleId = (int)($reservation['vehicle_id'] ?? 0);

            if ($vehicleId < 1) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'A vehicle must be selected before approval.'], 422);
                }
                $this->flash('error', 'A vehicle must be selected before approval.');
                $this->redirect('approvals');
            }

            $vehicle = $this->vehicleModel->findById($vehicleId);
            $driverId = (int)($vehicle['assigned_driver_id'] ?? 0);
            if ($driverId < 1) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Cannot approve: selected vehicle has no assigned driver.'], 422);
                }
                $this->flash('error', 'Cannot approve: selected vehicle has no assigned driver.');
                $this->redirect('approvals');
            }

            if ($this->model->hasVehicleOverlap(
                $vehicleId,
                $reservation['departure_date'],
                $reservation['departure_time'],
                $reservation['return_date'],
                $reservation['return_time'],
                ['approved', 'dispatched'],
                $id
            )) {
                $msg = 'Cannot approve: vehicle schedule overlaps another approved or dispatched trip.';
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => $msg], 409);
                }
                $this->flash('error', $msg);
                $this->redirect('approvals');
            }
        }

        $this->model->updateStatus($id, $newStatus, $remarks ?: null);

        $this->approvalModel->create([
            'reservation_id' => $id,
            'approved_by'    => (int)$_SESSION['user_id'],
            'approval_level' => 'staff',
            'decision'       => $decision,
            'remarks'        => $remarks ?: null,
        ]);

        if ($decision === 'approved') {
            $reservation = $this->model->findById($id);
            if ($reservation && $reservation['vehicle_id']) {
                $vehicle  = $this->vehicleModel->findById((int)$reservation['vehicle_id']);
                $driverId = $vehicle ? (int)($vehicle['assigned_driver_id'] ?? 0) : 0;

                if ($driverId > 0) {
                    $logModel = new DispatchLog();
                    $existing = $logModel->findByReservation($id);
                    if (!$existing) {
                        $logModel->create([
                            'reservation_id' => $id,
                            'driver_id'      => $driverId,
                            'vehicle_id'     => (int)$reservation['vehicle_id'],
                            'start_mileage'  => 0,
                        ]);
                    }
                }
            }
        }

        $msg = $decision === 'approved' ? 'Request approved successfully.' : 'Request has been rejected.';

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => $msg]);
        }
        $this->flash('success', 'Decision recorded successfully.');
        $this->redirect('approvals');
    }



    public function newForm(): void {
        $this->requireRole('requester');
        $vehicleTypes = ['Van','Bus','SUV','Pickup','Sedan','Motorcycle'];
        $this->view('requester.new_request', [
            'flash'        => $this->getFlash('success'),
            'error'        => $this->getFlash('error'),
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    public function store(): void {
        $this->requireRole('requester');
        $this->verifyCsrf();

        $data = [
            'requester_id'   => (int)$_SESSION['user_id'],
            'purpose'        => $this->postInput('purpose'),
            'destination'    => $this->postInput('destination'),
            'passengers'     => (int)($_POST['passengers']    ?? 1),
            'departure_date' => $_POST['departure_date']      ?? '',
            'departure_time' => $_POST['departure_time']      ?? '',
            'return_date'    => $_POST['return_date']         ?? '',
            'return_time'    => $_POST['return_time']         ?? '',
            'vehicle_id'     => !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null,
            'requester_remarks' => $this->postInput('requester_remarks'),
        ];


        foreach (['purpose','destination','departure_date','departure_time','return_date','return_time'] as $field) {
            if (empty($data[$field])) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Please fill in all required fields.'], 422);
                }
                $this->flash('error', 'Please fill in all required fields.');
                $this->redirect('requester/new');
            }
        }

        if (empty($data['vehicle_id'])) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please select a vehicle for this trip.'], 422);
            }
            $this->flash('error', 'Please select a vehicle for this trip.');
            $this->redirect('requester/new');
        }

        $vehicle = $this->vehicleModel->findById((int)$data['vehicle_id']);
        if (!$vehicle) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Selected vehicle not found.'], 422);
            }
            $this->flash('error', 'Selected vehicle not found.');
            $this->redirect('requester/new');
        }

        $capacity = (int)($vehicle['capacity'] ?? 0);
        if ($capacity > 0 && $data['passengers'] > $capacity) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Passenger count exceeds vehicle capacity.'], 422);
            }
            $this->flash('error', 'Passenger count exceeds vehicle capacity.');
            $this->redirect('requester/new');
        }

        if ($capacity > 0) {
            $occupancy = ($data['passengers'] / $capacity) * 100;
            if ($occupancy < 50 && empty($data['requester_remarks'])) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Please provide a justification for low occupancy.'], 422);
                }
                $this->flash('error', 'Please provide a justification for low occupancy.');
                $this->redirect('requester/new');
            }
        }


        if ($data['departure_date'] < date('Y-m-d')) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Departure date cannot be in the past.'], 422);
            }
            $this->flash('error', 'Departure date cannot be in the past.');
            $this->redirect('requester/new');
        }

        if ($data['return_date'] < $data['departure_date']) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Return date must be on or after departure date.'], 422);
            }
            $this->flash('error', 'Return date must be on or after departure date.');
            $this->redirect('requester/new');
        }

        if ($this->model->hasVehicleOverlap(
            (int)$data['vehicle_id'],
            $data['departure_date'],
            $data['departure_time'],
            $data['return_date'],
            $data['return_time'],
            ['dispatched']
        )) {
            $msg = 'Selected vehicle is currently dispatched during that time.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 409);
            }
            $this->flash('error', $msg);
            $this->redirect('requester/new');
        }

        $this->model->create($data);

        if ($this->isAjax()) {
            $this->json([
                'success'  => true,
                'message'  => 'Reservation submitted successfully!',
                'redirect' => BASE_URL . 'requester/my_requests',
            ]);
        }
        $this->flash('success', 'Reservation submitted successfully!');
        $this->redirect('requester/my_requests');
    }

    public function myRequests(): void {
        $this->requireRole('requester');
        $this->view('requester.my_requests', [
            'requests' => $this->model->byRequester((int)$_SESSION['user_id']),
            'flash'    => $this->getFlash('success'),
        ]);
    }

    public function cancel(): void {
        $this->requireRole('requester');
        $this->verifyCsrf();

        $id = (int)($_POST['reservation_id'] ?? 0);
        $this->model->cancel($id, (int)$_SESSION['user_id']);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Reservation cancelled.']);
        }
        $this->flash('success', 'Reservation cancelled.');
        $this->redirect('requester/my_requests');
    }
}