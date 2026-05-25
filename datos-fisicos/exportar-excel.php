<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ../login.php'); exit(); }

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$sql = "SELECT a.matricula_alum,
               CONCAT(a.nombres_alum,' ',a.ape_paterno_alum,' ',a.ape_materno_alum) AS nombre_completo,
               a.sexo, f.nombre_facultad,
               dfa.peso, dfa.talla, dfa.imc, dfa.clasificacion_imc,
               dfa.tension_arterial, dfa.glucosa, dfa.colesterol, dfa.trigliceridos,
               dfa.fecha
        FROM datos_fisicos_alumnos dfa
        INNER JOIN alumnos a ON dfa.matricula_alum = a.matricula_alum
        LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
        ORDER BY dfa.fecha DESC, a.matricula_alum ASC";

$result = $conn->query($sql);

$filename = 'datos_fisicos_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($out, ['Matrícula','Nombre Completo','Sexo','Facultad',
               'Peso (kg)','Talla (cm)','IMC','Clasificación IMC',
               'Tensión Arterial','Glucosa (mg/dL)','Colesterol (mg/dL)','Triglicéridos (mg/dL)',
               'Fecha Captura']);

while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['matricula_alum'], $row['nombre_completo'], $row['sexo'], $row['nombre_facultad'],
        $row['peso'], $row['talla'], $row['imc'], $row['clasificacion_imc'],
        $row['tension_arterial'], $row['glucosa'], $row['colesterol'], $row['trigliceridos'],
        $row['fecha'] ? date('d/m/Y H:i', strtotime($row['fecha'])) : '',
    ]);
}
fclose($out);
$conn->close();
?>
