<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['alumno'])) {
    header('Location: login-alumno.html');
    exit;
}

$alumno = $_SESSION['alumno'];
$conn   = getDBConnection();
$conn->set_charset("utf8mb4");
$mat    = $alumno['matricula'];

$iniciales      = strtoupper(substr($alumno['nombre'],0,1) . substr($alumno['apepa'],0,1));
$nombreCompleto = htmlspecialchars($alumno['nombre'].' '.$alumno['apepa'].' '.$alumno['apema']);
$nomFacultad    = htmlspecialchars($alumno['nom_facultad'] ?? '');
$nomCarrera     = htmlspecialchars($alumno['nom_carrera']  ?? '');

// ── DASS-21 ────────────────────────────────────────────────────────────
$dass = null;
$s = $conn->prepare("SELECT total_depresion, total_ansiedad, total_estres, total_general
                     FROM dass WHERE matricula_alum = ?
                     ORDER BY id_cuestionario DESC LIMIT 1");
$s->bind_param("s", $mat); $s->execute();
$r = $s->get_result();
if ($r->num_rows > 0) $dass = $r->fetch_assoc();
$s->close();

// ── PEPS-1 ─────────────────────────────────────────────────────────────
$peps = null;
$s = $conn->prepare("SELECT total, estado_saludable FROM estilo_de_vida
                     WHERE matricula_alum = ?
                     ORDER BY id_cuestionario DESC LIMIT 1");
$s->bind_param("s", $mat); $s->execute();
$r = $s->get_result();
if ($r->num_rows > 0) $peps = $r->fetch_assoc();
$s->close();

// ── Datos físicos ──────────────────────────────────────────────────────
$fisicos = null;
$s = $conn->prepare("SELECT peso, talla, imc, clasificacion_imc,
                            glucosa, colesterol, trigliceridos, tension_arterial,
                            mb, actividad1, get1, fecha
                     FROM datos_fisicos_alumnos WHERE matricula_alum = ?
                     ORDER BY fecha DESC LIMIT 1");
$s->bind_param("s", $mat); $s->execute();
$r = $s->get_result();
if ($r->num_rows > 0) $fisicos = $r->fetch_assoc();
$s->close();

$conn->close();

// ── Helpers de severidad ───────────────────────────────────────────────
function sevLabel($v, $tipo) {
    if ($tipo === 'dep') {
        if ($v <=  4) return ['Normal','#10b981'];
        if ($v <=  6) return ['Leve','#3b82f6'];
        if ($v <= 10) return ['Moderado','#f59e0b'];
        if ($v <= 13) return ['Severo','#ef4444'];
        return ['Extremadamente Severo','#dc2626'];
    }
    if ($tipo === 'ans') {
        if ($v <=  3) return ['Normal','#10b981'];
        if ($v <=  4) return ['Leve','#3b82f6'];
        if ($v <=  7) return ['Moderado','#f59e0b'];
        if ($v <=  9) return ['Severo','#ef4444'];
        return ['Extremadamente Severo','#dc2626'];
    }
    // estres
    if ($v <=  7) return ['Normal','#10b981'];
    if ($v <=  9) return ['Leve','#3b82f6'];
    if ($v <= 12) return ['Moderado','#f59e0b'];
    if ($v <= 16) return ['Severo','#ef4444'];
    return ['Extremadamente Severo','#dc2626'];
}

function imcColor($cls) {
    $cls = mb_strtolower($cls ?? '');
    if (strpos($cls,'normal') !== false)    return '#10b981';
    if (strpos($cls,'sobrepeso') !== false) return '#f59e0b';
    if (strpos($cls,'insuf') !== false)     return '#3b82f6';
    return '#ef4444';
}

function actividadLabel($factor) {
    $map = ['1.2'=>'Sedentario','1.375'=>'Actividad Ligera','1.55'=>'Actividad Moderada',
            '1.725'=>'Actividad Intensa','1.9'=>'Actividad Muy Intensa'];
    return $map[(string)$factor] ?? 'N/D';
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>UniSalud · Mi Portal</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="imagenes/unisalud-sf.png">
    <style>
        :root {
            --primary:      #003da5;
            --primary-dark: #002a70;
            --success:      #10b981;
            --success-bg:   #ecfdf5;
            --warning:      #f59e0b;
            --danger:       #ef4444;
            --bg:           #f1f5f9;
            --white:        #ffffff;
            --border:       #e2e8f0;
            --text:         #111827;
            --muted:        #6b7280;
            --shadow:       0 4px 12px rgba(0,0,0,.08);
        }
        *, *::before, *::after { box-sizing: border-box; margin:0; padding:0; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

        /* ── Topbar ── */
        .topbar {
            background: linear-gradient(135deg,var(--primary) 0%,#0052cc 60%,#1a6bdd 100%);
            padding:.9rem 2rem; display:flex; align-items:center;
            justify-content:space-between; gap:1rem; position:sticky; top:0; z-index:100;
            box-shadow:0 2px 12px rgba(0,61,165,.3);
        }
        .topbar-brand { display:flex; align-items:center; gap:.75rem; }
        .topbar-icon  { width:36px; height:36px; background:rgba(255,255,255,.18); border-radius:9px;
                        display:flex; align-items:center; justify-content:center; color:#fff; font-size:1rem; }
        .topbar-name  { font-size:.95rem; font-weight:700; color:#fff; line-height:1.2; }
        .topbar-sub   { font-size:.72rem; color:rgba(255,255,255,.7); }

        .topbar-nav   { display:flex; align-items:center; gap:.25rem; }
        .topbar-link  { color:rgba(255,255,255,.8); text-decoration:none; font-size:.82rem;
                        font-weight:500; padding:.4rem .8rem; border-radius:7px;
                        display:flex; align-items:center; gap:.35rem; transition:background .2s; }
        .topbar-link:hover { background:rgba(255,255,255,.15); color:#fff; }
        .topbar-link.active { background:rgba(255,255,255,.2); color:#fff; }
        .btn-logout   { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.3);
                        color:#fff; padding:.4rem .9rem; border-radius:7px; font-size:.82rem;
                        font-weight:500; cursor:pointer; display:flex; align-items:center;
                        gap:.35rem; transition:background .2s; }
        .btn-logout:hover { background:rgba(255,255,255,.22); }

        /* ── Page ── */
        .page { max-width:960px; margin:0 auto; padding:2rem 1.25rem 3rem; }

        /* ── Profile card ── */
        .profile-card { background:var(--white); border-radius:16px; box-shadow:var(--shadow);
                        overflow:hidden; margin-bottom:1.5rem; }
        .profile-banner { height:64px;
                          background:linear-gradient(135deg,var(--primary) 0%,#1a6bdd 100%);
                          position:relative; }
        .profile-banner::after { content:''; position:absolute; bottom:-1px; left:0; right:0;
                                  height:20px; background:var(--white);
                                  border-radius:60% 60% 0 0/100% 100% 0 0; }
        .profile-body  { padding:0 1.75rem 1.5rem; }
        .profile-avatar { width:68px; height:68px; border-radius:50%;
                          background:linear-gradient(135deg,var(--primary),#1a6bdd);
                          color:#fff; font-size:1.45rem; font-weight:700;
                          display:flex; align-items:center; justify-content:center;
                          border:4px solid var(--white); box-shadow:var(--shadow);
                          margin-top:-34px; margin-bottom:.5rem; position:relative; z-index:1; }
        .profile-name  { font-size:1.1rem; font-weight:700; margin-bottom:.1rem; }
        .profile-mat   { font-size:.8rem; color:var(--muted); display:flex;
                         align-items:center; gap:.3rem; margin-bottom:1.1rem; }
        .info-grid     { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr));
                         gap:.85rem; padding-top:1rem; border-top:1px solid var(--border); }
        .info-label    { font-size:.68rem; font-weight:700; text-transform:uppercase;
                         letter-spacing:.06em; color:var(--muted);
                         display:flex; align-items:center; gap:.3rem; margin-bottom:.15rem; }
        .info-value    { font-size:.88rem; font-weight:500; }

        /* ── Section heading ── */
        .sec-hd { font-size:.75rem; font-weight:700; text-transform:uppercase;
                  letter-spacing:.08em; color:var(--muted); margin-bottom:1rem;
                  display:flex; align-items:center; gap:.6rem; }
        .sec-hd::after { content:''; flex:1; height:1px; background:var(--border); }

        /* ── Cards grid ── */
        .cards-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
                      gap:1.25rem; margin-bottom:1.75rem; }

        .res-card { background:var(--white); border-radius:16px; box-shadow:var(--shadow);
                    border:1px solid var(--border); overflow:hidden; position:relative; }
        .res-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px;
                            background:linear-gradient(90deg,var(--primary),#1a6bdd); }
        .res-card.green::before { background:linear-gradient(90deg,var(--success),#34d399); }
        .res-card.amber::before { background:linear-gradient(90deg,var(--warning),#fbbf24); }

        .res-body  { padding:1.35rem; }
        .res-title { font-size:.88rem; font-weight:700; margin-bottom:1rem;
                     display:flex; align-items:center; gap:.5rem; }
        .res-icon  { width:36px; height:36px; border-radius:9px; display:flex;
                     align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }
        .res-icon.blue  { background:#e8effc; color:var(--primary); }
        .res-icon.green { background:var(--success-bg); color:var(--success); }
        .res-icon.amber { background:#fffbeb; color:var(--warning); }

        /* DASS row */
        .dass-rows { display:flex; flex-direction:column; gap:.55rem; }
        .dass-row  { display:flex; align-items:center; gap:.65rem; }
        .dass-lbl  { font-size:.78rem; color:var(--muted); width:80px; flex-shrink:0; }
        .dass-bar-wrap { flex:1; height:7px; background:var(--border); border-radius:99px; overflow:hidden; }
        .dass-bar  { height:100%; border-radius:99px; transition:width .6s ease; }
        .dass-val  { font-size:.78rem; font-weight:600; width:20px; text-align:right; flex-shrink:0; }
        .dass-sev  { font-size:.72rem; font-weight:600; padding:.15rem .5rem;
                     border-radius:99px; white-space:nowrap; flex-shrink:0; }

        /* DASS message block */
        .dass-msg { margin-top:1rem; padding:.85rem 1rem; border-radius:10px; font-size:.8rem; line-height:1.55; }
        .dass-msg.urgent { background:#fef2f2; border-left:3px solid #ef4444; color:#991b1b; }
        .dass-msg.warn   { background:#fffbeb; border-left:3px solid #f59e0b; color:#92400e; }
        .dass-msg.ok     { background:#ecfdf5; border-left:3px solid #10b981; color:#065f46; }
        .dass-msg strong { display:block; margin-bottom:.25rem; }
        .dass-contact { margin-top:.55rem; font-size:.76rem; font-weight:600;
                        display:flex; align-items:center; gap:.3rem; }

        /* PEPS result */
        .peps-big  { text-align:center; padding:.75rem 0; }
        .peps-num  { font-size:2.4rem; font-weight:700; line-height:1; }
        .peps-max  { font-size:.8rem; color:var(--muted); margin-bottom:.5rem; }
        .peps-badge { display:inline-flex; align-items:center; gap:.35rem;
                      font-size:.82rem; font-weight:600; padding:.3rem .9rem;
                      border-radius:99px; }

        /* Físicos */
        .fis-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.6rem; }
        .fis-item { background:var(--bg); border-radius:9px; padding:.65rem .85rem; }
        .fis-item-label { font-size:.68rem; color:var(--muted); font-weight:600;
                          text-transform:uppercase; letter-spacing:.05em; }
        .fis-item-val   { font-size:.95rem; font-weight:600; margin-top:.1rem; }

        /* pending card */
        .pending-card { background:var(--white); border-radius:16px; box-shadow:var(--shadow);
                        border:1px solid var(--border); padding:1.4rem;
                        display:flex; align-items:center; gap:1rem; flex-wrap:wrap; }
        .pending-text { flex:1; min-width:180px; }
        .pending-title { font-size:.9rem; font-weight:700; margin-bottom:.2rem; }
        .pending-sub   { font-size:.8rem; color:var(--muted); }
        .btn-ir { background:linear-gradient(135deg,var(--primary),#1a6bdd); color:#fff;
                  padding:.6rem 1.25rem; border-radius:9px; font-weight:600; font-size:.85rem;
                  text-decoration:none; display:inline-flex; align-items:center; gap:.4rem;
                  transition:opacity .2s; white-space:nowrap; }
        .btn-ir:hover { opacity:.88; color:#fff; }

        /* Modal */
        .modal-content { border-radius:14px; border:none; box-shadow:0 10px 24px rgba(0,0,0,.1); }

        @media(max-width:600px){
            .topbar { padding:.85rem 1rem; }
            .topbar-nav { display:none; }
            .page { padding:1.25rem .85rem 2.5rem; }
            .fis-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<!-- ── Topbar ──────────────────────────────────────────── -->
<header class="topbar">
    <div class="topbar-brand">
        <div class="topbar-icon" style="background:#fff;border-radius:10px;padding:3px;box-shadow:0 2px 8px rgba(0,0,0,.25);">
            <img src="imagenes/unisalud-sf.png" alt="UniSalud" style="width:100%;height:100%;object-fit:contain;">
        </div>
        <div>
            <div class="topbar-name">UniSalud</div>
            <div class="topbar-sub">UNACAR &middot; Portal del Estudiante</div>
        </div>
    </div>
    <nav class="topbar-nav">
        <a class="topbar-link active" href="inicio.php">
            <i class="bi bi-house-fill"></i> Inicio
        </a>
        <a class="topbar-link" href="../cuestionarios/menuAlum.php">
            <i class="bi bi-clipboard-check"></i> Cuestionarios
        </a>
    </nav>
    <button class="btn-logout" data-bs-toggle="modal" data-bs-target="#modalLogout">
        <i class="bi bi-box-arrow-right"></i> Salir
    </button>
</header>

<div class="page">

    <!-- ── Perfil ─────────────────────────────────────────── -->
    <div class="profile-card">
        <div class="profile-banner"></div>
        <div class="profile-body">
            <div class="profile-avatar"><?= $iniciales ?></div>
            <div class="profile-name"><?= $nombreCompleto ?></div>
            <div class="profile-mat">
                <i class="bi bi-person-badge"></i>
                <?= htmlspecialchars($mat) ?>
                &nbsp;&middot;&nbsp;
                <i class="bi bi-envelope"></i>
                <?= htmlspecialchars($alumno['correo'] ?? '') ?>
            </div>
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem;">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPerfil"
                        style="border-radius:8px;font-size:.8rem;font-weight:600;display:flex;align-items:center;gap:.35rem;">
                    <i class="bi bi-pencil-square"></i> Editar perfil
                </button>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalPassword"
                        style="border-radius:8px;font-size:.8rem;font-weight:600;display:flex;align-items:center;gap:.35rem;">
                    <i class="bi bi-shield-lock"></i> Cambiar contraseña
                </button>
            </div>
            <div class="info-grid">
                <div>
                    <div class="info-label"><i class="bi bi-building"></i> Facultad</div>
                    <div class="info-value"><?= $nomFacultad ?: '—' ?></div>
                </div>
                <div>
                    <div class="info-label"><i class="bi bi-mortarboard"></i> Carrera</div>
                    <div class="info-value"><?= $nomCarrera ?: '—' ?></div>
                </div>
                <div>
                    <div class="info-label"><i class="bi bi-gender-ambiguous"></i> Sexo</div>
                    <div class="info-value"><?= htmlspecialchars($alumno['sexo'] ?? '—') ?></div>
                </div>
                <div>
                    <div class="info-label"><i class="bi bi-calendar3"></i> Generación</div>
                    <div class="info-value"><?= htmlspecialchars($alumno['generacion'] ?? '—') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Cuestionarios pendientes ───────────────────────── -->
    <?php if (!$dass || !$peps): ?>
    <div class="sec-hd">Actividades pendientes</div>
    <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.75rem;">
        <?php if (!$peps): ?>
        <div class="pending-card">
            <div class="res-icon amber"><i class="bi bi-heart-pulse-fill"></i></div>
            <div class="pending-text">
                <div class="pending-title">Cuestionario Estilo de Vida (PEPS-1)</div>
                <div class="pending-sub">Aprox. 10 min · 48 preguntas sobre tus hábitos de salud</div>
            </div>
            <a class="btn-ir" href="../cuestionarios/PEPS-1.php">
                <i class="bi bi-play-fill"></i> Iniciar
            </a>
        </div>
        <?php endif; ?>
        <?php if (!$dass): ?>
        <div class="pending-card">
            <div class="res-icon amber"><i class="bi bi-clipboard-pulse-fill"></i></div>
            <div class="pending-text">
                <div class="pending-title">Evaluación Emocional (DASS-21)</div>
                <div class="pending-sub">Aprox. 5 min · 21 preguntas sobre tu estado emocional</div>
            </div>
            <a class="btn-ir" href="../cuestionarios/DASS-21.php">
                <i class="bi bi-play-fill"></i> Iniciar
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── Resultados ─────────────────────────────────────── -->
    <?php if ($dass || $peps): ?>
    <div class="sec-hd">Mis resultados</div>
    <div class="cards-grid">

        <!-- DASS-21 -->
        <?php if ($dass):
            [$lDep, $cDep] = sevLabel($dass['total_depresion'], 'dep');
            [$lAns, $cAns] = sevLabel($dass['total_ansiedad'],  'ans');
            [$lEst, $cEst] = sevLabel($dass['total_estres'],    'est');

            // Determinar el nivel de alerta más alto para el mensaje general
            $nivelMax = 0; // 0=normal,1=leve,2=mod,3=sev,4=ext.sev
            $sevOrder = ['Normal'=>0,'Leve'=>1,'Moderado'=>2,'Severo'=>3,'Extremadamente Severo'=>4];
            foreach([$lDep,$lAns,$lEst] as $lbl) {
                $n = $sevOrder[$lbl] ?? 0;
                if ($n > $nivelMax) $nivelMax = $n;
            }

            // Mensajes por dimensión según nivel
            $msgs = [];
            $dimensiones = [
                ['dim'=>'Depresión',  'lbl'=>$lDep, 'val'=>$dass['total_depresion'],
                 'Mod'=>'Tu nivel de depresión es moderado. Puede que te sientas con poca energía o desmotivado/a. Hablar con alguien de confianza o con un profesional puede ayudarte mucho.',
                 'Sev'=>'Presentas indicadores de depresión severa. Es muy importante que acudas a la Clínica Universitaria o busques apoyo profesional. No estás solo/a.',
                 'Ext'=>'Tus resultados muestran una depresión extremadamente severa. Por favor, busca ayuda profesional cuanto antes. La Clínica Universitaria puede orientarte.'],
                ['dim'=>'Ansiedad',   'lbl'=>$lAns, 'val'=>$dass['total_ansiedad'],
                 'Mod'=>'Tu nivel de ansiedad es moderado. Técnicas de respiración, ejercicio y una buena rutina de sueño pueden ayudarte. Considera hablar con un especialista.',
                 'Sev'=>'Presentas ansiedad severa. Estos niveles pueden afectar tu día a día. Te recomendamos acudir a la Clínica Universitaria para recibir orientación.',
                 'Ext'=>'Tu nivel de ansiedad es extremadamente severo. Es importante que busques atención profesional pronto. No tienes que manejarlo solo/a.'],
                ['dim'=>'Estrés',     'lbl'=>$lEst, 'val'=>$dass['total_estres'],
                 'Mod'=>'Tu nivel de estrés es moderado. Organizar tus tiempos, descansar bien y hacer actividad física puede ayudarte a manejarlo.',
                 'Sev'=>'Presentas estrés severo. Es recomendable que hables con un orientador o profesional de salud. La Clínica Universitaria tiene apoyo disponible para ti.',
                 'Ext'=>'Tu nivel de estrés es extremadamente severo. Te recomendamos buscar apoyo profesional lo antes posible para que recibas la ayuda que mereces.'],
            ];

            foreach($dimensiones as $d) {
                $dim = $d['dim']; $lbl = $d['lbl'];
                $mMod = $d['Mod']; $mSev = $d['Sev']; $mExt = $d['Ext'];
                $n = $sevOrder[$lbl] ?? 0;
                if ($n === 2) $msgs[] = ['warn',   $dim, $mMod];
                if ($n === 3) $msgs[] = ['urgent', $dim, $mSev];
                if ($n >= 4)  $msgs[] = ['urgent', $dim, $mExt];
            }
        ?>
        <div class="res-card">
            <div class="res-body">
                <div class="res-title">
                    <div class="res-icon blue"><i class="bi bi-clipboard-pulse-fill"></i></div>
                    Evaluación Emocional · DASS-21
                </div>
                <div class="dass-rows">
                    <?php foreach([
                        ['Depresión',  $dass['total_depresion'], 21, $lDep, $cDep],
                        ['Ansiedad',   $dass['total_ansiedad'],  21, $lAns, $cAns],
                        ['Estrés',     $dass['total_estres'],    21, $lEst, $cEst],
                    ] as [$lbl,$val,$max,$sev,$col]): ?>
                    <div class="dass-row">
                        <div class="dass-lbl"><?= $lbl ?></div>
                        <div class="dass-bar-wrap">
                            <div class="dass-bar" style="width:<?= round($val/$max*100) ?>%;background:<?= $col ?>"></div>
                        </div>
                        <div class="dass-val" style="color:<?= $col ?>"><?= $val ?></div>
                        <div class="dass-sev" style="background:<?= $col ?>22;color:<?= $col ?>"><?= $sev ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($msgs)): ?>
                <div style="margin-top:.9rem;display:flex;flex-direction:column;gap:.55rem;">
                    <?php foreach($msgs as [$tipo,$dim,$texto]): ?>
                    <div class="dass-msg <?= $tipo ?>">
                        <strong><i class="bi bi-<?= $tipo==='urgent'?'exclamation-triangle-fill':'info-circle-fill' ?>" style="margin-right:.3rem;"></i><?= $dim ?></strong>
                        <?= $texto ?>
                        <?php if($tipo === 'urgent'): ?>
                        <div class="dass-contact">
                            <i class="bi bi-telephone-fill"></i> Clínica Universitaria UNACAR &mdash; acude en horario escolar o habla con tu tutor.
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php elseif($nivelMax <= 1): ?>
                <div class="dass-msg ok" style="margin-top:.9rem;">
                    <strong><i class="bi bi-check-circle-fill" style="margin-right:.3rem;"></i>¡Buen estado emocional!</strong>
                    Tus resultados están dentro del rango normal. Sigue cuidando tu bienestar con buenos hábitos de sueño, actividad física y momentos de descanso.
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

        <!-- PEPS-1 -->
        <?php if ($peps):
            $saludable = $peps['estado_saludable'] === 'Saludable';
            $total     = (int)$peps['total'];
        ?>
        <div class="res-card <?= $saludable ? 'green' : 'amber' ?>">
            <div class="res-body">
                <div class="res-title">
                    <div class="res-icon <?= $saludable ? 'green' : 'amber' ?>">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                    Estilo de Vida · PEPS-1
                </div>
                <div class="peps-big">
                    <div class="peps-num" style="color:<?= $saludable ? 'var(--success)' : 'var(--warning)' ?>"><?= $total ?></div>
                    <div class="peps-max">de 192 puntos</div>
                    <div class="peps-badge" style="background:<?= $saludable ? '#ecfdf5' : '#fffbeb' ?>;color:<?= $saludable ? 'var(--success)' : 'var(--warning)' ?>">
                        <i class="bi <?= $saludable ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
                        <?= htmlspecialchars($peps['estado_saludable']) ?>
                    </div>
                    <?php if (!$saludable): ?>
                    <p style="font-size:.78rem;color:var(--muted);margin-top:.75rem;line-height:1.5;">
                        Tu puntaje indica oportunidades de mejora en hábitos de vida saludable. ¡Pequeños cambios hacen grandes diferencias!
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <!-- ── Datos Físicos ──────────────────────────────────── -->
    <?php if ($fisicos): ?>
    <div class="sec-hd">Datos físicos más recientes
        <span style="font-size:.7rem;font-weight:400;color:var(--muted);">
            (<?= date('d/m/Y', strtotime($fisicos['fecha'])) ?>)
        </span>
    </div>
    <div class="res-card" style="margin-bottom:1.75rem;">
        <div class="res-body">
            <div class="res-title">
                <div class="res-icon blue"><i class="bi bi-activity"></i></div>
                Mediciones
            </div>
            <div class="fis-grid">
                <div class="fis-item">
                    <div class="fis-item-label">Peso</div>
                    <div class="fis-item-val"><?= $fisicos['peso'] ?> kg</div>
                </div>
                <div class="fis-item">
                    <div class="fis-item-label">Talla</div>
                    <div class="fis-item-val"><?= $fisicos['talla'] ?> cm</div>
                </div>
                <div class="fis-item">
                    <div class="fis-item-label">IMC</div>
                    <div class="fis-item-val" style="color:<?= imcColor($fisicos['clasificacion_imc']) ?>">
                        <?= $fisicos['imc'] ?>
                        <span style="font-size:.75rem;font-weight:500;margin-left:.35rem;">
                            (<?= htmlspecialchars($fisicos['clasificacion_imc']) ?>)
                        </span>
                    </div>
                </div>
                <div class="fis-item">
                    <div class="fis-item-label">Tensión Arterial</div>
                    <div class="fis-item-val"><?= htmlspecialchars($fisicos['tension_arterial'] ?? '—') ?></div>
                </div>
                <div class="fis-item">
                    <div class="fis-item-label">Glucosa</div>
                    <div class="fis-item-val"><?= $fisicos['glucosa'] ? $fisicos['glucosa'].' mg/dL' : '—' ?></div>
                </div>
                <div class="fis-item">
                    <div class="fis-item-label">Colesterol</div>
                    <div class="fis-item-val"><?= $fisicos['colesterol'] ? $fisicos['colesterol'].' mg/dL' : '—' ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="sec-hd">Datos físicos</div>
    <div class="pending-card" style="margin-bottom:1.75rem;">
        <div class="res-icon blue"><i class="bi bi-activity"></i></div>
        <div class="pending-text">
            <div class="pending-title">Sin datos físicos registrados</div>
            <div class="pending-sub">Acude a la clínica universitaria para que el personal de salud registre tus medidas.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Reporte de Salud ──────────────────────────────── -->
    <?php if ($fisicos && $dass && $peps): ?>
    <div class="sec-hd">Reporte de Salud Integral</div>
    <div class="res-card" style="margin-bottom:1.75rem;">
        <div class="res-body" style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
            <div class="res-icon blue" style="width:48px;height:48px;font-size:1.4rem;flex-shrink:0;">
                <i class="bi bi-file-earmark-medical-fill"></i>
            </div>
            <div style="flex:1;min-width:180px;">
                <div style="font-size:.9rem;font-weight:700;margin-bottom:.2rem;">Tu reporte completo está disponible</div>
                <div style="font-size:.8rem;color:var(--muted);">Incluye todos tus resultados, análisis y recomendaciones personalizadas.</div>
            </div>
            <div style="display:flex;gap:.65rem;flex-wrap:wrap;">
                <a href="ver-reporte.php" target="_blank"
                   style="background:var(--primary);color:#fff;padding:.55rem 1.1rem;border-radius:9px;font-weight:600;font-size:.82rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
                    <i class="bi bi-eye-fill"></i> Ver reporte
                </a>
                <a href="ver-reporte.php?modo=descargar"
                   style="background:#1d4ed8;color:#fff;padding:.55rem 1.1rem;border-radius:9px;font-weight:600;font-size:.82rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
                    <i class="bi bi-download"></i> Descargar
                </a>
                <button onclick="imprimirReporte()"
                   style="background:#f1f5f9;color:var(--primary);border:1px solid var(--border);padding:.55rem 1.1rem;border-radius:9px;font-weight:600;font-size:.82rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;">
                    <i class="bi bi-printer-fill"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Plan Alimentario ──────────────────────────────── -->
    <?php if ($fisicos && !empty($fisicos['get1']) && $fisicos['get1'] > 0):
        $get     = (float)$fisicos['get1'];
        $mb      = (float)($fisicos['mb'] ?? 0);
        $actFac  = $fisicos['actividad1'] ?? '';
        $actLbl  = actividadLabel($actFac);

        // Macronutrientes recomendados
        $protKcal  = round($get * 0.20);
        $carbKcal  = round($get * 0.55);
        $grasaKcal = round($get * 0.25);
        $protG     = round($protKcal  / 4);
        $carbG     = round($carbKcal  / 4);
        $grasaG    = round($grasaKcal / 9);

        // Distribución por tiempos
        $tiempos = [
            ['Desayuno',           '7 – 9 am',   0.25],
            ['Colación matutina',  '10 – 11 am',  0.10],
            ['Comida',             '1 – 3 pm',   0.35],
            ['Colación vespertina','4 – 6 pm',   0.10],
            ['Cena',               '7 – 9 pm',   0.20],
        ];

        // Recomendaciones según nivel de actividad
        $recomendaciones = [
            '1.2'   => ['Incrementa la actividad física gradualmente. Caminar 30 min/día es un buen inicio.', 'Elige alimentos de bajo índice glucémico para mantener energía estable.', 'Evita alimentos ultraprocesados y bebidas azucaradas.'],
            '1.375' => ['Mantén una actividad regular de al menos 150 min/semana de intensidad moderada.', 'Incluye proteínas en cada comida para preservar masa muscular.', 'Prioriza grasas saludables: aguacate, aceite de oliva, nueces.'],
            '1.55'  => ['Tu nivel de actividad es adecuado. Asegura una buena recuperación con sueño de calidad.', 'Consume carbohidratos complejos (arroz integral, avena, leguminosas) como fuente de energía.', 'Hidratación: mínimo 2 litros de agua al día.'],
            '1.725' => ['Aumenta ligeramente la ingesta de proteínas para apoyar la recuperación muscular.', 'Consume una colación post-entrenamiento con proteínas y carbohidratos.', 'Monitorea tu hidratación; necesitas más agua con alta actividad.'],
            '1.9'   => ['Con actividad muy intensa es fundamental respetar los horarios de comida para rendir al máximo.', 'Considera consultar con un nutriólogo para un plan más personalizado.', 'Incluye alimentos anti-inflamatorios: frutas, verduras de colores, omega-3.'],
        ];
        $recs = $recomendaciones[$actFac] ?? ['Mantén una alimentación variada y equilibrada.', 'Incluye frutas y verduras en cada comida.', 'Limita el consumo de azúcar y sodio.'];
    ?>
    <div class="sec-hd">Plan Alimentario Personalizado</div>
    <div class="res-card" style="margin-bottom:1.75rem;">
        <div class="res-body">
            <div class="res-title">
                <div class="res-icon amber"><i class="bi bi-egg-fried"></i></div>
                Recomendaciones basadas en tu GET
            </div>

            <!-- GET + MB resumen -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.65rem;margin-bottom:1.25rem;">
                <div class="fis-item">
                    <div class="fis-item-label">Metabolismo Basal</div>
                    <div class="fis-item-val"><?= number_format($mb,0) ?> kcal</div>
                </div>
                <div class="fis-item">
                    <div class="fis-item-label">Nivel de Actividad</div>
                    <div class="fis-item-val" style="font-size:.82rem;"><?= htmlspecialchars($actLbl) ?></div>
                </div>
                <div class="fis-item" style="background:#eff6ff;border:1px solid #bfdbfe;">
                    <div class="fis-item-label" style="color:var(--primary);">GET Total</div>
                    <div class="fis-item-val" style="color:var(--primary);font-size:1.05rem;"><?= number_format($get,0) ?> kcal/día</div>
                </div>
            </div>

            <!-- Macronutrientes -->
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.65rem;">
                <i class="bi bi-pie-chart-fill me-1"></i> Distribución de macronutrientes
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:1.25rem;">
                <div style="background:#ecfdf5;border-radius:10px;padding:.8rem;text-align:center;">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;color:#065f46;margin-bottom:.25rem;">Proteínas · 20%</div>
                    <div style="font-size:1.2rem;font-weight:700;color:#059669;"><?= $protG ?> g</div>
                    <div style="font-size:.7rem;color:#6b7280;"><?= $protKcal ?> kcal</div>
                </div>
                <div style="background:#fffbeb;border-radius:10px;padding:.8rem;text-align:center;">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;color:#92400e;margin-bottom:.25rem;">Carbohidratos · 55%</div>
                    <div style="font-size:1.2rem;font-weight:700;color:#d97706;"><?= $carbG ?> g</div>
                    <div style="font-size:.7rem;color:#6b7280;"><?= $carbKcal ?> kcal</div>
                </div>
                <div style="background:#faf5ff;border-radius:10px;padding:.8rem;text-align:center;">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;color:#6b21a8;margin-bottom:.25rem;">Grasas · 25%</div>
                    <div style="font-size:1.2rem;font-weight:700;color:#9333ea;"><?= $grasaG ?> g</div>
                    <div style="font-size:.7rem;color:#6b7280;"><?= $grasaKcal ?> kcal</div>
                </div>
            </div>

            <!-- Tiempos de comida -->
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.65rem;">
                <i class="bi bi-clock-fill me-1"></i> Distribución calórica por tiempo de comida
            </div>
            <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.25rem;">
            <?php foreach ($tiempos as [$nombre,$hora,$pct]):
                $kcalTiempo = round($get * $pct);
                $barW = round($pct * 100);
            ?>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <div style="width:130px;flex-shrink:0;">
                        <div style="font-size:.78rem;font-weight:600;"><?= $nombre ?></div>
                        <div style="font-size:.67rem;color:var(--muted);"><?= $hora ?></div>
                    </div>
                    <div style="flex:1;height:8px;background:var(--border);border-radius:99px;overflow:hidden;">
                        <div style="width:<?= $barW ?>%;height:100%;background:linear-gradient(90deg,var(--primary),#1a6bdd);border-radius:99px;"></div>
                    </div>
                    <div style="font-size:.78rem;font-weight:600;width:65px;text-align:right;"><?= $kcalTiempo ?> kcal</div>
                    <div style="font-size:.72rem;color:var(--muted);width:35px;"><?= round($pct*100) ?>%</div>
                </div>
            <?php endforeach; ?>
            </div>

            <!-- Recomendaciones -->
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:.65rem;">
                <i class="bi bi-lightbulb-fill me-1"></i> Recomendaciones para tu nivel de actividad
            </div>
            <div style="display:flex;flex-direction:column;gap:.45rem;">
            <?php foreach ($recs as $i => $rec): ?>
                <div style="display:flex;align-items:flex-start;gap:.6rem;background:#f8fafc;border-radius:8px;padding:.65rem .85rem;">
                    <span style="background:var(--primary);color:#fff;border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0;margin-top:.05rem;"><?= $i+1 ?></span>
                    <span style="font-size:.82rem;line-height:1.5;"><?= htmlspecialchars($rec) ?></span>
                </div>
            <?php endforeach; ?>
            </div>

        </div>
    </div>
    <?php endif; ?>

</div><!-- /page -->

<!-- Modal Logout -->
<div class="modal fade" id="modalLogout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center px-4 py-4">
                <div style="font-size:1.8rem;margin-bottom:.5rem;color:#ef4444;">
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

<!-- Modal: Editar Perfil -->
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Editar perfil</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div id="perfilAlert" class="alert d-none"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">Correo electrónico</label>
                    <input type="email" id="perfilCorreo" class="form-control"
                           value="<?= htmlspecialchars($alumno['correo'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">Teléfono de emergencia</label>
                    <input type="text" id="perfilEmergencia" class="form-control" placeholder="10 dígitos"
                           value="<?= htmlspecialchars($alumno['emergencia'] ?? '') ?>">
                </div>
                <div class="mb-1">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">NSS <span class="text-muted fw-normal">(opcional)</span></label>
                    <input type="text" id="perfilNss" class="form-control" placeholder="11 dígitos"
                           value="<?= htmlspecialchars($alumno['nss'] ?? '') ?>">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarPerfil" class="btn btn-primary" style="border-radius:8px;font-weight:600;">
                    <i class="bi bi-check-lg me-1"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cambiar Contraseña -->
<div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2 text-secondary"></i>Cambiar contraseña</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div id="pwAlert" class="alert d-none"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">Contraseña actual</label>
                    <input type="password" id="pwActual" class="form-control" placeholder="••••••••">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">Nueva contraseña</label>
                    <input type="password" id="pwNueva" class="form-control" placeholder="Mínimo 8 caracteres">
                </div>
                <div class="mb-1">
                    <label class="form-label fw-semibold" style="font-size:.85rem;">Confirmar nueva contraseña</label>
                    <input type="password" id="pwConfirmar" class="form-control" placeholder="Repetir contraseña">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarPassword" class="btn btn-primary" style="border-radius:8px;font-weight:600;">
                    <i class="bi bi-check-lg me-1"></i> Cambiar contraseña
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function imprimirReporte() {
    const w = window.open('ver-reporte.php', '_blank');
    if (w) { w.onload = function() { w.print(); }; }
}
</script>

<script>
// Editar perfil
document.getElementById('btnGuardarPerfil').addEventListener('click', function () {
    const btn = this;
    const alerta = document.getElementById('perfilAlert');
    alerta.className = 'alert d-none';

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    fetch('actualizar-perfil.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            correo:     document.getElementById('perfilCorreo').value,
            emergencia: document.getElementById('perfilEmergencia').value,
            nss:        document.getElementById('perfilNss').value
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alerta.className = 'alert alert-success';
            alerta.textContent = data.message;
            setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('modalPerfil')).hide(), 1200);
        } else {
            alerta.className = 'alert alert-danger';
            alerta.textContent = data.error || 'Error al guardar.';
        }
    })
    .catch(() => {
        alerta.className = 'alert alert-danger';
        alerta.textContent = 'Error de conexión. Intente de nuevo.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar cambios';
    });
});

// Cambiar contraseña
document.getElementById('btnGuardarPassword').addEventListener('click', function () {
    const btn = this;
    const alerta = document.getElementById('pwAlert');
    alerta.className = 'alert d-none';

    const nueva    = document.getElementById('pwNueva').value;
    const confirmar = document.getElementById('pwConfirmar').value;

    if (nueva !== confirmar) {
        alerta.className = 'alert alert-danger';
        alerta.textContent = 'Las contraseñas nuevas no coinciden.';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    fetch('cambiar-password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            actual:    document.getElementById('pwActual').value,
            nueva:     nueva,
            confirmar: confirmar
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alerta.className = 'alert alert-success';
            alerta.textContent = data.message;
            document.getElementById('pwActual').value    = '';
            document.getElementById('pwNueva').value     = '';
            document.getElementById('pwConfirmar').value = '';
            setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('modalPassword')).hide(), 1500);
        } else {
            alerta.className = 'alert alert-danger';
            alerta.textContent = data.error || 'Error al cambiar contraseña.';
        }
    })
    .catch(() => {
        alerta.className = 'alert alert-danger';
        alerta.textContent = 'Error de conexión. Intente de nuevo.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Cambiar contraseña';
    });
});
</script>
</body>
</html>
