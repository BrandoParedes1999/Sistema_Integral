<?php
date_default_timezone_set('America/Mexico_City');
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/config.php';
$database = new Database();
$conn = $database->connect();

// ── Total alumnos ──
$r = $conn->query("SELECT COUNT(*) FROM alumnos");
$totalAlumnos = $r ? $r->fetch_row()[0] : 0;

// ── Expedientes completos (datos físicos + dass + estilo de vida) ──
$r = $conn->query("
    SELECT COUNT(DISTINCT a.matricula_alum)
    FROM alumnos a
    INNER JOIN datos_fisicos_alumnos dfa ON a.matricula_alum = dfa.matricula_alum
    INNER JOIN dass d ON a.matricula_alum = d.matricula_alum
    INNER JOIN estilo_de_vida edv ON a.matricula_alum = edv.matricula_alum
");
$totalCompletos = $r ? $r->fetch_row()[0] : 0;

// ── Datos físicos capturados hoy ──
$r = $conn->query("SELECT COUNT(*) FROM datos_fisicos_alumnos WHERE DATE(fecha) = CURDATE()");
$capturasHoy = $r ? $r->fetch_row()[0] : 0;

// ── Totales por módulo ──
$r = $conn->query("SELECT COUNT(*) FROM historial_alumnos");
$totalHistorial = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COUNT(*) FROM datos_fisicos_alumnos");
$totalDatosFisicos = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COUNT(*) FROM dass");
$totalDass = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COUNT(*) FROM estilo_de_vida");
$totalEstiloVida = $r ? $r->fetch_row()[0] : 0;

// ── DASS-21 pendientes ──
$dassPendientes = max(0, $totalAlumnos - $totalDass);

// ── Completitud % ──
$pctCompletos = $totalAlumnos > 0 ? round(($totalCompletos / $totalAlumnos) * 100) : 0;
$pctDass      = $totalAlumnos > 0 ? round(($totalDass      / $totalAlumnos) * 100) : 0;

// ── Últimas capturas de datos físicos ──
$ultimas = [];
$r = $conn->query("
    SELECT a.nombres_alum, a.ape_paterno_alum, dfa.fecha, dfa.matricula_alum
    FROM datos_fisicos_alumnos dfa
    INNER JOIN alumnos a ON dfa.matricula_alum = a.matricula_alum
    ORDER BY dfa.fecha DESC
    LIMIT 5
");
if ($r) { while ($row = $r->fetch_assoc()) { $ultimas[] = $row; } }

// ── Distribución por facultad (top 5) ──
$facultades = [];
$r = $conn->query("
    SELECT f.nombre_facultad, COUNT(a.matricula_alum) AS total
    FROM alumnos a
    INNER JOIN facultad f ON a.id_facultad = f.id_facultad
    GROUP BY f.id_facultad, f.nombre_facultad
    ORDER BY total DESC
    LIMIT 5
");
if ($r) { while ($row = $r->fetch_assoc()) { $facultades[] = $row; } }
$maxFacultad = !empty($facultades) ? $facultades[0]['total'] : 1;
$facColores  = ['#1d4ed8', '#7c3aed', '#16a34a', '#0891b2', '#d97706'];

// ── Admin info ──
$usuario = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT nombre_admi, rol FROM administradores WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($nombre_admi, $rol);
$stmt->fetch();
$stmt->close();
$_SESSION['nombre_admi'] = $nombre_admi;
$_SESSION['rol']         = $rol;

$partes   = explode(' ', trim($nombre_admi));
$iniciales = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));

$conn->close();

// ── Datos para Chart.js ──
$chartLabels     = json_encode(['Historial Clínico', 'Datos Físicos', 'DASS-21', 'Estilo de Vida']);
$chartCompletos  = json_encode([$totalHistorial, $totalDatosFisicos, $totalDass, $totalEstiloVida]);
$chartPendientes = json_encode([
    max(0, $totalAlumnos - $totalHistorial),
    max(0, $totalAlumnos - $totalDatosFisicos),
    $dassPendientes,
    max(0, $totalAlumnos - $totalEstiloVida),
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard – UniSalud UNACAR</title>
  <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="../css/menu.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body>

<!-- ════════════ SIDEBAR ════════════ -->
<nav id="sidebar">
  <div class="sb-brand">
    <div class="sb-brand-logo-wrap">
      <img src="../alumnos/imagenes/unisalud-sf.png" alt="UniSalud" class="sb-brand-logo">
    </div>
    <div class="sb-brand-text">
      <div class="b-name">UniSalud</div>
      <div class="b-sub">UNACAR &middot; Administración</div>
    </div>
  </div>

  <div class="sb-nav">
    <div class="sb-section">Principal</div>
    <a href="menu.php" class="sb-link active">
      <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="../historial/historial_clinico.html" class="sb-link">
      <i class="bi bi-folder2-open"></i> Historial Clínico
      <span class="sb-count"><?= $totalHistorial ?></span>
    </a>
    <a href="../datos-fisicos/datos_fisicos.html" class="sb-link">
      <i class="bi bi-activity"></i> Datos Físicos
    </a>

    <?php if ($rol === 'Administrador'): ?>
    <div class="sb-section">Alumnos</div>
    <a href="../gestion-alumnos/ListadoAlumnos.html" class="sb-link">
      <i class="bi bi-people-fill"></i> Alumnos
    </a>
    <div class="sb-sub">
      <a href="../gestion-alumnos/ListadoAlumnos.html" class="sb-sub-link">Listado</a>
    </div>

    <div class="sb-section">Reportes</div>
    <a href="../estadisticas/estadisticas.html" class="sb-link">
      <i class="bi bi-bar-chart-fill"></i> Estadísticas
    </a>
    <a href="../reportes/resultadoalumnos.html" class="sb-link">
      <i class="bi bi-graph-up-arrow"></i> Resultados
    </a>
    <a href="../credenciales/credencialesalumnos.html" class="sb-link">
      <i class="bi bi-person-badge-fill"></i> Credenciales
    </a>

    <div class="sb-section">Observatorio</div>
    <a href="../observatorio/observatorio.html" class="sb-link">
      <i class="bi bi-telescope-fill"></i> Observatorio
    </a>

    <div class="sb-section">Sistema</div>
    <a href="panel_control.php" class="sb-link">
      <i class="bi bi-shield-lock-fill"></i> Control del Sistema
    </a>
    <?php endif; ?>
  </div>

  <div class="sb-footer">
    <div class="sb-user-card">
      <div class="sb-av"><?= htmlspecialchars($iniciales) ?></div>
      <div>
        <div class="sb-u-name"><?= htmlspecialchars($nombre_admi) ?></div>
        <div class="sb-u-role"><?= htmlspecialchars($rol) ?></div>
      </div>
    </div>
  </div>
</nav>

<!-- ════════════ MAIN ════════════ -->
<div id="main-wrapper">

  <!-- TOPBAR -->
  <header id="topbar">
    <div class="tb-title">
      <img src="../alumnos/imagenes/unisalud-sf.png" alt="UniSalud" class="tb-logo"> UniSalud
    </div>
    <div class="search-box">
      <i class="bi bi-search"></i>
      <input type="text" id="buscadorAlumno" placeholder="Buscar alumno por nombre o matrícula…" />
    </div>
    <div class="tb-right">
      <?php if ($dassPendientes > 0): ?>
      <div class="tb-btn" title="<?= $dassPendientes ?> DASS-21 pendientes">
        <i class="bi bi-bell"></i>
        <span class="tb-notif"></span>
      </div>
      <?php endif; ?>
      <div class="tb-user">
        <div class="tb-av"><?= htmlspecialchars($iniciales) ?></div>
        <div>
          <div class="u-n"><?= htmlspecialchars($nombre_admi) ?></div>
          <div class="u-r"><?= htmlspecialchars($rol) ?></div>
        </div>
      </div>
      <form action="../auth/cerrar_sesion.php" method="POST" style="margin:0;">
        <button type="submit" class="btn-salir">
          <i class="bi bi-box-arrow-right"></i> Salir
        </button>
      </form>
    </div>
  </header>

  <!-- CONTENT -->
  <main id="content">

    <!-- WELCOME BANNER -->
    <div class="welcome-banner">
      <div class="wb-left">
        <h2>Bienvenido/a, <?= htmlspecialchars(explode(' ', $nombre_admi)[0]) ?></h2>
        <p>
          <i class="bi bi-calendar3 me-1"></i>
          <?= date('l, d \d\e F \d\e Y') ?> &nbsp;&middot;&nbsp; Sistema activo
        </p>
      </div>
      <div class="wb-stats">
        <div class="wb-stat">
          <div class="ws-n"><?= number_format($totalAlumnos) ?></div>
          <div class="ws-l">Total Alumnos</div>
        </div>
        <div class="wb-stat">
          <div class="ws-n"><?= $pctCompletos ?>%</div>
          <div class="ws-l">Completitud</div>
        </div>
        <div class="wb-stat">
          <div class="ws-n"><?= $capturasHoy ?></div>
          <div class="ws-l">Capturas Hoy</div>
        </div>
      </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="kpi-card blue">
          <div class="kpi-head">
            <div class="kpi-icon blue"><i class="bi bi-people-fill"></i></div>
            <span class="kpi-trend neutral"><i class="bi bi-dash"></i> Total</span>
          </div>
          <div class="kpi-num"><?= number_format($totalAlumnos) ?></div>
          <div class="kpi-lbl">Alumnos Registrados</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:100%;background:#1d4ed8;"></div></div>
          <div class="kpi-foot">
            <span class="kpi-pct" style="color:#1d4ed8;">Universo completo</span>
            <span class="kpi-sub">semestre actual</span>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="kpi-card green">
          <div class="kpi-head">
            <div class="kpi-icon green"><i class="bi bi-folder-check"></i></div>
            <span class="kpi-trend <?= $pctCompletos >= 70 ? 'up' : 'down' ?>">
              <i class="bi bi-arrow-<?= $pctCompletos >= 70 ? 'up' : 'down' ?>-short"></i><?= $pctCompletos ?>%
            </span>
          </div>
          <div class="kpi-num"><?= number_format($totalCompletos) ?></div>
          <div class="kpi-lbl">Expedientes Completos</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= $pctCompletos ?>%;background:#16a34a;"></div></div>
          <div class="kpi-foot">
            <span class="kpi-pct" style="color:#16a34a;"><?= $pctCompletos ?>% completitud</span>
            <span class="kpi-sub"><?= number_format($totalAlumnos - $totalCompletos) ?> pendientes</span>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="kpi-card amber">
          <div class="kpi-head">
            <div class="kpi-icon amber"><i class="bi bi-clipboard2-check-fill"></i></div>
            <span class="kpi-trend neutral"><i class="bi bi-dash"></i> Hoy</span>
          </div>
          <div class="kpi-num"><?= $capturasHoy ?></div>
          <div class="kpi-lbl">Datos Físicos Hoy</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= min(100, ($capturasHoy / max(1, 50)) * 100) ?>%;background:#d97706;"></div></div>
          <div class="kpi-foot">
            <span class="kpi-pct" style="color:#d97706;">Capturas del día</span>
            <span class="kpi-sub">Total: <?= number_format($totalDatosFisicos) ?></span>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="kpi-card red">
          <div class="kpi-head">
            <div class="kpi-icon red"><i class="bi bi-clipboard2-x-fill"></i></div>
            <span class="kpi-trend <?= $dassPendientes > 0 ? 'down' : 'up' ?>">
              <i class="bi bi-arrow-<?= $dassPendientes > 0 ? 'down' : 'up' ?>-short"></i>
              <?= $dassPendientes > 0 ? 'Pendientes' : 'Al día' ?>
            </span>
          </div>
          <div class="kpi-num"><?= number_format($dassPendientes) ?></div>
          <div class="kpi-lbl">DASS-21 Pendientes</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= $pctDass ?>%;background:#dc2626;"></div></div>
          <div class="kpi-foot">
            <span class="kpi-pct" style="color:#dc2626;"><?= $pctDass ?>% completados</span>
            <span class="kpi-sub"><?= number_format($dassPendientes) ?> restantes</span>
          </div>
        </div>
      </div>
    </div>

    <!-- FILA 2: Gráfica de barras + Feed de actividad -->
    <div class="row g-3 mb-3">
      <div class="col-xl-7">
        <div class="panel">
          <div class="panel-header">
            <div>
              <div class="panel-title">Completitud por Módulo</div>
              <div class="panel-sub">Registros completados vs. pendientes</div>
            </div>
          </div>
          <div class="panel-body">
            <canvas id="barChart" height="210"></canvas>
          </div>
        </div>
      </div>
      <div class="col-xl-5">
        <div class="panel h-100">
          <div class="panel-header">
            <div>
              <div class="panel-title">Últimas Capturas Físicas</div>
              <div class="panel-sub">Registros más recientes</div>
            </div>
            <a href="../datos-fisicos/datos_fisicos.html" style="font-size:12px;color:#2563eb;text-decoration:none;font-weight:500;">Ver todo</a>
          </div>
          <div class="panel-body" style="padding-top:8px;">
            <?php
            $avColors = [
                ['background:#eff6ff', 'color:#1d4ed8'],
                ['background:#f0fdf4', 'color:#16a34a'],
                ['background:#fffbeb', 'color:#d97706'],
                ['background:#fef2f2', 'color:#dc2626'],
                ['background:#f5f3ff', 'color:#7c3aed'],
            ];
            if (empty($ultimas)): ?>
              <p style="font-size:13px;color:#94a3b8;padding:12px 0;">Sin capturas registradas.</p>
            <?php else: foreach ($ultimas as $i => $u):
                $nombre = htmlspecialchars($u['nombres_alum'] . ' ' . $u['ape_paterno_alum']);
                $ini    = strtoupper(substr($u['nombres_alum'],0,1) . substr($u['ape_paterno_alum'],0,1));
                $col    = $avColors[$i % count($avColors)];
                $fecha  = $u['fecha'] ? date('d/m/Y H:i', strtotime($u['fecha'])) : '—';
            ?>
            <div class="act-item">
              <div class="act-av" style="<?= $col[0] ?>;<?= $col[1] ?>"><?= $ini ?></div>
              <div class="act-body">
                <strong><?= $nombre ?></strong>
                <p>Datos físicos capturados</p>
              </div>
              <span class="act-time"><?= $fecha ?></span>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- FILA 3: Donut + Accesos rápidos + Alertas -->
    <div class="row g-3 mb-3">
      <div class="col-xl-5">
        <div class="panel h-100">
          <div class="panel-header">
            <div>
              <div class="panel-title">Progreso General</div>
              <div class="panel-sub">Completitud al día de hoy</div>
            </div>
          </div>
          <div class="panel-body">
            <div class="row align-items-center">
              <div class="col-6">
                <div class="donut-wrap">
                  <canvas id="donutChart" width="190" height="190"></canvas>
                  <div class="donut-center">
                    <div class="dc-n"><?= $pctCompletos ?>%</div>
                    <div class="dc-l">completitud</div>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-list">
                  <div class="stat-row">
                    <span class="stat-label"><span class="stat-dot" style="background:#1d4ed8;"></span>Historial</span>
                    <span class="stat-value" style="color:#1d4ed8;"><?= number_format($totalHistorial) ?></span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label"><span class="stat-dot" style="background:#16a34a;"></span>Datos Físicos</span>
                    <span class="stat-value" style="color:#16a34a;"><?= number_format($totalDatosFisicos) ?></span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label"><span class="stat-dot" style="background:#d97706;"></span>DASS-21</span>
                    <span class="stat-value" style="color:#d97706;"><?= number_format($totalDass) ?></span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label"><span class="stat-dot" style="background:#7c3aed;"></span>Estilo de Vida</span>
                    <span class="stat-value" style="color:#7c3aed;"><?= number_format($totalEstiloVida) ?></span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label"><span class="stat-dot" style="background:#e2e8f0;"></span>Pendientes</span>
                    <span class="stat-value" style="color:#94a3b8;"><?= number_format(max(0, $totalAlumnos - $totalCompletos)) ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3">
        <div class="panel h-100">
          <div class="panel-header">
            <div>
              <div class="panel-title">Accesos Rápidos</div>
              <div class="panel-sub">Módulos frecuentes</div>
            </div>
          </div>
          <div class="panel-body">
            <div class="qa-grid">
              <a href="../historial/historial_clinico.html" class="qa-btn">
                <i class="bi bi-file-earmark-medical"></i> Historial
              </a>
              <a href="../datos-fisicos/datos_fisicos.html" class="qa-btn">
                <i class="bi bi-activity"></i> Datos Físicos
              </a>
              <a href="../gestion-alumnos/ListadoAlumnos.html" class="qa-btn">
                <i class="bi bi-people-fill"></i> Alumnos
              </a>
              <?php if ($rol === 'Administrador'): ?>
              <a href="../estadisticas/estadisticas.html" class="qa-btn">
                <i class="bi bi-bar-chart-fill"></i> Estadísticas
              </a>
              <a href="../reportes/resultadoalumnos.html" class="qa-btn">
                <i class="bi bi-graph-up-arrow"></i> Resultados
              </a>
              <a href="../credenciales/credencialesalumnos.html" class="qa-btn">
                <i class="bi bi-person-badge-fill"></i> Credenciales
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-4">
        <div class="panel h-100">
          <div class="panel-header">
            <div>
              <div class="panel-title">Alertas del Sistema</div>
              <div class="panel-sub">Estado de los módulos</div>
            </div>
            <?php $urgentes = ($dassPendientes > 0 ? 1 : 0) + (max(0, $totalAlumnos - $totalEstiloVida) > 0 ? 1 : 0); ?>
            <?php if ($urgentes > 0): ?>
            <span style="background:#fef2f2;color:#dc2626;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;">
              <?= $urgentes ?> urgente<?= $urgentes > 1 ? 's' : '' ?>
            </span>
            <?php endif; ?>
          </div>
          <div class="panel-body" style="padding-top:8px;">
            <?php if ($dassPendientes > 0): ?>
            <div class="alert-row">
              <span class="alert-dot" style="background:#dc2626;"></span>
              <div class="alert-txt"><?= $dassPendientes ?> alumnos sin DASS-21<span>Cuestionario pendiente</span></div>
              <span class="badge-a danger">Urgente</span>
            </div>
            <?php endif; ?>
            <?php $sinEstilo = max(0, $totalAlumnos - $totalEstiloVida); if ($sinEstilo > 0): ?>
            <div class="alert-row">
              <span class="alert-dot" style="background:#dc2626;"></span>
              <div class="alert-txt"><?= $sinEstilo ?> sin Estilo de Vida<span>PEPS-1 pendiente de captura</span></div>
              <span class="badge-a danger">Urgente</span>
            </div>
            <?php endif; ?>
            <?php $sinHistorial = max(0, $totalAlumnos - $totalHistorial); if ($sinHistorial > 0): ?>
            <div class="alert-row">
              <span class="alert-dot" style="background:#d97706;"></span>
              <div class="alert-txt"><?= $sinHistorial ?> sin historial clínico<span>Requieren captura inicial</span></div>
              <span class="badge-a warn">Atención</span>
            </div>
            <?php endif; ?>
            <?php $sinDatos = max(0, $totalAlumnos - $totalDatosFisicos); if ($sinDatos > 0): ?>
            <div class="alert-row">
              <span class="alert-dot" style="background:#d97706;"></span>
              <div class="alert-txt"><?= $sinDatos ?> sin datos físicos<span>Examen pendiente</span></div>
              <span class="badge-a warn">Atención</span>
            </div>
            <?php endif; ?>
            <?php if ($capturasHoy === 0): ?>
            <div class="alert-row">
              <span class="alert-dot" style="background:#2563eb;"></span>
              <div class="alert-txt">Sin capturas hoy<span>No hay registros del día</span></div>
              <span class="badge-a info">Info</span>
            </div>
            <?php else: ?>
            <div class="alert-row">
              <span class="alert-dot" style="background:#16a34a;"></span>
              <div class="alert-txt"><?= $capturasHoy ?> captura<?= $capturasHoy > 1 ? 's' : '' ?> hoy<span>Actividad registrada</span></div>
              <span class="badge-a info">Info</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- FILA 4: Tabla + Facultades -->
    <div class="row g-3">
      <div class="col-xl-8">
        <div class="panel">
          <div class="panel-header">
            <div>
              <div class="panel-title">Últimas Capturas de Datos Físicos</div>
              <div class="panel-sub">Los 5 registros más recientes</div>
            </div>
            <a href="../datos-fisicos/datos_fisicos.html" class="btn btn-sm" style="font-size:12px;border:1px solid #e2e8f0;color:#64748b;border-radius:8px;">Ver todo</a>
          </div>
          <div class="panel-body" style="padding-top:8px;">
            <table class="data-table">
              <thead>
                <tr><th>Alumno</th><th>Matrícula</th><th>Fecha</th><th>Estado</th></tr>
              </thead>
              <tbody>
                <?php if (empty($ultimas)): ?>
                <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px;">Sin registros</td></tr>
                <?php else: foreach ($ultimas as $i => $u):
                    $col    = $avColors[$i % count($avColors)];
                    $nombre = htmlspecialchars($u['nombres_alum'] . ' ' . $u['ape_paterno_alum']);
                    $ini    = strtoupper(substr($u['nombres_alum'],0,1) . substr($u['ape_paterno_alum'],0,1));
                    $fecha  = $u['fecha'] ? date('d/m/Y H:i', strtotime($u['fecha'])) : '—';
                ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="t-av" style="<?= $col[0] ?>;<?= $col[1] ?>"><?= $ini ?></div>
                      <span style="font-weight:500;"><?= $nombre ?></span>
                    </div>
                  </td>
                  <td style="color:#94a3b8;font-size:12px;"><?= htmlspecialchars($u['matricula_alum']) ?></td>
                  <td style="color:#94a3b8;font-size:12px;"><?= $fecha ?></td>
                  <td><span class="badge-s ok"><i class="bi bi-check-circle-fill" style="font-size:9px;"></i> Registrado</span></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-xl-4">
        <div class="panel">
          <div class="panel-header">
            <div>
              <div class="panel-title">Distribución por Facultad</div>
              <div class="panel-sub">Top <?= count($facultades) ?> con más alumnos</div>
            </div>
          </div>
          <div class="panel-body">
            <?php if (empty($facultades)): ?>
              <p style="font-size:13px;color:#94a3b8;">Sin datos de facultad.</p>
            <?php else: foreach ($facultades as $i => $fac):
                $pct   = round(($fac['total'] / $maxFacultad) * 100);
                $color = $facColores[$i % count($facColores)];
            ?>
            <div class="fac-item">
              <div class="fac-row">
                <span><?= htmlspecialchars($fac['nombre_facultad']) ?></span>
                <span style="font-weight:700;color:<?= $color ?>;"><?= number_format($fac['total']) ?></span>
              </div>
              <div class="fac-bar-bg">
                <div class="fac-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>

  </main>
</div><!-- #main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── BAR CHART ──
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: <?= $chartLabels ?>,
    datasets: [{
      label: 'Completados',
      data: <?= $chartCompletos ?>,
      backgroundColor: '#1d4ed8', borderRadius: 6, borderSkipped: false,
    },{
      label: 'Pendientes',
      data: <?= $chartPendientes ?>,
      backgroundColor: '#e2e8f0', borderRadius: 6, borderSkipped: false,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { position: 'top', labels: { font: { family: 'Inter', size: 12 }, color: '#64748b', boxWidth: 12, boxHeight: 12 } }
    },
    scales: {
      x: { stacked: true, grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 }, color: '#94a3b8' } },
      y: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter', size: 11 }, color: '#94a3b8' } }
    }
  }
});

// ── DONUT CHART ──
new Chart(document.getElementById('donutChart'), {
  type: 'doughnut',
  data: {
    labels: ['Historial', 'Datos Físicos', 'DASS-21', 'Estilo de Vida', 'Pendientes'],
    datasets: [{
      data: [
        <?= $totalHistorial ?>, <?= $totalDatosFisicos ?>,
        <?= $totalDass ?>, <?= $totalEstiloVida ?>,
        <?= max(0, $totalAlumnos - $totalCompletos) ?>
      ],
      backgroundColor: ['#1d4ed8','#16a34a','#d97706','#7c3aed','#e2e8f0'],
      borderWidth: 3, borderColor: '#fff', hoverOffset: 5
    }]
  },
  options: {
    responsive: true, cutout: '68%',
    plugins: { legend: { display: false } }
  }
});

// ── Buscador ──
document.getElementById('buscadorAlumno').addEventListener('keypress', function(e) {
  if (e.key === 'Enter' && this.value.trim()) {
    window.location.href = '../gestion-alumnos/ListadoAlumnos.html?q=' + encodeURIComponent(this.value.trim());
  }
});
</script>
</body>
</html>
