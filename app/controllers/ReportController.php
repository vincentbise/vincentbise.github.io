<?php
class ReportController extends Controller {

    public function index(): void {
        $this->requireRole('admin');

        $db   = Database::getInstance();
        $type = $_GET['type'] ?? 'utilization';
        $allowed = ['utilization', 'monthly', 'drivers'];
        if (!in_array($type, $allowed, true)) {
            $type = 'utilization';
        }

        $data = match ($type) {
            'utilization' => $this->utilization($db),
            'monthly'     => $this->monthly($db),
            'drivers'     => $this->driverSummary($db),
            default       => [],
        };

        $this->view('admin.reports', [
            'type'  => $type,
            'data'  => $data,
            'flash' => $this->getFlash('success'),
        ]);
    }

    private function utilization(PDO $db): array {
        $stmt = $db->query(
            'SELECT v.make_model, v.plate_number, v.status,
                    COUNT(dl.log_id) AS trips_completed
             FROM   vehicles v
             LEFT JOIN dispatch_logs dl ON dl.vehicle_id = v.vehicle_id
             GROUP  BY v.vehicle_id
             ORDER  BY trips_completed DESC'
        );
        return $stmt->fetchAll();
    }

    private function monthly(PDO $db): array {
        $stmt = $db->query(
            'SELECT DATE_FORMAT(departure_date, "%Y-%m") AS month,
                    COUNT(*) AS total,
                    SUM(status = "completed")  AS completed,
                    SUM(status = "rejected")   AS rejected,
                    SUM(status = "cancelled")  AS cancelled
             FROM   vw_reservation_summary
             GROUP  BY month
             ORDER  BY month DESC
             LIMIT  12'
        );
        return $stmt->fetchAll();
    }

    private function driverSummary(PDO $db): array {
        $stmt = $db->query(
            'SELECT u.full_name AS driver,
                    COUNT(dl.log_id) AS trips
             FROM   drivers d
             JOIN   users u ON u.user_id = d.user_id
             LEFT JOIN dispatch_logs dl ON dl.driver_id = d.driver_id
                   AND dl.actual_return IS NOT NULL
             GROUP  BY d.driver_id
             ORDER  BY trips DESC'
        );
        return $stmt->fetchAll();
    }
}