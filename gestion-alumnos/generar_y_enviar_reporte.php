<?php
require_once '../config/config.php';
// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
set_time_limit(120);
ini_set('memory_limit', '256M');

// Función para enviar JSON
function enviarJSON($data, $statusCode = 200) {
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($data);
    exit;
}

// Limpiar buffer
ob_start();
ob_end_clean();
header('Content-Type: application/json; charset=UTF-8');

// Validar método
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    enviarJSON(['error' => 'Método no permitido'], 405);
}

// Validar datos requeridos
if (empty($_POST['matricula'])) {
    enviarJSON(['error' => 'Falta la matrícula'], 400);
}

$matricula = $_POST['matricula'];
$correo_destino = $_POST['correo1'] ?? '';


try {
    error_log("=== INICIO GENERACIÓN DE REPORTE - Matrícula: $matricula ===");

$conn = getDBConnection();
    if ($conn->connect_error) {
        throw new Exception('Conexión fallida: ' . $conn->connect_error);
    }

    // PASO 1: Obtener datos consolidados
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

    // 🔥 LOG DE DEBUG: Ver qué datos tenemos
    error_log("=== DATOS RECIBIDOS DE LA API ===");
    error_log("total_depresion: " . ($datos_pdf['total_depresion'] ?? 'NO EXISTE'));
    error_log("total_ansiedad: " . ($datos_pdf['total_ansiedad'] ?? 'NO EXISTE'));
    error_log("total_estres: " . ($datos_pdf['total_estres'] ?? 'NO EXISTE'));
    error_log("puntuacion_depresion: " . ($datos_pdf['puntuacion_depresion'] ?? 'NO EXISTE'));
    error_log("puntuacion_ansiedad: " . ($datos_pdf['puntuacion_ansiedad'] ?? 'NO EXISTE'));
    error_log("puntuacion_estres: " . ($datos_pdf['puntuacion_estres'] ?? 'NO EXISTE'));
    error_log("total_estilo_vida: " . ($datos_pdf['total_estilo_vida'] ?? 'NO EXISTE'));
    error_log("total_nutricion: " . ($datos_pdf['total_nutricion'] ?? 'NO EXISTE'));

    // PASO 2: Validar que todos los datos estén completos
    $datos_faltantes = [];

    // 🔥 VALIDACIÓN MEJORADA DE DASS
    // Verificar si existe al menos UNA puntuación de DASS
    $tiene_dass = false;
    
    // Primero intentar con los campos de puntuación
    if (isset($datos_pdf['puntuacion_depresion']) && $datos_pdf['puntuacion_depresion'] !== null && $datos_pdf['puntuacion_depresion'] !== '') {
        $tiene_dass = true;
    }
    if (isset($datos_pdf['puntuacion_ansiedad']) && $datos_pdf['puntuacion_ansiedad'] !== null && $datos_pdf['puntuacion_ansiedad'] !== '') {
        $tiene_dass = true;
    }
    if (isset($datos_pdf['puntuacion_estres']) && $datos_pdf['puntuacion_estres'] !== null && $datos_pdf['puntuacion_estres'] !== '') {
        $tiene_dass = true;
    }
    
    // Si no hay puntuaciones, intentar con totales
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
        error_log("DASS no encontrado - ningún campo válido");
        $datos_faltantes[] = 'DASS-21 (Depresión, Ansiedad y Estrés)';
    } else {
        error_log("DASS encontrado - validación OK");
    }

    // 🔥 VALIDACIÓN MEJORADA DE ESTILO DE VIDA
    $tiene_estilo_vida = false;
    
    // Verificar si existe el total
    if (isset($datos_pdf['total_estilo_vida']) && $datos_pdf['total_estilo_vida'] !== null && $datos_pdf['total_estilo_vida'] !== '') {
        $tiene_estilo_vida = true;
    }
    
    // O verificar si existen las subcategorías
    if (!$tiene_estilo_vida) {
        if ((isset($datos_pdf['total_nutricion']) && $datos_pdf['total_nutricion'] !== null && $datos_pdf['total_nutricion'] !== '') ||
            (isset($datos_pdf['total_ejercicio']) && $datos_pdf['total_ejercicio'] !== null && $datos_pdf['total_ejercicio'] !== '') ||
            (isset($datos_pdf['total_salud']) && $datos_pdf['total_salud'] !== null && $datos_pdf['total_salud'] !== '')) {
            $tiene_estilo_vida = true;
        }
    }
    
    if (!$tiene_estilo_vida) {
        error_log("Estilo de Vida no encontrado");
        $datos_faltantes[] = 'Estilo de Vida';
    } else {
        error_log("Estilo de Vida encontrado - validación OK");
    }

    // 🔥 VALIDACIÓN DE DATOS FÍSICOS
    $tiene_datos_fisicos = false;
    if ((isset($datos_pdf['peso']) && $datos_pdf['peso'] !== null && $datos_pdf['peso'] !== '') ||
        (isset($datos_pdf['talla']) && $datos_pdf['talla'] !== null && $datos_pdf['talla'] !== '') ||
        (isset($datos_pdf['imc']) && $datos_pdf['imc'] !== null && $datos_pdf['imc'] !== '')) {
        $tiene_datos_fisicos = true;
    }
    
    if (!$tiene_datos_fisicos) {
        error_log("Datos Físicos no encontrados");
        $datos_faltantes[] = 'Datos Físicos';
    } else {
        error_log("Datos Físicos encontrados - validación OK");
    }

    // Si faltan datos, retornar advertencia
    if (!empty($datos_faltantes)) {
        error_log("=== DATOS INCOMPLETOS ===");
        error_log("Faltan: " . implode(", ", $datos_faltantes));
        $conn->close();
        
        enviarJSON([
            'warning' => true,
            'mensaje' => 'El alumno aún no ha completado los siguientes cuestionarios',
            'cuestionarios_faltantes' => $datos_faltantes
        ], 200);
    }

    error_log("=== TODOS LOS DATOS COMPLETOS - PROCEDIENDO A GENERAR PDF ===");

    // PASO 3: Si llegamos aquí, generar el PDF
    // Obtener el correo si no fue enviado
    if (empty($correo_destino) && !empty($datos_pdf['correo_alum'])) {
        $correo_destino = $datos_pdf['correo_alum'];
    }

    if (empty($correo_destino)) {
        throw new Exception('No se encontró el correo del alumno');
    }

    error_log("Generando PDF para enviar a: $correo_destino");

    // Llamar al generador con todos los parámetros necesarios
    $url_generar = $protocol . "://" . $host . "/guardar_datos_fisicos_alumnos.php";
    
    // Preparar datos para enviar - INCLUYENDO el correo
    $post_data = http_build_query([
        'matricula' => $matricula,
        'correo1' => $correo_destino,
        'solo_generar_pdf' => 'true',
        // Agregar campos dummy para evitar errores de validación
        'peso1' => '0',
        'talla1' => '0',
        'imc1' => '0',
        'glucosa1' => '0',
        'colesterol1' => '0',
        'trigliceridos1' => '0',
        'tensionarterial1' => '0',
        'cintura1' => '0',
        'cadera1' => '0',
        'icc1' => '0',
        'ice' => '0',
        'mb1' => '0',
        'actividad1' => '0',
        'get1' => '0',
        'pormasagrasa1' => '0',
        'valoridealgrasa1' => '0',
        'masamagra1' => '0',
        'aguatotal1' => '0',
        'porcentajeaguatotal1' => '0',
        'clasificacioncadcin1' => '',
        'clasificacionicc1' => '',
        'clasificacionimc1' => '',
        'clasificaciongrasa1' => '',
        'clasificacionglucosa1' => '',
        'clasificaciontrigliceridos1' => '',
        'clasificacioncolesterol1' => '',
        'clasificacionta1' => ''
    ]);

    // Hacer la petición con cURL
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $url_generar);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
    
    error_log("Enviando petición a: $url_generar");
    error_log("Con correo: $correo_destino");
    
    $response = curl_exec($ch2);
    $curl_error2 = curl_error($ch2);
    $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    if ($response === false) {
        throw new Exception('Error al generar PDF: ' . $curl_error2);
    }

    error_log("Respuesta HTTP: $http_code2");
    error_log("Respuesta del servidor (primeros 500 chars): " . substr($response, 0, 500));

    $resultado = json_decode($response, true);
    
    if (!$resultado) {
        error_log("ERROR: Respuesta no es JSON válido");
        throw new Exception('Respuesta inválida del generador de PDF: ' . substr($response, 0, 200));
    }

    // Verificar si hay advertencia de datos incompletos
    if (isset($resultado['warning'])) {
        $conn->close();
        enviarJSON($resultado);
    }

    $conn->close();

    // Retornar el resultado
    if (isset($resultado['success']) || isset($resultado['pdf_url'])) {
        error_log("=== PDF GENERADO EXITOSAMENTE ===");
        error_log("Correo enviado: " . ($resultado['correo_enviado'] ? 'SÍ' : 'NO'));
        if (!$resultado['correo_enviado'] && isset($resultado['error_correo'])) {
            error_log("Error correo: " . $resultado['error_correo']);
        }
        
        enviarJSON([
            'success' => true,
            'mensaje' => 'Reporte generado y enviado exitosamente',
            'pdf_url' => $resultado['pdf_url'] ?? null,
            'pdf_nombre' => $resultado['pdf_nombre'] ?? null,
            'correo_enviado' => $resultado['correo_enviado'] ?? false,
            'error_correo' => $resultado['error_correo'] ?? ''
        ]);
    } else {
        throw new Exception($resultado['error'] ?? 'Error desconocido al generar PDF');
    }

} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    error_log("Stack: " . $e->getTraceAsString());
    if (isset($conn)) {
        $conn->close();
    }
    enviarJSON(['error' => $e->getMessage()], 500);
}
?>