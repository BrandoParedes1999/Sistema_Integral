<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ../login.php'); exit(); }

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$busqueda = trim($_GET['busqueda'] ?? '');
$facultad = (int)($_GET['facultad'] ?? 0);
$sexo     = trim($_GET['sexo']     ?? '');

$sql    = "SELECT a.matricula_alum, a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum,
                  a.sexo, a.correo_alum, a.fe_nacimiento_alum, a.generacion,
                  f.nombre_facultad, c.nombre_carrera,
                  a.tipo_sangre, a.nss, a.emergencia,
                  a.fecha_ingreso
           FROM alumnos a
           LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
           LEFT JOIN carrera  c ON a.id_carrera  = c.id_carrera
           WHERE 1=1";
$params = []; $types = '';

if ($busqueda !== '') {
    $like = '%'.$busqueda.'%';
    $sql .= " AND (a.matricula_alum LIKE ? OR a.nombres_alum LIKE ? OR a.ape_paterno_alum LIKE ? OR a.ape_materno_alum LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $types .= 'ssss';
}
if ($facultad > 0) { $sql .= " AND a.id_facultad = ?"; $params[] = $facultad; $types .= 'i'; }
if ($sexo !== '')  { $sql .= " AND a.sexo = ?";        $params[] = $sexo;     $types .= 's'; }
$sql .= " ORDER BY a.matricula_alum ASC";

$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();

$filename = 'alumnos_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM para Excel

fputcsv($out, ['Matrícula','Nombre','Ap. Paterno','Ap. Materno','Sexo','Correo','Fecha Nacimiento',
               'Generación','Facultad','Carrera','Tipo Sangre','NSS','Tel. Emergencia','Fecha Registro']);

while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['matricula_alum'], $row['nombres_alum'], $row['ape_paterno_alum'], $row['ape_materno_alum'],
        $row['sexo'], $row['correo_alum'], $row['fe_nacimiento_alum'], $row['generacion'],
        $row['nombre_facultad'], $row['nombre_carrera'],
        $row['tipo_sangre'], $row['nss'], $row['emergencia'],
        $row['fecha_ingreso'] ? date('d/m/Y', strtotime($row['fecha_ingreso'])) : '',
    ]);
}
fclose($out);
$stmt->close(); $conn->close();
?>
