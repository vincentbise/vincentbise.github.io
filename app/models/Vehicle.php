<?php
// Vehicle Model
class Vehicle extends Model {

    public function all(): array {
        return $this->query(
            'SELECT v.*, u.full_name AS assigned_driver_name
             FROM   vehicles v
             LEFT JOIN drivers d ON d.driver_id = v.assigned_driver_id
             LEFT JOIN users u ON u.user_id = d.user_id
             ORDER  BY v.make_model'
        );
    }

    /** Available vehicles for assignment (with assigned driver info). */
    public function available(): array {
        return $this->query(
            'SELECT v.*, u.full_name AS assigned_driver_name, d.driver_id AS assigned_driver_db_id,
                    d.license_no AS assigned_driver_license
             FROM   vehicles v
             LEFT JOIN drivers d ON d.driver_id = v.assigned_driver_id
             LEFT JOIN users u ON u.user_id = d.user_id
             WHERE  v.status = ?
             ORDER  BY v.make_model',
            ['available']
        );
    }

    /** Available vehicles filtered by type and minimum capacity. */
    public function availableByFilter(?string $type = null, int $minCapacity = 1): array {
        $sql = 'SELECT v.*, u.full_name AS assigned_driver_name
                FROM   vehicles v
                LEFT JOIN drivers d ON d.driver_id = v.assigned_driver_id
                LEFT JOIN users u ON u.user_id = d.user_id
                WHERE  v.status = ?
                  AND  v.capacity >= ?';
        $params = ['available', $minCapacity];

        if ($type && $type !== 'all') {
            $sql .= ' AND v.vehicle_type = ?';
            $params[] = $type;
        }

        $sql .= ' ORDER BY v.capacity ASC, v.make_model';

        return $this->query($sql, $params);
    }

    public function findById(int $id): ?array {
        return $this->queryOne('SELECT * FROM vehicles WHERE vehicle_id = ?', [$id]);
    }

    public function create(array $data): void {
        $this->execute(
            'INSERT INTO vehicles (plate_number, make_model, vehicle_type, capacity, year, color, assigned_driver_id, notes)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $data['plate_number'],
                $data['make_model'],
                $data['vehicle_type'] ?? null,
                $data['capacity']     ?? 1,
                $data['year']         ?? null,
                $data['color']        ?? null,
                $data['assigned_driver_id'] ?? null,
                $data['notes']        ?? null,
            ]
        );
    }

    public function update(int $id, array $data): void {
        $this->execute(
            'UPDATE vehicles
             SET plate_number=?, make_model=?, vehicle_type=?,
                 capacity=?, year=?, color=?, status=?, assigned_driver_id=?, notes=?
             WHERE vehicle_id=?',
            [
                $data['plate_number'],
                $data['make_model'],
                $data['vehicle_type'] ?? null,
                $data['capacity']     ?? 1,
                $data['year']         ?? null,
                $data['color']        ?? null,
                $data['status']       ?? 'available',
                $data['assigned_driver_id'] ?? null,
                $data['notes']        ?? null,
                $id,
            ]
        );
    }

    public function setStatus(int $id, string $status): void {
        $this->execute(
            'UPDATE vehicles SET status = ? WHERE vehicle_id = ?',
            [$status, $id]
        );
    }

    public function countByStatus(string $status): int {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS n FROM vehicles WHERE status = ?', [$status]
        );
        return (int)($row['n'] ?? 0);
    }

    public function countAll(): int {
        $row = $this->queryOne('SELECT COUNT(*) AS n FROM vehicles');
        return (int)($row['n'] ?? 0);
    }
}
