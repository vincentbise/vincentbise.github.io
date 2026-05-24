<?php
// User Model
class User extends Model {

    public function all(): array {
        return $this->query('SELECT * FROM users ORDER BY created_at DESC');
    }

    public function findById(int $id): ?array {
        return $this->queryOne('SELECT * FROM users WHERE user_id = ?', [$id]);
    }

    public function findByUsername(string $username): ?array {
        return $this->queryOne('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function create(array $data): void {
        // Auto-generate employee_id from auto-increment sequence
        $db = Database::getInstance();
        $row = $db->query("SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'")->fetch();
        $nextId = (int)($row['next_id'] ?? 1);
        $employeeId = 'USR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $this->execute(
            'INSERT INTO users
             (employee_id, full_name, email, username, password_hash, role, department, contact_no)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $employeeId,
                $data['full_name'],
                $data['email'],
                $data['username'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['role']       ?? 'requester',
                $data['department'] ?? null,
                $data['contact_no'] ?? null,
            ]
        );
    }

    public function update(int $id, array $data): void {
        $sql = 'UPDATE users SET full_name=?, email=?, role=?, department=?, contact_no=?';
        $params = [
            $data['full_name'],
            $data['email'],
            $data['role'],
            $data['department'] ?? null,
            $data['contact_no'] ?? null,
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $sql .= ', password_hash=?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= ' WHERE user_id=?';
        $params[] = $id;

        $this->execute($sql, $params);
    }

    /** Toggle the is_active flag. */
    public function toggleActive(int $id): void {
        $this->execute(
            'UPDATE users SET is_active = NOT is_active WHERE user_id = ?',
            [$id]
        );
    }

    public function countAll(): int {
        $row = $this->queryOne('SELECT COUNT(*) AS n FROM users');
        return (int)($row['n'] ?? 0);
    }

    public function countByRole(string $role): int {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS n FROM users WHERE role = ? AND is_active = 1',
            [$role]
        );
        return (int)($row['n'] ?? 0);
    }
}
