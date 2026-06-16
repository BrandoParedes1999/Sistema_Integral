<?php
require_once '../config/config.php';
require_once '../vendor/fpdf/fpdf.php';

$matricula = trim($_GET['matricula'] ?? '');
if (!$matricula) { http_response_code(400); die('Matrícula no especificada.'); }

$conn = getDBConnection();
$conn->set_charset('utf8mb4');

$stmt = $conn->prepare(
    "SELECT a.matricula_alum,
            CONCAT(a.nombres_alum,' ',a.ape_paterno_alum,' ',a.ape_materno_alum) AS nombre_completo,
            a.nombres_alum, a.ape_paterno_alum, a.tipo_sangre, a.emergencia, a.nss,
            f.nombre_facultad, c.nombre_carrera
     FROM alumnos a
     LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
     LEFT JOIN carrera  c ON a.id_carrera  = c.id_carrera
     WHERE a.matricula_alum = ?"
);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$alumno) { http_response_code(404); die('Alumno no encontrado.'); }

// Portrait: 54mm x 85.6mm (igual que el HTML)
$pdf = new FPDF('P', 'mm', [54, 85.6]);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$W = 54;
$H = 85.6;

// ── Fondo blanco completo ─────────────────────────────────────────────────
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, $W, $H, 'F');

// ── Franja azul izquierda (simula la curva CSS) ───────────────────────────
$pdf->SetFillColor(0, 40, 85);       // #002855
$pdf->Rect(0, 0, 20, $H, 'F');

// ── Línea dorada (borde derecho de la franja azul) ───────────────────────
$pdf->SetFillColor(196, 160, 6);     // #c4a006
$pdf->Rect(20, 0, 1.5, $H, 'F');

// ── Logo UNACAR (área blanca, arriba) ─────────────────────────────────────
$logoPath = dirname(__DIR__) . '/imagenes/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 23, 3, 28);
}

// ── Separador bajo el logo ────────────────────────────────────────────────
$pdf->SetDrawColor(196, 160, 6);
$pdf->SetLineWidth(0.4);
$pdf->Line(22, 14, $W - 1, 14);

// ── Avatar con iniciales ──────────────────────────────────────────────────
$ini = strtoupper(
    substr($alumno['nombres_alum'], 0, 1) .
    substr($alumno['ape_paterno_alum'], 0, 1)
);
// Caja gris de foto (24mm x 30mm centrada en área blanca)
$xBox = 22 + (($W - 22) / 2) - 12;   // centrar en área blanca
$yBox = 17;
$pdf->SetFillColor(204, 204, 204);    // #ccc
$pdf->SetDrawColor(170, 170, 170);
$pdf->SetLineWidth(0.3);
$pdf->Rect($xBox, $yBox, 24, 30, 'FD');

// Iniciales en la caja
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY($xBox, $yBox + 10);
$pdf->Cell(24, 10, $ini, 0, 0, 'C');

// ── Nombre del alumno ─────────────────────────────────────────────────────
$nombre = mb_strtoupper($alumno['nombre_completo'], 'UTF-8');
$nombre = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nombre);
$pdf->SetFont('Arial', 'B', 6);
$pdf->SetTextColor(0, 40, 85);
$yNombre = $yBox + 32;
$pdf->SetXY($xBox, $yNombre);
$pdf->MultiCell(24, 3.5, $nombre, 0, 'C');

// ── Texto de expiración ───────────────────────────────────────────────────
$fechaExp = date('d/m/Y', strtotime('+4 years'));
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->SetTextColor(0, 40, 85);
$pdf->SetXY(22, $H - 22);
$pdf->Cell($W - 22, 4, 'EXPIRA: ' . $fechaExp, 0, 1, 'L');

// ── Matrícula pill (rectángulo azul) ─────────────────────────────────────
$pdf->SetFillColor(0, 40, 85);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);
$xPill = 22;
$pdf->SetXY($xPill, $H - 17);
$pdf->Cell(22, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $alumno['matricula_alum']), 0, 0, 'C', true);

// ── QR Code (esquina inferior derecha) ───────────────────────────────────
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$urlQR = "$protocol://$host/gestion-alumnos/perfil_alumno.php?m=" . urlencode($alumno['matricula_alum']);
$qrUrl = 'https://quickchart.io/qr?text=' . urlencode($urlQR) . '&size=150&margin=1';

// Descargar QR a archivo temporal para FPDF
$tmpQR = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
$qrData = @file_get_contents($qrUrl);
if ($qrData) {
    file_put_contents($tmpQR, $qrData);
    $pdf->Image($tmpQR, $W - 17, $H - 18, 16, 16);
    unlink($tmpQR);
}

// ── Texto "DCE" sobre el QR ───────────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 5);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($W - 12, $H - 11);
$pdf->Cell(8, 3, 'DCE', 0, 0, 'C', true);

// ── Facultad en franja azul (vertical simulado como texto pequeño) ────────
$fac = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($alumno['nombre_facultad'] ?? ''));
$pdf->SetFont('Arial', 'B', 4);
$pdf->SetTextColor(196, 160, 6);
// Escribir letra por letra verticalmente en la franja azul
$letters = str_split($fac);
$yStart = 18;
foreach ($letters as $i => $letter) {
    if ($letter === ' ') { $yStart += 1; continue; }
    $pdf->SetXY(1, $yStart + ($i * 3.2));
    $pdf->Cell(18, 3.2, $letter, 0, 0, 'C');
    if ($yStart + ($i * 3.2) > $H - 10) break;
}

// ── Output ────────────────────────────────────────────────────────────────
$filename = 'credencial_' . preg_replace('/[^a-z0-9]/i', '_', $alumno['matricula_alum']) . '.pdf';
$pdf->Output('D', $filename);
