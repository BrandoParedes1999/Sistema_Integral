<?php
require_once '../config/config.php';
session_start();

// Deshabilitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Seguridad
if (!isset($_SESSION['alumno']) || !isset($_SESSION['alumno']['matricula'])) {
    header("Location: registro.html");
    exit;
}
date_default_timezone_set('America/Mexico_City');
$alumno = $_SESSION['alumno'];
$matricula = $alumno['matricula'];

// Conexión

$conn = getDBConnection();
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

?>
<!doctype html>
<html lang="es">
<head>
    <title>Cuestionario PEPS-1</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
    <style>
        :root {
            --primary: #003da5;
            --primary-dark: #002a70;
            --secondary: #ffd100;
            --white: #ffffff;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #003da5 0%, #0056e0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0;
            overflow-x: hidden;
        }

        /* ── BARRA SUPERIOR ── */
        .top-bar {
            width: 100%;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(0,0,0,0.15);
        }
        .top-bar .logo-text {
            color: rgba(255,255,255,0.9);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .top-bar .counter {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            font-weight: 600;
            background: rgba(255,255,255,0.15);
            padding: 4px 12px;
            border-radius: 20px;
        }

        /* ── BARRA DE PROGRESO ── */
        .progress-wrap {
            width: 100%;
            height: 5px;
            background: rgba(255,255,255,0.2);
        }
        .progress-fill {
            height: 100%;
            background: var(--secondary);
            transition: width 0.4s ease;
            border-radius: 0 3px 3px 0;
        }

        /* ── PANTALLA INTRO ── */
        .screen-intro {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 24px;
            max-width: 600px;
            margin: 0 auto;
            min-height: 80vh;
            color: white;
        }
        .screen-intro .emoji-big { font-size: 64px; margin-bottom: 20px; }
        .screen-intro h1 { font-size: 2rem; font-weight: 700; margin-bottom: 12px; }
        .screen-intro p { font-size: 1rem; opacity: 0.88; line-height: 1.7; margin-bottom: 32px; }
        .screen-intro .key-hint {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 16px 24px;
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 36px;
            line-height: 1.8;
        }
        .screen-intro .key-hint span {
            display: inline-block;
            background: var(--secondary);
            color: #003da5;
            font-weight: 700;
            border-radius: 6px;
            padding: 0 8px;
            margin-right: 4px;
        }

        /* ── PANTALLA DE SECCIÓN ── */
        .screen-section {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 24px;
            max-width: 600px;
            margin: 0 auto;
            min-height: 80vh;
            color: white;
        }
        .screen-section .section-emoji { font-size: 72px; margin-bottom: 20px; }
        .screen-section h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; }
        .screen-section p { font-size: 1rem; opacity: 0.88; margin-bottom: 32px; }

        /* ── PANTALLA DE PREGUNTA ── */
        .screen-question {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px 40px;
            width: 100%;
            max-width: 680px;
            margin: 0 auto;
            min-height: 80vh;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.9);
            font-size: 12px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 22px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .question-number {
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .question-text {
            font-size: clamp(1.1rem, 3vw, 1.45rem);
            font-weight: 600;
            color: white;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 36px;
            max-width: 580px;
        }

        /* ── OPCIONES DE RESPUESTA ── */
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            width: 100%;
            max-width: 560px;
        }
        @media (max-width: 480px) {
            .options-grid { grid-template-columns: 1fr; }
        }

        .opt-btn {
            position: relative;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.25);
            color: white;
            border-radius: 14px;
            padding: 18px 16px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(6px);
        }
        .opt-btn:hover {
            background: rgba(255,255,255,0.22);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-2px);
        }
        .opt-btn.selected {
            background: var(--secondary);
            border-color: var(--secondary);
            color: #003da5;
            font-weight: 700;
            transform: scale(1.03);
            box-shadow: 0 8px 24px rgba(255,209,0,0.4);
        }
        .opt-btn .key-badge {
            background: rgba(255,255,255,0.25);
            color: inherit;
            font-size: 11px;
            font-weight: 700;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .opt-btn.selected .key-badge {
            background: rgba(0,61,165,0.15);
        }

        /* ── BOTONES DE NAVEGACIÓN ── */
        .nav-btns {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            align-items: center;
        }
        .btn-nav {
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-nav:hover { background: rgba(255,255,255,0.25); }
        .btn-nav.primary {
            background: var(--secondary);
            border-color: var(--secondary);
            color: #003da5;
        }
        .btn-nav.primary:hover { background: #ffe033; }
        .btn-nav:disabled { opacity: 0.35; cursor: not-allowed; }

        /* ── PANTALLA FINAL ── */
        .screen-final {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 24px;
            max-width: 600px;
            margin: 0 auto;
            min-height: 80vh;
            color: white;
        }
        .screen-final .big-emoji { font-size: 80px; margin-bottom: 24px; animation: pop 0.5s ease; }
        @keyframes pop { 0%{transform:scale(0.5);opacity:0} 80%{transform:scale(1.15)} 100%{transform:scale(1);opacity:1} }
        .screen-final h2 { font-size: 2rem; font-weight: 700; margin-bottom: 12px; }
        .screen-final p { font-size: 1rem; opacity: 0.88; margin-bottom: 36px; line-height: 1.7; }

        /* ── ANIMACIONES DE TRANSICIÓN ── */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideOut {
            from { opacity: 1; transform: translateY(0); }
            to   { opacity: 0; transform: translateY(-30px); }
        }
        .animate-in  { animation: slideIn  0.35s ease forwards; }
        .animate-out { animation: slideOut 0.25s ease forwards; }

        /* ── FORMULARIO OCULTO ── */
        #hidden-form { display: none; }
    </style>
</head>

<body>

<!-- Barra superior -->
<div class="top-bar">
    <span class="logo-text">UNACAR · Perfil de Estilo de Vida</span>
    <span class="counter" id="top-counter">0 / 48</span>
</div>
<div class="progress-wrap">
    <div class="progress-fill" id="progress-fill" style="width:0%"></div>
</div>

<!-- ══ PANTALLA: INTRO ══ -->
<div class="screen-intro" id="screen-intro">
    <div class="emoji-big">👋</div>
    <h1>¡Hola, <?php echo htmlspecialchars($alumno['nombres_alum'] ?? 'estudiante'); ?>!</h1>
    <p>Vamos a explorar tus hábitos de vida con <strong>48 preguntas cortas</strong>.<br>
       No hay respuestas buenas ni malas — solo sé honesto(a) contigo mismo(a).</p>
    <div class="key-hint">
        Elige con los botones o presiona <span>1</span> <span>2</span> <span>3</span> <span>4</span> en tu teclado<br>
        <span style="background:rgba(255,255,255,0.2);color:white;">→</span> avanza · <span style="background:rgba(255,255,255,0.2);color:white;">←</span> retrocede
    </div>
    <button class="btn-nav primary" onclick="startQuiz()">¡Empecemos! &nbsp;🚀</button>
</div>

<!-- ══ PANTALLA: PREGUNTA (se genera desde JS) ══ -->
<div class="screen-question" id="screen-question" style="display:none;"></div>

<!-- ══ PANTALLA: PAUSA DE SECCIÓN (se genera desde JS) ══ -->
<div class="screen-section" id="screen-section"></div>

<!-- ══ PANTALLA: FINAL ══ -->
<div class="screen-final" id="screen-final">
    <div class="big-emoji">🎉</div>
    <h2>¡Listo, lo lograste!</h2>
    <p>Respondiste las 48 preguntas. Tus resultados ayudarán a personalizar tu plan de bienestar.</p>
    <button class="btn-nav primary" onclick="submitForm()" id="btn-submit-final">Enviar mis respuestas &nbsp;✅</button>
</div>

<!-- ══ FORMULARIO OCULTO ══ -->
<form id="hidden-form" action="PEPS-1.php" method="post"></form>

<script>
// ─── DATOS ───────────────────────────────────────────────
const OPCIONES = ['Nunca','A veces','Frecuentemente','Rutinariamente'];

const SECCIONES = [
    {
        emoji: '🥗', nombre: 'Nutrición',
        desc: 'Preguntas sobre tus hábitos alimenticios.',
        preguntas: [1,5,14,19,26,35]
    },
    {
        emoji: '🏃', nombre: 'Ejercicio',
        desc: 'Tu nivel de actividad física.',
        preguntas: [4,13,22,30,38]
    },
    {
        emoji: '🩺', nombre: 'Responsabilidad en Salud',
        desc: 'Qué tan al tanto estás de tu salud.',
        preguntas: [2,7,15,20,28,32,33,42,43,46]
    },
    {
        emoji: '🤝', nombre: 'Soporte Interpersonal',
        desc: 'Tus relaciones con los demás.',
        preguntas: [10,18,24,25,31,39,47]
    },
    {
        emoji: '🧘', nombre: 'Manejo de Estrés',
        desc: 'Cómo manejas la tensión del día a día.',
        preguntas: [6,11,27,36,40,41,45]
    },
    {
        emoji: '✨', nombre: 'Autoactualizacion',
        desc: 'Tu crecimiento personal y propósito de vida.',
        preguntas: [3,8,9,12,16,17,21,23,29,34,37,44,48]
    }
];

const PREGUNTAS = {
    1:"Tomas algún alimento al levantarte por las mañanas",
    2:"Relatas al médico cualquier síntoma extraño relacionado con tu salud",
    3:"Te quieres a ti misma(o)",
    4:"Realizas ejercicios para relajar tus músculos al menos 3 veces por día o por semana",
    5:"Seleccionas comidas que no contienen ingredientes artificiales o químicos",
    6:"Tomas tiempo cada día para el relajamiento",
    7:"Conoces el nivel de colesterol en tu sangre",
    8:"Eres entusiasta y optimista con referencia a tu vida",
    9:"Crees que estás creciendo y cambiando personalmente en direcciones positivas",
    10:"Discutes con personas cercanas tus preocupaciones y problemas personales",
    11:"Eres consciente de las fuentes que producen tensión en tu vida",
    12:"Te sientes feliz y contento(a)",
    13:"Realizas ejercicio vigoroso por 20 o 30 minutos al menos tres veces a la semana",
    14:"Comes tres comidas al día",
    15:"Lees revistas o folletos sobre cómo cuidar tu salud",
    16:"Eres consciente de tus capacidades y debilidades personales",
    17:"Trabajas en apoyo de metas a largo plazo en tu vida",
    18:"Elogias fácilmente a otras personas por sus éxitos",
    19:"Lees las etiquetas de las comidas para identificar nutrientes",
    20:"Buscas otra opinión médica cuando no estás de acuerdo con la recomendación",
    21:"Miras hacia el futuro",
    22:"Participas en programas de ejercicio físico bajo supervisión",
    23:"Eres consciente de lo que te importa en la vida",
    24:"Te gusta expresar y que te expresen cariño personas cercanas a ti",
    25:"Mantienes relaciones interpersonales que te dan satisfacción",
    26:"Incluyes en tu dieta alimentos que contienen fibra",
    27:"Pasas de 15 a 20 minutos diariamente en relajamiento o meditación",
    28:"Discutes con profesionales calificados tus inquietudes de salud",
    29:"Respetas tus propios éxitos",
    30:"Checas tu pulso durante el ejercicio físico",
    31:"Pasas tiempo con amigos cercanos",
    32:"Haces medir tu presión arterial y sabes el resultado",
    33:"Asistes a programas educativos sobre el mejoramiento del medio ambiente",
    34:"Ves cada día como interesante y desafiante",
    35:"Planeas comidas que incluyan los cuatro grupos básicos de nutrientes",
    36:"Relajas conscientemente tus músculos antes de dormir",
    37:"Encuentras agradable y satisfecho el ambiente de tu vida",
    38:"Realizas actividades físicas de recreo (caminar, nadar, etc.)",
    39:"Expresas fácilmente interés, amor y calor humano hacia otros",
    40:"Te concentras en pensamientos agradables a la hora de dormir",
    41:"Pides información a los profesionales para cuidar de tu salud",
    42:"Encuentras maneras positivas para expresar tus sentimientos",
    43:"Observas al menos cada mes tu cuerpo para ver cambios físicos",
    44:"Eres realista en las metas que te propones",
    45:"Usas métodos específicos para controlar la tensión",
    46:"Asistes a programas educativos sobre el cuidado de la salud personal",
    47:"Te gusta mostrar y que te muestren afecto (abrazos, caricias)",
    48:"Crees que tu vida tiene un propósito"
};

// Orden lineal de preguntas agrupadas por sección
const ORDER = []; // [{type:'section',idx}, {type:'question',num}]
SECCIONES.forEach((sec, si) => {
    ORDER.push({ type: 'section', idx: si });
    sec.preguntas.forEach(num => ORDER.push({ type: 'question', num }));
});

// Estado
const answers = {};
let cursor = 0; // índice en ORDER
let started = false;

// ─── UTILIDADES ──────────────────────────────────────────
function answeredCount() { return Object.keys(answers).length; }

function updateProgress() {
    const pct = (answeredCount() / 48) * 100;
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('top-counter').textContent = answeredCount() + ' / 48';
}

function sectionOfQuestion(num) {
    return SECCIONES.find(s => s.preguntas.includes(num));
}

// ─── INICIO ──────────────────────────────────────────────
function startQuiz() {
    hide('screen-intro');
    cursor = 0;
    showCurrent(true);
}

// ─── MOSTRAR PANTALLA ACTUAL ──────────────────────────────
function showCurrent(forward = true) {
    if (cursor >= ORDER.length) { showFinal(); return; }

    const item = ORDER[cursor];
    if (item.type === 'section') {
        showSectionCard(item.idx, forward);
    } else {
        showQuestionCard(item.num, forward);
    }
}

function showSectionCard(idx, forward) {
    const sec = SECCIONES[idx];
    hide('screen-question');
    const el = document.getElementById('screen-section');
    el.innerHTML = `
        <div class="section-emoji">${sec.emoji}</div>
        <h2>${sec.nombre}</h2>
        <p>${sec.desc}</p>
        <div style="display:flex;gap:12px;">
            ${cursor > 0 ? `<button class="btn-nav" onclick="goBack()">← Atrás</button>` : ''}
            <button class="btn-nav primary" onclick="advanceCursor(true)">Comenzar &nbsp;→</button>
        </div>`;
    el.style.display = 'flex';
    el.classList.remove('animate-in','animate-out');
    void el.offsetWidth;
    el.classList.add('animate-in');
}

function showQuestionCard(num, forward) {
    hide('screen-section');
    const sec = sectionOfQuestion(num);
    const qIndex = ORDER.filter(o => o.type === 'question' && ORDER.indexOf(o) <= cursor).length;
    const el = document.getElementById('screen-question');
    const selected = answers[num];

    el.innerHTML = `
        <div class="section-badge">${sec.emoji} ${sec.nombre}</div>
        <div class="question-number">Pregunta ${num} de 48</div>
        <div class="question-text">${PREGUNTAS[num]}</div>
        <div class="options-grid" id="opts-${num}">
            ${OPCIONES.map((label, i) => `
                <button type="button" class="opt-btn${selected === i+1 ? ' selected' : ''}"
                        onclick="selectAnswer(${num}, ${i+1})">
                    <span class="key-badge">${i+1}</span>
                    ${label}
                </button>`).join('')}
        </div>
        <div class="nav-btns">
            <button class="btn-nav" onclick="goBack()" ${cursor === 0 ? 'disabled' : ''}>← Atrás</button>
            <button class="btn-nav primary" id="btn-next" onclick="advanceCursor(true)" ${selected ? '' : 'disabled'}>
                ${cursor === ORDER.length - 1 ? 'Finalizar ✓' : 'Siguiente →'}
            </button>
        </div>`;

    el.style.display = 'flex';
    el.classList.remove('animate-in','animate-out');
    void el.offsetWidth;
    el.classList.add('animate-in');
}

// ─── SELECCIONAR RESPUESTA ───────────────────────────────
function selectAnswer(num, val) {
    answers[num] = val;
    updateProgress();
    // Actualizar botones
    document.querySelectorAll(`#opts-${num} .opt-btn`).forEach((btn, i) => {
        btn.classList.toggle('selected', i + 1 === val);
    });
    const btnNext = document.getElementById('btn-next');
    if (btnNext) btnNext.disabled = false;
    // Auto-avanzar con pequeño delay
    setTimeout(() => advanceCursor(true), 420);
}

// ─── NAVEGACIÓN ──────────────────────────────────────────
function advanceCursor(forward) {
    cursor++;
    showCurrent(forward);
}

function goBack() {
    if (cursor <= 0) return;
    cursor--;
    // Si retrocedemos a una sección, la mostramos; si a una pregunta, igual
    showCurrent(false);
}

// ─── OCULTAR PANTALLAS ───────────────────────────────────
function hide(id) {
    const el = document.getElementById(id);
    el.style.display = 'none';
}

function showFinal() {
    hide('screen-question');
    hide('screen-section');
    const el = document.getElementById('screen-final');
    el.style.display = 'flex';
    el.classList.remove('animate-in');
    void el.offsetWidth;
    el.classList.add('animate-in');
    updateProgress();
}

// ─── ENVIAR FORMULARIO ───────────────────────────────────
function submitForm() {
    if (answeredCount() < 48) {
        alert('Aún hay preguntas sin responder. Por favor completa el cuestionario.');
        return;
    }
    const form = document.getElementById('hidden-form');
    form.innerHTML = '';
    for (let i = 1; i <= 48; i++) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'p' + i;
        inp.value = answers[i] || '';
        form.appendChild(inp);
    }
    form.submit();
}

// ─── ATAJOS DE TECLADO ────────────────────────────────────
document.addEventListener('keydown', e => {
    if (!started && e.key === 'Enter') { startQuiz(); return; }
    if (cursor >= ORDER.length) return;
    const item = ORDER[cursor];
    if (item && item.type === 'question') {
        const val = parseInt(e.key);
        if (val >= 1 && val <= 4) { selectAnswer(item.num, val); return; }
    }
    if (e.key === 'ArrowRight' || e.key === 'Enter') {
        if (item && item.type === 'section') advanceCursor(true);
    }
    if (e.key === 'ArrowLeft') goBack();
});

// ─── RESTAURAR BORRADOR desde localStorage ───────────────
(function restoreDraft() {
    const saved = localStorage.getItem('peps1_draft');
    if (saved) {
        try {
            const data = JSON.parse(saved);
            Object.assign(answers, data);
        } catch(e) {}
    }
})();

// ─── GUARDAR BORRADOR automáticamente ───────────────────
setInterval(() => {
    if (answeredCount() > 0)
        localStorage.setItem('peps1_draft', JSON.stringify(answers));
}, 3000);
</script>

</body>
</html>