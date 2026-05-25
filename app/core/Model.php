<?php
abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $userId = $_SESSION['user_id'] ?? null;
        $stmt = $this->db->prepare('SET @app_user_id = ?');
        $stmt->execute([$userId]);
    }

    /** Execute a prepared statement and return all rows. */
    protected function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Execute a prepared statement and return a single row. */
    protected function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Execute INSERT / UPDATE / DELETE and return affected rows. */
    protected function execute(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Return the last inserted ID. */
    protected function lastId(): string {
        return $this->db->lastInsertId();
    }
}