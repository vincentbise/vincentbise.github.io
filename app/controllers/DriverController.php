<?php
class DriverController extends Controller {

    private Driver      $driverModel;
    private DispatchLog $logModel;
    private Reservation $reservationModel;
    private Vehicle     $vehicleModel;

    public function __construct() {
        $this->driverModel      = new Driver();
        $this->logModel         = new DispatchLog();
        $this->reservationModel = new Reservation();
        $this->vehicleModel     = new Vehicle();
    }

    public function myTrips(): void {
        $this->requireRole('driver');
        $driver = $this->driverModel->findByUserId((int)$_SESSION['user_id']);
        if (!$driver) { die('Driver profile not found.'); }
        $trips = $this->logModel->byDriver((int)$driver['driver_id']);
        $this->view('driver.my_trips', ['driver' => $driver, 'trips' => $trips]);
    }

    /** Driver marks themselves as dispatched (trip started). */
    public function dispatch(): void {
        $this->requireRole('driver');
        $this->verifyCsrf();

        $reservationId = (int)($_POST['reservation_id'] ?? 0);
        $actualPassengers = (int)($_POST['actual_passengers'] ?? 0);

        $reservation = $this->reservationModel->findById($reservationId);
        $driver      = $this->driverModel->findByUserId((int)$_SESSION['user_id']);

        if (!$reservation || !$driver) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Invalid reservation or driver.'], 422);
            }
            $this->redirect('driver/dashboard');
        }

        if ($reservation['status'] === 'dispatched') {
            $msg = 'This trip is already in progress.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 409);
            }
            $this->flash('error', $msg);
            $this->redirect('driver/dashboard');
        }

        if ($reservation['status'] === 'completed') {
            $msg = 'This trip is already completed.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 409);
            }
            $this->flash('error', $msg);
            $this->redirect('driver/dashboard');
        }

        if ($this->logModel->driverHasActiveTrip((int)$driver['driver_id'])) {
            $msg = 'You cannot start another trip while you are dispatched.';
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => $msg], 409);
            }
            $this->flash('error', $msg);
            $this->redirect('driver/dashboard');
        }

        if ($actualPassengers < 1) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Please enter the passenger count before starting the trip.'], 422);
            }
            $this->flash('error', 'Please enter the passenger count before starting the trip.');
            $this->redirect('driver/dashboard');
        }

        $vehicleId = (int)($reservation['vehicle_id'] ?? 0);
        if ($vehicleId > 0) {
            $vehicle = $this->vehicleModel->findById($vehicleId);
            $capacity = (int)($vehicle['capacity'] ?? 0);
            if ($capacity > 0 && $actualPassengers > $capacity) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Passenger count exceeds vehicle capacity.'], 422);
                }
                $this->flash('error', 'Passenger count exceeds vehicle capacity.');
                $this->redirect('driver/dashboard');
            }
        }

        $existingLog = $this->logModel->findByReservation($reservationId);
        if ($existingLog) {
            $this->logModel->startTrip($reservationId, 0, $actualPassengers);
        } else {
            $this->logModel->create([
                'reservation_id' => $reservationId,
                'driver_id'      => (int)$driver['driver_id'],
                'vehicle_id'     => (int)$reservation['vehicle_id'],
                'actual_passengers' => $actualPassengers,
                'start_mileage'  => 0,
            ]);
        }

        $this->reservationModel->updateStatus($reservationId, 'dispatched');
        $this->driverModel->setAvailability((int)$driver['driver_id'], false);

        if ($reservation['vehicle_id']) {
            $this->vehicleModel->setStatus((int)$reservation['vehicle_id'], 'in_use');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Trip started. Safe travels!']);
        }
        $this->flash('success', 'Trip started. Safe travels!');
        $this->redirect('driver/dashboard');
    }

    /** Driver marks trip as complete. */
    public function complete(): void {
        $this->requireRole('driver');
        $this->verifyCsrf();

        $reservationId = (int)($_POST['reservation_id'] ?? 0);

        $this->logModel->complete($reservationId, [
            'end_mileage'   => null,
            'fuel_consumed' => null,
            'trip_notes'    => trim($_POST['trip_notes'] ?? ''),
        ]);

        $reservation = $this->reservationModel->findById($reservationId);
        $this->reservationModel->updateStatus($reservationId, 'completed');

        if ($reservation && $reservation['vehicle_id']) {
            $this->vehicleModel->setStatus((int)$reservation['vehicle_id'], 'available');
        }

        $driver = $this->driverModel->findByUserId((int)$_SESSION['user_id']);
        if ($driver) {
            $this->driverModel->setAvailability((int)$driver['driver_id'], true);
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Trip marked as complete.']);
        }
        $this->flash('success', 'Trip marked as complete.');
        $this->redirect('driver/dashboard');
    }
}