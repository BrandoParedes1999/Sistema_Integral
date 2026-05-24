<?php
session_start();

// Deshabilitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Seguridad
if (!isset($_SESSION['alumno']) || !isset($_SESSION['alumno']['matricula'])) {
    header("Location: registro.php");
    exit;
}
date_default_timezone_set('America/Mexico_City');
$alumno = $_SESSION['alumno'];
$matricula = $alumno['matricula'];

// Conexión
$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar previo
$sql_verificar = "SELECT 1 FROM estilo_de_vida WHERE matricula_alum = ?";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("s", $matricula);
$stmt_verificar->execute();
$resultado = $stmt_verificar->get_result();

if ($resultado->num_rows > 0) {
    $stmt_verificar->close();
    $conn->close();
    header("Location: menuAlum.php");
    exit;
}
$stmt_verificar->close();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9', 'p10', 'p11', 'p12', 'p13', 'p14', 'p15', 'p16', 'p17', 'p18', 'p19', 'p20', 'p21', 'p22', 'p23', 'p24', 'p25', 'p26', 'p27', 'p28', 'p29', 'p30', 'p31', 'p32', 'p33', 'p34', 'p35', 'p36', 'p37', 'p38', 'p39', 'p40', 'p41', 'p42', 'p43', 'p44', 'p45', 'p46', 'p47', 'p48'];
    $all_valid = true;
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || !is_numeric($_POST[$field]) || $_POST[$field] < 1 || $_POST[$field] > 4) {
            $all_valid = false;
            break;
        }
    }

    if (!$all_valid) {
        $error_message = "Error: Todas las preguntas deben responderse con un valor entre 1 y 4.";
    } else {
        // Calcular puntajes
        $p1 = (int) $_POST['p1']; $p5 = (int) $_POST['p5']; $p14 = (int) $_POST['p14']; $p19 = (int) $_POST['p19']; $p26 = (int) $_POST['p26']; $p35 = (int) $_POST['p35'];
        $total_nutricion = $p1 + $p5 + $p14 + $p19 + $p26 + $p35;
        $saludable_nutricion = ($total_nutricion > 15) ? 'Saludable' : 'No Saludable';

        $p4 = (int) $_POST['p4']; $p13 = (int) $_POST['p13']; $p22 = (int) $_POST['p22']; $p30 = (int) $_POST['p30']; $p38 = (int) $_POST['p38'];
        $total_ejercicio = $p4 + $p13 + $p22 + $p30 + $p38;
        $saludable_ejercicio = ($total_ejercicio > 13) ? 'Saludable' : 'No Saludable';

        $p2 = (int) $_POST['p2']; $p7 = (int) $_POST['p7']; $p15 = (int) $_POST['p15']; $p20 = (int) $_POST['p20']; $p28 = (int) $_POST['p28']; $p32 = (int) $_POST['p32']; $p33 = (int) $_POST['p33']; $p42 = (int) $_POST['p42']; $p43 = (int) $_POST['p43']; $p46 = (int) $_POST['p46'];
        $total_salud = $p2 + $p7 + $p15 + $p20 + $p28 + $p32 + $p33 + $p42 + $p43 + $p46;
        $saludable_salud = ($total_salud > 25) ? 'Saludable' : 'No Saludable';

        $p10 = (int) $_POST['p10']; $p18 = (int) $_POST['p18']; $p24 = (int) $_POST['p24']; $p25 = (int) $_POST['p25']; $p31 = (int) $_POST['p31']; $p39 = (int) $_POST['p39']; $p47 = (int) $_POST['p47'];
        $total_soporte = $p10 + $p18 + $p24 + $p25 + $p31 + $p39 + $p47;
        $saludable_soporte = ($total_soporte > 17) ? 'Saludable' : 'No Saludable';

        $p6 = (int) $_POST['p6']; $p11 = (int) $_POST['p11']; $p27 = (int) $_POST['p27']; $p36 = (int) $_POST['p36']; $p40 = (int) $_POST['p40']; $p41 = (int) $_POST['p41']; $p45 = (int) $_POST['p45'];
        $total_estres = $p6 + $p11 + $p27 + $p36 + $p40 + $p41 + $p45;
        $saludable_estres = ($total_estres > 17) ? 'Saludable' : 'No Saludable';

        $p3 = (int) $_POST['p3']; $p8 = (int) $_POST['p8']; $p9 = (int) $_POST['p9']; $p12 = (int) $_POST['p12']; $p16 = (int) $_POST['p16']; $p17 = (int) $_POST['p17']; $p21 = (int) $_POST['p21']; $p23 = (int) $_POST['p23']; $p29 = (int) $_POST['p29']; $p34 = (int) $_POST['p34']; $p37 = (int) $_POST['p37']; $p44 = (int) $_POST['p44']; $p48 = (int) $_POST['p48'];
        $total_auto = $p3 + $p8 + $p9 + $p12 + $p16 + $p17 + $p21 + $p23 + $p29 + $p34 + $p37 + $p44 + $p48;
        $saludable_auto = ($total_auto > 32) ? 'Saludable' : 'No Saludable';

        $total_general = $total_nutricion + $total_ejercicio + $total_salud + $total_soporte + $total_estres + $total_auto;
        $estado_saludable = ($total_general > 120) ? 'Saludable' : 'No Saludable';

        // Insertar en la tabla estilo_de_vida
        $fecha_actual = date('Y-m-d H:i:s');
        $sql_cuestionario = "INSERT INTO estilo_de_vida (matricula_alum, total, fecha, estado_saludable) VALUES (?, ?, ?, ?)";
        $stmt_cuestionario = $conn->prepare($sql_cuestionario);
        $stmt_cuestionario->bind_param("siss", $matricula, $total_general, $fecha_actual, $estado_saludable);
        
        if ($stmt_cuestionario->execute()) {
            $id_cuestionario = $conn->insert_id;
            
            // Insertar Nutricion
            $sql_nutricion = "INSERT INTO nutricion (id_cuestionario, p1, p5, p14, p19, p26, p35, total_nutricion, saludable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_nutricion = $conn->prepare($sql_nutricion);
            $stmt_nutricion->bind_param("iiiiiiiss", $id_cuestionario, $p1, $p5, $p14, $p19, $p26, $p35, $total_nutricion, $saludable_nutricion);
            $stmt_nutricion->execute();
            $stmt_nutricion->close();
            
            // Insertar Ejercicio
            $sql_ejercicio = "INSERT INTO ejercicio (id_cuestionario, p4, p13, p22, p30, p38, total_ejercicio, saludable_ejercicio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_ejercicio = $conn->prepare($sql_ejercicio);
            $stmt_ejercicio->bind_param("iiiiiiis", $id_cuestionario, $p4, $p13, $p22, $p30, $p38, $total_ejercicio, $saludable_ejercicio);
            $stmt_ejercicio->execute();
            $stmt_ejercicio->close();

            // Insertar Salud
            $sql_salud = "INSERT INTO salud (id_cuestionario, p2, p7, p15, p20, p28, p32, p33, p42, p43, p46, total_salud, saludable_salud) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_salud = $conn->prepare($sql_salud);
            $stmt_salud->bind_param("iiiiiiiiiiiis", $id_cuestionario, $p2, $p7, $p15, $p20, $p28, $p32, $p33, $p42, $p43, $p46, $total_salud, $saludable_salud);
            $stmt_salud->execute();
            $stmt_salud->close();

            // Insertar Soporte
            $sql_soporte = "INSERT INTO soporte_interpersonal (id_cuestionario, p10, p18, p24, p25, p31, p39, p47, total_soporte, saludable_soporte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_soporte = $conn->prepare($sql_soporte);
            $stmt_soporte->bind_param("iiiiiiiiis", $id_cuestionario, $p10, $p18, $p24, $p25, $p31, $p39, $p47, $total_soporte, $saludable_soporte);
            $stmt_soporte->execute();
            $stmt_soporte->close();

            // Insertar Estres
            $sql_estres = "INSERT INTO manejo_de_estres (id_cuestionario, p6, p11, p27, p36, p40, p41, p45, total_manejoestres, saludable_manejo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_estres = $conn->prepare($sql_estres);
            $stmt_estres->bind_param("iiiiiiiiis", $id_cuestionario, $p6, $p11, $p27, $p36, $p40, $p41, $p45, $total_estres, $saludable_estres);
            $stmt_estres->execute();
            $stmt_estres->close();

            // Insertar Autoactualizacion
            $sql_auto = "INSERT INTO autoactualizacion (id_cuestionario, p3, p8, p9, p12, p16, p17, p21, p23, p29, p34, p37, p44, p48, total_autoactualizacion, saludable_autoactualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_auto = $conn->prepare($sql_auto);
            $stmt_auto->bind_param("iiiiiiiiiiiiiiis", $id_cuestionario, $p3, $p8, $p9, $p12, $p16, $p17, $p21, $p23, $p29, $p34, $p37, $p44, $p48, $total_auto, $saludable_auto);
            $stmt_auto->execute();
            $stmt_auto->close();

            $conn->close();
            header('Location: menuAlum.php');
            exit;
        } else {
            $error_message = "Error al guardar.";
        }
        $stmt_cuestionario->close();
    }
}
$conn->close();

// --- FUNCIÓN AGREGADA AQUÍ ---
function renderOpcionesPEPS($idPregunta) {
    echo '
    <div class="options-group">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_1" value="1" required>
            <label class="form-check-label" for="'.$idPregunta.'_1">Nunca</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_2" value="2">
            <label class="form-check-label" for="'.$idPregunta.'_2">A veces</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_3" value="3">
            <label class="form-check-label" for="'.$idPregunta.'_3">Frecuentemente</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="'.$idPregunta.'" id="'.$idPregunta.'_4" value="4">
            <label class="form-check-label" for="'.$idPregunta.'_4">Rutinariamente</label>
        </div>
    </div>';
}
// -----------------------------
?>

<!doctype html>
<html lang="es">
<head>
    <title>Cuestionario PEPS-1</title>
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
        <p>Perfil de Estilo de Vida (PEPS-1)</p>
    </div>

    <div class="form-container">
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="instructions-box">
            <h5 class="text-primary mb-3">Instrucciones:</h5>
            <ul class="mb-0" style="padding-left: 1.2rem;">
                <li>Este cuestionario pregunta sobre tus hábitos personales actuales.</li>
                <li>No hay respuestas correctas o incorrectas.</li>
                <li>Escoge la respuesta que refleje mejor tu forma de vivir.</li>
                <li><strong>1=Nunca, 2=A veces, 3=Frecuentemente, 4=Rutinariamente</strong></li>
            </ul>
        </div>

        <form action="PEPS-1.php" method="post">
            <div class="form-section">
                <h4 class="section-title">Preguntas</h4>
                
                <div class="questions-grid">
                    <?php
                    $preguntas = [
                        1 => "Tomas algún alimento al levantarte por las mañanas",
                        2 => "Relatas al médico cualquier síntoma extraño relacionado con tu salud",
                        3 => "Te quieres a ti misma(o)",
                        4 => "Realizas ejercicios para relajar tus músculos al menos 3 veces por día o por semana",
                        5 => "Seleccionas comidas que no contienen ingredientes artificiales o químicos",
                        6 => "Tomas tiempo cada dia para el relajamiento",
                        7 => "Conoces el nivel de colesterol en tu sangre",
                        8 => "Eres entusiasta y optimista con referencia a tu vida",
                        9 => "Crees que estás creciendo y cambiando personalmente en direcciones positivas",
                        10 => "Discutes con personas cercanas tus preocupaciones y problemas personales",
                        11 => "Eres consciente de las fuentes que producen tensión en tu vida",
                        12 => "Te sientes feliz y contento(a)",
                        13 => "Realizas ejercicio vigoroso por 20 o 30 minutos al menos tres veces a la semana",
                        14 => "Comes tres comidas al día",
                        15 => "Lees revistas o folletos sobre como cuidar tu salud",
                        16 => "Eres consciente de tus capacidades y debilidades personales",
                        17 => "Trabajas en apoyo de metas a largo plazo en tu vida",
                        18 => "Elogias fácilmente a otras personas por sus éxitos",
                        19 => "Lees las etiquetas de las comidas para identificar nutrientes",
                        20 => "Buscas otra opinión médica cuando no estas de acuerdo con la recomendación",
                        21 => "Miras hacia el futuro",
                        22 => "Participas en programas de ejercicio físico bajo supervisión",
                        23 => "Eres consciente de lo que te importa en la vida",
                        24 => "Te gusta expresar y que te expresen cariño personas cercanas a ti",
                        25 => "Mantienes relaciones interpersonales que te dan satisfacción",
                        26 => "Incluyes en tu dieta alimentos que contienen fibra",
                        27 => "Pasas de 15 a 20 minutos diariamente en relajamiento o meditación",
                        28 => "Discutes con profesionales calificados tus inquietudes de salud",
                        29 => "Respetas tus propios éxitos",
                        30 => "Checas tu pulso durante el ejercicio físico",
                        31 => "Pasas tiempo con amigos cercanos",
                        32 => "Haces medir tu presión arterial y sabes el resultado",
                        33 => "Asistes a programas educativos sobre el mejoramiento del medio ambiente",
                        34 => "Ves cada día como interesante y desafiante",
                        35 => "Planeas comidas que incluyan los cuatro grupos básicos de nutrientes",
                        36 => "Relajas conscientemente tus músculos antes de dormir",
                        37 => "Encuentras agradable y satisfecho el ambiente de tu vida",
                        38 => "Realizas actividades físicas de recreo (caminar, nadar, etc.)",
                        39 => "Expresas fácilmente interés, amor y calor humano hacia otros",
                        40 => "Te concentras en pensamientos agradables a la hora de dormir",
                        41 => "Pides información a los profesionales para cuidar de tu salud",
                        42 => "Encuentras maneras positivas para expresar tus sentimientos",
                        43 => "Observas al menos cada mes tu cuerpo para ver cambios físicos",
                        44 => "Eres realista en las metas que te propones",
                        45 => "Usas métodos específicos para controlar la tensión",
                        46 => "Asistes a programas educativos sobre el cuidado de la salud personal",
                        47 => "Te gusta mostrar y que te muestren afecto (abrazos, caricias)",
                        48 => "Crees que tu vida tiene un propósito"
                    ];

                    foreach ($preguntas as $num => $texto) {
                        echo '<div class="question-card">';
                        echo '<label class="question-label">' . $num . '. ' . $texto . '</label>';
                        renderOpcionesPEPS('p' . $num);
                        echo '</div>';
                    }
                    ?>
                </div> </div>

            <div class="submit-container">
                <button class="btn-submit" type="submit">Enviar Resultados</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>