<?php
// ============================================================
// ULMS — Database Connection (PDO Singleton)
// Core Layer: core/Database.php
// ============================================================

require_once __DIR__ . '/../config/config.php';

class Database {

    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct() {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode([
                'error'   => true,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]));
        }
    }

    /** Return the singleton instance */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Return the PDO connection */
    public function getConnection(): PDO {
        return $this->pdo;
    }

    /** Convenience: prepare a statement */
    public function prepare(string $sql): PDOStatement {
        return $this->pdo->prepare($sql);
    }

    /** Convenience: last insert id */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    /** Begin transaction */
    public function beginTransaction(): void {
        $this->pdo->beginTransaction();
    }

    /** Commit transaction */
    public function commit(): void {
        $this->pdo->commit();
    }

    /** Rollback transaction */
    public function rollBack(): void {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /** Prevent cloning of the singleton */
    private function __clone() {}
}
