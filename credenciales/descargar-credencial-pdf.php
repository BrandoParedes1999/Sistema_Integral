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

// ── FPDF setup ────────────────────────────────────────────────────────────
// Tarjeta CR80: 85.6mm x 53.98mm → landscape for better layout
$pdf = new FPDF('L', 'mm', [85.6, 53.98]);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$W = 85.6; $H = 53.98;

// Background: dark blue left strip
$pdf->SetFillColor(0, 40, 85);           // #002855
$pdf->Rect(0, 0, 22, $H, 'F');

// Gold accent line
$pdf->SetFillColor(196, 160, 6);         // #c4a006
$pdf->Rect(22, 0, 1.5, $H, 'F');

// White background (right area)
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(23.5, 0, $W - 23.5, $H, 'F');

// ── Avatar box (initials) ─────────────────────────────────────────────────
$pdf->SetFillColor(0, 61, 165);
$pdf->Rect(3, 12, 16, 16, 'F');

$ini = strtoupper(substr($alumno['nombres_alum'],0,1).substr($alumno['ape_paterno_alum'],0,1));
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(3, 15);
$pdf->Cell(16, 10, $ini, 0, 0, 'C');

// ── UniSalud text (left strip, horizontal) ───────────────────────────────
$pdf->SetFont('Arial', 'B', 4.5);
$pdf->SetTextColor(196, 160, 6);
$pdf->SetXY(1, $H - 8);
$pdf->Cell(20, 4, 'UNISALUD', 0, 1, 'C');
$pdf->SetX(1);
$pdf->Cell(20, 4, 'UNACAR', 0, 0, 'C');

// ── Right content area ───────────────────────────────────────────────────
$xL = 26; $yTop = 5;

// "CREDENCIAL DE SALUD" header
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetTextColor(0, 40, 85);
$pdf->SetXY($xL, $yTop);
$pdf->Cell($W - $xL - 3, 5, 'CREDENCIAL DE SALUD', 0, 1, 'L');

// Divider
$pdf->SetDrawColor(0, 40, 85);
$pdf->SetLineWidth(0.4);
$pdf->Line($xL, $yTop + 5, $W - 3, $yTop + 5);

// Name
$nombre = mb_strtoupper($alumno['nombre_completo'], 'UTF-8');
// Transliterate accented chars for FPDF Latin-1
$nombre = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nombre);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(17, 24, 39);
$pdf->SetXY($xL, $yTop + 7);
$pdf->MultiCell($W - $xL - 3, 4.5, $nombre, 0, 'L');

// Faculty
$fac = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $alumno['nombre_facultad'] ?? '');
$pdf->SetFont('Arial', '', 6);
$pdf->SetTextColor(107, 114, 128);
$pdf->SetX($xL);
$pdf->MultiCell($W - $xL - 3, 3.5, $fac, 0, 'L');

// Separator
$yData = $pdf->GetY() + 2;
$pdf->SetDrawColor(226, 232, 240);
$pdf->SetLineWidth(0.2);
$pdf->Line($xL, $yData, $W - 3, $yData);
$yData += 2;

// Data fields
function addField($pdf, $x, $y, $label, $value, $colW = 27) {
    $value = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$value);
    $pdf->SetFont('Arial', 'B', 5);
    $pdf->SetTextColor(107, 114, 128);
    $pdf->SetXY($x, $y);
    $pdf->Cell($colW, 3, strtoupper($label), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->SetTextColor(17, 24, 39);
    $pdf->SetXY($x, $y + 3);
    $pdf->Cell($colW, 3.5, $value ?: '—', 0, 0, 'L');
}

addField($pdf, $xL,          $yData,      'Matrícula',      $alumno['matricula_alum']);
addField($pdf, $xL + 28,     $yData,      'Tipo de Sangre', $alumno['tipo_sangre'] ?? 'N/D');
addField($pdf, $xL,          $yData + 8,  'NSS',            $alumno['nss'] ?? '—');
addField($pdf, $xL + 28,     $yData + 8,  'Emergencia',     $alumno['emergencia'] ?? '—');

// Expiry
$pdf->SetFont('Arial', '', 5.5);
$pdf->SetTextColor(156, 163, 175);
$pdf->SetXY($xL, $H - 7);
$pdf->Cell($W - $xL - 3, 3.5, 'Vigencia: '.date('d/m/Y', strtotime('+4 years')), 0, 0, 'L');

// Matricula pill
$pdf->SetFillColor(0, 40, 85);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY($W - 28, $H - 8);
$pdf->Cell(25, 5, $alumno['matricula_alum'], 0, 0, 'C', true);

// ── Output ────────────────────────────────────────────────────────────────
$filename = 'credencial_' . preg_replace('/[^a-z0-9]/i', '_', $alumno['matricula_alum']) . '.pdf';
$pdf->Output('D', $filename);
