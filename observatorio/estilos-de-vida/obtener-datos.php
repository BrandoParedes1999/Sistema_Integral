<?php
header('Content-Type: application/json; charset=utf-8');

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

$conn->set_charset("utf8");

$tipo = $_GET["tipo"] ?? '';

// --- FACULTADES ---
if ($tipo == "facultad") {
    $sql = "SELECT id_facultad, nombre_facultad FROM facultad ORDER BY nombre_facultad ASC";
}

// --- CARRERAS POR FACULTAD ---
elseif ($tipo == "carrera" && isset($_GET["idfacultad"])) {
    $idfacultad = intval($_GET["idfacultad"]);
    $sql = "SELECT id_carrera, nombre_carrera FROM carrera WHERE id_facultad = $idfacultad ORDER BY nombre_carrera ASC";
}

// --- AÑOS DE NACIMIENTO DISPONIBLES ---
elseif ($tipo == "anios") {
    $sql = "SELECT DISTINCT YEAR(fe_nacimiento_alum) as anio 
            FROM alumnos 
            WHERE fe_nacimiento_alum IS NOT NULL 
            ORDER BY anio DESC";
}

// --- DATOS DE ESTILOS DE VIDA CON FILTROS ---
elseif ($tipo == "estilos_vida") {
    $where = [];
    $params = [];
    $types = "";
    
    if (!empty($_GET["facultad"])) {
        $where[] = "a.id_facultad = ?";
        $params[] = intval($_GET["facultad"]);
        $types .= "i";
    }
    
    if (!empty($_GET["carrera"])) {
        $where[] = "a.id_carrera = ?";
        $params[] = intval($_GET["carrera"]);
        $types .= "i";
    }
    
    if (!empty($_GET["sexo"])) {
        $where[] = "a.sexo = ?";
        $params[] = $_GET["sexo"];
        $types .= "s";
    }
    
    if (!empty($_GET["anio"])) {
        $where[] = "YEAR(a.fe_nacimiento_alum) = ?";
        $params[] = intval($_GET["anio"]);
        $types .= "i";
    }
    
    $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT 
                e.id_cuestionario,
                e.matricula_alum,
                e.fecha,
                e.total,
                e.estado_saludable,
                a.nombres_alum,
                a.ape_paterno_alum,
                a.ape_materno_alum,
                a.edad_alum,
                a.sexo,
                YEAR(a.fe_nacimiento_alum) as anio_nacimiento,
                c.nombre_carrera,
                f.nombre_facultad
            FROM estilo_de_vida e
            INNER JOIN alumnos a ON e.matricula_alum = a.matricula_alum
            INNER JOIN carrera c ON a.id_carrera = c.id_carrera
            INNER JOIN facultad f ON a.id_facultad = f.id_facultad
            $whereClause
            ORDER BY e.fecha DESC";
    
    if (count($params) > 0) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die(json_encode(["error" => "Error en prepare: " . $conn->error]));
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
}

// --- COMPARACIÓN ENTRE FACULTADES ---
elseif ($tipo == "comparar_facultades") {
    $where = [];
    $params = [];
    $types = "";
    
    if (!empty($_GET["sexo"])) {
        $where[] = "a.sexo = ?";
        $params[] = $_GET["sexo"];
        $types .= "s";
    }
    
    if (!empty($_GET["anio"])) {
        $where[] = "YEAR(a.fe_nacimiento_alum) = ?";
        $params[] = intval($_GET["anio"]);
        $types .= "i";
    }
    
    $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT 
                f.nombre_facultad,
                e.estado_saludable,
                COUNT(*) as cantidad
            FROM estilo_de_vida e
            INNER JOIN alumnos a ON e.matricula_alum = a.matricula_alum
            INNER JOIN facultad f ON a.id_facultad = f.id_facultad
            $whereClause
            GROUP BY f.nombre_facultad, e.estado_saludable
            ORDER BY f.nombre_facultad, e.estado_saludable";
    
    if (count($params) > 0) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die(json_encode(["error" => "Error en prepare: " . $conn->error]));
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
}

// --- RESPUESTA PREDETERMINADA ---
else {
    echo json_encode([]);
    $conn->close();
    exit;
}

// --- EJECUTAR QUERY Y RETORNAR DATOS ---
if (!isset($result)) {
    $result = $conn->query($sql);
}

if (!$result) {
    die(json_encode(["error" => $conn->error]));
}

$datos = [];
while ($fila = $result->fetch_assoc()) {
    $datos[] = $fila;
}

echo json_encode($datos, JSON_UNESCAPED_UNICODE);
$conn->close();
?>