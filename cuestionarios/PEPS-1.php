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
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
<title>Cuestionario de Bienestar</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --bg:#0f172a;--card:#1e293b;--border:#334155;
    --accent:#6366f1;--accent2:#8b5cf6;
    --yellow:#fbbf24;--text:#f1f5f9;--muted:#94a3b8;
}
html,body{height:100%;overflow:hidden;}
body{
    font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);
    display:flex;flex-direction:column;
    touch-action:manipulation;-webkit-tap-highlight-color:transparent;
}
/* Stories bar */
.stories-bar{display:flex;gap:3px;padding:14px 14px 0;flex-shrink:0;}
.story-seg{flex:1;height:4px;border-radius:2px;background:rgba(255,255,255,.15);}
.story-seg.done{background:var(--accent);}
.story-seg.cur{background:linear-gradient(90deg,var(--accent),var(--accent2));}
/* Header */
.top-row{display:flex;align-items:center;justify-content:space-between;padding:10px 16px 14px;flex-shrink:0;}
.q-counter{font-size:13px;font-weight:700;color:var(--muted);}
.q-counter span{color:var(--text);}
.streak{display:flex;align-items:center;gap:5px;background:rgba(251,191,36,.12);
    border:1px solid rgba(251,191,36,.25);padding:4px 12px;border-radius:20px;
    font-size:13px;font-weight:700;color:var(--yellow);}
/* Main */
.main{flex:1;display:flex;flex-direction:column;align-items:center;
    justify-content:flex-start;padding:0 18px 8px;overflow:hidden;}
/* Emoji */
.emoji-wrap{margin-bottom:8px;animation:popIn .4s cubic-bezier(.34,1.56,.64,1);}
@keyframes popIn{from{transform:scale(.3);opacity:0}to{transform:scale(1);opacity:1}}
.q-emoji{font-size:clamp(54px,13vw,74px);line-height:1;display:block;text-align:center;}
/* Texto */
.q-text{font-size:clamp(1rem,4.2vw,1.3rem);font-weight:700;line-height:1.45;
    text-align:center;margin-bottom:20px;max-width:520px;
    animation:slideUp .35s ease;}
@keyframes slideUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
/* Opciones */
.opts{display:flex;flex-direction:column;gap:10px;width:100%;max-width:440px;
    animation:slideUp .4s ease .05s both;}
.opt{
    display:flex;align-items:center;gap:14px;
    background:var(--card);border:2px solid var(--border);
    border-radius:16px;padding:14px 18px;
    cursor:pointer;transition:all .18s;
    -webkit-tap-highlight-color:transparent;
    text-align:left;width:100%;font-family:'Poppins',sans-serif;color:var(--text);
}
.opt:active{transform:scale(.97);}
.opt:hover,.opt:focus{border-color:var(--accent);background:#1e2a4a;}
.opt.sel{
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    border-color:var(--accent);box-shadow:0 6px 20px rgba(99,102,241,.35);
    transform:scale(1.02);
}
.opt-left{display:flex;align-items:center;gap:10px;flex:1;}
.opt-emoji{font-size:22px;flex-shrink:0;}
.opt-label{font-size:.95rem;font-weight:600;}
.opt-key{background:rgba(255,255,255,.08);color:var(--muted);font-size:11px;font-weight:700;
    width:24px;height:24px;border-radius:6px;display:flex;align-items:center;
    justify-content:center;flex-shrink:0;}
.opt.sel .opt-key{background:rgba(255,255,255,.2);color:white;}
/* Nav */
.nav-row{display:flex;align-items:center;justify-content:center;gap:14px;
    padding:10px 18px 16px;flex-shrink:0;}
.btn-back{background:var(--card);border:2px solid var(--border);color:var(--muted);
    font-family:'Poppins',sans-serif;font-size:.85rem;font-weight:600;
    padding:10px 20px;border-radius:50px;cursor:pointer;transition:all .2s;}
.btn-back:hover{border-color:var(--accent);color:var(--text);}
.btn-back:disabled{opacity:.3;cursor:default;}
.tip-key{font-size:11px;color:var(--muted);opacity:.55;}
/* Milestone overlay */
.milestone{position:fixed;inset:0;z-index:100;display:flex;flex-direction:column;
    align-items:center;justify-content:center;background:rgba(15,23,42,.93);
    backdrop-filter:blur(10px);text-align:center;padding:32px;
    animation:fadeM .3s ease;}
@keyframes fadeM{from{opacity:0}to{opacity:1}}
.milestone.hidden{display:none;}
.m-emoji{font-size:80px;animation:popIn .5s cubic-bezier(.34,1.56,.64,1);}
.m-title{font-size:1.8rem;font-weight:800;margin:18px 0 8px;}
.m-sub{font-size:1rem;color:var(--muted);margin-bottom:30px;line-height:1.5;}
.btn-cont{background:linear-gradient(135deg,var(--accent),var(--accent2));
    color:white;font-family:'Poppins',sans-serif;font-weight:700;font-size:1rem;
    padding:14px 36px;border-radius:50px;border:none;cursor:pointer;
    box-shadow:0 6px 20px rgba(99,102,241,.4);}
/* Confetti */
#cfv{position:fixed;inset:0;pointer-events:none;z-index:99;}
/* Pantalla final */
.s-final{position:fixed;inset:0;z-index:200;display:flex;flex-direction:column;
    align-items:center;justify-content:center;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    text-align:center;padding:32px;animation:fadeM .4s ease;}
.s-final.hidden{display:none;}
.f-emoji{font-size:90px;animation:popIn .6s cubic-bezier(.34,1.56,.64,1);}
.f-title{font-size:2rem;font-weight:800;margin:20px 0 10px;}
.f-sub{font-size:1rem;opacity:.88;margin-bottom:32px;line-height:1.6;}
.btn-send{background:white;color:#6366f1;font-family:'Poppins',sans-serif;
    font-weight:800;font-size:1.05rem;padding:16px 40px;border-radius:50px;
    border:none;cursor:pointer;box-shadow:0 8px 28px rgba(0,0,0,.2);}
</style>
</head>
<body>

<div class="stories-bar" id="sb"></div>
<div class="top-row">
    <div class="q-counter" id="qc">Pregunta <span>1</span> de 48</div>
    <div class="streak" id="stk">🔥 0</div>
</div>
<div class="main" id="main"></div>
<div class="nav-row">
    <button class="btn-back" id="btn-back" onclick="goBack()" disabled>← Atrás</button>
    <div class="tip-key">Presiona 1 2 3 4 en teclado</div>
</div>

<!-- Hito -->
<div class="milestone hidden" id="mstone">
    <div class="m-emoji" id="m-e"></div>
    <div class="m-title" id="m-t"></div>
    <div class="m-sub" id="m-s"></div>
    <button class="btn-cont" onclick="closeMilestone()">¡Seguir! 🚀</button>
</div>

<canvas id="cfv"></canvas>

<!-- Final -->
<div class="s-final hidden" id="sfinal">
    <div class="f-emoji">🎉</div>
    <div class="f-title">¡Lo lograste!</div>
    <div class="f-sub">Respondiste las 48 preguntas.<br>Tus resultados están listos.</div>
    <button class="btn-send" onclick="submitForm()">Ver mis resultados ✅</button>
</div>

<form id="hf" action="PEPS-1.php" method="post" style="display:none"></form>

<script>
const EM={1:'🌅',2:'👨‍⚕️',3:'💖',4:'💪',5:'🥦',6:'😌',7:'🩸',8:'🌟',9:'🌱',10:'💬',
11:'⚡',12:'😊',13:'🏃',14:'🍽️',15:'📖',16:'🧠',17:'🎯',18:'🙌',19:'🏷️',20:'🔍',
21:'🔭',22:'🏋️',23:'🧭',24:'🤗',25:'👫',26:'🌾',27:'🧘',28:'💊',29:'🏆',30:'❤️',
31:'👥',32:'🩺',33:'🌍',34:'🎉',35:'🥗',36:'😴',37:'🌈',38:'🚴',39:'❤️‍🔥',40:'💭',
41:'📚',42:'🎭',43:'🔎',44:'✅',45:'🛡️',46:'🎓',47:'🫂',48:'✨'};
const OE=['😶','🤔','😊','🌟'];
const OL=['Nunca','A veces','Frecuentemente','Rutinariamente'];
const PQ={1:"¿Tomas algún alimento al levantarte por las mañanas?",2:"¿Relatas al médico cualquier síntoma extraño de tu salud?",3:"¿Te quieres a ti misma(o)?",4:"¿Realizas ejercicios para relajar tus músculos al menos 3 veces por semana?",5:"¿Seleccionas comidas sin ingredientes artificiales o químicos?",6:"¿Tomas tiempo cada día para relajarte?",7:"¿Conoces el nivel de colesterol en tu sangre?",8:"¿Eres entusiasta y optimista con tu vida?",9:"¿Crees que estás creciendo y cambiando en dirección positiva?",10:"¿Discutes con personas cercanas tus preocupaciones personales?",11:"¿Eres consciente de las fuentes que te generan tensión?",12:"¿Te sientes feliz y contento(a)?",13:"¿Realizas ejercicio vigoroso por 20–30 min al menos 3 veces por semana?",14:"¿Comes tres comidas al día?",15:"¿Lees sobre cómo cuidar tu salud?",16:"¿Eres consciente de tus capacidades y debilidades?",17:"¿Trabajas hacia metas a largo plazo en tu vida?",18:"¿Elogias fácilmente a otros por sus éxitos?",19:"¿Lees las etiquetas de los alimentos para identificar nutrientes?",20:"¿Buscas otra opinión médica cuando no estás de acuerdo?",21:"¿Miras hacia el futuro con esperanza?",22:"¿Participas en programas de ejercicio bajo supervisión?",23:"¿Eres consciente de lo que te importa en la vida?",24:"¿Te gusta expresar y recibir cariño de personas cercanas?",25:"¿Mantienes relaciones que te dan satisfacción?",26:"¿Incluyes alimentos con fibra en tu dieta?",27:"¿Pasas 15–20 min diariamente en relajamiento o meditación?",28:"¿Discutes con profesionales tus inquietudes de salud?",29:"¿Respetas y reconoces tus propios éxitos?",30:"¿Checas tu pulso durante el ejercicio físico?",31:"¿Pasas tiempo de calidad con amigos cercanos?",32:"¿Te mides la presión arterial y conoces el resultado?",33:"¿Asistes a programas educativos sobre el medio ambiente?",34:"¿Ves cada día como interesante y desafiante?",35:"¿Planeas comidas con los cuatro grupos básicos de nutrientes?",36:"¿Relajas conscientemente tus músculos antes de dormir?",37:"¿Encuentras el ambiente de tu vida agradable y satisfactorio?",38:"¿Realizas actividades físicas de recreo (caminar, nadar, bailar...)?",39:"¿Expresas fácilmente interés, amor y calidez hacia otros?",40:"¿Te concentras en pensamientos agradables a la hora de dormir?",41:"¿Pides información a profesionales para cuidar tu salud?",42:"¿Encuentras maneras positivas de expresar tus sentimientos?",43:"¿Observas tu cuerpo al menos cada mes para notar cambios?",44:"¿Eres realista con las metas que te propones?",45:"¿Usas métodos específicos para controlar la tensión?",46:"¿Asistes a programas educativos sobre cuidado de la salud?",47:"¿Te gusta mostrar y recibir afecto físico (abrazos, caricias)?",48:"¿Crees que tu vida tiene un propósito?"};
const MS={12:{e:'🔥',t:'¡Vas increíble!',s:'Ya llevas 12 respuestas. ¡Sigue así!'},
24:{e:'⚡',t:'¡Mitad del camino!',s:'24 de 48. ¡Eres imparable!'},
36:{e:'💎',t:'¡Casi lo tienes!',s:'Solo 12 más. ¡No pares ahora!'}};

const ans={};let cur=1,streak=0,pending=null;

function buildBar(){
    const b=document.getElementById('sb');b.innerHTML='';
    for(let i=1;i<=48;i++){
        const s=document.createElement('div');
        s.className='story-seg'+(i<cur?' done':i===cur?' cur':'');
        b.appendChild(s);
    }
}
function render(){
    const sel=ans[cur]??null;
    document.getElementById('qc').innerHTML=`Pregunta <span>${cur}</span> de 48`;
    document.getElementById('btn-back').disabled=cur<=1;
    document.getElementById('stk').textContent='🔥 '+streak;
    document.getElementById('main').innerHTML=`
        <div class="emoji-wrap"><span class="q-emoji">${EM[cur]}</span></div>
        <div class="q-text">${PQ[cur]}</div>
        <div class="opts">
            ${OL.map((l,i)=>`<button class="opt${sel===i+1?' sel':''}" onclick="pick(${i+1})">
                <div class="opt-left"><span class="opt-emoji">${OE[i]}</span><span class="opt-label">${l}</span></div>
                <span class="opt-key">${i+1}</span></button>`).join('')}
        </div>`;
    buildBar();
}
function pick(v){
    const first=ans[cur]===undefined;
    ans[cur]=v;
    if(first)streak++;
    render();
    setTimeout(()=>{
        if(MS[cur]&&first){pending=cur;showMilestone(cur);}
        else advance();
    },380);
}
function advance(){if(cur<48){cur++;render();}else showFinal();save();}
function goBack(){if(cur>1){cur--;render();}}
function showMilestone(n){
    const m=MS[n];
    document.getElementById('m-e').textContent=m.e;
    document.getElementById('m-t').textContent=m.t;
    document.getElementById('m-s').textContent=m.s;
    document.getElementById('mstone').classList.remove('hidden');
    confetti();
}
function closeMilestone(){
    document.getElementById('mstone').classList.add('hidden');
    if(pending!==null){pending=null;advance();}
}
function showFinal(){confetti();setTimeout(()=>document.getElementById('sfinal').classList.remove('hidden'),300);}
function submitForm(){
    if(Object.keys(ans).length<48){alert('Faltan preguntas.');return;}
    const f=document.getElementById('hf');f.innerHTML='';
    for(let i=1;i<=48;i++){const ip=document.createElement('input');ip.type='hidden';ip.name='p'+i;ip.value=ans[i]||'';f.appendChild(ip);}
    localStorage.removeItem('peps1_draft');f.submit();
}
function confetti(){
    const c=document.getElementById('cfv'),x=c.getContext('2d');
    c.width=window.innerWidth;c.height=window.innerHeight;
    const ps=Array.from({length:100},()=>({
        x:Math.random()*c.width,y:Math.random()*c.height-c.height,
        r:Math.random()*7+3,d:Math.random()*90,
        col:`hsl(${Math.random()*360},80%,60%)`,sp:Math.random()*3+1.5
    }));
    let fr;function draw(){x.clearRect(0,0,c.width,c.height);
    ps.forEach(p=>{x.beginPath();x.arc(p.x,p.y,p.r,0,Math.PI*2);x.fillStyle=p.col;x.fill();
    p.y+=p.sp;p.x+=Math.sin(p.d)*1.5;p.d+=.05;if(p.y>c.height)p.y=-10;});fr=requestAnimationFrame(draw);}
    draw();setTimeout(()=>{cancelAnimationFrame(fr);x.clearRect(0,0,c.width,c.height);},3500);
}
function save(){localStorage.setItem('peps1_draft',JSON.stringify({ans,cur,streak}));}
(function load(){try{const d=JSON.parse(localStorage.getItem('peps1_draft')||'null');
    if(d){Object.assign(ans,d.ans||{});cur=d.cur||1;streak=d.streak||0;}}catch(e){}})();
setInterval(save,4000);
document.addEventListener('keydown',e=>{
    if(!document.getElementById('mstone').classList.contains('hidden')){
        if(e.key==='Enter'||e.key===' ')closeMilestone();return;}
    const v=parseInt(e.key);if(v>=1&&v<=4){pick(v);return;}
    if(e.key==='ArrowLeft')goBack();
});
render();
</script>
</body>
</html>
