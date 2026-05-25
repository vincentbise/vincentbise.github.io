<?php
abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? null;
        $stmt = $this->db->prepare('SET @app_user_id = ?');
        $stmt->execute([$userId]);
    }

    protected function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    protected function execute(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    protected function lastId(): string {
        return $this->db->lastInsertId();
    }
}