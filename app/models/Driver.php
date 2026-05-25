<?php
class Driver extends Model {

    public function all(): array {
        return $this->query(
            'SELECT d.*, u.full_name, u.email, u.contact_no
             FROM   drivers d
             JOIN   users u ON u.user_id = d.user_id
             ORDER  BY u.full_name'
        );
    }

    public function available(): array {
        return $this->query(
            'SELECT d.*, u.full_name, u.email, u.contact_no
             FROM   drivers d
             JOIN   users u ON u.user_id = d.user_id
             WHERE  d.is_available = 1
               AND  u.is_active = 1
             ORDER  BY u.full_name'
        );
    }

    public function findById(int $id): ?array {
        return $this->queryOne(
            'SELECT d.*, u.full_name, u.email
             FROM   drivers d
             JOIN   users u ON u.user_id = d.user_id
             WHERE  d.driver_id = ?', [$id]
        );
    }

    public function findByUserId(int $userId): ?array {
        return $this->queryOne(
            'SELECT d.*, u.full_name, u.email
             FROM   drivers d
             JOIN   users u ON u.user_id = d.user_id
             WHERE  d.user_id = ?', [$userId]
        );
    }

    public function setAvailability(int $driverId, bool $available): void {
        $this->execute(
            'UPDATE drivers SET is_available = ? WHERE driver_id = ?',
            [$available ? 1 : 0, $driverId]
        );
    }

    public function countAvailable(): int {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS n FROM drivers d
             JOIN users u ON u.user_id = d.user_id
             WHERE d.is_available = 1 AND u.is_active = 1'
        );
        return (int)($row['n'] ?? 0);
    }

    public function createForUser(int $userId, bool $available = true): void {
        $this->execute(
            'INSERT INTO drivers (user_id, is_available)
             VALUES (?,?)',
            [$userId, $available ? 1 : 0]
        );
    }

    public function updateByUserId(int $userId, bool $available = true): void {
        $this->execute(
            'UPDATE drivers
             SET is_available = ?
             WHERE user_id = ?',
            [$available ? 1 : 0, $userId]
        );
    }
}