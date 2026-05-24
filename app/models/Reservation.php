<?php
// Reservation Model
class Reservation extends Model {

    /** All reservations with requester and vehicle info. */
    public function all(): array {
        return $this->query(
            'SELECT r.*, u.full_name AS requester_name, u.department,
                    v.make_model, v.plate_number
             FROM   reservations r
             JOIN   users    u ON u.user_id    = r.requester_id
             LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             ORDER  BY r.requested_at DESC'
        );
    }

    /** Find a reservation by ID with full details. */
    public function findById(int $id): ?array {
        return $this->queryOne(
            'SELECT r.*, u.full_name AS requester_name, u.department,
                    u.email AS requester_email, u.contact_no,
                    v.make_model, v.plate_number
             FROM   reservations r
             JOIN   users    u ON u.user_id    = r.requester_id
             LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE  r.reservation_id = ?', [$id]
        );
    }

    /** Reservations by requester with vehicle info. */
    public function byRequester(int $userId): array {
        return $this->query(
            'SELECT r.*, v.make_model, v.plate_number
             FROM   reservations r
             LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE  r.requester_id = ?
             ORDER  BY r.requested_at DESC',
            [$userId]
        );
    }

    /** Pending reservations (for staff review). */
    public function pending(): array {
        return $this->query(
            'SELECT r.*, u.full_name AS requester_name, u.department
             FROM   reservations r
             JOIN   users u ON u.user_id = r.requester_id
             WHERE  r.status = ?
             ORDER  BY r.requested_at ASC',
            ['pending']
        );
    }

    /** Approved reservations (ready for assignment). */
    public function approved(): array {
        return $this->query(
            'SELECT r.*, u.full_name AS requester_name, u.department
             FROM   reservations r
             JOIN   users u ON u.user_id = r.requester_id
             WHERE  r.status = ?
             ORDER  BY r.requested_at ASC',
            ['approved']
        );
    }

    /** Create a new reservation with auto-generated reference number. */
    public function create(array $data): void {
        $refNo = 'VRS-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $this->execute(
            'INSERT INTO reservations
             (reference_no, requester_id, purpose, destination, passengers,
              departure_date, departure_time, return_date, return_time, vehicle_id, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [
                $refNo,
                $data['requester_id'],
                $data['purpose'],
                $data['destination'],
                $data['passengers'],
                $data['departure_date'],
                $data['departure_time'],
                $data['return_date'],
                $data['return_time'],
                $data['vehicle_id'] ?? null,
                'pending',
            ]
        );
    }

    /** Assign a vehicle and keep status approved. */
    public function assignVehicle(int $reservationId, int $vehicleId): void {
        $this->execute(
            'UPDATE reservations SET vehicle_id = ?, status = ? WHERE reservation_id = ?',
            [$vehicleId, 'approved', $reservationId]
        );
    }

    /** Update status (and optionally remarks). */
    public function updateStatus(int $id, string $status, ?string $remarks = null): void {
        if ($remarks !== null) {
            $this->execute(
                'UPDATE reservations SET status = ?, remarks = ? WHERE reservation_id = ?',
                [$status, $remarks, $id]
            );
        } else {
            $this->execute(
                'UPDATE reservations SET status = ? WHERE reservation_id = ?',
                [$status, $id]
            );
        }
    }

    /** Cancel a reservation (only if requester owns it and it's still pending). */
    public function cancel(int $id, int $requesterId): void {
        $this->execute(
            'UPDATE reservations SET status = ?
             WHERE  reservation_id = ? AND requester_id = ? AND status = ?',
            ['cancelled', $id, $requesterId, 'pending']
        );
    }



    public function countByStatus(string $status): int {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS n FROM reservations WHERE status = ?', [$status]
        );
        return (int)($row['n'] ?? 0);
    }

    public function countAll(): int {
        $row = $this->queryOne('SELECT COUNT(*) AS n FROM reservations');
        return (int)($row['n'] ?? 0);
    }

    /** Reservations for a specific month. */
    public function byMonth(int $year, int $month): array {
        return $this->query(
            'SELECT r.*, u.full_name AS requester_name, v.make_model, v.plate_number
             FROM   reservations r
             JOIN   users    u ON u.user_id    = r.requester_id
             LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE  YEAR(r.departure_date) = ? AND MONTH(r.departure_date) = ?
             ORDER  BY r.departure_date',
            [$year, $month]
        );
    }

    /** Active trips (approved / dispatched) for a specific driver via dispatch_logs. */
    public function activeForDriver(int $driverId): array {
        return $this->query(
            'SELECT r.*, v.make_model, v.plate_number,
                    dl.start_mileage
             FROM   reservations r
             JOIN   dispatch_logs dl ON dl.reservation_id = r.reservation_id
             JOIN   vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE  dl.driver_id = ?
               AND  r.status IN (?,?)
             ORDER  BY r.departure_date ASC',
            [$driverId, 'approved', 'dispatched']
        );
    }
}
