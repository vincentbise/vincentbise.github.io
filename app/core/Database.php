<?php
class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                self::logError($e->getMessage());
                http_response_code(500);
                die(json_encode(['error' => 'Database connection failed.']));
            }
        }
        return self::$instance;
    }

    private static function logError(string $msg): void {
        if (defined('LOG_PATH')) {
            $line = '[' . date('Y-m-d H:i:s') . '] DB ERROR: ' . $msg . PHP_EOL;
            file_put_contents(LOG_PATH, $line, FILE_APPEND | LOCK_EX);
        }
    }
    private function __construct() {}
    private function __clone()     {}
}