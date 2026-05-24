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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = strtoupper(trim($_POST["matricula"] ?? ''));

    if (empty($matricula)) {
        echo json_encode(['error' => 'La matrícula es obligatoria']);
        exit;
    }

    $sql = "SELECT matricula_alum, nombres_alum, ape_paterno_alum, ape_materno_alum, edad_alum, sexo, correo_alum, fe_nacimiento_alum, id_carrera, id_facultad 
            FROM alumnos 
            WHERE matricula_alum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $alumno = $result->fetch_assoc();
        $_SESSION['alumno'] = [
            'matricula' => $alumno['matricula_alum'],
            'nombre' => $alumno['nombres_alum'],
            'apepa' => $alumno['ape_paterno_alum'],
            'apema' => $alumno['ape_materno_alum'],
            'edad' => $alumno['edad_alum'],
            'sexo' => $alumno['sexo'],
            'correo' => $alumno['correo_alum'],
            'fecha' => $alumno['fe_nacimiento_alum'],
            'id_carrera' => $alumno['id_carrera'],
            'id_facultad' => $alumno['id_facultad']
        ];
        $_SESSION['bienvenida'] = true;
        echo json_encode(['success' => true, 'redirect' => 'menuAlum.php']);
    } else {
        // Matrícula no encontrada (nuevo registro)
        
        // --- INICIO DE LA CORRECCIÓN ---
        // Limpiamos cualquier dato de alumno de una sesión anterior.
        // Esto previene el conflicto cuando se registra un nuevo alumno.
        unset($_SESSION['alumno']);
        unset($_SESSION['bienvenida']);
        // --- FIN DE LA CORRECCIÓN ---

        echo json_encode(['success' => false]);
    }

    $stmt->close();
}

$conn->close();
?>