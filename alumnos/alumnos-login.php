<?php
require_once '../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Rate limiting (5 intentos / 10 minutos por IP) ──────────────────────────
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ahora = time();
$ventana = 600; // 10 minutos
$maxIntentos = 5;

if (!isset($_SESSION['rl_alumnos'])) { $_SESSION['rl_alumnos'] = []; }
// Limpiar IPs con ventana expirada
foreach ($_SESSION['rl_alumnos'] as $k => $d) {
    if ($ahora - $d['primero'] >= $ventana) { unset($_SESSION['rl_alumnos'][$k]); }
}
if (!isset($_SESSION['rl_alumnos'][$ip])) {
    $_SESSION['rl_alumnos'][$ip] = ['count' => 0, 'primero' => $ahora];
}
if ($_SESSION['rl_alumnos'][$ip]['count'] >= $maxIntentos) {
    $espera = $ventana - ($ahora - $_SESSION['rl_alumnos'][$ip]['primero']);
    http_response_code(429);
    echo json_encode(['error' => "Demasiados intentos fallidos. Espera " . ceil($espera / 60) . " min antes de intentar de nuevo."], JSON_UNESCAPED_UNICODE);
    exit;
}
// ────────────────────────────────────────────────────────────────────────────

try {
$conn = getDBConnection();

    if ($conn->connect_error) {
        throw new Exception("Error de conexión a la base de datos");
    }

    $conn->set_charset("utf8mb4");
    date_default_timezone_set('America/Mexico_City');

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido");
    }

    $matricula = isset($_POST["matricula"]) ? strtoupper(trim($_POST["matricula"])) : '';
    $passwordIngresada = isset($_POST["password"]) ? trim($_POST["password"]) : '';

    if (empty($matricula)) {
        echo json_encode([
            'error' => 'La matrícula es obligatoria'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($passwordIngresada)) {
        echo json_encode([
            'error' => 'La contraseña es obligatoria'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Consulta con los nombres correctos de columnas
    $sql = "SELECT a.*, c.nombre_carrera, f.nombre_facultad 
            FROM alumnos a
            LEFT JOIN carrera c ON a.id_carrera = c.id_carrera
            LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
            WHERE a.matricula_alum = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la consulta preparada: " . $conn->error);
    }

    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'error' => 'Matrícula no encontrada. Verifica tus datos.'
        ], JSON_UNESCAPED_UNICODE);
        $stmt->close();
        $conn->close();
        exit;
    }

    $alumno = $result->fetch_assoc();
    $stmt->close();

    // Verificar si tiene contraseña registrada
    if (empty($alumno['password'])) {
        echo json_encode([
            'error' => 'No tienes contraseña registrada. Contacta al administrador.'
        ], JSON_UNESCAPED_UNICODE);
        $conn->close();
        exit;
    }

    // Verificar la contraseña con password_verify
    if (password_verify($passwordIngresada, $alumno['password'])) {
        // Contraseña correcta — limpiar conteo de intentos
        unset($_SESSION['rl_alumnos'][$ip]);
        session_regenerate_id(true);
        
        $_SESSION['alumno'] = [
            'matricula' => $alumno['matricula_alum'],
            'nombre'    => $alumno['nombres_alum'],
            'apepa'     => $alumno['ape_paterno_alum'],
            'apema'     => $alumno['ape_materno_alum'],
            'edad'      => $alumno['edad_alum'],
            'sexo'      => $alumno['sexo'],
            'correo'    => $alumno['correo_alum'],
            'fecha'     => $alumno['fe_nacimiento_alum'],
            'id_carrera' => $alumno['id_carrera'],
            'id_facultad' => $alumno['id_facultad'],
            'generacion' => $alumno['generacion'],
            'nom_carrera' => $alumno['nombre_carrera'],
            'nom_facultad' => $alumno['nombre_facultad']
        ];

        $_SESSION['loggedin'] = true;
        $_SESSION['tipo_usuario'] = 'alumno';

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirect' => 'inicio.php',
            'nombre_completo' => $alumno['nombres_alum'] . ' ' . $alumno['ape_paterno_alum']
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        // Contraseña incorrecta — incrementar contador
        $_SESSION['rl_alumnos'][$ip]['count']++;
        $restantes = $maxIntentos - $_SESSION['rl_alumnos'][$ip]['count'];
        $msgExtra  = $restantes > 0 ? " ($restantes intentos restantes)" : '';
        echo json_encode([
            'error' => 'Contraseña incorrecta. Verifica tus datos.' . $msgExtra
        ], JSON_UNESCAPED_UNICODE);
    }

    $conn->close();

} catch (Exception $e) {
    error_log("Error en login alumno: " . $e->getMessage());
    
    echo json_encode([
        'error' => 'Error del servidor. Por favor intenta más tarde.'
        // Quitar 'debug' en producción para no mostrar errores internos
        // ,'debug' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>