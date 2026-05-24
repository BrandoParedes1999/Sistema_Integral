<?php
// ============================================================
// CONFIGURACIÓN DE BASE DE DATOS
// Cambia solo estos valores para migrar a otra BD
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'unisalud');

class Database {
    private $conn;

    public function connect() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
        return $this->conn;
    }

    public function getConnection() {
        if (!$this->conn || !$this->conn->ping()) {
            return $this->connect();
        }
        return $this->conn;
    }

    public function disconnect() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

function getDBConnection() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db->connect();
}
