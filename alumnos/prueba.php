
<?php
// Archivo de prueba para debuggear el login
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json; charset=utf-8');

// Log de lo que llega
$datos_recibidos = [
    'metodo' => $_SERVER["REQUEST_METHOD"],
    'post_data' => $_POST,
    'matricula' => isset($_POST["matricula"]) ? $_POST["matricula"] : 'NO RECIBIDA',
    'password' => isset($_POST["password"]) ? 'RECIBIDA' : 'NO RECIBIDA'
];

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode([
        'error' => 'Error de conexión',
        'detalle' => $conn->connect_error,
        'datos_recibidos' => $datos_recibidos
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn->set_charset("utf8mb4");

// Si es POST, intentar buscar un alumno de prueba
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = isset($_POST["matricula"]) ? strtoupper(trim($_POST["matricula"])) : '';
    
    if (!empty($matricula)) {
        $sql = "SELECT matricula_alum, nombres_alum, password FROM alumnos WHERE matricula_alum = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $matricula);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $alumno = $result->fetch_assoc();
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Alumno encontrado',
                    'matricula' => $alumno['matricula_alum'],
                    'nombre' => $alumno['nombres_alum'],
                    'tiene_password' => !empty($alumno['password']) ? 'SÍ' : 'NO',
                    'datos_recibidos' => $datos_recibidos
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'error' => 'Matrícula no encontrada',
                    'matricula_buscada' => $matricula,
                    'datos_recibidos' => $datos_recibidos
                ], JSON_UNESCAPED_UNICODE);
            }
            $stmt->close();
        } else {
            echo json_encode([
                'error' => 'Error en prepare',
                'detalle' => $conn->error,
                'datos_recibidos' => $datos_recibidos
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode([
            'error' => 'Matrícula vacía',
            'datos_recibidos' => $datos_recibidos
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'error' => 'Método no es POST',
        'datos_recibidos' => $datos_recibidos
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>