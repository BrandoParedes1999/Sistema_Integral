<?php
session_start();
if (!isset($_SESSION['usuario'])) { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit(); }

require_once '../config/config.php';
$conn = getDBConnection();

if (isset($_GET['matricula_alum'])) {
    $matricula = $_GET['matricula_alum'];

    $query = "SELECT * FROM historial_alumnos WHERE matricula_alum = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(new stdClass()); // objeto vacío en lugar de mensaje de error
}


    $stmt->close();
    $conn->close();
} else {
    echo json_encode(new stdClass());
}
?>