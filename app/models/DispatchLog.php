<?php
class DispatchLog extends Model {

    public function findByReservation(int $reservationId): ?array {
        return $this->queryOne(
            'SELECT dl.*, u.full_name AS driver_name, v.make_model, v.plate_number
             FROM   dispatch_logs dl
             JOIN   drivers d ON d.driver_id = dl.driver_id
             JOIN   users   u ON u.user_id   = d.user_id
             JOIN   vehicles v ON v.vehicle_id = dl.vehicle_id
             WHERE  dl.reservation_id = ?',
            [$reservationId]
        );
    }

    public function byDriver(int $driverId): array {
        return $this->query(
            'SELECT dl.*, r.reference_no, r.destination, r.departure_date,
                    r.return_date, r.status, v.make_model, v.plate_number
             FROM   dispatch_logs dl
             JOIN   reservations r ON r.reservation_id = dl.reservation_id
             JOIN   vehicles v ON v.vehicle_id = dl.vehicle_id
             WHERE  dl.driver_id = ?
             ORDER  BY dl.logged_at DESC',
            [$driverId]
        );
    }

    public function create(array $data): void {
        $this->execute(
            'INSERT INTO dispatch_logs
             (reservation_id, driver_id, vehicle_id, actual_passengers, actual_departure)
             VALUES (?,?,?,?,NOW())',
            [
                $data['reservation_id'],
                $data['driver_id'],
                $data['vehicle_id'],
                $data['actual_passengers'] ?? null,
            ]
        );
    }

    public function driverHasActiveTrip(int $driverId): bool {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS n
             FROM   dispatch_logs dl
             JOIN   reservations r ON r.reservation_id = dl.reservation_id
             WHERE  dl.driver_id = ?
               AND  r.status = ?
               AND  dl.actual_return IS NULL',
            [$driverId, 'dispatched']
        );

        return (int)($row['n'] ?? 0) > 0;
    }

    public function startTrip(int $reservationId, ?int $actualPassengers = null): void {
        $this->execute(
            'UPDATE dispatch_logs
             SET actual_passengers=?, actual_departure=NOW()
             WHERE reservation_id=?',
            [$actualPassengers, $reservationId]
        );
    }

    public function complete(int $reservationId, array $data): void {
        $this->execute(
            'UPDATE dispatch_logs
             SET actual_return=NOW(), trip_notes=?
             WHERE reservation_id=?',
            [
                $data['trip_notes'] ?? null,
                $reservationId,
            ]
        );
    }

    public function countCompleted(): int {
        $row = $this->queryOne('SELECT COUNT(*) AS n FROM dispatch_logs WHERE actual_return IS NOT NULL');
        return (int)($row['n'] ?? 0);
    }
}