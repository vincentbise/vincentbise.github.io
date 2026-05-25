<?php
class User extends Model {

    public function all(): array {
        return $this->query('SELECT * FROM users ORDER BY user_id ASC');
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

    public function create(array $data): int {
        $this->execute(
            'INSERT INTO users
             (full_name, email, username, password_hash, role, department, contact_no)
             VALUES (?,?,?,?,?,?,?)',
            [
                $data['full_name'],
                $data['email'],
                $data['username'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['role']       ?? 'requester',
                $data['department'] ?? null,
                $data['contact_no'] ?? null,
            ]
        );

        return (int)$this->lastId();
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

        if (!empty($data['password'])) {
            $sql .= ', password_hash=?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= ' WHERE user_id=?';
        $params[] = $id;

        $this->execute($sql, $params);
    }

    public function updateProfile(int $id, array $data): void {
        $sql = 'UPDATE users SET full_name=?, email=?, department=?, contact_no=?';
        $params = [
            $data['full_name'],
            $data['email'],
            $data['department'] ?? null,
            $data['contact_no'] ?? null,
        ];

        if (!empty($data['password'])) {
            $sql .= ', password_hash=?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= ' WHERE user_id=?';
        $params[] = $id;

        $this->execute($sql, $params);
    }

    public function toggleActive(int $id): void {
        $this->execute(
            'UPDATE users SET is_active = NOT is_active WHERE user_id = ?',
            [$id]
        );
    }

    public function delete(int $id): void {
        $this->execute('DELETE FROM users WHERE user_id = ?', [$id]);
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