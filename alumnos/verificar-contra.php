<?php
header('Content-Type: application/json; charset=utf-8');

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Conexión fallida']));
}

$conn->set_charset("utf8mb4");

// Cambiar esta matrícula por la que quieres verificar
$matricula_prueba = "191263"; // <-- CAMBIA ESTO

$sql = "SELECT matricula_alum, nombres_alum, fe_nacimiento_alum, password FROM alumnos WHERE matricula_alum = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula_prueba);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $alumno = $result->fetch_assoc();
    
    // Probar diferentes formatos
    $fecha_nacimiento = $alumno['fe_nacimiento_alum'];
    $password_hash = $alumno['password'];
    
    // Extraer año, mes, día de la fecha
    $fecha_obj = new DateTime($fecha_nacimiento);
    $anio = $fecha_obj->format('Y');
    $mes = $fecha_obj->format('m');
    $dia = $fecha_obj->format('d');
    
    // Probar diferentes formatos comunes
    $formatos_prueba = [
        "Salud$anio/$mes/$dia",      // Salud2000/07/24
        "Salud$anio$mes$dia",         // Salud20000724
        "Salud$anio-$mes-$dia",       // Salud2000-07-24
        "salud$anio/$mes/$dia",       // salud2000/07/24 (minúscula)
        "SALUD$anio/$mes/$dia",       // SALUD2000/07/24 (mayúscula)
    ];
    
    $resultados = [];
    foreach ($formatos_prueba as $formato) {
        $coincide = password_verify($formato, $password_hash);
        $resultados[] = [
            'formato' => $formato,
            'coincide' => $coincide ? 'SÍ ✓' : 'NO ✗'
        ];
    }
    
    echo json_encode([
        'matricula' => $alumno['matricula_alum'],
        'nombre' => $alumno['nombres_alum'],
        'fecha_nacimiento' => $fecha_nacimiento,
        'hash_almacenado' => substr($password_hash, 0, 30) . '...',
        'pruebas' => $resultados
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} else {
    echo json_encode([
        'error' => 'Matrícula no encontrada',
        'matricula_buscada' => $matricula_prueba
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
?>