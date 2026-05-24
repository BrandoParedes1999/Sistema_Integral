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

// --- SEVERIDADES (CLASIFICACIONES) DE TRIGLICERIDOS ---
elseif ($tipo == "severidad") {
    $sql = "SELECT DISTINCT clasificacion_trigliceridos 
            FROM datos_fisicos_alumnos 
            WHERE clasificacion_trigliceridos IS NOT NULL AND clasificacion_trigliceridos != ''
            ORDER BY 
                CASE clasificacion_trigliceridos
                    WHEN 'Sin resultados' THEN 1
                    WHEN 'Bajo' THEN 2
                    WHEN 'Normal' THEN 3
                    WHEN 'Límite Alto' THEN 4
                    WHEN 'Alto' THEN 5
                    WHEN 'Muy Alto' THEN 6
                END";
}

// --- DATOS DE TRIGLICERIDOS CON FILTROS (OPTIMIZADO) ---
elseif ($tipo == "trigliceridos_datos") {
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
    
    if (!empty($_GET["severidad"])) {
        $where[] = "df.clasificacion_trigliceridos = ?";
        $params[] = $_GET["severidad"];
        $types .= "s";
    }
    
    $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
    
    // OPTIMIZACIÓN: Query simplificada
    $sql = "SELECT 
                df.clasificacion_trigliceridos,
                COUNT(*) as cantidad
            FROM datos_fisicos_alumnos df
            INNER JOIN alumnos a ON df.matricula_alum = a.matricula_alum
            $whereClause
            GROUP BY df.clasificacion_trigliceridos
            ORDER BY 
                CASE df.clasificacion_trigliceridos
                    WHEN 'Sin resultados' THEN 1
                    WHEN 'Bajo' THEN 2
                    WHEN 'Normal' THEN 3
                    WHEN 'Límite Alto' THEN 4
                    WHEN 'Alto' THEN 5
                    WHEN 'Muy Alto' THEN 6
                END";
    
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

// --- COMPARACIÓN ENTRE FACULTADES (TRIGLICERIDOS) ---
elseif ($tipo == "comparar_facultades_trigliceridos") {
    $where = [];
    $params = [];
    $types = "";
    
    if (!empty($_GET["sexo"])) {
        $where[] = "a.sexo = ?";
        $params[] = $_GET["sexo"];
        $types .= "s";
    }
    
    if (!empty($_GET["severidad"])) {
        $where[] = "df.clasificacion_trigliceridos = ?";
        $params[] = $_GET["severidad"];
        $types .= "s";
    }
    
    $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT 
                f.nombre_facultad,
                df.clasificacion_trigliceridos,
                COUNT(*) as cantidad
            FROM datos_fisicos_alumnos df
            INNER JOIN alumnos a ON df.matricula_alum = a.matricula_alum
            INNER JOIN facultad f ON a.id_facultad = f.id_facultad
            $whereClause
            GROUP BY f.nombre_facultad, df.clasificacion_trigliceridos
            ORDER BY f.nombre_facultad, 
                CASE df.clasificacion_trigliceridos
                    WHEN 'Sin resultados' THEN 1
                    WHEN 'Bajo' THEN 2
                    WHEN 'Normal' THEN 3
                    WHEN 'Límite Alto' THEN 4
                    WHEN 'Alto' THEN 5
                    WHEN 'Muy Alto' THEN 6
                END";
    
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