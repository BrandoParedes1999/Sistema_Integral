<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

$nombre_admi = isset($_SESSION['nombre_admi']) ? $_SESSION['nombre_admi'] : '';
$rol         = isset($_SESSION['rol'])         ? $_SESSION['rol']         : '';

$partes    = explode(' ', trim($nombre_admi));
$iniciales = strtoupper(
    substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : '')
);
?>
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
    <a href="../admin/menu.php" class="sb-link">
      <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="../historial/historial_clinico.html" class="sb-link">
      <i class="bi bi-folder2-open"></i> Historial Clínico
    </a>
    <a href="../datos-fisicos/datos_fisicos.html" class="sb-link">
      <i class="bi bi-activity"></i> Datos Físicos
    </a>

    <div class="sb-section">Alumnos</div>
    <a href="../gestion-alumnos/ListadoAlumnos.html" class="sb-link">
      <i class="bi bi-people-fill"></i> Alumnos
    </a>

    <div class="sb-section">Reportes</div>
    <a href="../estadisticas/estadisticas.html" class="sb-link">
      <i class="bi bi-bar-chart-fill"></i> Estadísticas
    </a>
    <a href="../reportes/resultadoalumnos.html" class="sb-link">
      <i class="bi bi-graph-up-arrow"></i> Resultados
    </a>
    <a href="../reportes/descargar_datos.php" class="sb-link">
      <i class="bi bi-download"></i> Descargar Datos
    </a>
    <a href="../credenciales/credencialesalumnos.html" class="sb-link">
      <i class="bi bi-person-badge-fill"></i> Credenciales
    </a>

    <div class="sb-section">Observatorio</div>
    <a href="../observatorio/observatorio.html" class="sb-link">
      <i class="bi bi-telescope-fill"></i> Observatorio
    </a>

    <div class="sb-section">Sistema</div>
    <a href="../admin/panel_control.php" class="sb-link">
      <i class="bi bi-shield-lock-fill"></i> Control del Sistema
    </a>
  </div>

  <div class="sb-footer">
    <div class="sb-user-card">
      <div class="sb-av"><?php echo htmlspecialchars($iniciales ?: '?'); ?></div>
      <div>
        <div class="sb-u-name"><?php echo htmlspecialchars($nombre_admi ?: 'Administrador'); ?></div>
        <div class="sb-u-role"><?php echo htmlspecialchars($rol ?: ''); ?></div>
      </div>
    </div>
  </div>
</nav>
