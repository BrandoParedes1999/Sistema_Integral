<?php

date_default_timezone_set('America/Mexico_City');
session_start();


$tiempo_limite = 1800;
if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];

    // Si pasó más tiempo del límite, cerrar sesión en BD y sacar al usuario
    if ($tiempo_transcurrido > $tiempo_limite) {
        // Conectar solo para cerrar la sesión en BD
        $conn_timeout = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");
        if (!$conn_timeout->connect_errno && isset($_SESSION['usuario'])) {
            $usr = $_SESSION['usuario'];
            $sql = "UPDATE registro_ingresos SET fecha_salida = NOW() WHERE usuario = ? AND fecha_salida IS NULL";
            $stmt = $conn_timeout->prepare($sql);
            $stmt->bind_param("s", $usr);
            $stmt->execute();
            $stmt->close();
        }

        session_unset();
        session_destroy();
        header('Location: login.php?error=timeout');
        exit();
    }
}
// Actualizar el reloj de actividad
$_SESSION['ultimo_acceso'] = time();

// Validar si el usuario está logueado y si tiene permiso (por ejemplo solo administradores)
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: login.php');
    exit();
}

// Conexión a base de datos
$conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

if ($conn->connect_errno) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener estadísticas
$sqlTotal = "SELECT COUNT(*) as total FROM registro_ingresos";
$resultTotal = $conn->query($sqlTotal);
$totalRegistros = $resultTotal->fetch_assoc()['total'];

$sqlActivos = "SELECT COUNT(*) as activos FROM registro_ingresos WHERE fecha_salida IS NULL";
$resultActivos = $conn->query($sqlActivos);
$usuariosActivos = $resultActivos->fetch_assoc()['activos'];

$sqlHoy = "SELECT COUNT(*) as hoy FROM registro_ingresos WHERE DATE(fecha_ingreso) = CURDATE()";
$resultHoy = $conn->query($sqlHoy);
$ingresosHoy = $resultHoy->fetch_assoc()['hoy'];

// Consultar registros con ordenamiento
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
$sql = "SELECT id, usuario, nombre_completo, rol, fecha_ingreso, fecha_salida 
        FROM registro_ingresos";

if ($filtro === 'activos') {
    $sql .= " WHERE fecha_salida IS NULL";
} elseif ($filtro === 'finalizados') {
    $sql .= " WHERE fecha_salida IS NOT NULL";
} elseif ($filtro === 'hoy') {
    $sql .= " WHERE DATE(fecha_ingreso) = CURDATE()";
}

$sql .= " ORDER BY fecha_ingreso DESC LIMIT 100";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Accesos</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="panel_control.css">
    <link rel="icon" type="image/x-icon" href="/ico/logo_pequeno.ico">
</head>

<body>
    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-title">
                <span class="header-icon">📊</span>
                <div>
                    <h1>Panel de Control</h1>
                    <p class="header-subtitle">Monitoreo de accesos y actividad del sistema</p>
                </div>
            </div>
            <a href="menu.php" class="btn-volver">
                <i class="bi bi-arrow-left-circle"></i>
                Volver al Menú
            </a>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Registros</div>
                    <div class="stat-value"><?php echo number_format($totalRegistros); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Usuarios Activos</div>
                    <div class="stat-value"><?php echo number_format($usuariosActivos); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="bi bi-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Ingresos Hoy</div>
                    <div class="stat-value"><?php echo number_format($ingresosHoy); ?></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Registros -->
        <div class="table-wrapper">
            <div class="table-header">
                <h2 class="table-title">
                    <i class="bi bi-table"></i>
                    Historial de Accesos
                </h2>

                <div class="filters-section">
                    <button class="filter-btn <?php echo $filtro === 'todos' ? 'active' : ''; ?>"
                        onclick="location.href='panel_control.php?filtro=todos'">
                        <i class="bi bi-list-ul"></i>
                        Todos
                    </button>
                    <button class="filter-btn <?php echo $filtro === 'activos' ? 'active' : ''; ?>"
                        onclick="location.href='panel_control.php?filtro=activos'">
                        <i class="bi bi-circle-fill" style="color: #10b981; font-size: 0.6rem;"></i>
                        Activos
                    </button>
                    <button class="filter-btn <?php echo $filtro === 'finalizados' ? 'active' : ''; ?>"
                        onclick="location.href='panel_control.php?filtro=finalizados'">
                        <i class="bi bi-check-circle"></i>
                        Finalizados
                    </button>
                    <button class="filter-btn <?php echo $filtro === 'hoy' ? 'active' : ''; ?>"
                        onclick="location.href='panel_control.php?filtro=hoy'">
                        <i class="bi bi-clock"></i>
                        Hoy
                    </button>

                    <?php if ($usuariosActivos > 0): ?>
                        <button class="btn-cerrar-sesiones" onclick="cerrarTodasSesiones()" id="btnCerrarSesiones">
                            <i class="bi bi-x-circle"></i>
                            Cerrar Todas las Sesiones (<?php echo $usuariosActivos; ?>)
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Rol</th>
                            <th>Hora de Ingreso</th>
                            <th>Hora de Salida</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($row['usuario'], 0, 1)); ?>
                                            </div>
                                            <strong><?php echo htmlspecialchars($row['usuario']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                                    <td>
                                        <?php
                                        $rolClass = 'rol-usuario';
                                        if ($row['rol'] === 'Administrador') {
                                            $rolClass = 'rol-administrador';
                                        } elseif ($row['rol'] === 'Docente') {
                                            $rolClass = 'rol-docente';
                                        }
                                        ?>
                                        <span class="rol-badge <?php echo $rolClass; ?>">
                                            <?php echo htmlspecialchars($row['rol']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-box-arrow-in-right text-primary"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($row['fecha_ingreso'])); ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['fecha_salida']) {
                                            echo '<i class="bi bi-box-arrow-right text-secondary"></i> ';
                                            echo date('d/m/Y H:i', strtotime($row['fecha_salida']));
                                        } else {
                                            echo '<span class="text-conectado">Conectado</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['fecha_salida']) {
                                            echo '<span class="badge badge-finalizado">
                                                <i class="bi bi-check-circle"></i> Finalizado
                                              </span>';
                                        } else {
                                            echo '<span class="badge badge-activo">
                                                <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Activo
                                              </span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (!$row['fecha_salida'] && $row['usuario'] != $_SESSION['usuario']) {
                                            echo '<button class="btn-accion-sesion" onclick="cerrarSesion(' . $row['id'] . ')" title="Cerrar sesión">
                                                <i class="bi bi-power"></i> Cerrar
                                              </button>';
                                        } else if (!$row['fecha_salida'] && $row['usuario'] == $_SESSION['usuario']) {
                                            echo '<span class="text-muted" style="font-size: 0.8rem;">
                                                <i class="bi bi-person-fill"></i> Tu sesión
                                              </span>';
                                        } else {
                                            echo '<span class="text-muted" style="font-size: 0.8rem;">-</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>No se encontraron registros con los criterios seleccionados</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para auto-actualización -->
    <script>
        // Función para cerrar todas las sesiones
        function cerrarTodasSesiones() {
            if (!confirm('¿Estás seguro de que deseas cerrar todas las sesiones activas?\n\nEsta acción cerrará todas las sesiones excepto la tuya y no se puede deshacer.')) {
                return;
            }

            const btn = document.getElementById('btnCerrarSesiones');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

            const formData = new FormData();
            formData.append('accion', 'cerrar_todas');

            fetch('cerrar_sesiones.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    console.log('Status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert('✅ ' + data.message);
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            alert('❌ Error: ' + data.message);
                            btn.disabled = false;
                            btn.innerHTML = originalHTML;
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        console.error('Response was:', text);
                        alert('❌ Error al procesar la respuesta del servidor');
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('❌ Error de conexión: ' + error.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                });
        }

        // Función para cerrar una sesión específica
        function cerrarSesion(id) {
            if (!confirm('¿Estás seguro de que deseas cerrar esta sesión?')) {
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'cerrar_una');
            formData.append('id', id);

            fetch('cerrar_sesiones.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    console.log('Status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert('✅ ' + data.message);
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            alert('❌ Error: ' + data.message);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        console.error('Response was:', text);
                        alert('❌ Error al procesar la respuesta del servidor');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('❌ Error de conexión: ' + error.message);
                });
        }

        // Auto-actualizar la página cada 30 segundos para mostrar cambios en tiempo real
        let autoRefresh = true;
        let refreshTimer = null;

        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            if (autoRefresh) {
                startAutoRefresh();
            } else {
                if (refreshTimer) {
                    clearTimeout(refreshTimer);
                }
            }
        }

        function startAutoRefresh() {
            if (autoRefresh) {
                refreshTimer = setTimeout(() => {
                    console.log('Auto-actualizando página...');
                    location.reload();
                }, 30000); // 30 segundos
            }
        }

        // Iniciar auto-actualización
        startAutoRefresh();

        // Mostrar última actualización
        const now = new Date();
        console.log('✅ Panel de Control cargado');
        console.log('📅 Última actualización:', now.toLocaleString('es-MX'));
        console.log('🔄 Auto-actualización: ' + (autoRefresh ? 'Activada (30s)' : 'Desactivada'));
    </script>
</body>

</html>

<?php
$conn->close();
?>