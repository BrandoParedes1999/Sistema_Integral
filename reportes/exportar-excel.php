<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ../login.php'); exit(); }

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$busqueda = trim($_GET['busqueda'] ?? '');
$facultad = (int)($_GET['facultad'] ?? 0);
$sexo     = trim($_GET['sexo']     ?? '');

$sql    = "SELECT a.matricula_alum,
                  CONCAT(a.nombres_alum,' ',a.ape_paterno_alum,' ',a.ape_materno_alum) AS nombre_completo,
                  a.sexo, f.nombre_facultad,
                  d.total_depresion, d.total_ansiedad, d.total_estres, d.total_general,
                  edv.total AS peps_total, edv.estado_saludable
           FROM alumnos a
           LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
           LEFT JOIN (SELECT matricula_alum, total_depresion, total_ansiedad, total_estres, total_general,
                             MAX(id_cuestionario) AS max_id FROM dass GROUP BY matricula_alum) d
                  ON a.matricula_alum = d.matricula_alum
           LEFT JOIN (SELECT matricula_alum, total, estado_saludable,
                             MAX(id_cuestionario) AS max_id FROM estilo_de_vida GROUP BY matricula_alum) edv
                  ON a.matricula_alum = edv.matricula_alum
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

function sevLabel($v, $tipo) {
    if ($v === null) return '—';
    if ($tipo === 'dep') {
        if ($v <= 4) return 'Normal'; if ($v <= 6) return 'Leve';
        if ($v <= 10) return 'Moderado'; if ($v <= 13) return 'Severo';
        return 'Extremadamente Severo';
    }
    if ($tipo === 'ans') {
        if ($v <= 3) return 'Normal'; if ($v <= 4) return 'Leve';
        if ($v <= 7) return 'Moderado'; if ($v <= 9) return 'Severo';
        return 'Extremadamente Severo';
    }
    if ($v <= 7) return 'Normal'; if ($v <= 9) return 'Leve';
    if ($v <= 12) return 'Moderado'; if ($v <= 16) return 'Severo';
    return 'Extremadamente Severo';
}

$filename = 'resultados_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($out, ['Matrícula','Nombre Completo','Sexo','Facultad',
               'Depresión (pts)','Nivel Depresión',
               'Ansiedad (pts)','Nivel Ansiedad',
               'Estrés (pts)','Nivel Estrés',
               'Total DASS','PEPS-1 (pts)','Estado Saludable']);

while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['matricula_alum'], $row['nombre_completo'], $row['sexo'], $row['nombre_facultad'],
        $row['total_depresion'] ?? '—', sevLabel($row['total_depresion'], 'dep'),
        $row['total_ansiedad']  ?? '—', sevLabel($row['total_ansiedad'],  'ans'),
        $row['total_estres']    ?? '—', sevLabel($row['total_estres'],    'est'),
        $row['total_general']   ?? '—',
        $row['peps_total']      ?? '—', $row['estado_saludable'] ?? '—',
    ]);
}
fclose($out);
$stmt->close(); $conn->close();
?>
