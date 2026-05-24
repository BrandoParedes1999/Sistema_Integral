<?php

require_once '../config/config.php';

$tipo = $_GET["tipo"] ?? '';

if ($tipo == "facultades") {
    $sql = "SELECT id_facultad, nombre_facultad FROM facultad";
} elseif ($tipo == "carreras" && isset($_GET["idfacultad"])) {
    $idfacultad = intval($_GET["idfacultad"]);
    $sql = "SELECT id_carrera, nombre_carrera FROM carrera WHERE id_facultad = $idfacultad";
} else {
    echo json_encode([]);
    exit;
}

$result = $conn->query($sql) or die(json_encode(["error" => $conn->error]));
$datos = [];

while ($fila = $result->fetch_assoc()) {
    $datos[] = $fila;
}

echo json_encode($datos);
?>