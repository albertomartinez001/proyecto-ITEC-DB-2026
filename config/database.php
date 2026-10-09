<?php
/**
 * ITEC Academy - Conexión a Oracle 19c
 */

class Database {
    private static $instance = null;
    private $conn;

    private $host     = 'localhost';
    private $port     = '1521';
    private $service = 'orcl';         // el PDB que abrimos
    private $username = 'system';
    private $password = 'Oracle1234';
    private $charset  = 'AL32UTF8';

    private function __construct() {
        $dsn = "oci:dbname=//{$this->host}:{$this->port}/{$this->service};charset={$this->charset}";
        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_CASE               => PDO::CASE_LOWER,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Error de conexión a Oracle: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}