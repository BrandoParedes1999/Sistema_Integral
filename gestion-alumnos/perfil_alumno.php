<?php
require_once '../config/config.php';
$conn = getDBConnection();
$conn->set_charset("utf8mb4");

$matricula = isset($_GET['m']) ? trim($_GET['m']) : '';
if (empty($matricula)) die("Matrícula no especificada.");

$stmt = $conn->prepare(
    "SELECT a.matricula_alum,
            a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum,
            a.nss, a.tipo_sangre, a.enfermedades, a.emergencia
     FROM alumnos a
     WHERE a.matricula_alum = ?"
);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$alumno) die("Alumno no encontrado.");

$iniciales = strtoupper(
    substr($alumno['nombres_alum'], 0, 1) .
    substr($alumno['ape_paterno_alum'], 0, 1)
);
$nombreCompleto = htmlspecialchars(
    $alumno['nombres_alum'] . ' ' .
    $alumno['ape_paterno_alum'] . ' ' .
    $alumno['ape_materno_alum']
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de Emergencia – <?= $nombreCompleto ?></title>
    <link rel="icon" type="image/png" href="../alumnos/imagenes/unisalud-sf.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 12px;
        }

        /* Banner de emergencia */
        .emergency-banner {
            width: 100%;
            max-width: 480px;
            background: #c62828;
            color: #fff;
            text-align: center;
            padding: 10px;
            border-radius: 10px 10px 0 0;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .card {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 0 0 14px 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.13);
            overflow: hidden;
        }

        /* Cabecera azul con iniciales */
        .header {
            background: #002855;
            padding: 24px 20px 18px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .avatar {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #003da5, #1a6bdd);
            border: 3px solid #c4a006;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .header-info { color: #fff; }
        .header-info h2 { font-size: 1rem; line-height: 1.3; }
        .header-info .matricula {
            display: inline-block;
            background: #c4a006;
            color: #002855;
            font-size: .75rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 5px;
        }

        /* Sección de datos */
        .section { padding: 16px 20px; }
        .section-title {
            font-size: .7rem;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .data-item { }
        .data-item .label {
            font-size: .68rem;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
        }
        .data-item .value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #002855;
            margin-top: 2px;
        }

        /* Caja de enfermedades */
        .enf-box {
            background: #fff8e1;
            border-left: 4px solid #f9a825;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 4px;
        }
        .enf-box .label { font-size: .68rem; color: #888; font-weight: 700; text-transform: uppercase; }
        .enf-box .value { font-size: .95rem; color: #5d4037; font-weight: 600; margin-top: 3px; }

        /* Contacto de emergencia */
        .emergencia-box {
            background: #ffebee;
            border-left: 4px solid #c62828;
            border-radius: 6px;
            padding: 14px 16px;
            margin: 0 20px 20px;
        }
        .emergencia-box .label { font-size: .68rem; color: #c62828; font-weight: 700; text-transform: uppercase; }
        .emergencia-box a {
            display: block;
            font-size: 1.4rem;
            font-weight: 800;
            color: #c62828;
            text-decoration: none;
            margin-top: 4px;
            word-break: break-all;
        }
        .emergencia-box a:hover { text-decoration: underline; }

        /* Sin dato */
        .nd { color: #bbb; font-weight: 400; font-size: .9rem; }

        .divider { border: none; border-top: 1px solid #eee; margin: 0 20px; }

        .logo-footer {
            margin-top: 20px;
            opacity: .5;
            height: 28px;
        }
    </style>
</head>
<body>

    <div class="emergency-banner">🚨 DATOS DE EMERGENCIA — UNISALUD UNACAR</div>

    <div class="card">
        <!-- Cabecera -->
        <div class="header">
            <div class="avatar"><?= htmlspecialchars($iniciales) ?></div>
            <div class="header-info">
                <h2><?= $nombreCompleto ?></h2>
                <span class="matricula"><?= htmlspecialchars($alumno['matricula_alum']) ?></span>
            </div>
        </div>

        <!-- NSS y Tipo de Sangre -->
        <div class="section">
            <div class="section-title">Información médica</div>
            <div class="data-grid">
                <div class="data-item">
                    <div class="label">Tipo de Sangre</div>
                    <div class="value">
                        <?= $alumno['tipo_sangre']
                            ? htmlspecialchars($alumno['tipo_sangre'])
                            : '<span class="nd">N/D</span>' ?>
                    </div>
                </div>
                <div class="data-item">
                    <div class="label">NSS</div>
                    <div class="value">
                        <?= $alumno['nss']
                            ? htmlspecialchars($alumno['nss'])
                            : '<span class="nd">N/D</span>' ?>
                    </div>
                </div>
            </div>

            <!-- Enfermedades / Alergias -->
            <div class="enf-box" style="margin-top:14px;">
                <div class="label">⚠️ Enfermedades / Alergias</div>
                <div class="value">
                    <?= $alumno['enfermedades']
                        ? htmlspecialchars($alumno['enfermedades'])
                        : 'Ninguna registrada' ?>
                </div>
            </div>
        </div>

        <hr class="divider">

        <!-- Contacto de emergencia -->
        <div style="padding: 16px 20px 4px;">
            <div class="section-title">📞 Contacto de emergencia</div>
        </div>
        <div class="emergencia-box">
            <?php if (!empty($alumno['emergencia'])): ?>
                <div class="label">Llamar al número</div>
                <a href="tel:<?= htmlspecialchars($alumno['emergencia']) ?>">
                    <?= htmlspecialchars($alumno['emergencia']) ?>
                </a>
            <?php else: ?>
                <div class="label">Sin contacto registrado</div>
            <?php endif; ?>
        </div>
    </div>

    <img src="../imagenes/logo.png" alt="UNACAR" class="logo-footer">

</body>
</html>
