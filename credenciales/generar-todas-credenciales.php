<?php
session_start();

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Obtener filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$facultad = isset($_GET['facultad']) ? trim($_GET['facultad']) : '';
$sexo = isset($_GET['sexo']) ? trim($_GET['sexo']) : '';

$sql = "SELECT 
            a.matricula_alum,
            CONCAT(a.nombres_alum, ' ', a.ape_paterno_alum, ' ', a.ape_materno_alum) AS nombre_completo,
            f.nombre_facultad
        FROM alumnos a
        INNER JOIN facultad f ON a.id_facultad = f.id_facultad
        WHERE 1=1";

if (!empty($busqueda)) {
    $busqueda_escape = $conn->real_escape_string($busqueda);
    $sql .= " AND (a.matricula_alum LIKE '%$busqueda_escape%' 
              OR a.nombres_alum LIKE '%$busqueda_escape%'
              OR a.ape_paterno_alum LIKE '%$busqueda_escape%'
              OR a.ape_materno_alum LIKE '%$busqueda_escape%')";
}

if (!empty($facultad)) {
    $facultad_escape = $conn->real_escape_string($facultad);
    $sql .= " AND a.id_facultad = '$facultad_escape'";
}

if (!empty($sexo)) {
    $sexo_escape = $conn->real_escape_string($sexo);
    $sql .= " AND a.sexo = '$sexo_escape'";
}

$sql .= " ORDER BY a.matricula_alum ASC";

$resultado = $conn->query($sql);
$fecha_expiracion = date('d/m/Y', strtotime('+4 years'));


$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/ico/logo_pequeno.ico">
    <title>Credenciales - Impresión Masiva</title>
    <style>
        :root {
            --unacar-blue: #002855;
            --unacar-gold: #c4a006;
            --placeholder-gray: #ccc;
        }

        body {
            background-color: #f0f0f0;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
        }

        .credencial-container {
            width: 54mm;
            height: 85.6mm;
            background-color: white;
            border-radius: 3mm;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            padding: 2mm;
            box-sizing: border-box;
            float: left;
            margin: 5mm;
            page-break-inside: avoid;

        }

        .background-curve-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .background-curve-layer::before {
            content: '';
            position: absolute;
            top: -5%;
            left: -45%;
            width: 70%;
            height: 110%;
            background-color: var(--unacar-blue);
            border-top-right-radius: 40%;
            border-bottom-right-radius: 50%;
            transform: rotate(-2deg);
        }

        .background-curve-layer::after {
            content: '';
            position: absolute;
            top: -5%;
            left: -42%;
            width: 68%;
            height: 110%;
            background-color: var(--unacar-gold);
            border-top-right-radius: 40%;
            border-bottom-right-radius: 50%;
            transform: rotate(-2deg);
            z-index: -1;
        }

        .content-layer {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .header {
            height: 12mm;
            display: flex;
            align-items: center;
            padding-left: 12mm;
        }

        .header img {

            height: 10mm;
            width: auto;
            max-width: 100%;
            object-fit: contain;
        }

        .main-body {
            flex: 1;
            display: flex;
            position: relative;
            padding-top: 5mm;
        }

        .center-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 18mm;
            margin-right: 8mm;
            width: 25mm;
        }

        .photo-skeleton {
            width: 24mm;
            height: 30mm;
            background-color: var(--placeholder-gray);
            border: 1px solid #aaa;
            margin-bottom: 4mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #666;
        }

        .name-skeleton {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .name-text {
            font-size: 6pt;
            font-weight: bold;
            color: var(--unacar-blue);
            line-height: 1.2;
            word-wrap: break-word;
            max-width: 100%;
        }

        .vertical-text {
            position: absolute;
            right: 0mm;
            top: 5mm;
            bottom: 2mm;
            width: 6mm;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            text-align: center;
            font-size: 5pt;
            font-weight: bold;
            color: var(--unacar-blue);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2mm 0;
        }

        .footer {
            height: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding-bottom: 2mm;
            position: relative;
        }

        .expiry-text {
            font-size: 6pt;
            color: var(--unacar-blue);
            font-weight: bold;
            margin-bottom: 2mm;
            margin-left: -8mm;
        }

        .id-pill-skeleton {
            width: 20mm;
            height: 6mm;
            background-color: var(--unacar-blue);
            border-radius: 10mm;
            margin-left: -12mm;
            color: white;
            text-align: center;
            margin-top: 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9pt;
            font-weight: bold;
        }

        .qr-container {
            position: absolute;
            bottom: 0mm;
            right: 2mm;
            width: 16mm;
            height: 16mm;
            background-color: white;
            z-index: 20;
        }

        .qr-container img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .dce-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.9);
            font-size: 6pt;
            font-weight: 900;
            padding: 1px 3px;
            color: black;
            border-radius: 2px;
            pointer-events: none;
            /* Para que no estorbe */
        }

        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 30px;
            background-color: var(--unacar-blue);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-print:hover {
            background-color: var(--unacar-gold);
        }

        .total-info {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            font-weight: bold;
            color: var(--unacar-blue);
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .credencial-container {
                box-shadow: none;
                border: none;
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .btn-print,
            .total-info {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="total-info">
            📋 Total de credenciales: <?php echo $resultado->num_rows; ?>
        </div>
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Todas</button>


        <?php while ($alumno = $resultado->fetch_assoc()):
            // Generar URL para cada alumno dentro del loop
            $url_destino = "$protocol://$host/perfil_alumno.php?m=" . $alumno['matricula_alum'];
            $qr_src = "https://quickchart.io/qr?text=" . urlencode($url_destino) . "&size=150&margin=1";
            ?>
            <div class="credencial-container">
                <div class="background-curve-layer"></div>

                <div class="content-layer">
                    <div class="header">
                        <div><img src="/imagenes/logo.png" alt="Logo UNACAR"></div>
                    </div>

                    <div class="main-body">
                        <div class="center-column">
                            <div class="photo-skeleton">
                                👤
                            </div>

                            <div class="name-skeleton">
                                <div class="name-text"><?php echo htmlspecialchars($alumno['nombre_completo']); ?></div>
                            </div>
                        </div>

                        <div class="vertical-text">
                            <?php echo htmlspecialchars($alumno['nombre_facultad']); ?>
                        </div>
                    </div>

                    <div class="footer">
                        <div class="expiry-text">EXPIRA: <?php echo $fecha_expiracion; ?></div>
                        <div class="id-pill-skeleton"><?php echo htmlspecialchars($alumno['matricula_alum']); ?></div>

                        <div class="qr-container">
                            <img src="<?php echo $qr_src; ?>" alt="QR">
                            <div class="dce-overlay">DCE</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h2>No se encontraron alumnos con los criterios seleccionados</h2>
            <a href="credencialesalumnos.html"
                style="padding: 10px 20px; background-color: var(--unacar-blue); color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;">Volver</a>
        </div>
    <?php endif; ?>
</body>

</html>
<?php
if (isset($conn))
    $conn->close();
?>