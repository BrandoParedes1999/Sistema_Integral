<?php
session_start();
if (!isset($_SESSION['alumno'])) {
    header('Location: registro.html');
    exit;
}

$alumno = $_SESSION['alumno'];
$nombreCompleto = htmlspecialchars($alumno['nombre']. ' ' . $alumno['apepa'].' '. $alumno['apema']);

// Conexión a la base de datos (Tu código original)
$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si ya respondió el formulario Estilo de Vida
$yaRespondioEstilo = false;
$sqlCheck = "SELECT 1 FROM estilo_de_vida WHERE matricula_alum = ?";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("i", $alumno['matricula']);
$stmt->execute();
$stmt->store_result();
$yaRespondioEstilo = $stmt->num_rows > 0;
$stmt->close();

// Verificar si ya respondió DASS
$yaRespondioDASS = false;
$sqlCheckDASS = "SELECT 1 FROM dass WHERE matricula_alum = ?";
$stmtDASS = $conn->prepare($sqlCheckDASS);
$stmtDASS->bind_param("i", $alumno['matricula']);
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
?>

<!doctype html>
<html lang="es">

<head>
    <title>Menú Cuestionarios</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/menualum.css">
    <link rel="icon" type="image/x-icon" href="/ico/logo_pequeno.ico">
</head>

<body>
    
    <div class="main-container">
        
        <header class="dashboard-header">
            <div>
                <h2>Sistema Integral de Salud</h2>
                <div class="subtext">Panel del Estudiante</div>
            </div>
            <button class="btn-logout-header" data-bs-toggle="modal" data-bs-target="#modalLogout">
                Cerrar Sesión <span>&#10162;</span>
            </button>
        </header>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($alumno['nombre'], 0, 1)); ?>
                </div>
                <div class="profile-title">
                    <h3><?php echo $nombreCompleto; ?></h3>
                </div>
            </div>
            <div class="profile-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Matrícula</label>
                        <span><?php echo htmlspecialchars($alumno['matricula']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Correo Institucional</label>
                        <span><?php echo htmlspecialchars($alumno['correo']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Facultad</label>
                        <span><?php echo htmlspecialchars($nombreFacultad); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Carrera</label>
                        <span><?php echo htmlspecialchars($nombreCarrera); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="actions-title">
            <span>📋</span> Mis Actividades
        </div>

        <div class="actions-grid">
            
            <div class="action-card <?php echo $yaRespondioEstilo ? 'completed' : ''; ?>">
                <div>
                    <h4>Cuestionario Estilo de Vida</h4>
                    <p>
                        <?php if ($yaRespondioEstilo): ?>
                            Gracias por completar tu información de hábitos y salud.
                        <?php else: ?>
                            Ayúdanos a conocer tus hábitos para mejorar los servicios de salud universitaria.
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if ($yaRespondioEstilo): ?>
                    <div class="status-badge">Completado</div>
                <?php else: ?>
                    <form action="PEPS-1.php">
                        <button type="submit" class="btn-action">Iniciar Cuestionario</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="action-card <?php echo $yaRespondioDASS ? 'completed' : ''; ?>">
                <div>
                    <h4>Evaluación Emocional (DASS-21)</h4>
                    <p>
                        <?php if ($yaRespondioDASS): ?>
                            Registro de estado emocional guardado correctamente.
                        <?php else: ?>
                            Breve cuestionario confidencial sobre tu estado emocional actual.
                        <?php endif; ?>
                    </p>
                </div>

                <?php if ($yaRespondioDASS): ?>
                    <div class="status-badge">Completado</div>
                <?php else: ?>
                    <a href="DASS-21.php" class="btn-action">Iniciar Evaluación</a>
                <?php endif; ?>
            </div>
        </div>

    </div> <?php if (isset($_SESSION['bienvenida']) && $_SESSION['bienvenida']) : ?>
    <div class="modal fade" id="modalBienvenida" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">¡Bienvenido al Sistema!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-1">Hola,</p>
                    <h4 class="text-primary mb-3"><?php echo $nombreCompleto; ?></h4>
                    <p class="text-muted">Es un gusto tenerte aquí. Por favor completa tus pendientes.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
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
                <div class="modal-header bg-success text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title">🎉 ¡Excelente Trabajo!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p>Has completado todos los requisitos iniciales.</p>
                    <p class="small text-muted">Tus respuestas nos ayudan a crear una mejor universidad para ti.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">Finalizar</button>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cerrar Sesión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas salir de tu cuenta?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <a href="logoutAlumno.php" class="btn btn-danger">Sí, Salir</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>
</html>