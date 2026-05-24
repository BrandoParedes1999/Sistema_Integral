<?php
// config.php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "4528622_pisi";
    private $conn;
    
    public function __construct() {
        // Constructor vacío o puedes inicializar aquí si quieres
    }
    
    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8");
        
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

// Crear una instancia global si lo prefieres
function getDBConnection() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db->connect();
}
?>