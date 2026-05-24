<?php
// dass.php - API para el Dashboard DASS-21

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de la base de datos
$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

$conn->set_charset("utf8");

// Router simple basado en el parámetro 'endpoint'
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

switch ($endpoint) {
    case 'facultades':
        getFacultades($conn);
        break;
    case 'carreras':
        getCarreras($conn);
        break;
    case 'years-nacimiento':
        getYearsNacimiento($conn);
        break;
    case 'dass-stats':
        getDassStats($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint no encontrado. Usa: ?endpoint=facultades|carreras|years-nacimiento|dass-stats']);
        break;
}

$conn->close();

// ============================================
// ENDPOINT: Obtener Facultades
// ============================================
function getFacultades($conn) {
    $sql = "SELECT id_facultad, nombre_facultad FROM facultad ORDER BY nombre_facultad";
    $result = $conn->query($sql);
    
    if ($result) {
        $facultades = [];
        while ($row = $result->fetch_assoc()) {
            $facultades[] = $row;
        }
        echo json_encode($facultades);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener facultades: ' . $conn->error]);
    }
}

// ============================================
// ENDPOINT: Obtener Carreras por Facultad
// ============================================
function getCarreras($conn) {
    $id_facultad = isset($_GET['id_facultad']) && $_GET['id_facultad'] !== '' ? intval($_GET['id_facultad']) : null;
    
    if ($id_facultad) {
        $sql = "SELECT id_carrera, nombre_carrera, id_facultad 
                FROM carrera 
                WHERE id_facultad = ? 
                ORDER BY nombre_carrera";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_facultad);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT id_carrera, nombre_carrera, id_facultad 
                FROM carrera 
                ORDER BY nombre_carrera";
        $result = $conn->query($sql);
    }
    
    if ($result) {
        $carreras = [];
        while ($row = $result->fetch_assoc()) {
            $carreras[] = $row;
        }
        echo json_encode($carreras);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener carreras: ' . $conn->error]);
    }
}

// ============================================
// ENDPOINT: Obtener Años de Nacimiento Únicos
// ============================================
function getYearsNacimiento($conn) {
    $sql = "SELECT DISTINCT YEAR(fe_nacimiento_alum) as fe_nacimiento_alum 
            FROM alumnos 
            WHERE fe_nacimiento_alum IS NOT NULL 
            ORDER BY fe_nacimiento_alum DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        $years = [];
        while ($row = $result->fetch_assoc()) {
            $years[] = $row;
        }
        echo json_encode($years);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener años de nacimiento: ' . $conn->error]);
    }
}

// ============================================
// ENDPOINT: Obtener Estadísticas DASS
// ============================================
function getDassStats($conn) {
    $view = isset($_GET['view']) ? $_GET['view'] : 'ansiedad';
    $facultad = isset($_GET['facultad']) && $_GET['facultad'] !== '' ? intval($_GET['facultad']) : null;
    $carrera = isset($_GET['carrera']) && $_GET['carrera'] !== '' ? intval($_GET['carrera']) : null;
    $sexo = isset($_GET['sexo']) && $_GET['sexo'] !== '' ? $_GET['sexo'] : null;
    $nacimiento = isset($_GET['nacimiento']) && $_GET['nacimiento'] !== '' ? intval($_GET['nacimiento']) : null;
    $severidad = isset($_GET['severidad']) && $_GET['severidad'] !== '' ? $_GET['severidad'] : null;
    
    // Seleccionar tabla según la vista
    $tabla = '';
    switch ($view) {
        case 'ansiedad':
            $tabla = 'dass_ansiedad';
            break;
        case 'depresion':
            $tabla = 'dass_depresion';
            break;
        case 'estres':
            $tabla = 'dass_estres';
            break;
        default:
            $tabla = 'dass_ansiedad';
    }
    
    // Construir query base para obtener conteos por severidad
    $sql = "SELECT d.severidad, COUNT(*) as conteo
            FROM {$tabla} d
            INNER JOIN dass da ON d.id_cuestionario = da.id_cuestionario
            INNER JOIN alumnos a ON da.matricula_alum = a.matricula_alum
            WHERE 1=1";
    
    $types = "";
    $params = [];
    
    // Aplicar filtros
    if ($facultad !== null) {
        $sql .= " AND a.id_facultad = ?";
        $types .= "i";
        $params[] = $facultad;
    }
    
    if ($carrera !== null) {
        $sql .= " AND a.id_carrera = ?";
        $types .= "i";
        $params[] = $carrera;
    }
    
    if ($sexo !== null) {
        $sql .= " AND a.sexo = ?";
        $types .= "s";
        $params[] = $sexo;
    }
    
    if ($nacimiento !== null) {
        $sql .= " AND YEAR(a.fe_nacimiento_alum) = ?";
        $types .= "i";
        $params[] = $nacimiento;
    }
    
    if ($severidad !== null) {
        $sql .= " AND d.severidad = ?";
        $types .= "s";
        $params[] = $severidad;
    }
    
    $sql .= " GROUP BY d.severidad";
    
    // Ejecutar query
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al preparar consulta: ' . $conn->error]);
            return;
        }
    } else {
        $result = $conn->query($sql);
    }
    
    // Obtener total general
    $sqlTotal = "SELECT COUNT(*) as total
                 FROM {$tabla} d
                 INNER JOIN dass da ON d.id_cuestionario = da.id_cuestionario
                 INNER JOIN alumnos a ON da.matricula_alum = a.matricula_alum
                 WHERE 1=1";
    
    // Aplicar los mismos filtros para el total
    if ($facultad !== null) {
        $sqlTotal .= " AND a.id_facultad = " . intval($facultad);
    }
    
    if ($carrera !== null) {
        $sqlTotal .= " AND a.id_carrera = " . intval($carrera);
    }
    
    if ($sexo !== null) {
        $sqlTotal .= " AND a.sexo = '" . $conn->real_escape_string($sexo) . "'";
    }
    
    if ($nacimiento !== null) {
        $sqlTotal .= " AND YEAR(a.fe_nacimiento_alum) = " . intval($nacimiento);
    }
    
    if ($severidad !== null) {
        $sqlTotal .= " AND d.severidad = '" . $conn->real_escape_string($severidad) . "'";
    }
    
    $resultTotal = $conn->query($sqlTotal);
    $totalGeneral = 0;
    
    if ($resultTotal) {
        $rowTotal = $resultTotal->fetch_assoc();
        $totalGeneral = $rowTotal['total'];
    }
    
    // Formatear respuesta con todos los niveles de severidad
    $conteos = [
        'Normal' => 0,
        'Leve' => 0,
        'Moderado' => 0,
        'Severo' => 0,
        'Extremadamente Severo' => 0
    ];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sev = $row['severidad'];
            if (isset($conteos[$sev])) {
                $conteos[$sev] = (int)$row['conteo'];
            }
        }
    }
    
    $response = [
        'total' => (int)$totalGeneral,
        'conteos' => $conteos,
        'view' => $view
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>