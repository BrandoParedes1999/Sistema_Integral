<?php
// Configuración de errores y output buffering
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
set_time_limit(120);
ini_set('memory_limit', '256M');

// Definición de Colores
define('COLOR_PRIMARY', [30, 70, 120]);
define('COLOR_BACKGROUND', [248, 248, 248]);
define('COLOR_LABEL_BG', [240, 240, 240]);
define('COLOR_SEPARATOR', [180, 180, 180]);

// Función para garantizar que SIEMPRE se envíe JSON
function enviarJSON($data, $statusCode = 200)
{
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($data);
    exit;
}

// Manejador de errores fatales
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("Error fatal: " . print_r($error, true));
        enviarJSON(['error' => 'Error fatal del servidor: ' . $error['message']], 500);
    }
});

ob_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require('fpdf/fpdf.php');

ob_end_clean();
header('Content-Type: application/json; charset=UTF-8');

function pdf_text($text)
{
    return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
}

// ✅ Variable para determinar si solo se genera PDF sin guardar en BD
$solo_generar_pdf = isset($_POST['solo_generar_pdf']) && $_POST['solo_generar_pdf'] === 'true';

// Validar método
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    enviarJSON(['error' => 'Método no permitido'], 405);
}

// 🔥 DEBUG: Ver qué datos llegan
error_log("=== DATOS RECIBIDOS ===");
error_log("Matrícula: " . ($_POST['matricula'] ?? 'NO ENVIADA'));
error_log("Correo: " . ($_POST['correo1'] ?? 'NO ENVIADO'));
error_log("Solo generar PDF: " . ($solo_generar_pdf ? 'SÍ' : 'NO'));

// Validar datos requeridos
if (empty($_POST['matricula'])) {
    enviarJSON(['error' => 'Falta la matrícula'], 400);
}

if (empty($_POST['correo1'])) {
    enviarJSON(['error' => 'Falta el correo del destinatario'], 400);
}

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

// =================================================================
// FUNCIÓN DE LAYOUT: addGridRow Para las tablas.
// =================================================================
function addGridRow($pdf, $label1, $value1, $label2, $value2, $line_break = true)
{
    $COLOR_GRID_BG = [240, 240, 240];

    $pdf->SetFillColor($COLOR_GRID_BG[0], $COLOR_GRID_BG[1], $COLOR_GRID_BG[2]);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(45, 6, pdf_text($label1 . ':'), 'B', 0, 'L', true);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(45, 6, pdf_text($value1), 'B', 0, 'L');

    if ($label2 || $value2) {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetX(100);
        $pdf->SetFillColor($COLOR_GRID_BG[0], $COLOR_GRID_BG[1], $COLOR_GRID_BG[2]);
        $pdf->Cell(45, 6, pdf_text($label2 . ':'), 'B', 0, 'L', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, pdf_text($value2), 'B', $line_break ? 1 : 0, 'L');
    } else {
        $pdf->Ln(6);
    }
}

try {

    if ($solo_generar_pdf) {
        error_log("=== INICIO GENERACIÓN PDF - Matrícula: " . $_POST['matricula'] . " ===");
    } else {
        error_log("=== INICIO GUARDADO - Matrícula: " . $_POST['matricula'] . " ===");
    }

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception('Conexión fallida: ' . $conn->connect_error);
    }

    // SOLO GUARDAR EN BD SI NO ES MODO PDF
    if (!$solo_generar_pdf) {
        $sql_guardar = "INSERT INTO datos_fisicos_alumnos 
                (matricula_alum, fecha, cintura, cadera, clasificacion_cintura_cadera, icc,  clasificacion_de_icc, peso, talla, imc, clasificacion_imc, ice, clasificacionice, mb, actividad1, get1, porcentaje_masa_grasa, valor_ideal_porcentaje_grasa,
                clasificacion_porcentaje_grasa, masa_magra, agua_total, porcentaje_agua_total, glucosa, clasificacion_glucosa, trigliceridos, clasificacion_trigliceridos, colesterol, clasificacion_colesterol, tension_arterial, clasificacion_tension_arterial) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql_guardar);
        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $conn->error);
        }

        $fecha_actual = date("Y-m-d");
        $stmt->bind_param(
            "isddsdsdddsdsdsddssddddsdsdsss",

            $_POST["matricula"],
            $fecha_actual,
            $_POST["cintura1"],
            $_POST["cadera1"],
            $_POST["clasificacioncadcin1"],
            $_POST["icc1"],
            $_POST["clasificacionicc1"],
            $_POST["peso1"],
            $_POST["talla1"],
            $_POST["imc1"],
            $_POST["clasificacionimc1"],
            $_POST["ice"],
            $_POST["clasificacionice"],
            $_POST["mb1"],
            $_POST["actividad1"],
            $_POST["get1"],
            $_POST["pormasagrasa1"],
            $_POST["valoridealgrasa1"],
            $_POST["clasificaciongrasa1"],
            $_POST["masamagra1"],
            $_POST["aguatotal1"],
            $_POST["porcentajeaguatotal1"],
            $_POST["glucosa1"],
            $_POST["clasificacionglucosa1"],
            $_POST["trigliceridos1"],
            $_POST["clasificaciontrigliceridos1"],
            $_POST["colesterol1"],
            $_POST["clasificacioncolesterol1"],
            $_POST["tensionarterial1"],
            $_POST["clasificacionta1"]
        );

        if (!$stmt->execute()) {
            throw new Exception('Error al guardar: ' . $stmt->error);
        }
        $stmt->close();
        error_log("Datos guardados en BD exitosamente");
    } else {
        error_log("Modo solo PDF: saltando guardado en BD");
    }

    $matricula = $_POST['matricula'];
    $destinatario_email = $_POST['correo1'];

    error_log("Correo destinatario confirmado: " . $destinatario_email);

    // PASO 2: Crear carpeta de PDFs si no existe
    $carpetaPDFs = __DIR__ . '/reportes_salud';
    if (!file_exists($carpetaPDFs)) {
        if (!mkdir($carpetaPDFs, 0755, true)) {
            error_log("No se pudo crear carpeta: " . $carpetaPDFs);
            throw new Exception('Error al crear carpeta de reportes');
        }
    }

    // PASO 3: Obtener datos consolidados
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $url_api = $protocol . "://" . $host . "/resultadospdf.php?matricula_alum=" . urlencode($matricula);

    error_log("Consultando API: " . $url_api);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $json_data = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($json_data === false) {
        throw new Exception('Error al contactar servicio de reportes: ' . $curl_error);
    }

    if ($http_code !== 200) {
        throw new Exception('Servicio de reportes respondió con código: ' . $http_code);
    }

    $datos_pdf = json_decode($json_data, true);

    if (!$datos_pdf || isset($datos_pdf['error'])) {
        throw new Exception('No se encontraron datos consolidados: ' . ($datos_pdf['error'] ?? 'Respuesta vacía'));
    }

    // ✅ VALIDACIÓN: Solo cuando NO es modo PDF
    if (!$solo_generar_pdf) {
        $datos_faltantes = [];

        // 🔥 VALIDACIÓN MEJORADA DE DASS
        $tiene_dass = false;

        if (isset($datos_pdf['puntuacion_depresion']) && $datos_pdf['puntuacion_depresion'] !== null && $datos_pdf['puntuacion_depresion'] !== '') {
            $tiene_dass = true;
        }
        if (isset($datos_pdf['puntuacion_ansiedad']) && $datos_pdf['puntuacion_ansiedad'] !== null && $datos_pdf['puntuacion_ansiedad'] !== '') {
            $tiene_dass = true;
        }
        if (isset($datos_pdf['puntuacion_estres']) && $datos_pdf['puntuacion_estres'] !== null && $datos_pdf['puntuacion_estres'] !== '') {
            $tiene_dass = true;
        }

        if (!$tiene_dass) {
            if (isset($datos_pdf['total_depresion']) && $datos_pdf['total_depresion'] !== null && $datos_pdf['total_depresion'] !== '') {
                $tiene_dass = true;
            }
            if (isset($datos_pdf['total_ansiedad']) && $datos_pdf['total_ansiedad'] !== null && $datos_pdf['total_ansiedad'] !== '') {
                $tiene_dass = true;
            }
            if (isset($datos_pdf['total_estres']) && $datos_pdf['total_estres'] !== null && $datos_pdf['total_estres'] !== '') {
                $tiene_dass = true;
            }
        }

        if (!$tiene_dass) {
            $datos_faltantes[] = 'DASS-21 (Depresión, Ansiedad y Estrés)';
        }

        // 🔥 VALIDACIÓN MEJORADA DE ESTILO DE VIDA
        $tiene_estilo_vida = false;

        if (isset($datos_pdf['total_estilo_vida']) && $datos_pdf['total_estilo_vida'] !== null && $datos_pdf['total_estilo_vida'] !== '') {
            $tiene_estilo_vida = true;
        }

        if (!$tiene_estilo_vida) {
            if (
                (isset($datos_pdf['total_nutricion']) && $datos_pdf['total_nutricion'] !== null && $datos_pdf['total_nutricion'] !== '') ||
                (isset($datos_pdf['total_ejercicio']) && $datos_pdf['total_ejercicio'] !== null && $datos_pdf['total_ejercicio'] !== '') ||
                (isset($datos_pdf['total_salud']) && $datos_pdf['total_salud'] !== null && $datos_pdf['total_salud'] !== '')
            ) {
                $tiene_estilo_vida = true;
            }
        }

        if (!$tiene_estilo_vida) {
            $datos_faltantes[] = 'Estilo de Vida';
        }

        // Si faltan datos, enviar respuesta especial
        if (!empty($datos_faltantes)) {
            error_log("Datos incompletos para matrícula: " . $matricula);
            $conn->close();
            enviarJSON([
                'warning' => true,
                'mensaje' => 'El alumno aún no ha completado los siguientes cuestionarios',
                'cuestionarios_faltantes' => $datos_faltantes,
                'datos_guardados' => true
            ], 200);
        }
    } else {
        error_log("Modo PDF: Saltando validación de datos completos");
    }

    error_log("Datos consolidados obtenidos correctamente");

    // PASO 4: Generar PDF
    class PDF extends FPDF
    {
        private $RUTA_IMAGEN_FONDO;
        private $RUTA_LOGO;

        public $CAT_FISICAS = [
            'Peso insuficiente',
            'Peso normal',
            'Sobrepeso',
            'Obesidad grado 1',
            'Obesidad grado 2',
            'Obesidad grado 3 (mórbida)'
        ];

        public $CAT_DASS = [
            'Normal',
            'Leve',
            'Moderado',
            'Severo',
            'Extremadamente severo'
        ];
        public $ANCHO_TOTAL_BARRA = 150;

        public $COLOR_TITULO_BG = [34, 49, 63];
        public $COLOR_TITULO_TEXT = [255, 255, 255];
        public $COLOR_SUBTITULO_BG = [220, 220, 220];
        public $COLOR_SEPARADOR = [150, 150, 150];

        public function __construct()
        {
            parent::__construct();

            $this->RUTA_IMAGEN_FONDO = $_SERVER['DOCUMENT_ROOT'] . '/imagenes/despedida.png';
            $this->RUTA_LOGO = $_SERVER['DOCUMENT_ROOT'] . '/imagenes/logo_unacar_sf.png';

            error_log("=== RUTAS DE IMÁGENES ===");
            error_log("DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);
            error_log("Imagen fondo: " . $this->RUTA_IMAGEN_FONDO);
            error_log("Logo: " . $this->RUTA_LOGO);
        }

        private function convertirAImagenValida($rutaImagen)
        {
            if (!file_exists($rutaImagen)) {
                error_log("Imagen no existe: " . $rutaImagen);
                return null;
            }

            $imageInfo = @getimagesize($rutaImagen);
            if (!$imageInfo) {
                error_log("No se puede leer la imagen: " . $rutaImagen);
                return null;
            }

            $tipo = $imageInfo[2];
            error_log("Tipo de imagen detectado: " . $tipo . " para " . basename($rutaImagen));

            if ($tipo === IMAGETYPE_PNG) {
                error_log("✓ Imagen PNG válida: " . basename($rutaImagen));
                return $rutaImagen;
            }

            if ($tipo === IMAGETYPE_JPEG) {
                error_log("Convirtiendo JPEG a PNG: " . basename($rutaImagen));
                $img = imagecreatefromjpeg($rutaImagen);
                if (!$img) {
                    error_log("Error al crear imagen desde JPEG");
                    return null;
                }
                $rutaTemporal = sys_get_temp_dir() . '/' . uniqid() . '_' . basename($rutaImagen, '.png') . '.png';
                imagepng($img, $rutaTemporal);
                imagedestroy($img);
                error_log("✓ Imagen convertida a: " . $rutaTemporal);
                return $rutaTemporal;
            }

            if ($tipo === IMAGETYPE_GIF) {
                error_log("Convirtiendo GIF a PNG: " . basename($rutaImagen));
                $img = imagecreatefromgif($rutaImagen);
                if (!$img) {
                    error_log("Error al crear imagen desde GIF");
                    return null;
                }
                $rutaTemporal = sys_get_temp_dir() . '/' . uniqid() . '_' . basename($rutaImagen, '.png') . '.png';
                imagepng($img, $rutaTemporal);
                imagedestroy($img);
                error_log("✓ Imagen convertida a: " . $rutaTemporal);
                return $rutaTemporal;
            }

            error_log("Formato de imagen no soportado: " . $tipo);
            return null;
        }

        function Header()
        {
            global $datos_pdf;

            $ancho_pagina = $this->GetPageWidth();
            $alto_pagina = $this->GetPageHeight();

            $ANCHO_IMAGEN_CENTRO = 150;
            $ALTO_IMAGEN_CENTRO = 150;

            $x_centro = ($ancho_pagina / 2) - ($ANCHO_IMAGEN_CENTRO / 2);
            $y_centro = ($alto_pagina / 2) - ($ALTO_IMAGEN_CENTRO / 2);

            $rutaImagenValida = $this->convertirAImagenValida($this->RUTA_IMAGEN_FONDO);
            if ($rutaImagenValida) {
                try {
                    $this->Image($rutaImagenValida, $x_centro, $y_centro, $ANCHO_IMAGEN_CENTRO, $ALTO_IMAGEN_CENTRO);
                    error_log("✓ Imagen de fondo cargada correctamente");
                } catch (Exception $e) {
                    error_log("ERROR al cargar imagen de fondo: " . $e->getMessage());
                }
            }

            $this->SetFillColor($this->COLOR_TITULO_BG[0], $this->COLOR_TITULO_BG[1], $this->COLOR_TITULO_BG[2]);
            $this->Rect(0, 0, $this->GetPageWidth(), 20, 'F');

            $ANCHO_LOGO = 15;
            $ALTO_LOGO = 15;

            $x_pos = $ancho_pagina - 10 - $ANCHO_LOGO;
            $y_pos = 2;

            $rutaLogoValida = $this->convertirAImagenValida($this->RUTA_LOGO);
            if ($rutaLogoValida) {
                try {
                    $this->Image($rutaLogoValida, $x_pos, $y_pos, $ANCHO_LOGO, $ALTO_LOGO);
                    error_log("✓ Logo cargado correctamente");
                } catch (Exception $e) {
                    error_log("ERROR al cargar logo: " . $e->getMessage());
                }
            }

            $this->SetY(5);
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor($this->COLOR_TITULO_TEXT[0], $this->COLOR_TITULO_TEXT[1], $this->COLOR_TITULO_TEXT[2]);
            $this->Cell(0, 7, pdf_text('REPORTE DE SALUD INTEGRAL'), 0, 1, 'C');

            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 5, pdf_text('UNACAR - Generado el: ' . $datos_pdf['fecha']), 0, 1, 'C');

            $this->SetTextColor(0, 0, 0);
            $this->SetY(25);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);

            $ancho_contenido = $this->GetPageWidth() - 20;

            $this->Cell($ancho_contenido * 0.7, 10, pdf_text('Nota: Estos resultados son un acercamiento para un DX completo debes acudir a tu servicio medico a traves de tu seguro facultativo'), 0, 0, 'L');
            $this->Cell($ancho_contenido * 0.3, 10, pdf_text('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
        }

        function getEstadoColor($estado)
        {
            $estado = trim(strtolower($estado));
            $color = [200, 200, 200];
            $texto_salida = ucwords($estado);

            switch ($estado) {
                case 'bajo':
                case 'normal':
                case 'peso normal':
                case 'deseable':
                case 'leve':
                case 'moderada':
                case 'saludable':
                    $color = [178, 223, 178];
                    break;

                case 'peso insuficiente':
                case 'sobrepeso':
                case 'riesgo moderado':
                case 'moderado':
                case 'límite':
                case 'severo':
                case 'riesgo':
                    $color = [255, 204, 153];
                    break;

                case 'obesidad grado 1':
                case 'obesidad grado 2':
                case 'obesidad grado 3 (mórbida)':
                case 'riesgo alto':
                case 'extrema':
                case 'extremadamente severo':
                case 'no saludable':
                    $color = [255, 179, 179];
                    if ($estado == 'extremadamente severo') {
                        $texto_salida = 'EXTREMO';
                    } else {
                        $texto_salida = strtoupper($estado);
                    }
                    break;
            }

            return [pdf_text($texto_salida), $color];
        }

        function calcularAnchoBarra($categorias, $estado_actual)
        {
            $estado_actual = trim(strtolower($estado_actual));
            $ancho_unidad = $this->ANCHO_TOTAL_BARRA / count($categorias);
            $ancho_barra = 0;

            foreach ($categorias as $i => $cat) {
                $cat_std = trim(strtolower($cat));
                $ancho_barra += $ancho_unidad;

                if ($cat_std == $estado_actual) {
                    return $ancho_barra;
                }
            }

            if ($estado_actual == 'deseable' || $estado_actual == 'saludable') {
                return $ancho_unidad * 2;
            }

            return 0;
        }

        function drawCategoryHeaders($categorias, $y)
        {
            $x_inicio = 50;
            $ancho_unidad = $this->ANCHO_TOTAL_BARRA / count($categorias);
            $this->SetY($y);
            $this->SetX($x_inicio);
            $this->SetFont('Arial', 'B', 5);
            $this->SetFillColor(230, 230, 230);
            foreach ($categorias as $cat) {
                $this->Cell($ancho_unidad, 4, pdf_text($cat), 1, 0, 'C', true);
            }
            $this->Ln(4);
        }

        function addIndicadorProgresivo($label, $valor, $estado, $x, &$y, $categorias)
        {
            list($estado_texto, $color) = $this->getEstadoColor($estado);
            $ancho_barra = $this->calcularAnchoBarra($categorias, $estado);
            $x_inicio_barra = $x + 40;
            $alto_barra_rect = 6;
            $y_barra_rect = $y + 1;

            $this->SetFont('Arial', '', 10);
            $this->SetXY($x, $y);
            $this->Cell(25, $alto_barra_rect, pdf_text($label . ":"), 0, 0, 'L');
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(15, $alto_barra_rect, pdf_text($valor), 0, 0, 'L');

            $this->SetDrawColor(150, 150, 150);
            $this->SetFillColor(240, 240, 240);
            $this->Rect($x_inicio_barra, $y_barra_rect, $this->ANCHO_TOTAL_BARRA, $alto_barra_rect, 'FD');

            $this->SetFillColor($color[0], $color[1], $color[2]);
            $this->Rect($x_inicio_barra, $y_barra_rect, $ancho_barra, $alto_barra_rect, 'F');

            $this->SetFont('Arial', 'B', 7);
            $this->SetTextColor(255, 255, 255);

            $this->SetXY($x_inicio_barra, $y_barra_rect);
            $this->Cell($ancho_barra - 1, $alto_barra_rect, $estado_texto, 0, 0, 'R');
            $this->SetTextColor(0, 0, 0);

            $y += $alto_barra_rect + 2;
        }

        function addTablaVida($datos)
        {
            $x_center = 45;

            $ancho_col1 = 60;
            $ancho_col2 = 40;
            $ancho_col3 = 30;
            $ancho_col4 = 30;
            $alto_fila = 8;
            $alto_barra = 6;

            $this->SetFont('Arial', 'B', 10);
            $this->SetFillColor(220, 220, 220);

            $this->SetX($x_center);

            $this->Cell($ancho_col1, 8, pdf_text('Indicador'), 1, 0, 'C', true);
            $this->Cell($ancho_col3, 8, pdf_text('Estado'), 1, 0, 'C', true);
            $this->Cell($ancho_col4, 8, pdf_text('Nivel'), 1, 1, 'C', true);

            $this->SetFont('Arial', '', 9);
            $fill = false;
            foreach ($datos as $dato) {
                $total_puntos = isset($dato['total']) ? $dato['total'] : '';

                list($estado_texto, $color) = $this->getEstadoColor($dato['estado']);
                $y_celda = $this->GetY();
                $x_inicial = $this->GetX();

                $this->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);

                $this->SetX($x_center);

                $this->Cell($ancho_col1, $alto_fila, pdf_text($dato['nombre']), 'LRB', 0, 'L', true);
                $this->Cell($ancho_col3, $alto_fila, $estado_texto, 'RB', 0, 'C', true);
                $this->Cell($ancho_col4, $alto_fila, '', 'RB', 1, 'C', true);

                $x_barra = $x_center + $x_inicial + $ancho_col1 + $ancho_col3 + ($ancho_col4 / 2) - ($alto_barra / 2);
                $y_barra = $y_celda + ($alto_fila / 2) - ($alto_barra / 2);

                $this->SetFillColor($color[0], $color[1], $color[2]);
                $this->Rect($x_barra, $y_barra, $alto_barra, $alto_barra, 'F');

                $fill = !$fill;
            }
        }

        function sectionSeparator($text, $ln_after = 2)
        {
            $this->Ln(3);
            $this->SetFont('Arial', 'B', 12);

            $this->SetFillColor($this->COLOR_SUBTITULO_BG[0], $this->COLOR_SUBTITULO_BG[1], $this->COLOR_SUBTITULO_BG[2]);
            $this->Cell(0, 7, pdf_text($text), 0, 1, 'L', true);

            $this->SetDrawColor($this->COLOR_SEPARADOR[0], $this->COLOR_SEPARADOR[1], $this->COLOR_SEPARADOR[2]);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->Ln($ln_after);
        }

        // ========== INICIO SISTEMA DE RECOMENDACIONES ==========

        function getRecomendaciones($indicador, $valor, $clasificacion)
        {
            $recomendaciones = [];

            switch ($indicador) {
                case 'IMC':
                    $recomendaciones = $this->getRecomendacionesIMC($clasificacion);
                    break;
                case 'GLUCOSA':
                    $recomendaciones = $this->getRecomendacionesGlucosa($valor, $clasificacion);
                    break;
                case 'COLESTEROL':
                    $recomendaciones = $this->getRecomendacionesColesterol($valor, $clasificacion);
                    break;
                case 'TRIGLICERIDOS':
                    $recomendaciones = $this->getRecomendacionesTrigliceridos($valor, $clasificacion);
                    break;
                case 'TENSION':
                    $recomendaciones = $this->getRecomendacionesTension($clasificacion);
                    break;
                case 'DASS_ANSIEDAD':
                    $recomendaciones = $this->getRecomendacionesAnsiedad($clasificacion);
                    break;
                case 'DASS_ESTRES':
                    $recomendaciones = $this->getRecomendacionesEstres($clasificacion);
                    break;
                case 'DASS_DEPRESION':
                    $recomendaciones = $this->getRecomendacionesDepresion($clasificacion);
                    break;
            }

            return $recomendaciones;
        }

        private function getRecomendacionesIMC($clasificacion)
        {
            $clasificacion = trim(strtolower($clasificacion));

            switch ($clasificacion) {
                case 'peso insuficiente':
                    return [
                        'nutricion' => [
                            'Si tienes un peso inferior al recomendado, es importante incluir alimentos nutritivos y energéticos en tus comidas, como proteínas, cereales integrales y grasas saludables, para fortalecer tu cuerpo de manera progresiva.',
                            'Incorporar frutas, verduras, frutos secos y semillas no solo aporta nutrientes esenciales, sino que también ayuda a que tu alimentación sea más equilibrada y variada.',
                            'Consultar a un nutriólogo puede ser muy útil para establecer un plan de alimentación adaptado a tus necesidades y hábitos.'
                        ],
                        'ejercicio' => [
                            'Realizar ejercicios de fuerza ligera unas cuantas veces por semana ayuda a ganar masa muscular de manera gradual, favoreciendo tu fuerza y resistencia.',
                            'Es fundamental respetar los periodos de descanso entre entrenamientos, ya que el cuerpo necesita recuperarse para crecer y mantenerse saludable.'
                        ],
                        'medico' => [
                            'Si notas cambios importantes en tu energía o apetito, consulta con un médico para descartar posibles causas subyacentes.'
                        ]
                    ];

                case 'peso normal':
                    return [
                        'nutricion' => [
                            'Tu peso está en un buen rango, pero mantener una alimentación equilibrada con frutas, verduras, proteínas y cereales integrales es importante para tu bienestar.',
                            'Beber suficiente agua durante el día ayuda a mantener la concentración y el buen funcionamiento del cuerpo.',
                            'Un nutriólogo puede ofrecerte recomendaciones personalizadas para optimizar tu alimentación sin necesidad de cambios drásticos.'
                        ],
                        'ejercicio' => [
                            'Continuar con actividad física regular contribuye a mantener un buen estado físico y mental.',
                            'Probar diferentes tipos de ejercicios o deportes puede hacer que tu rutina sea más entretenida y motivadora.',
                            'Incluir estiramientos o ejercicios de flexibilidad ayuda a prevenir molestias musculares y mejorar tu movilidad.'
                        ],
                        'habitos' => [
                            'Seguir con tus hábitos saludables es la clave para mantener tu bienestar a largo plazo.',
                            'Realizar chequeos médicos ocasionales permite detectar a tiempo cualquier cambio en tu salud.'
                        ]
                    ];

                case 'sobrepeso':
                    return [
                        'nutricion' => [
                            'Reducir las porciones y comer con atención ayuda a sentirte más ligero y mejorar la digestión.',
                            'Evitar bebidas azucaradas y preferir agua o infusiones naturales contribuye a mantener estables los niveles de energía y glucosa.',
                            'Incluir más verduras y proteínas magras como pollo, pescado o legumbres ayuda a equilibrar tu alimentación.',
                            'Un nutriólogo puede enseñarte a planificar tus comidas de manera práctica y adaptada a tu estilo de vida.'
                        ],
                        'ejercicio' => [
                            'Comenzar con caminatas diarias y aumentar progresivamente la duración e intensidad mejora tu resistencia y bienestar.',
                            'Elegir actividades que disfrutes hace que las rutinas sean más fáciles de mantener.'
                        ],
                        'habitos' => [
                            'Dormir adecuadamente favorece la regulación hormonal y el equilibrio energético.',
                            'Prestar atención a las señales de saciedad ayuda a comer solo lo necesario y a evitar excesos.',
                            'Planificar tus comidas y horarios contribuye a mantener un estilo de vida más organizado y saludable.',
                            'Contar con el apoyo de amigos, familiares o alguien de confianza puede motivarte y hacer que el proceso sea más llevadero.'
                        ],
                        'medico' => [
                            'Consultar a un nutriólogo y a un médico permite diseñar un plan personalizado que mejore tu salud sin comprometer tu bienestar general.',
                            'Controlar periódicamente la presión arterial y los niveles de glucosa es recomendable para prevenir complicaciones; consulta a tu médico para más información al respecto.'
                        ]
                    ];

                case 'obesidad grado 1':
                case 'obesidad grado 2':
                case 'obesidad grado 3 (mórbida)':
                case 'obesidad grado 3 (morbida)':
                    return [
                        'nutricion' => [
                            'Realizar cambios progresivos en la alimentación, como reducir azúcares y grasas poco saludables, e incrementar frutas, verduras y proteínas magras, mejora tu bienestar general.',
                            'Comer porciones más pequeñas varias veces al día ayuda a mantener la energía.',
                            'Un nutriólogo puede elaborar un plan alimenticio adaptado a tus preferencias y necesidades, facilitando la implementación de los cambios.'
                        ],
                        'ejercicio' => [
                            'Moverte diariamente, aunque sea poco, tiene beneficios significativos; caminar, nadar o andar en bicicleta son buenas opciones para empezar.',
                            'Antes de ejercicios más intensos, consulta a un profesional de la salud para asegurarte de que sean adecuados.'
                        ],
                        'medico' => [
                            'Realizar chequeos médicos completos permite conocer tu estado de salud y detectar posibles factores de riesgo.',
                            'El seguimiento profesional asegura que las recomendaciones sean seguras y efectivas.'
                        ],
                        'psicologico' => [
                            'Contar con apoyo psicológico facilita el cambio de hábitos y mejora la motivación.',
                            'Un psicólogo puede ayudarte a mantener la constancia y encontrar formas positivas de cuidarte.'
                        ]
                    ];

                default:
                    return [];
            }
        }

        private function getRecomendacionesGlucosa($valor, $clasificacion)
        {
            $clasificacion = trim(strtolower($clasificacion));

            if ($clasificacion == 'normal' || $clasificacion == 'deseable') {
                return [
                    'nutricion' => [
                        'Tus niveles de glucosa son adecuados, por lo que mantener una alimentación balanceada y variada es suficiente para conservar un buen estado de salud.',
                        'Priorizar carbohidratos complejos y alimentos ricos en fibra ayuda a estabilizar la energía durante el día.',
                        'Reducir el consumo de dulces y postres contribuye a mantener niveles saludables.'
                    ],
                    'habitos' => [
                        'Continuar con un estilo de vida activo y saludable permite conservar los niveles de glucosa dentro del rango deseable.',
                        'Controlar periódicamente tus niveles de glucosa es una forma de prevenir complicaciones a largo plazo.'
                    ]
                ];
            } elseif ($clasificacion == 'limite' || $clasificacion == 'riesgo' || $clasificacion == 'riesgo moderado') {
                return [
                    'nutricion' => [
                        'Reducir el consumo de azúcares añadidos y bebidas azucaradas ayuda a mantener estables los niveles de glucosa.',
                        'Consumir alimentos con bajo índice glucémico y aumentar la fibra soluble puede mejorar el control glucémico.',
                        'Controlar las porciones de carbohidratos en cada comida favorece un equilibrio saludable.'
                    ],
                    'ejercicio' => [
                        'Realizar ejercicio aeróbico regularmente contribuye a mejorar la sensibilidad a la insulina y el bienestar general.',
                        'Caminar o mantenerte activo después de las comidas ayuda a regular los niveles de glucosa y a sentirte mejor y con más energía.',
                        'Perder peso de manera moderada puede impactar positivamente en tus niveles glucémicos, pero no te sobreexijas, ya que hacer todo de golpe puede dañarte en vez de ayudarte.'
                    ],
                    'medico' => [
                        'Consultar con un médico permite recibir orientación específica y, si es necesario, estudios complementarios para evaluar tu condición.',
                        'Un seguimiento más frecuente con el especialista puede ser necesario para mantener el control.'
                    ]
                ];
            } else {
                return [
                    'medico' => [
                        'Es importante acudir a un especialista cuanto antes, ya que los niveles elevados de glucosa pueden requerir evaluación y tratamiento profesional.',
                        'Es recomendable que hagas estudios adicionales para descartar complicaciones, solo como medida de prevención.',
                        'El seguimiento médico regular es fundamental para un manejo seguro de tu salud.'
                    ],
                    'nutricion' => [
                        'Un nutriólogo especializado puede ayudarte a aprender a manejar tu alimentación de manera adecuada.',
                        'El control de carbohidratos y la orientación profesional son esenciales para prevenir complicaciones.'
                    ],
                    'monitoreo' => [
                        'El monitoreo constante de los niveles de glucosa puede ser necesario hasta estabilizar la condición; contar con un especialista experto en el área puede darte una mejor orientación.'
                    ]
                ];
            }
        }

        private function getRecomendacionesColesterol($valor, $clasificacion)
        {
            $clasificacion = trim(strtolower($clasificacion));

            if ($clasificacion == 'normal' || $clasificacion == 'deseable') {
                return [
                    'nutricion' => [
                        'Mantener hábitos alimenticios equilibrados ayuda a conservar el colesterol dentro de rangos saludables.',
                        'Incluir pescado graso, nueces y semillas aporta grasas saludables beneficiosas para el corazón.',
                        'Evitar alimentos ultraprocesados contribuye a un perfil lipídico favorable; es decir, te ayuda a ingerir nutrientes que son esenciales.'
                    ],
                    'habitos' => [
                        'El seguimiento periódico y la actividad física regular ayudan a mantener un corazón saludable.',
                        'Realizar chequeos médicos preventivos permite detectar cambios a tiempo.'
                    ]
                ];
            } elseif ($clasificacion == 'limite' || $clasificacion == 'riesgo moderado') {
                return [
                    'nutricion' => [
                        'Reducir grasas saturadas y trans mejora tu salud cardiovascular, lo cual ayuda a tu cuerpo a sentirse mejor.',
                        'Aumentar el consumo de fibra, aceite de oliva, aguacate y frutos secos ayuda a controlar los niveles de colesterol.',
                        'Un plan de alimentación equilibrado puede prevenir complicaciones a largo plazo.'
                    ],
                    'ejercicio' => [
                        'El ejercicio aeróbico regular, como caminar, trotar o nadar, ayuda a controlar el colesterol, mejorar la salud cardiovascular y quemar grasas; acudir a un experto en el área puede darte más orientación.'
                    ],
                    'medico' => [
                        'Consultar con un médico para evaluar la salud cardiovascular y monitorear los niveles de colesterol es recomendable.',
                        'Se pueden necesitar controles periódicos adicionales según la evolución de tus niveles.'
                    ]
                ];
            } else {
                return [
                    'medico' => [
                        'Es esencial acudir a un especialista cardiovascular para evaluar tu situación.',
                        'Se pueden requerir estudios detallados y tratamientos específicos.'
                    ],
                    'nutricion' => [
                        'Seguir un plan alimenticio especializado y supervisado por un profesional es fundamental para reducir riesgos.'
                    ],
                    'ejercicio' => [
                        'La actividad física supervisada ayuda a mejorar el perfil lipídico y el bienestar general.'
                    ]
                ];
            }
        }

        private function getRecomendacionesTrigliceridos($valor, $clasificacion)
        {
            $clasificacion = trim(strtolower($clasificacion));

            if ($clasificacion == 'normal' || $clasificacion == 'deseable') {
                return [
                    'nutricion' => [
                        'Mantener hábitos alimenticios equilibrados ayuda a mantener los niveles dentro de los valores saludables.',
                        'Moderar el consumo de alcohol y azúcares simples contribuye a mantener un buen perfil lipídico.'
                    ]
                ];
            } elseif ($clasificacion == 'limite' || $clasificacion == 'riesgo moderado') {
                return [
                    'nutricion' => [
                        'Reducir azúcares añadidos y carbohidratos refinados es clave para mantener tu cuerpo saludable.',
                        'Aumentar el consumo de pescado graso y preferir frutas enteras sobre jugos favorece el equilibrio nutricional.'
                    ],
                    'ejercicio' => [
                        'Mantener actividad física regular y moderada ayuda a mejorar la salud general.'
                    ],
                    'medico' => [
                        'Consultar con un médico y realizar controles periódicos es recomendable para prevenir complicaciones.'
                    ]
                ];
            } else {
                return [
                    'medico' => [
                        'Acudir a un especialista es esencial para evaluar y tratar los triglicéridos altos de manera segura.'
                    ],
                    'nutricion' => [
                        'Seguir un plan nutricional supervisado por profesionales es fundamental.'
                    ]
                ];
            }
        }

        private function getRecomendacionesTension($clasificacion)
        {
            $clasificacion = trim(strtolower($clasificacion));

            if ($clasificacion == 'normal' || $clasificacion == 'deseable') {
                return [
                    'habitos' => [
                        'Mantener hábitos saludables, como alimentación equilibrada y actividad física regular, ayuda a conservar la presión arterial dentro de valores normales.',
                        'Monitorear la presión de forma periódica permite detectar cambios a tiempo.'
                    ]
                ];
            } elseif ($clasificacion == 'limite' || $clasificacion == 'riesgo moderado') {
                return [
                    'nutricion' => [
                        'Reducir el consumo de sal y evitar alimentos procesados favorece el control de la presión arterial.',
                        'Consumir frutas y verduras ricas en potasio ayuda a mantener un equilibrio saludable.'
                    ],
                    'ejercicio' => [
                        'El ejercicio aeróbico moderado, como caminar o nadar, contribuye a regular la presión arterial.',
                        'Prácticas como yoga o estiramientos ayudan a manejar el estrés, que impacta en la presión.'
                    ],
                    'habitos' => [
                        'Dormir bien y gestionar el estrés son hábitos fundamentales para mantener la presión arterial estable.'
                    ],
                    'medico' => [
                        'Consultar con un médico y realizar controles periódicos ayuda a prevenir complicaciones.'
                    ]
                ];
            } else {
                return [
                    'medico' => [
                        'Es importante buscar atención médica especializada de inmediato.',
                        'El seguimiento profesional asegura un manejo seguro de la presión arterial.'
                    ],
                    'nutricion' => [
                        'Seguir un plan alimenticio supervisado puede ser necesario para reducir riesgos.'
                    ],
                    'monitoreo' => [
                        'El monitoreo frecuente y las visitas regulares al médico son fundamentales.'
                    ]
                ];
            }
        }

        private function getRecomendacionesAnsiedad($severidad)
        {
            $severidad = trim(strtolower($severidad));

            switch ($severidad) {
                case 'normal':
                    return [
                        'habitos' => [
                            'Mantener técnicas de relajación, rutinas de sueño regulares y actividades recreativas ayuda a prevenir la ansiedad.'
                        ]
                    ];

                case 'leve':
                    return [
                        'tecnicas' => [
                            'Practicar respiración profunda, meditación guiada y ejercicio regular contribuye a reducir la ansiedad leve.'
                        ],
                        'habitos' => [
                            'Reducir estimulantes, mantener horarios de sueño constantes y limitar la exposición a situaciones estresantes favorece la tranquilidad.'
                        ]
                    ];

                case 'moderado':
                    return [
                        'psicologico' => [
                            'Consultar con un psicólogo puede ofrecer herramientas efectivas para manejar la ansiedad moderada.'
                        ],
                        'tecnicas' => [
                            'Expresar emociones por escrito y técnicas de conexión con el presente ayudan a controlar la ansiedad.'
                        ],
                        'social' => [
                            'Mantener relaciones sociales y grupos de apoyo proporciona contención y bienestar emocional.'
                        ]
                    ];

                case 'severo':
                case 'extremadamente severo':
                case 'extremo':
                    return [
                        'medico' => [
                            'Buscar apoyo profesional especializado es crucial en casos de ansiedad severa.',
                            'Existen tratamientos efectivos que pueden mejorar significativamente la calidad de vida.'
                        ],
                        'crisis' => [
                            'En situaciones de crisis, buscar ayuda inmediata es vital. No enfrentes la situación solo.'
                        ],
                        'inmediato' => [
                            'Evitar decisiones importantes y buscar apoyo de personas de confianza es recomendable.'
                        ]
                    ];

                default:
                    return [];
            }
        }

        private function getRecomendacionesEstres($severidad)
        {
            $severidad = trim(strtolower($severidad));

            switch ($severidad) {
                case 'normal':
                    return [
                        'habitos' => [
                            'Mantener un equilibrio entre actividades, descanso adecuado y tiempo de ocio ayuda a prevenir el estrés.'
                        ]
                    ];

                case 'leve':
                    return [
                        'organizacion' => [
                            'Organizar el tiempo, dividir tareas grandes y establecer prioridades realistas ayuda a reducir el estrés leve.'
                        ],
                        'tecnicas' => [
                            'Practicar respiración, descansos regulares y ejercicio físico contribuye al manejo del estrés.'
                        ]
                    ];

                case 'moderado':
                    return [
                        'psicologico' => [
                            'Consultar con un psicólogo permite adquirir herramientas útiles para manejar el estrés moderado.'
                        ],
                        'organizacion' => [
                            'Reevaluar la carga de actividades, compartir responsabilidades y planificar descansos ayuda a manejar el estrés.'
                        ],
                        'autocuidado' => [
                            'Priorizar sueño adecuado, alimentación equilibrada y ejercicio regular es fundamental para el bienestar.'
                        ]
                    ];

                case 'severo':
                case 'extremadamente severo':
                case 'extremo':
                    return [
                        'medico' => [
                            'Buscar apoyo profesional especializado es necesario, ya que el estrés crónico puede afectar la salud general.'
                        ],
                        'inmediato' => [
                            'Ajustar la carga de actividades, comunicar la situación y tomar descansos temporales son acciones prioritarias.'
                        ],
                        'apoyo' => [
                            'Buscar redes de apoyo y mantener comunicación con personas cercanas contribuye al manejo del estrés severo.'
                        ]
                    ];

                default:
                    return [];
            }
        }

        private function getRecomendacionesDepresion($severidad)
        {
            $severidad = trim(strtolower($severidad));

            switch ($severidad) {
                case 'normal':
                    return [
                        'habitos' => [
                            'Mantener rutinas saludables, relaciones sociales positivas y actividades que disfrutes ayuda a prevenir síntomas depresivos.'
                        ]
                    ];

                case 'leve':
                    return [
                        'activacion' => [
                            'Establecer rutinas diarias, planificar actividades agradables y exponerte a luz natural mejora el estado de ánimo.'
                        ],
                        'social' => [
                            'Mantener contacto con personas cercanas y evitar el aislamiento favorece el bienestar emocional.'
                        ],
                        'ejercicio' => [
                            'Realizar ejercicio físico regular, preferentemente al aire libre, aporta beneficios documentados para el ánimo.'
                        ]
                    ];

                case 'moderado':
                    return [
                        'psicologico' => [
                            'Consultar con un psicólogo y buscar apoyo temprano facilita la recuperación en casos de depresión moderada.'
                        ],
                        'activacion' => [
                            'Mantener rutinas básicas y establecer metas pequeñas ayuda a recuperar motivación y control.'
                        ],
                        'social' => [
                            'Participar en grupos de apoyo y comunicar tu situación ofrece contención emocional.'
                        ],
                        'autocuidado' => [
                            'Priorizar sueño adecuado, alimentación nutritiva y limitar redes sociales negativas contribuye al bienestar.'
                        ]
                    ];

                case 'severo':
                case 'extremadamente severo':
                case 'extremo':
                    return [
                        'medico' => [
                            'Buscar atención profesional especializada es fundamental, ya que existen tratamientos efectivos disponibles.',
                            'El seguimiento cercano permite intervenir rápidamente ante cualquier complicación.'
                        ],
                        'psicologico' => [
                            'La terapia psicológica intensiva y el apoyo emocional constante son recomendables para manejar la depresión severa.'
                        ],
                        'social' => [
                            'Mantener comunicación frecuente con personas de confianza ayuda a reducir el aislamiento y brindar contención.'
                        ],
                        'inmediato' => [
                            'Si surgen ideas de autolesión o riesgo, acudir a un servicio de emergencia de inmediato es prioritario.'
                        ]
                    ];

                default:
                    return [];
            }
        }

        function addSeccionRecomendaciones($datos_pdf)
        {
            $this->AddPage();
            $this->sectionSeparator('RECOMENDACIONES PERSONALIZADAS');

            $this->SetFont('Arial', 'I', 9);
            $this->SetTextColor(100, 100, 100);
            $this->MultiCell(0, 5, pdf_text('Las siguientes recomendaciones están basadas en tus resultados individuales. Recuerda que son orientativas y no sustituyen la consulta médica profesional.'), 0, 'L');
            $this->Ln(3);
            $this->SetTextColor(0, 0, 0);

            // Recomendaciones IMC
            if (!empty($datos_pdf['clasificacion_imc'])) {
                $recomendaciones_imc = $this->getRecomendaciones('IMC', $datos_pdf['imc'], $datos_pdf['clasificacion_imc']);
                if (!empty($recomendaciones_imc)) {
                    $this->addBloqueRecomendaciones(
                        'Índice de Masa Corporal (IMC: ' . $datos_pdf['imc'] . ')',
                        $datos_pdf['clasificacion_imc'],
                        $recomendaciones_imc
                    );
                }
            }

            // Recomendaciones Glucosa
            if (!empty($datos_pdf['clasificacion_glucosa'])) {
                $recomendaciones_glucosa = $this->getRecomendaciones('GLUCOSA', $datos_pdf['glucosa'], $datos_pdf['clasificacion_glucosa']);
                if (!empty($recomendaciones_glucosa)) {
                    $this->addBloqueRecomendaciones(
                        'Glucosa (' . $datos_pdf['glucosa'] . ' mg/dL)',
                        $datos_pdf['clasificacion_glucosa'],
                        $recomendaciones_glucosa
                    );
                }
            }

            // Recomendaciones Colesterol
            if (!empty($datos_pdf['clasificacion_colesterol'])) {
                $recomendaciones_colesterol = $this->getRecomendaciones('COLESTEROL', $datos_pdf['colesterol'], $datos_pdf['clasificacion_colesterol']);
                if (!empty($recomendaciones_colesterol)) {
                    $this->addBloqueRecomendaciones(
                        'Colesterol (' . $datos_pdf['colesterol'] . ' mg/dL)',
                        $datos_pdf['clasificacion_colesterol'],
                        $recomendaciones_colesterol
                    );
                }
            }

            // Recomendaciones Triglicéridos
            if (!empty($datos_pdf['clasificacion_trigliceridos'])) {
                $recomendaciones_trigliceridos = $this->getRecomendaciones('TRIGLICERIDOS', $datos_pdf['trigliceridos'], $datos_pdf['clasificacion_trigliceridos']);
                if (!empty($recomendaciones_trigliceridos)) {
                    $this->addBloqueRecomendaciones(
                        'Triglicéridos (' . $datos_pdf['trigliceridos'] . ' mg/dL)',
                        $datos_pdf['clasificacion_trigliceridos'],
                        $recomendaciones_trigliceridos
                    );
                }
            }

            // Recomendaciones Tensión Arterial
            if (!empty($datos_pdf['clasificacion_tension_arterial'])) {
                $recomendaciones_tension = $this->getRecomendaciones('TENSION', null, $datos_pdf['clasificacion_tension_arterial']);
                if (!empty($recomendaciones_tension)) {
                    $this->addBloqueRecomendaciones(
                        'Tension Arterial (' . $datos_pdf['tension_arterial'] . ' mmHg)',
                        $datos_pdf['clasificacion_tension_arterial'],
                        $recomendaciones_tension
                    );
                }
            }

            // Recomendaciones DASS - Ansiedad
            if (!empty($datos_pdf['severidad_ansiedad'])) {
                $recomendaciones_ansiedad = $this->getRecomendaciones('DASS_ANSIEDAD', null, $datos_pdf['severidad_ansiedad']);
                if (!empty($recomendaciones_ansiedad)) {
                    $this->addBloqueRecomendaciones(
                        'Ansiedad ' . $datos_pdf['puntuacion_ansiedad'],
                        $datos_pdf['severidad_ansiedad'],
                        $recomendaciones_ansiedad
                    );
                }
            }

            // Recomendaciones DASS - Estrés
            if (!empty($datos_pdf['severidad_estres'])) {
                $recomendaciones_estres = $this->getRecomendaciones('DASS_ESTRES', null, $datos_pdf['severidad_estres']);
                if (!empty($recomendaciones_estres)) {
                    $this->addBloqueRecomendaciones(
                        'Estres ' . $datos_pdf['puntuacion_estres'],
                        $datos_pdf['severidad_estres'],
                        $recomendaciones_estres
                    );
                }
            }
        }

        private function addBloqueRecomendaciones($titulo, $estado, $recomendaciones)
        {
            // Calcular espacio mínimo necesario
            $espacio_minimo = 35;

            if ($this->GetY() > (297 - 15 - $espacio_minimo)) {
                $this->AddPage();
            }

            $this->Ln(3);

            // === DISEÑO PROFESIONAL LIMPIO ===
            $x_inicial = 15;
            $ancho_caja = 180;
            $y_box_inicio = $this->GetY();

            list($estado_texto, $color) = $this->getEstadoColor($estado);

            // === HEADER CON TÍTULO Y ESTADO ===
            // Fondo del header
            $this->SetFillColor($color[0], $color[1], $color[2]);
            $this->Rect($x_inicial, $y_box_inicio, $ancho_caja, 9, 'F');

            // Título blanco sobre fondo de color
            $this->SetXY($x_inicial + 4, $y_box_inicio + 2);
            $this->SetFont('Arial', 'B', 11);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(140, 5, pdf_text($titulo), 0, 0, 'L');

            // Estado en badge blanco
            $this->SetFont('Arial', 'B', 9);
            $ancho_estado = $this->GetStringWidth($estado_texto) + 10;
            $x_estado = $x_inicial + $ancho_caja - $ancho_estado - 4;

            $this->SetFillColor(255, 255, 255);
            $this->RoundedRect($x_estado, $y_box_inicio + 2, $ancho_estado, 5, 1.5, 'F');

            $this->SetTextColor($color[0], $color[1], $color[2]);
            $this->SetXY($x_estado, $y_box_inicio + 2);
            $this->Cell($ancho_estado, 5, $estado_texto, 0, 0, 'C');

            // Posicionar después del header
            $this->SetY($y_box_inicio + 9);
            $y_contenido_inicio = $this->GetY();

            // Dibujar fondo suave del contenido
            $this->SetFillColor(250, 250, 250);
            $altura_estimada = 10;
            foreach ($recomendaciones as $items) {
                $altura_estimada += 7 + (count($items) * 5);
            }
            $this->Rect($x_inicial, $y_contenido_inicio, $ancho_caja, min($altura_estimada, 200), 'F');

            $this->SetTextColor(0, 0, 0);

            // === CONTENIDO ===
            foreach ($recomendaciones as $categoria => $items) {
                // Calcular espacio necesario para esta categoría
                $espacio_categoria = 7 + (count($items) * 5.5);

                if ($this->GetY() + $espacio_categoria > (297 - 15)) {
                    // Cerrar caja actual
                    $altura_usada = $this->GetY() - $y_contenido_inicio;
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetLineWidth(0.4);
                    $this->Rect($x_inicial, $y_box_inicio, $ancho_caja, $altura_usada + 9, 'D');

                    // Nueva página
                    $this->AddPage();
                    $y_box_inicio = $this->GetY();
                    $y_contenido_inicio = $y_box_inicio;

                    // Nuevo fondo
                    $this->SetFillColor(250, 250, 250);
                    $this->Rect($x_inicial, $y_contenido_inicio, $ancho_caja, 150, 'F');
                }

                $this->Ln(2);

                // === TÍTULO DE CATEGORÍA (LIMPIO, SIN ICONOS) ===
                $this->SetX($x_inicial + 4);
                $this->SetFont('Arial', 'B', 10);

                // Color según categoría
                switch ($categoria) {
                    case 'nutricion':
                        $this->SetTextColor(46, 125, 50);
                        $nombre_cat = 'Nutrición';
                        break;
                    case 'ejercicio':
                        $this->SetTextColor(3, 169, 244);
                        $nombre_cat = 'Actividad Física';
                        break;
                    case 'medico':
                        $this->SetTextColor(211, 47, 47);
                        $nombre_cat = 'Atención Médica';
                        break;
                    case 'psicologico':
                        $this->SetTextColor(123, 31, 162);
                        $nombre_cat = 'Salud Mental';
                        break;
                    case 'habitos':
                        $this->SetTextColor(255, 143, 0);
                        $nombre_cat = 'Hábitos Saludables';
                        break;
                    case 'crisis':
                        $this->SetTextColor(198, 40, 40);
                        $nombre_cat = 'URGENTE';
                        break;
                    case 'social':
                        $this->SetTextColor(0, 150, 136);
                        $nombre_cat = 'Apoyo Social';
                        break;
                    case 'inmediato':
                        $this->SetTextColor(255, 87, 34);
                        $nombre_cat = 'Acción Inmediata';
                        break;
                    case 'organizacion':
                        $this->SetTextColor(96, 125, 139);
                        $nombre_cat = 'Organización';
                        break;
                    case 'autocuidado':
                        $this->SetTextColor(156, 39, 176);
                        $nombre_cat = 'Autocuidado';
                        break;
                    case 'activacion':
                        $this->SetTextColor(255, 152, 0);
                        $nombre_cat = 'Activación';
                        break;
                    case 'tecnicas':
                        $this->SetTextColor(63, 81, 181);
                        $nombre_cat = 'Técnicas';
                        break;
                    case 'monitoreo':
                        $this->SetTextColor(121, 85, 72);
                        $nombre_cat = 'Monitoreo';
                        break;
                    case 'academico':
                        $this->SetTextColor(25, 118, 210);
                        $nombre_cat = 'Académico';
                        break;
                    case 'apoyo':
                        $this->SetTextColor(0, 137, 123);
                        $nombre_cat = 'Apoyo';
                        break;
                    default:
                        $this->SetTextColor(30, 70, 120);
                        $nombre_cat = ucfirst($categoria);
                }

                $this->Cell(0, 6, pdf_text($nombre_cat), 0, 1, 'L');

                // === ITEMS (SIN NUMERACIÓN, SOLO VIÑETAS SIMPLES) ===
                $this->SetFont('Arial', '', 9);
                $this->SetTextColor(60, 60, 60);

                foreach ($items as $item) {
                    if ($this->GetY() > 275) {
                        // Cerrar y nueva página
                        $altura_usada = $this->GetY() - $y_contenido_inicio;
                        $this->SetDrawColor(200, 200, 200);
                        $this->Rect($x_inicial, $y_box_inicio, $ancho_caja, $altura_usada + 9, 'D');

                        $this->AddPage();
                        $y_box_inicio = $this->GetY();
                        $y_contenido_inicio = $y_box_inicio;

                        $this->SetFillColor(250, 250, 250);
                        $this->Rect($x_inicial, $y_contenido_inicio, $ancho_caja, 150, 'F');
                    }

                    $this->SetX($x_inicial + 8);

                    // Viñeta simple y texto
                    $y_actual_item = $this->GetY();

                    // Dibujar viñeta (punto cuadrado pequeño)
                    $this->SetFillColor(100, 100, 100);
                    $this->Rect($x_inicial + 9, $y_actual_item + 1.5, 1.5, 1.5, 'F');

                    // Texto del item
                    $this->SetX($x_inicial + 12);
                    $this->MultiCell($ancho_caja - 16, 4.5, pdf_text($item), 0, 'L');
                }

                $this->Ln(1.5);
            }

            // Cerrar caja final
            $altura_total = $this->GetY() - $y_box_inicio;
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.4);
            $this->Rect($x_inicial, $y_box_inicio, $ancho_caja, $altura_total, 'D');

            $this->Ln(4);
        }

        private function RoundedRect($x, $y, $w, $h, $r, $style = '')
        {
            $k = $this->k;
            $hp = $this->h;

            if ($style == 'F')
                $op = 'f';
            elseif ($style == 'FD' || $style == 'DF')
                $op = 'B';
            else
                $op = 'S';

            $MyArc = 4 / 3 * (sqrt(2) - 1);

            $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
            $xc = $x + $w - $r;
            $yc = $y + $r;
            $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
            $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
            $xc = $x + $w - $r;
            $yc = $y + $h - $r;
            $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
            $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
            $xc = $x + $r;
            $yc = $y + $h - $r;
            $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
            $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
            $xc = $x + $r;
            $yc = $y + $r;
            $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
            $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
            $this->_out($op);
        }

        private function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
        {
            $h = $this->h;
            $this->_out(sprintf(
                '%.2F %.2F %.2F %.2F %.2F %.2F c ',
                $x1 * $this->k,
                ($h - $y1) * $this->k,
                $x2 * $this->k,
                ($h - $y2) * $this->k,
                $x3 * $this->k,
                ($h - $y3) * $this->k
            ));
        }
        // ========== FIN SISTEMA DE RECOMENDACIONES ==========
        // ========== FIN SISTEMA DE RECOMENDACIONES ==========
    }

    // === CREAR PDF ===
    error_log("Iniciando creación del PDF...");
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    // SECCIÓN: DATOS DEL ALUMNO
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(30, 70, 120);
    $pdf->Cell(0, 8, pdf_text('DATOS DEL ALUMNO'), 0, 1, 'L');

    $pdf->SetTextColor(0, 0, 0);

    addGridRow($pdf, 'Nombre Completo', $datos_pdf['nombres_alum'] . ' ' . $datos_pdf['ape_paterno_alum'] . ' ' . $datos_pdf['ape_materno_alum'], '', '');
    addGridRow($pdf, 'Matricula', $datos_pdf['matricula_alum'], '', '');
    addGridRow($pdf, 'Facultad', $datos_pdf['nombre_facultad'], '', '');
    addGridRow($pdf, 'Carrera', $datos_pdf['nombre_carrera'], '', '');
    addGridRow($pdf, 'Correo', $datos_pdf['correo_alum'], 'Edad', $datos_pdf['edad_alum']);
    addGridRow($pdf, 'Fecha de Reporte', $datos_pdf['fecha'], '', '');

    $pdf->Ln(5);

    // === Indicadores de Salud Física ===
    $pdf->sectionSeparator('Indicadores de Salud Física');

    $y = $pdf->GetY();
    $pdf->drawCategoryHeaders($pdf->CAT_FISICAS, $y);
    $y += 6;
    $pdf->addIndicadorProgresivo('IMC', $datos_pdf['imc'], $datos_pdf['clasificacion_imc'], 10, $y, $pdf->CAT_FISICAS);

    $pdf->Ln(5);

    $y = $pdf->GetY();
    $y += 6;
    $pdf->Ln(5);
    addGridRow($pdf, 'Peso (kg)', $datos_pdf['peso'], '', '');
    addGridRow($pdf, 'Talla (cm)', $datos_pdf['talla'], '', '');
    addGridRow($pdf, 'ICC (Cintura-Cadera)', $datos_pdf['icc'], 'Resultado', $datos_pdf['clasificacion_de_icc']);
    addGridRow($pdf, 'ICE (Cintura-Estatura)', $datos_pdf['ice'], '', '');
    addGridRow($pdf, 'Masa Muscular (kg)', $datos_pdf['masa_magra'], '', '');
    addGridRow($pdf, 'Masa grasa (%)', $datos_pdf['porcentaje_masa_grasa'], 'Resultado', $datos_pdf['clasificacion_porcentaje_grasa']);
    addGridRow($pdf, 'Agua total (Litros)', $datos_pdf['agua_total'], '', '');
    addGridRow($pdf, 'Gasto Energético Total (kcal)', $datos_pdf['get1'], '', '');

    $pdf->Ln(5);

    // === Perfil Sanguíneo YTA ===
    $pdf->sectionSeparator('Perfil Sanguíneo y Tensión Arterial');

    addGridRow($pdf, 'Glucosa (mg/dL)', $datos_pdf['glucosa'], 'Resultado', $datos_pdf['clasificacion_glucosa']);
    addGridRow($pdf, 'Colesterol (mg/dL)', $datos_pdf['colesterol'], 'Resultado', $datos_pdf['clasificacion_colesterol']);
    addGridRow($pdf, 'Triglicéridos (mg/dL)', $datos_pdf['trigliceridos'], 'Resultado', $datos_pdf['clasificacion_trigliceridos']);
    addGridRow($pdf, 'Tensión Arterial (mmHg)', $datos_pdf['tension_arterial'], 'Resultado', $datos_pdf['clasificacion_tension_arterial']);

    $pdf->Ln(5);

    // === Perfil Estilo de Vida ===
    $pdf->sectionSeparator('Perfil Estilo de Vida');

    $datosVida = [
        ['nombre' => 'Nutrición', 'estado' => $datos_pdf['saludable_nutricion']],
        ['nombre' => 'Ejercicio', 'estado' => $datos_pdf['saludable_ejercicio']],
        ['nombre' => 'Salud', 'estado' => $datos_pdf['saludable_salud']],
        ['nombre' => 'Soporte Interpersonal', 'estado' => $datos_pdf['saludable_soporte']],
        ['nombre' => 'Manejo de Estrés', 'estado' => $datos_pdf['saludable_manejo']],
        ['nombre' => 'Autoactualizacion', 'estado' => $datos_pdf['saludable_autoactualizacion']]
    ];
    $pdf->addTablaVida($datosVida);
    $pdf->Ln(8);

    // === Perfil DASS ===
    if ($pdf->GetY() > 30) {
        $pdf->AddPage();
    }

    $pdf->sectionSeparator('Perfil DASS');

    $y = $pdf->GetY();
    $pdf->drawCategoryHeaders($pdf->CAT_DASS, $y);
    $y += 5;

    $pdf->addIndicadorProgresivo('Ansiedad', $datos_pdf['puntuacion_ansiedad'], $datos_pdf['severidad_ansiedad'], 10, $y, $pdf->CAT_DASS);
    $pdf->addIndicadorProgresivo('Estrés', $datos_pdf['puntuacion_estres'], $datos_pdf['severidad_estres'], 10, $y, $pdf->CAT_DASS);
    $pdf->addIndicadorProgresivo('Depresión', $datos_pdf['puntuacion_depresion'], $datos_pdf['severidad_depresion'], 10, $y, $pdf->CAT_DASS);


    // ========== AGREGAR SECCION DE RECOMENDACIONES ==========
    $pdf->addSeccionRecomendaciones($datos_pdf);
    // ========== FIN SECCION DE RECOMENDACIONES ==========

    // GUARDAR PDF EN CARPETA PERMANENTE
    $matricula_sanitizada = preg_replace('/[^a-zA-Z0-9_-]/', '', $matricula);
    $timestamp = date('Y-m-d_H-i-s');

    $carpetaMatricula = $carpetaPDFs . '/' . $matricula_sanitizada;

    if (!file_exists($carpetaMatricula)) {
        if (!mkdir($carpetaMatricula, 0755, true)) {
            throw new Exception('No se pudo crear la carpeta para la matrícula: ' . $matricula_sanitizada);
        }
        error_log("Carpeta creada: " . $carpetaMatricula);
    }

    $nombreArchivo = 'reporte_' . $matricula_sanitizada . '_' . $timestamp . '.pdf';
    $rutaPDF = $carpetaMatricula . '/' . $nombreArchivo;

    $pdf->Output('F', $rutaPDF);

    if (!file_exists($rutaPDF) || filesize($rutaPDF) == 0) {
        throw new Exception('Error al crear el PDF');
    }
    error_log("PDF creado exitosamente: " . $rutaPDF . " (" . filesize($rutaPDF) . " bytes)");

    // PASO 5: Enviar correo
    $correoEnviado = false;
    $errorCorreo = '';

    error_log("=== INICIANDO ENVÍO DE CORREO ===");
    error_log("Destinatario: " . $destinatario_email);
    error_log("Archivo PDF: " . $rutaPDF);

    try {
        ob_start();

        $mail = new PHPMailer(true);


        $mail->SMTPDebug = 0;
        $mail->Debugoutput = function ($str, $level) {
            error_log("PHPMailer [$level]: $str");
        };

        $mail->isSMTP();
        $mail->Host = 'mail.sistema-integral-de-salud-unacar.com.mx';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply@sistema-integral-de-salud-unacar.com.mx';
        $mail->Password = 'sklike5522';
        $mail->Port = 25;
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Timeout = 60;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        error_log("Configuración SMTP completada");

        $mail->setFrom('noreply@sistema-integral-de-salud-unacar.com.mx', 'Sistema Integral de Salud UNACAR');
        $mail->addAddress($destinatario_email);

        error_log("Remitente y destinatario configurados");

        if (!file_exists($rutaPDF)) {
            throw new Exception("El archivo PDF no existe: $rutaPDF");
        }

        $mail->addAttachment($rutaPDF, $nombreArchivo);
        error_log("Adjunto agregado: " . $nombreArchivo);

        $mail->isHTML(true);
        $mail->Subject = 'Resultados de tu Evaluación de Salud - UNACAR';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
                .header { background-color: #1e4678; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background-color: white; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; padding: 12px 24px; background-color: #1e4678; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                /* Estilo para la nota médica (Amarillo) */
                .highlight { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; font-size: 14px; }
                /* Estilo para la alerta de datos falsos (Rojo suave) */
                .pilot-alert { background-color: #f8d7da; color: #721c24; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; font-size: 14px; }
                .pilot-tag { background-color: #ffc107; color: #333; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; display: inline-block; margin-top: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🏥 Sistema Integral de Salud UNACAR</h1>
                    <div class="pilot-tag">⚠️ Prueba Piloto</div>
                </div>
                <div class="content">
                    <h2>Estimado(a) estudiante,</h2>
                    
                    <p>Estás recibiendo este correo porque tu usuario forma parte de la fase de <strong>Prueba Piloto</strong> del sistema.</p>

                    <div class="pilot-alert">
                        <strong>🚧 AVISO IMPORTANTE DE PRUEBA:</strong><br>
                        Ten en cuenta que, debido a la naturaleza de esta prueba piloto, <strong>algunos datos, métricas o resultados mostrados en el reporte adjunto podrían ser ficticios, simulados o generados automáticamente</strong> para verificar el funcionamiento técnico del sistema. Por favor, no los tomes como valores clínicos reales.
                    </div>

                    <p>Te informamos que tu <strong>Reporte de Salud Integral (Simulado)</strong> ha sido generado exitosamente el día <strong>' . date('d/m/Y') . '</strong>.</p>
                    
                    <p>🔎 Encontrarás adjunto a este correo el documento PDF correspondiente.</p>
                    
                    <div class="highlight">
                        <strong>👨‍⚕️ Nota Informativa:</strong> Recuerda siempre que este sistema es una herramienta de apoyo. Para un diagnóstico completo y profesional, debes acudir presencialmente a tu servicio médico a través del seguro facultativo.
                    </div>
                    
                    <p>El reporte de prueba incluye:</p>
                    <ul>
                        <li>✓ Indicadores de Salud Física (IMC, ICC, ICE)</li>
                        <li>✓ Perfil Sanguíneo y Tensión Arterial</li>
                        <li>✓ Perfil de Estilo de Vida</li>
                        <li>✓ Evaluación DASS (Ansiedad, Estrés, Depresión)</li>
                    </ul>
                    
                    <p>Agradecemos tu participación en esta etapa de pruebas.</p>
                    
                    <p>Atentamente,<br><strong>Equipo de Desarrollo - Salud UNACAR</strong></p>
                </div>
                <div class="footer">
                    <p>📧 Este correo fue generado automáticamente por el sistema de pruebas.</p>
                    <p>&copy; ' . date('Y') . ' UNACAR - Universidad Autónoma del Carmen</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Estimado(a) estudiante,\n\n"
            . "*** AVISO DE PRUEBA PILOTO ***\n\n"
            . "IMPORTANTE: Este reporte contiene DATOS SIMULADOS O FICTICIOS generados con fines de prueba técnica. No deben ser considerados como resultados clínicos reales.\n\n"
            . "Tu Reporte de Salud Integral (Simulado) ha sido generado el " . date('d/m/Y') . ".\n\n"
            . "Encontrarás adjunto tu reporte completo en formato PDF.\n\n"
            . "Recuerda que para un diagnóstico real debes acudir a tu servicio médico.\n\n"
            . "Saludos,\nEquipo de Desarrollo - Salud UNACAR";

        error_log("Contenido del correo configurado");
        error_log("Intentando enviar correo...");

        if ($mail->send()) {
            $correoEnviado = true;
            error_log("✅ Correo enviado exitosamente a: $destinatario_email");
        } else {
            throw new Exception("El método send() retornó false");
        }

        ob_end_clean();

    } catch (Exception $e) {
        ob_end_clean();
        $errorCorreo = $e->getMessage();
        error_log("❌ ERROR al enviar correo: " . $errorCorreo);
        error_log("Detalles PHPMailer: " . ($mail->ErrorInfo ?? 'N/A'));
    }

    $conn->close();

    
    $urlDescarga = $protocol . "://" . $host . "/reportes_salud/" . $matricula_sanitizada . "/" . $nombreArchivo;

    
    if ($solo_generar_pdf) {
        if ($correoEnviado) {
            $mensaje = 'Reporte generado y enviado por correo exitosamente.';
        } else {
            $mensaje = 'Reporte generado exitosamente. ' . ($errorCorreo ? 'Error al enviar correo: ' . $errorCorreo : 'PDF disponible para descarga.');
        }
        error_log("=== FIN GENERACIÓN PDF ===");
    } else {
        if ($correoEnviado) {
            $mensaje = 'Datos guardados y reporte enviado por correo exitosamente.';
        } else {
            $mensaje = 'Datos guardados exitosamente. ' . ($errorCorreo ? 'Error al enviar correo: ' . $errorCorreo : 'PDF disponible para descarga.');
        }
        error_log("=== FIN GUARDADO ===");
    }

    enviarJSON([
        'success' => true,
        'mensaje' => $mensaje,
        'pdf_url' => $urlDescarga,
        'pdf_nombre' => $nombreArchivo,
        'correo_enviado' => $correoEnviado,
        'error_correo' => $errorCorreo
    ]);

} catch (Exception $e) {
    error_log("ERROR GENERAL: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    if (isset($conn)) {
        $conn->close();
    }
    enviarJSON(['error' => $e->getMessage()], 500);
}
?>