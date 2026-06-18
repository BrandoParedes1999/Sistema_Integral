<?php
header("Content-Type: application/json; charset=utf-8");

require_once '../config/config.php';

try {
    $database = new Database();
    $conexion = $database->connect();

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        throw new Exception("JSON inválido.");
    }

    $idMenuAlumno = intval($datos['id_menu_alumno'] ?? 0);
    $idDia = intval($datos['id_dia'] ?? 0);
    $idTiempo = intval($datos['id_tiempo'] ?? 0);
    $idPlatillo = intval($datos['id_platillo'] ?? 0);

    if ($idMenuAlumno <= 0 || $idDia <= 0 || $idTiempo <= 0 || $idPlatillo <= 0) {
        throw new Exception("Datos incompletos para guardar.");
    }

    $sql = "
        INSERT INTO pm_menu_detalle 
        (id_menu_alumno, id_dia, id_tiempo, id_platillo)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            id_platillo = VALUES(id_platillo),
            fecha_registro = CURRENT_TIMESTAMP
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiii", $idMenuAlumno, $idDia, $idTiempo, $idPlatillo);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "mensaje" => "Platillo guardado correctamente."
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}