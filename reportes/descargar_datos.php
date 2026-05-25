<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ../login.php'); exit(); }

require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$busqueda = trim($_GET['busqueda'] ?? '');
$facultad = (int)($_GET['facultad'] ?? 0);
$sexo     = trim($_GET['sexo']     ?? '');

$facRes = $conn->query("SELECT id_facultad, nombre_facultad FROM facultad ORDER BY nombre_facultad ASC");
$facultades = [];
while ($f = $facRes->fetch_assoc()) { $facultades[] = $f; }

$sql = "SELECT a.matricula_alum,
               CONCAT(a.nombres_alum,' ',a.ape_paterno_alum,' ',a.ape_materno_alum) AS nombre_completo,
               a.sexo, f.nombre_facultad,
               d.total_depresion, d.total_ansiedad, d.total_estres, d.total_general,
               edv.total AS peps_total, edv.estado_saludable
        FROM alumnos a
        LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
        LEFT JOIN (SELECT matricula_alum, total_depresion, total_ansiedad, total_estres, total_general
                   FROM dass GROUP BY matricula_alum) d ON a.matricula_alum = d.matricula_alum
        LEFT JOIN (SELECT matricula_alum, total, estado_saludable
                   FROM estilo_de_vida GROUP BY matricula_alum) edv ON a.matricula_alum = edv.matricula_alum
        WHERE 1=1";
$params = []; $types = '';
if ($busqueda !== '') {
    $like = '%'.$busqueda.'%';
    $sql .= " AND (a.matricula_alum LIKE ? OR a.nombres_alum LIKE ? OR a.ape_paterno_alum LIKE ? OR a.ape_materno_alum LIKE ?)";
    $params = array_merge($params, [$like,$like,$like,$like]); $types .= 'ssss';
}
if ($facultad > 0) { $sql .= " AND a.id_facultad = ?"; $params[] = $facultad; $types .= 'i'; }
if ($sexo !== '')  { $sql .= " AND a.sexo = ?";        $params[] = $sexo;     $types .= 's'; }
$sql .= " ORDER BY a.matricula_alum ASC LIMIT 200";
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close(); $conn->close();

function sevLabel($v, $tipo) {
    if ($v === null) return '—';
    if ($tipo === 'dep') { if ($v<=4) return 'Normal'; if ($v<=6) return 'Leve'; if ($v<=10) return 'Moderado'; if ($v<=13) return 'Severo'; return 'Ext. Severo'; }
    if ($tipo === 'ans') { if ($v<=3) return 'Normal'; if ($v<=4) return 'Leve'; if ($v<=7) return 'Moderado'; if ($v<=9) return 'Severo'; return 'Ext. Severo'; }
    if ($v<=7) return 'Normal'; if ($v<=9) return 'Leve'; if ($v<=12) return 'Moderado'; if ($v<=16) return 'Severo'; return 'Ext. Severo';
}
function sevClass($lbl) {
    return match($lbl) { 'Normal'=>'sev-normal','Leve'=>'sev-leve','Moderado'=>'sev-mod','Severo'=>'sev-sev', default=>'sev-ext' };
}
$csvParams = http_build_query(array_filter(['busqueda'=>$busqueda,'facultad'=>$facultad?:null,'sexo'=>$sexo]));
?>
<!doctype html>
<html lang="es">
<head>
  <title>Descarga de Datos · UniSalud</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
  <link rel="stylesheet" href="../css/menu.css">
  <style>
    body { font-family:'Inter',sans-serif; background:#f1f5f9; }
    .page { max-width:1200px; margin:0 auto; padding:1.5rem 1.25rem 3rem; }
    .card-box { background:#fff; border-radius:14px; box-shadow:0 4px 14px rgba(0,0,0,.07); padding:1.5rem; margin-bottom:1.5rem; }
    .page-title { font-size:1.25rem; font-weight:700; color:#003da5; margin-bottom:.2rem; }
    .page-sub   { font-size:.82rem; color:#6b7280; }
    .filter-row { display:flex; gap:.75rem; flex-wrap:wrap; align-items:flex-end; }
    .filter-row input, .filter-row select { border:1px solid #e2e8f0; border-radius:8px; padding:.45rem .8rem; font-size:.85rem; }
    .btn-prim { background:#003da5; color:#fff; border:none; border-radius:8px; padding:.5rem 1.2rem; font-size:.85rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; }
    .btn-prim:hover { background:#002a70; }
    .btn-csv  { background:#10b981; color:#fff; border:none; border-radius:8px; padding:.5rem 1.2rem; font-size:.85rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; text-decoration:none; }
    .btn-csv:hover { background:#059669; color:#fff; }
    .tbl { width:100%; border-collapse:collapse; font-size:.8rem; }
    .tbl th { background:#f8fafc; color:#374151; font-weight:600; padding:.6rem .75rem; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
    .tbl td { padding:.55rem .75rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .tbl tr:hover td { background:#f8fafc; }
    .sev-badge  { display:inline-block; font-size:.68rem; font-weight:700; padding:.15rem .5rem; border-radius:99px; }
    .sev-normal { background:#ecfdf5; color:#065f46; }
    .sev-leve   { background:#eff6ff; color:#1e40af; }
    .sev-mod    { background:#fffbeb; color:#92400e; }
    .sev-sev    { background:#fef2f2; color:#991b1b; }
    .sev-ext    { background:#fce7f3; color:#9d174d; }
    .count-badge{ background:#003da5; color:#fff; font-size:.72rem; font-weight:700; padding:.2rem .6rem; border-radius:99px; }
    .empty-state{ text-align:center; padding:3rem 1rem; color:#9ca3af; }
  </style>
</head>
<body>
<div id="admin-sidebar-container"></div>
<div class="page">
  <div class="card-box">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
      <div>
        <div class="page-title"><i class="bi bi-download"></i> Descarga de Datos</div>
        <div class="page-sub">Resultados de alumnos — DASS-21 y Estilos de Vida</div>
      </div>
      <a href="exportar-excel.php?<?= htmlspecialchars($csvParams) ?>" class="btn-csv">
        <i class="bi bi-file-earmark-spreadsheet"></i> Descargar CSV completo
      </a>
    </div>

    <form method="GET" class="filter-row" style="margin-bottom:1rem;">
      <input type="text" name="busqueda" placeholder="Buscar matrícula o nombre…" value="<?= htmlspecialchars($busqueda) ?>" style="flex:1;min-width:200px;">
      <select name="facultad">
        <option value="">Todas las facultades</option>
        <?php foreach ($facultades as $fac): ?>
          <option value="<?= $fac['id_facultad'] ?>" <?= $facultad==(int)$fac['id_facultad']?'selected':'' ?>>
            <?= htmlspecialchars($fac['nombre_facultad']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select name="sexo">
        <option value="">Ambos sexos</option>
        <option value="Masculino" <?= $sexo==='Masculino'?'selected':'' ?>>Masculino</option>
        <option value="Femenino"  <?= $sexo==='Femenino' ?'selected':'' ?>>Femenino</option>
      </select>
      <button type="submit" class="btn-prim"><i class="bi bi-search"></i> Filtrar</button>
      <?php if ($busqueda||$facultad||$sexo): ?>
        <a href="descargar_datos.php" style="font-size:.82rem;color:#6b7280;align-self:center;">Limpiar filtros</a>
      <?php endif; ?>
    </form>

    <div style="margin-bottom:.75rem;">
      <span class="count-badge"><?= count($filas) ?></span>
      <span style="font-size:.8rem;color:#6b7280;margin-left:.4rem;">
        resultados<?= count($filas)===200?' (máx. 200 — usa el botón CSV para el listado completo)':'' ?>
      </span>
    </div>

    <?php if (empty($filas)): ?>
      <div class="empty-state"><i class="bi bi-inbox" style="font-size:2rem;"></i><p>No se encontraron resultados.</p></div>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="tbl">
        <thead>
          <tr>
            <th>Matrícula</th><th>Nombre</th><th>Sexo</th><th>Facultad</th>
            <th>Depresión</th><th>Ansiedad</th><th>Estrés</th><th>Total DASS</th>
            <th>PEPS-1</th><th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filas as $row):
            $lDep = sevLabel($row['total_depresion'], 'dep');
            $lAns = sevLabel($row['total_ansiedad'],  'ans');
            $lEst = sevLabel($row['total_estres'],    'est');
            $saludable = $row['estado_saludable'];
            $estadoHtml = $saludable
              ? '<span class="sev-badge '.($saludable==='Saludable'?'sev-normal':'sev-mod').'">'.htmlspecialchars($saludable).'</span>'
              : '—';
          ?>
          <tr>
            <td><a href="../gestion-alumnos/expediente.html?matricula_alum=<?= htmlspecialchars($row['matricula_alum']) ?>" style="color:#003da5;font-weight:600;"><?= htmlspecialchars($row['matricula_alum']) ?></a></td>
            <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($row['sexo'] ?? '—') ?></td>
            <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($row['nombre_facultad']??'') ?>"><?= htmlspecialchars($row['nombre_facultad'] ?? '—') ?></td>
            <td><?= $row['total_depresion'] !== null ? $row['total_depresion'] : '—' ?> <span class="sev-badge <?= sevClass($lDep) ?>"><?= $lDep ?></span></td>
            <td><?= $row['total_ansiedad']  !== null ? $row['total_ansiedad']  : '—' ?> <span class="sev-badge <?= sevClass($lAns) ?>"><?= $lAns ?></span></td>
            <td><?= $row['total_estres']    !== null ? $row['total_estres']    : '—' ?> <span class="sev-badge <?= sevClass($lEst) ?>"><?= $lEst ?></span></td>
            <td><?= $row['total_general']   !== null ? $row['total_general']   : '—' ?></td>
            <td><?= $row['peps_total']      !== null ? $row['peps_total']      : '—' ?></td>
            <td><?= $estadoHtml ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/admin-layout.js"></script>
</body>
</html>
