<?php
class DashboardController extends Controller {

    public function adminDashboard(): void {
        $this->requireRole('admin');

        $reservationModel = new Reservation();
        $vehicleModel     = new Vehicle();
        $driverModel      = new Driver();

        $this->view('admin.dashboard', [
            'totalReservations' => $reservationModel->countAll(),
            'pending'           => $reservationModel->countByStatus('pending'),
            'dispatched'        => $reservationModel->countByStatus('dispatched'),
            'completed'         => $reservationModel->countByStatus('completed'),
            'totalVehicles'     => $vehicleModel->countAll(),
            'availableVehicles' => $vehicleModel->countByStatus('available'),
            'availableDrivers'  => $driverModel->countAvailable(),
            'recentReservations'=> array_slice($reservationModel->all(), 0, 8),
        ]);
    }

    public function requesterDashboard(): void {
        $this->requireRole('requester');

        $model = new Reservation();
        $this->view('requester.dashboard', [
            'requests' => $model->byRequester((int)$_SESSION['user_id']),
        ]);
    }

    public function driverDashboard(): void {
        $this->requireRole('driver');

        $driverModel      = new Driver();
        $reservationModel = new Reservation();

        $driver = $driverModel->findByUserId((int)$_SESSION['user_id']);
        if (!$driver) {
            die('Driver profile not found. Contact the administrator.');
        }

        $trips = $reservationModel->activeForDriver((int)$driver['driver_id']);

        $this->view('driver.dashboard', [
            'driver' => $driver,
            'trips'  => $trips,
            'flash'  => $this->getFlash('success'),
        ]);
    }
}