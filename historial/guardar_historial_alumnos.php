<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once '../config/config.php';
$conn = getDBConnection();
if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = trim($_POST["matricula"] ?? '');
    $fecha_historial = date("Y-m-d");

    // Los checkboxes llegan como arrays; unir con coma o "ninguno" si vacío
    function obtenerFamiliares($campo) {
        if (!empty($_POST[$campo]) && is_array($_POST[$campo])) {
            return implode(', ', $_POST[$campo]);
        }
        return 'ninguno';
    }

    $sobrepeso      = obtenerFamiliares("uno1");
    $diabetes       = obtenerFamiliares("dos1");
    $hipertension   = obtenerFamiliares("tres1");
    $trigliceridos  = obtenerFamiliares("cuatro1");
    $colesterol     = obtenerFamiliares("cinco1");
    $hepatitis      = obtenerFamiliares("seis1");
    $higado_graso   = obtenerFamiliares("siete1");
    $cardiopatias   = obtenerFamiliares("ocho1");
    $nefropatias    = obtenerFamiliares("nueve1");
    $estreñimiento  = obtenerFamiliares("diez1");
    $gastritis      = obtenerFamiliares("once1");
    $colitis        = obtenerFamiliares("doce1");
    $cancer         = obtenerFamiliares("trece1");
    $otros          = obtenerFamiliares("catorce1");
    $observaciones  = trim($_POST["observaciones1"] ?? '');

    $conn->begin_transaction();

    try {
        $sql1 = "INSERT INTO historial_alumnos
                 (matricula_alum, fecha_historial, sobrepeso, diabetes, hipertension, trigliceridos, colesterol, hepatitis, higado_graso, cardiopatias, nefropatias, estreñimiento, gastritis, colitis, cancer, otros, observaciones)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sssssssssssssssss",
            $matricula, $fecha_historial,
            $sobrepeso, $diabetes, $hipertension, $trigliceridos, $colesterol,
            $hepatitis, $higado_graso, $cardiopatias, $nefropatias, $estreñimiento,
            $gastritis, $colitis, $cancer, $otros, $observaciones
        );
        $stmt1->execute();
        $stmt1->close();

        if (!empty($_POST['enfermedad']) && is_array($_POST['enfermedad'])) {
            $sql2 = "INSERT INTO patologias_alumnos (enfermedad, tratamiento, fecha, matricula_alum) VALUES (?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);

            foreach ($_POST['enfermedad'] as $index => $enfermedad) {
                $tratamiento = $_POST['tratamiento'][$index] ?? '';
                $stmt2->bind_param("ssss", $enfermedad, $tratamiento, $fecha_historial, $matricula);
                $stmt2->execute();
            }
            $stmt2->close();
        }

        $conn->commit();
        echo json_encode(["success" => "Historial y patologías guardados correctamente"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["error" => "Error al guardar: " . $e->getMessage()]);
    }
}

$conn->close();
?>
