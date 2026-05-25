<?php
class Reservation extends Model {

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

    public function pending(): array {
        return $this->query(
            'SELECT r.*, u.full_name AS requester_name, u.department,
                    v.make_model, v.plate_number, v.assigned_driver_id
             FROM   reservations r
             JOIN   users u ON u.user_id = r.requester_id
             LEFT JOIN vehicles v ON v.vehicle_id = r.vehicle_id
             WHERE  r.status = ?
             ORDER  BY r.requested_at ASC',
            ['pending']
        );
    }

    public function oldestPendingId(): ?int {
        $row = $this->queryOne(
            'SELECT reservation_id
             FROM   reservations
             WHERE  status = ?
             ORDER  BY requested_at ASC, reservation_id ASC
             LIMIT  1',
            ['pending']
        );
        return $row ? (int)$row['reservation_id'] : null;
    }

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

    public function create(array $data): void {
        $stmt = $this->db->prepare(
            'CALL sp_create_reservation(?,?,?,?,?,?,?,?,?,?,@o_reservation_id,@o_reference_no)'
        );
        $stmt->execute([
            $data['requester_id'],
            $data['purpose'],
            $data['destination'],
            $data['passengers'],
            $data['departure_date'],
            $data['departure_time'],
            $data['return_date'],
            $data['return_time'],
            $data['vehicle_id'] ?? null,
            $data['requester_remarks'] ?? null,
        ]);

        $this->queryOne('SELECT @o_reservation_id, @o_reference_no');
    }

    public function assignVehicle(int $reservationId, int $vehicleId): void {
        $this->execute(
            'UPDATE reservations SET vehicle_id = ?, status = ? WHERE reservation_id = ?',
            [$vehicleId, 'approved', $reservationId]
        );
    }

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

    public function hasVehicleOverlap(
        int $vehicleId,
        string $startDate,
        string $startTime,
        string $endDate,
        string $endTime,
        array $statuses,
        ?int $excludeReservationId = null
    ): bool {
        if (empty($statuses)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $sql = 'SELECT COUNT(*) AS n
                FROM   reservations
                WHERE  vehicle_id = ?
                  AND  status IN (' . $placeholders . ')
                  AND  CONCAT(departure_date, " ", departure_time) < ?
                  AND  CONCAT(return_date, " ", return_time) > ?';

        $start = $startDate . ' ' . $startTime;
        $end   = $endDate . ' ' . $endTime;
        $params = array_merge([$vehicleId], $statuses, [$end, $start]);

        if ($excludeReservationId !== null) {
            $sql .= ' AND reservation_id <> ?';
            $params[] = $excludeReservationId;
        }

        $row = $this->queryOne($sql, $params);
        return (int)($row['n'] ?? 0) > 0;
    }

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

    public function dispatchedWindowsForVehicle(int $vehicleId): array {
        return $this->query(
            'SELECT reference_no, departure_date, departure_time, return_date, return_time
             FROM   reservations
             WHERE  vehicle_id = ?
               AND  status = ?
             ORDER  BY departure_date ASC, departure_time ASC',
            [$vehicleId, 'dispatched']
        );
    }
}