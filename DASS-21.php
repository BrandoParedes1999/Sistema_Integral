<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['alumno']) || !isset($_SESSION['alumno']['matricula'])) {
    header("Location: registro.php");
    exit();
}

$alumno = $_SESSION['alumno'];
$matricula = $alumno['matricula'];

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql_verificar = "SELECT id_cuestionario FROM dass WHERE matricula_alum = ?";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("i", $matricula);
$stmt_verificar->execute();
$resultado = $stmt_verificar->get_result();

if ($resultado->num_rows > 0) {
    $stmt_verificar->close();
    $conn->close();
    header("Location: menuAlum.php");
    exit();
}
$stmt_verificar->close();

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Tu lógica de procesamiento POST se mantiene idéntica aquí) ...
    $required_fields = ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9', 'p10', 'p11', 'p12', 'p13', 'p14', 'p15', 'p16', 'p17', 'p18', 'p19', 'p20', 'p21'];
    $all_valid = true;
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || !is_numeric($_POST[$field]) || $_POST[$field] < 0 || $_POST[$field] > 3) {
            $all_valid = false;
            break;
        }
    }

    if (!$all_valid) {
        $error_message = "Error: Todas las preguntas deben ser respondidas con un valor entre 0 y 3.";
    } else {

        // Depresión
        $p3 = (int)$_POST['p3'];
        $p5 = (int)$_POST['p5'];
        $p10 = (int)$_POST['p10'];
        $p13= (int)$_POST['p13'];
        $p16 = (int)$_POST['p16'];
        $p17 = (int)$_POST['p17'];
        $p21 = (int)$_POST['p21'];
        $total_depresion = $p3 + $p5 + $p10 + $p13 + $p16 + $p17 + $p21;
        $severidad_dep = ($total_depresion <= 4) ? 'Normal' :
            (($total_depresion <= 6) ? 'Leve' :
                (($total_depresion <= 10) ? 'Moderado' :
                    (($total_depresion <= 13) ? 'Severo' : 'Extremadamente Severo')));

        // Ansiedad
        $p2 = (int)$_POST['p2'];
        $p4 = (int)$_POST['p4'];
        $p7 = (int)$_POST['p7'];
        $p9= (int)$_POST['p9'];
        $p15 = (int)$_POST['p15'];
        $p19 = (int)$_POST['p19'];
        $p20 = (int)$_POST['p20'];
        $total_ansiedad = $p2 + $p4 + $p7 + $p9 + $p15 + $p19 + $p20;
        $severidad_ans = ($total_ansiedad <= 3) ? 'Normal' :
            (($total_ansiedad <= 4) ? 'Leve' :
                (($total_ansiedad <= 7) ? 'Moderado' :
                    (($total_ansiedad <= 9) ? 'Severo' : 'Extremadamente Severo')));

        // Estrés
        $p1 = (int)$_POST['p1'];
        $p6 = (int)$_POST['p6'];
        $p8 = (int)$_POST['p8'];
        $p11= (int)$_POST['p11'];
        $p12 = (int)$_POST['p12'];
        $p14 = (int)$_POST['p14'];
        $p18 = (int)$_POST['p18'];
        $total_estres = $p1 + $p6 + $p8 + $p11 + $p12 + $p14 + $p18;
        $severidad_estres = ($total_estres <= 7) ? 'Normal' :
            (($total_estres <= 9) ? 'Leve' :
                (($total_estres <= 12) ? 'Moderado' :
                    (($total_estres <= 16) ? 'Severo' : 'Extremadamente Severo')));

        $total_general = $total_depresion + $total_ansiedad + $total_estres;
        
        // Insertar en tabla principal
        $sql_cuestionario = "INSERT INTO dass (matricula_alum, total_depresion, total_ansiedad, total_estres, total_general) VALUES (?, ?, ?, ?, ?)";
        $stmt_cuestionario = $conn->prepare($sql_cuestionario);
        $stmt_cuestionario->bind_param("iiiii", $matricula, $total_depresion, $total_ansiedad, $total_estres, $total_general);

        if ($stmt_cuestionario->execute()) {
            $id_cuestionario = $conn->insert_id;
            $stmt_cuestionario->close();

            // Insertar en dass_depresion
            $sql_dep = "INSERT INTO dass_depresion (id_cuestionario, p3, p5, p10, p13, p16, p17, p21, total_depresion, severidad)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_dep = $conn->prepare($sql_dep);
            $stmt_dep->bind_param("iiiiiiiiis", $id_cuestionario, $p3, $p5, $p10, $p13, $p16, $p17, $p21, $total_depresion, $severidad_dep);
            $stmt_dep->execute();
            $stmt_dep->close();

            // Insertar en dass_ansiedad
            $sql_ans = "INSERT INTO dass_ansiedad (id_cuestionario, p2, p4, p7, p9, p15, p19, p20, total_ansiedad, severidad)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_ans = $conn->prepare($sql_ans);
            $stmt_ans->bind_param("iiiiiiiiis", $id_cuestionario, $p2, $p4, $p7, $p9, $p15, $p19, $p20, $total_ansiedad, $severidad_ans);
            $stmt_ans->execute();
            $stmt_ans->close();

            // Insertar en dass_estres
            $sql_est = "INSERT INTO dass_estres (id_cuestionario, p1, p6, p8, p11, p12, p14, p18, total_estres, severidad)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_est = $conn->prepare($sql_est);
            $stmt_est->bind_param("iiiiiiiiis", $id_cuestionario, $p1, $p6, $p8, $p11, $p12, $p14, $p18, $total_estres, $severidad_estres);
            $stmt_est->execute();
            $stmt_est->close();

            $conn->close();
            header("Location: menuAlum.php");
            exit();
        } else {
            $error_message = "Error al guardar los datos del formulario.";
        }
    }
}
$conn->close();

// Función auxiliar para renderizar opciones de forma limpia
function renderOpcionesDASS($idPregunta) {
    echo '
    <div class="options-group">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_0" value="0" required>
            <label class="form-check-label" for="'.$idPregunta.'_0">No me ha ocurrido</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_1" value="1">
            <label class="form-check-label" for="'.$idPregunta.'_1">Me ha ocurrido un poco</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_2" value="2">
            <label class="form-check-label" for="'.$idPregunta.'_2">Me ha ocurrido bastante</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_3" value="3">
            <label class="form-check-label" for="'.$idPregunta.'_3">Me ha ocurrido mucho</label>
        </div>
    </div>';
}
?>

<!doctype html>
<html lang="es">
<head>
    <title>Cuestionario DASS-21</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="PEPS-1.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/ico/logo_pequeno.ico">
</head>

<body>
    <div class="header">
        <h1>Sistema Integral de Salud UNACAR</h1>
        <p>Evaluación de Estrés, Ansiedad y Depresión</p>
    </div>

    <div class="form-container">
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="instructions-box">
            <h5 class="text-primary mb-3">Instrucciones:</h5>
            <p>Por favor lea las siguientes afirmaciones e indique cuánto le ha ocurrido o afectado cada situación <strong>durante la semana pasada</strong>.</p>
            <p class="mb-0">No hay respuestas correctas o incorrectas. Seleccione la opción que mejor describa su estado.</p>
        </div>

        <form action="DASS-21.php" method="post">
            <div class="form-section">
                <h4 class="section-title">Cuestionario</h4>
                
                <div class="questions-grid">
                    <div class="question-card">
                        <label class="question-label">1. Me ha costado mucho descargar la tensión</label>
                        <?php renderOpcionesDASS('p1'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">2. Me di cuenta que tenía la boca seca</label>
                        <?php renderOpcionesDASS('p2'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">3. No podía sentir ningún sentimiento positivo</label>
                        <?php renderOpcionesDASS('p3'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">4. Se me hizo difícil respirar</label>
                        <?php renderOpcionesDASS('p4'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">5. Se me hizo difícil tomar la iniciativa para hacer cosas</label>
                        <?php renderOpcionesDASS('p5'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">6. Reaccioné exageradamente en ciertas situaciones</label>
                        <?php renderOpcionesDASS('p6'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">7. Sentí que mis manos temblaban</label>
                        <?php renderOpcionesDASS('p7'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">8. He sentido que estaba gastando una gran cantidad de energía</label>
                        <?php renderOpcionesDASS('p8'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">9. Estaba preocupado por situaciones en las cuales podía tener pánico</label>
                        <?php renderOpcionesDASS('p9'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">10. He sentido que no había nada que me ilusionara</label>
                        <?php renderOpcionesDASS('p10'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">11. Me he sentido inquieto</label>
                        <?php renderOpcionesDASS('p11'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">12. Se me hizo difícil relajarme</label>
                        <?php renderOpcionesDASS('p12'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">13. Me sentí triste y deprimido</label>
                        <?php renderOpcionesDASS('p13'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">14. No toleré nada que no me permitiera continuar con lo que estaba haciendo</label>
                        <?php renderOpcionesDASS('p14'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">15. Sentí que estaba al punto de pánico</label>
                        <?php renderOpcionesDASS('p15'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">16. No me pude entusiasmar por nada</label>
                        <?php renderOpcionesDASS('p16'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">17. Sentí que valía muy poco como persona</label>
                        <?php renderOpcionesDASS('p17'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">18. He tendido a sentirme enfadado con facilidad</label>
                        <?php renderOpcionesDASS('p18'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">19. Sentí los latidos de mi corazón a pesar de no haber hecho esfuerzo físico</label>
                        <?php renderOpcionesDASS('p19'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">20. Tuve miedo sin razón</label>
                        <?php renderOpcionesDASS('p20'); ?>
                    </div>

                    <div class="question-card">
                        <label class="question-label">21. Sentí que la vida no tenía ningún sentido</label>
                        <?php renderOpcionesDASS('p21'); ?>
                    </div>
                </div> </div>

            <div class="submit-container">
                <button class="btn-submit" type="submit">Enviar Resultados</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>