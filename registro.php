<?php
session_start();
header('Content-Type: application/json');

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['error' => "Error de conexión: " . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");
date_default_timezone_set('America/Mexico_City');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = strtoupper(trim($_POST["matricula"] ?? ''));
    $nombre = strtoupper(trim($_POST["nombre"] ?? ''));
    $apepa = strtoupper(trim($_POST["apepa"] ?? ''));
    $apema = strtoupper(trim($_POST["apema"] ?? ''));
    $edad = trim($_POST["edad"] ?? '');
    $sexo = strtoupper(trim($_POST["sexo"] ?? ''));
    $correo = strtolower(trim($_POST["correo"] ?? ''));
    $fecha = trim($_POST["fecha"] ?? '');
    $id_carrera = trim($_POST["carreras"] ?? '');
    $id_facultad = trim($_POST["facultades"] ?? '');
    $generacion = trim($_POST["generacion"] ?? '');
    $sangre = trim($_POST["sangre"] ?? '');
    $nss = trim($_POST["nss"] ?? '');
    $enfermedad = trim($_POST["enfermedad"] ?? '');
    $emergencia = trim($_POST["emergencia"] ?? '');

    if (empty($matricula) || empty($nombre) || empty($apepa) || empty($apema) || empty($sexo) || empty($fecha) || empty($id_carrera) || empty($id_facultad) || empty($generacion) || empty($sangre) || empty($emergencia)) {
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        exit;
    }


    $sql_check = "SELECT matricula_alum FROM alumnos WHERE matricula_alum = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $matricula);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo json_encode(['error' => "Bienvenido: $matricula"]);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    $sql_verificar_carrera = "SELECT 1 FROM carrera WHERE id_carrera = ? AND id_facultad = ?";
    $stmt_verificar = $conn->prepare($sql_verificar_carrera);
    $stmt_verificar->bind_param("ii", $id_carrera, $id_facultad);
    $stmt_verificar->execute();
    $stmt_verificar->store_result();

    if ($stmt_verificar->num_rows === 0) {
        echo json_encode(['error' => 'La carrera no pertenece a la facultad seleccionada.']);
        $stmt_verificar->close();
        exit;
    }
    $stmt_verificar->close();

    $passwordPlana = "Salud" . substr($fecha, 0, 10);
    $passwordHash = password_hash($passwordPlana, PASSWORD_BCRYPT);
    $fecha_actual = date('Y-m-d H:i:s');

    $sql = "INSERT INTO alumnos (matricula_alum, nombres_alum, ape_paterno_alum, ape_materno_alum, edad_alum, sexo, correo_alum, fe_nacimiento_alum, id_carrera, id_facultad, generacion, fecha_ingreso, password, tipo_sangre, nss, enfermedades, emergencia) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisssiisssssss", $matricula, $nombre, $apepa, $apema, $edad, $sexo, $correo, $fecha, $id_carrera, $id_facultad, $generacion, $fecha_actual, $passwordHash, $sangre, $nss, $enfermedad, $emergencia);

    if ($stmt->execute()) {
        // Guardar toda la info del alumno en la sesión
        $_SESSION['alumno'] = [
            'matricula' => $matricula,
            'nombre' => $nombre,
            'apepa' => $apepa,
            'apema' => $apema,
            'edad' => $edad,
            'sexo' => $sexo,
            'correo' => $correo,
            'fecha' => $fecha,
            'id_carrera' => $id_carrera,
            'id_facultad' => $id_facultad,
            'generacion' => $generacion
        ];

        $_SESSION['bienvenida'] = true;

        
        echo json_encode([
            'success' => true,
            'redirect' => 'menuAlum.php'
        ]);
    } else {
        echo json_encode(['error' => 'Error al registrar: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>