<?php

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);
$conn->set_charset("utf8mb4");

$matricula = isset($_GET['m']) ? trim($_GET['m']) : '';

if(empty($matricula)) die("Matrícula no especificada.");

$sql = "SELECT * FROM alumnos WHERE matricula_alum = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$resultado = $stmt->get_result();
$alumno = $resultado->fetch_assoc();

if(!$alumno) die("Alumno no encontrado.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f4f4; padding-top: 20px; }
        .card-profile {
            max-width: 400px;
            margin: 0 auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header-bg {
            background-color: #002855;
            height: 100px;
            position: relative;
        }
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            background-color: #ddd;
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            object-fit: cover;
            display: flex; /* Para centrar el emoji si no hay foto */
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }
        .content {
            padding-top: 70px;
            padding-bottom: 20px;
            text-align: center;
        }
        .data-row {
            padding: 10px 20px;
            border-bottom: 1px solid #eee;
            text-align: left;
            display: flex;
            justify-content: space-between;
        }
        .data-label { font-weight: bold; color: #555; }
        .data-value { color: #002855; font-weight: 600; }
        .emergency-box {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            margin: 20px;
            border-radius: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card card-profile">
            <div class="header-bg">
                <div class="avatar">👤</div>
            </div>
            <div class="content">
                <h3><?php echo htmlspecialchars($alumno['nombres_alum'] . " " . $alumno['ape_paterno_alum']); ?></h3>
                <span class="badge bg-primary"><?php echo htmlspecialchars($alumno['matricula_alum']); ?></span>
                
                <div class="mt-4 text-start">
                    <div class="data-row">
                        <span class="data-label">NSS:</span>
                        <span class="data-value"><?php echo htmlspecialchars($alumno['nss'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Tipo de Sangre:</span>
                        <span class="data-value"><?php echo htmlspecialchars($alumno['tipo_sangre'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Alergias/Enf:</span>
                        <span class="data-value"><?php echo htmlspecialchars($alumno['enfermedades'] ?: 'Ninguna'); ?></span>
                    </div>
                </div>

                <?php if(!empty($alumno['emergencia'])): ?>
                <div class="emergency-box">
                    🚨 EMERGENCIA:<br>
                    <a href="tel:<?php echo htmlspecialchars($alumno['emergencia']); ?>" style="color: inherit; text-decoration: underline;">
                        <?php echo htmlspecialchars($alumno['emergencia']); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>