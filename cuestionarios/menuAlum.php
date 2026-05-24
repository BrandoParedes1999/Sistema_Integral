<?php
require_once '../config/config.php';
session_start();
if (!isset($_SESSION['alumno'])) {
    header('Location: registro.html');
    exit;
}

$alumno = $_SESSION['alumno'];
$nombreCompleto = htmlspecialchars($alumno['nombre']. ' ' . $alumno['apepa'].' '. $alumno['apema']);

// Conexión a la base de datos (Tu código original)
$conn = getDBConnection();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si ya respondió el formulario Estilo de Vida
$yaRespondioEstilo = false;
$sqlCheck = "SELECT 1 FROM estilo_de_vida WHERE matricula_alum = ?";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("s", $alumno['matricula']);
$stmt->execute();
$stmt->store_result();
$yaRespondioEstilo = $stmt->num_rows > 0;
$stmt->close();

// Verificar si ya respondió DASS
$yaRespondioDASS = false;
$sqlCheckDASS = "SELECT 1 FROM dass WHERE matricula_alum = ?";
$stmtDASS = $conn->prepare($sqlCheckDASS);
$stmtDASS->bind_param("s", $alumno['matricula']);
$stmtDASS->execute();
$stmtDASS->store_result();
$yaRespondioDASS = ($stmtDASS->num_rows > 0); // Corrección: num_rows es propiedad, no método
$stmtDASS->close();

// Obtener nombres de carrera y facultad
$nombreCarrera = "Desconocida";
$nombreFacultad = "Desconocida";

$sql = "SELECT c.nombre_carrera, f.nombre_facultad 
        FROM carrera c 
        JOIN facultad f ON c.id_facultad = f.id_facultad
        WHERE c.id_carrera = ? AND c.id_facultad = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $alumno['id_carrera'], $alumno['id_facultad']);
$stmt->execute();
$stmt->bind_result($nombreCarrera, $nombreFacultad);
$stmt->fetch();
$stmt->close();

$conn->close();

// Iniciales del alumno (2 letras)
$iniciales = strtoupper(
    substr($alumno['nombre'], 0, 1) .
    substr($alumno['apepa'], 0, 1)
);

// Progreso
$completados = ($yaRespondioEstilo ? 1 : 0) + ($yaRespondioDASS ? 1 : 0);
$pct = ($completados / 2) * 100;
?>

<!doctype html>
<html lang="es">

<head>
    <title>Panel del Estudiante · UNACAR</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/menualum.css">
    <link rel="icon" type="image/x-icon" href="/ico/logo_pequeno.ico">
</head>

<body>

    <!-- ── Topbar ──────────────────────────────────────── -->
    <header class="topbar">
        <div class="topbar-brand">
            <div class="topbar-icon">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div>
                <div class="topbar-name">Sistema Integral de Salud</div>
                <div class="topbar-sub">UNACAR &middot; Panel del Estudiante</div>
            </div>
        </div>
        <button class="btn-logout" data-bs-toggle="modal" data-bs-target="#modalLogout">
            <i class="bi bi-box-arrow-right"></i> Salir
        </button>
    </header>

    <div class="page">

        <!-- ── Perfil ───────────────────────────────────── -->
        <div class="profile-card">
            <div class="profile-banner"></div>
            <div class="profile-body">
                <div class="profile-avatar"><?php echo $iniciales; ?></div>
                <div class="profile-name"><?php echo $nombreCompleto; ?></div>
                <div class="profile-mat">
                    <i class="bi bi-person-badge"></i>
                    <?php echo htmlspecialchars($alumno['matricula']); ?>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="bi bi-envelope"></i> Correo</div>
                        <div class="info-value"><?php echo htmlspecialchars($alumno['correo']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="bi bi-building"></i> Facultad</div>
                        <div class="info-value"><?php echo htmlspecialchars($nombreFacultad); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="bi bi-mortarboard"></i> Carrera</div>
                        <div class="info-value"><?php echo htmlspecialchars($nombreCarrera); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Progreso ─────────────────────────────────── -->
        <div class="progress-section">
            <div class="progress-label">
                Progreso: <span class="<?php echo $completados == 2 ? 'done-clr' : ''; ?>"><?php echo $completados; ?> de 2</span> actividades completadas
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill <?php echo $completados == 2 ? 'all-done' : ''; ?>"
                     style="width: <?php echo $pct; ?>%"></div>
            </div>
            <div class="progress-pct"><?php echo (int)$pct; ?>%</div>
        </div>

        <!-- ── Actividades ──────────────────────────────── -->
        <div class="section-hd">Mis actividades</div>

        <div class="cards-grid">

            <!-- Estilo de Vida -->
            <div class="act-card <?php echo $yaRespondioEstilo ? 'completed' : ''; ?>">
                <div class="act-body">
                    <div class="act-icon <?php echo $yaRespondioEstilo ? 'green' : 'blue'; ?>">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                    <div class="act-title">Cuestionario Estilo de Vida</div>
                    <div class="act-desc">
                        <?php if ($yaRespondioEstilo): ?>
                            Tu información de hábitos y salud fue registrada correctamente.
                        <?php else: ?>
                            Responde preguntas sobre tus hábitos diarios para ayudarnos a mejorar los servicios de salud universitaria.
                        <?php endif; ?>
                    </div>
                    <div class="act-meta">
                        <i class="bi bi-clock"></i>
                        <?php echo $yaRespondioEstilo ? 'Completado' : 'Aprox. 10 min · 48 preguntas'; ?>
                    </div>
                </div>
                <div class="act-footer">
                    <?php if ($yaRespondioEstilo): ?>
                        <div class="badge-done">
                            <i class="bi bi-check-circle-fill"></i> Completado
                        </div>
                    <?php else: ?>
                        <form action="PEPS-1.php">
                            <button type="submit" class="btn-start">
                                <i class="bi bi-play-fill"></i> Iniciar Cuestionario
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- DASS-21 -->
            <div class="act-card <?php echo $yaRespondioDASS ? 'completed' : ''; ?>">
                <div class="act-body">
                    <div class="act-icon <?php echo $yaRespondioDASS ? 'green' : 'blue'; ?>">
                        <i class="bi bi-clipboard-pulse-fill"></i>
                    </div>
                    <div class="act-title">Evaluación Emocional (DASS-21)</div>
                    <div class="act-desc">
                        <?php if ($yaRespondioDASS): ?>
                            Tu estado emocional fue registrado correctamente de forma confidencial.
                        <?php else: ?>
                            Cuestionario breve y confidencial sobre tu estado emocional actual (depresión, ansiedad y estrés).
                        <?php endif; ?>
                    </div>
                    <div class="act-meta">
                        <i class="bi bi-clock"></i>
                        <?php echo $yaRespondioDASS ? 'Completado' : 'Aprox. 5 min · 21 preguntas'; ?>
                    </div>
                </div>
                <div class="act-footer">
                    <?php if ($yaRespondioDASS): ?>
                        <div class="badge-done">
                            <i class="bi bi-check-circle-fill"></i> Completado
                        </div>
                    <?php else: ?>
                        <a href="DASS-21.php" class="btn-start">
                            <i class="bi bi-play-fill"></i> Iniciar Evaluación
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div><!-- /page -->

    <?php if (isset($_SESSION['bienvenida']) && $_SESSION['bienvenida']) : ?>
    <div class="modal fade" id="modalBienvenida" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center px-4 pb-2">
                    <div style="font-size:2.5rem;margin-bottom:.5rem;">👋</div>
                    <h5 class="fw-bold mb-1">¡Bienvenido al Sistema!</h5>
                    <p class="text-primary fw-semibold mb-2"><?php echo $nombreCompleto; ?></p>
                    <p class="text-muted small">Tu registro fue exitoso. Completa las actividades para continuar.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">¡Listo, empecemos!</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('modalBienvenida')).show();
        });
    </script>
    <?php unset($_SESSION['bienvenida']); ?>
    <?php endif; ?>

    <?php if ($yaRespondioEstilo && $yaRespondioDASS): ?>
    <div class="modal fade" id="modalCompletado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center px-4 pb-2">
                    <div style="font-size:2.8rem;margin-bottom:.5rem;">🎉</div>
                    <h5 class="fw-bold mb-1 text-success">¡Todo completado!</h5>
                    <p class="text-muted small">Terminaste todas las actividades requeridas. Tus respuestas nos ayudan a construir una mejor universidad.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('modalCompletado')).show();
        });
    </script>
    <?php endif; ?>

    <div class="modal fade" id="modalLogout" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center px-4 py-4">
                    <div style="font-size:2rem;margin-bottom:.6rem;color:#ef4444;">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <h6 class="fw-bold mb-1">¿Cerrar sesión?</h6>
                    <p class="text-muted small mb-0">Podrás volver a ingresar cuando quieras.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <a href="../auth/logoutAlumno.php" class="btn btn-danger">Sí, salir</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>
</html>