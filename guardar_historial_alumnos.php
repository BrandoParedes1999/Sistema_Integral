<?php
$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = trim($_POST["matricula"] ?? '');
    $fecha_historial = date("Y-m-d");

    function obtenerValor($campo) {
        return isset($_POST[$campo]) && $_POST[$campo] !== "" ? $_POST[$campo] : "ninguno";
    }

    // Datos para historial_alumnos
    $sobrepeso = obtenerValor("uno1");
    $diabetes = obtenerValor("dos1");
    $hipertension = obtenerValor("tres1");
    $trigliceridos = obtenerValor("cuatro1");
    $colesterol = obtenerValor("cinco1");
    $hepatitis = obtenerValor("seis1");
    $higado_graso = obtenerValor("siete1");
    $cardiopatias = obtenerValor("ocho1");
    $nefropatias = obtenerValor("nueve1");
    $estreñimiento = obtenerValor("diez1");
    $gastritis = obtenerValor("once1");
    $colitis = obtenerValor("doce1");
    $cancer = obtenerValor("trece1");
    $otros = obtenerValor("otra1");

    // **Iniciar transacción**
    $conn->begin_transaction();

    try {
        // **Insertar en historial_alumnos**
        $sql1 = "INSERT INTO historial_alumnos (matricula_alum, fecha_historial, sobrepeso, diabetes, hipertension, trigliceridos, colesterol, hepatitis, higado_graso, cardiopatias, nefropatias, estreñimiento, gastritis, colitis, cancer, otros) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ssssssssssssssss", $matricula, $fecha_historial, $sobrepeso, $diabetes, $hipertension, $trigliceridos, $colesterol, $hepatitis, $higado_graso, $cardiopatias, $nefropatias, $estreñimiento, $gastritis, $colitis, $cancer, $otros);
        $stmt1->execute();
        $stmt1->close();

        // **Insertar en patologias_alumnos**
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

        // **Confirmar transacción**
        $conn->commit();
        echo json_encode(["success" => "Historial y patologías guardados correctamente"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["error" => "Error al guardar: " . $e->getMessage()]);
    }
}

$conn->close();
?>