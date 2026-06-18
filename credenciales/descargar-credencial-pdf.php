<?php
require_once '../config/config.php';
require_once '../vendor/fpdf/fpdf.php';

// ── Extensión FPDF con soporte de rotación y paths bezier ─────────────────
class CredencialPDF extends FPDF {
    private $angle = 0;

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c  = cos($angle); $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf(
                'q %.5f %.5f %.5f %.5f %.2f %.2f cm',
                $c, $s, -$s, $c,
                $cx + $s * $cy - $c * $cx,
                $cy - $c * $cy - $s * $cx
            ));
        }
    }

    function _endpage() {
        if ($this->angle != 0) { $this->angle = 0; $this->_out('Q'); }
        parent::_endpage();
    }

    // Dibuja una forma cerrada con comandos M / L / C (bezier) y la rellena
    function FilledPath($cmds, $r, $g, $b) {
        $k = $this->k;
        $h = $this->h;
        $s = sprintf('%.3f %.3f %.3f rg ', $r/255, $g/255, $b/255);
        foreach ($cmds as $c) {
            switch ($c[0]) {
                case 'M':
                    $s .= sprintf('%.3f %.3f m ', $c[1]*$k, $h - $c[2]*$k); break;
                case 'L':
                    $s .= sprintf('%.3f %.3f l ', $c[1]*$k, $h - $c[2]*$k); break;
                case 'C':
                    $s .= sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c ',
                        $c[1]*$k, $h-$c[2]*$k,
                        $c[3]*$k, $h-$c[4]*$k,
                        $c[5]*$k, $h-$c[6]*$k); break;
                case 'Z':
                    $s .= 'h '; break;
            }
        }
        $this->_out($s . 'f');
    }
}

// ── Datos del alumno ──────────────────────────────────────────────────────
$matricula = trim($_GET['matricula'] ?? '');
if (!$matricula) { http_response_code(400); die('Matrícula no especificada.'); }

$conn = getDBConnection();
$conn->set_charset('utf8mb4');
$stmt = $conn->prepare(
    "SELECT a.matricula_alum,
            CONCAT(a.nombres_alum,' ',a.ape_paterno_alum,' ',a.ape_materno_alum) AS nombre_completo,
            a.nombres_alum, a.ape_paterno_alum,
            f.nombre_facultad
     FROM alumnos a
     LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
     WHERE a.matricula_alum = ?"
);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
if (!$alumno) { http_response_code(404); die('Alumno no encontrado.'); }

// ── Setup PDF: Portrait 54 × 85.6 mm ─────────────────────────────────────
$pdf = new CredencialPDF('P', 'mm', [54, 85.6]);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$W = 54; $H = 85.6;

// ── 1. Fondo blanco ───────────────────────────────────────────────────────
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, $W, $H, 'F');

// ── 2. Forma dorada (ligeramente más ancha que la azul → franja visible) ──
// La forma simula el CSS: left:-45%, width:70%, border-radius curvo derecho
$gold = [
    ['M',  0,    0   ],
    ['L',  17,   0   ],
    ['C',  17,   28,   21, 57,   15,  $H  ],
    ['L',  0,    $H  ],
    ['Z']
];
$pdf->FilledPath($gold, 196, 160, 6);  // #c4a006

// ── 3. Forma azul encima (misma curva, un poco más a la izquierda) ────────
$blue = [
    ['M',  0,    0   ],
    ['L',  14,   0   ],
    ['C',  14,   28,   18, 57,   12,  $H  ],
    ['L',  0,    $H  ],
    ['Z']
];
$pdf->FilledPath($blue, 0, 40, 85);   // #002855

// ── 4. Logo UNACAR ────────────────────────────────────────────────────────
$logoPath = dirname(__DIR__) . '/imagenes/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 16, 2, 35);   // centrado en área blanca
}

// ── 5. Caja de foto (gris, con ícono de persona) ─────────────────────────
$xBox = 16; $yBox = 16;
$boxW = 22; $boxH = 28;
$pdf->SetFillColor(204, 204, 204);
$pdf->SetDrawColor(170, 170, 170);
$pdf->SetLineWidth(0.3);
$pdf->Rect($xBox, $yBox, $boxW, $boxH, 'FD');

// Círculo cabeza
$pdf->SetFillColor(130, 100, 160);
$cx = $xBox + $boxW/2; $cy = $yBox + 9;
$r = 4;
// Círculo usando Ellipse-like con Cell + border-radius no existe en FPDF base,
// así que lo dibujo como una elipse mediante bezier (approx)
$k = $pdf->k ?? (72/25.4);
$pdf->FilledPath([
    ['M', $cx,       $cy - $r    ],
    ['C', $cx+$r*0.55, $cy-$r, $cx+$r, $cy-$r*0.55, $cx+$r, $cy    ],
    ['C', $cx+$r,    $cy+$r*0.55, $cx+$r*0.55, $cy+$r, $cx,   $cy+$r ],
    ['C', $cx-$r*0.55,$cy+$r, $cx-$r, $cy+$r*0.55, $cx-$r, $cy    ],
    ['C', $cx-$r,    $cy-$r*0.55, $cx-$r*0.55, $cy-$r, $cx,   $cy-$r ],
    ['Z']
], 130, 100, 160);

// Cuerpo (trapecio/semicírculo inferior)
$bx = $cx; $by = $cy + $r + 1;
$bw = 7; $bh = 8;
$pdf->FilledPath([
    ['M', $bx - $bw/2,  $by + $bh ],
    ['C', $bx - $bw/2,  $by,  $bx - $bw*0.3, $by, $bx,          $by      ],
    ['C', $bx + $bw*0.3,$by,  $bx + $bw/2,   $by, $bx + $bw/2,  $by+$bh  ],
    ['Z']
], 130, 100, 160);

// ── 6. Nombre del alumno ──────────────────────────────────────────────────
$nombre = mb_strtoupper($alumno['nombre_completo'], 'UTF-8');
$nombre = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nombre);
$pdf->SetFont('Arial', 'B', 6);
$pdf->SetTextColor(0, 40, 85);
$yNombre = $yBox + $boxH + 2;
$pdf->SetXY($xBox, $yNombre);
$pdf->MultiCell($boxW, 3.5, $nombre, 0, 'C');

// ── 7. Texto vertical de facultad (lado derecho) ──────────────────────────
$fac = mb_strtoupper($alumno['nombre_facultad'] ?? '', 'UTF-8');
$fac = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fac);
$pdf->SetFont('Arial', 'B', 4.5);
$pdf->SetTextColor(0, 40, 85);
// Rotar 90° en el margen derecho
$pdf->Rotate(90, $W - 3, $H/2);
$pdf->SetXY($W - 3 - ($H * 0.6)/2, $H/2 - 1.5);
$pdf->Cell($H * 0.6, 3, $fac, 0, 0, 'C');
$pdf->Rotate(0);

// ── 8. Línea separadora antes del footer ─────────────────────────────────
$yFooter = $H - 24;
$pdf->SetDrawColor(196, 160, 6);
$pdf->SetLineWidth(0.3);
$pdf->Line(14, $yFooter, $W - 3, $yFooter);

// ── 9. EXPIRA ─────────────────────────────────────────────────────────────
$fechaExp = date('d/m/Y', strtotime('+4 years'));
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->SetTextColor(0, 40, 85);
$pdf->SetXY(14, $yFooter + 1.5);
$pdf->Cell(30, 4, 'EXPIRA: ' . $fechaExp, 0, 0, 'L');

// ── 10. Pill de matrícula (rectángulo azul redondeado) ────────────────────
$pdf->SetFillColor(0, 40, 85);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(14, $yFooter + 7);
$pdf->Cell(22, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $alumno['matricula_alum']), 0, 0, 'C', true);

// ── 11. QR code ───────────────────────────────────────────────────────────
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$urlQR    = "$protocol://$host/gestion-alumnos/perfil_alumno.php?m=" . urlencode($alumno['matricula_alum']);
$qrUrl    = 'https://quickchart.io/qr?text=' . urlencode($urlQR) . '&size=150&margin=1';

$tmpQR = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
$qrData = @file_get_contents($qrUrl);
if ($qrData) {
    file_put_contents($tmpQR, $qrData);
    $pdf->Image($tmpQR, $W - 18, $yFooter + 4, 15, 15);
    unlink($tmpQR);
}

// Texto "DCE" sobre QR
$pdf->SetFont('Arial', 'B', 5);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($W - 12.5, $yFooter + 10.5);
$pdf->Cell(6, 2.5, 'DCE', 0, 0, 'C', true);

// ── Output ────────────────────────────────────────────────────────────────
$filename = 'credencial_' . preg_replace('/[^a-z0-9]/i', '_', $alumno['matricula_alum']) . '.pdf';
$pdf->Output('D', $filename);
