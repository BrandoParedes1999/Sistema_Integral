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

// --- DATOS DE SALUD CON FILTROS (OPTIMIZADO) ---
elseif ($tipo == "salud_datos") {
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
    
    // OPTIMIZACIÓN 1: Solo seleccionar columnas necesarias para las gráficas
    // OPTIMIZACIÓN 2: Usar STRAIGHT_JOIN si MySQL elige mal el orden de joins
    $sql = "SELECT 
                s.saludable_salud,
                COUNT(*) as cantidad
            FROM salud s
            INNER JOIN estilo_de_vida e ON s.id_cuestionario = e.id_cuestionario
            INNER JOIN alumnos a ON e.matricula_alum = a.matricula_alum
            $whereClause
            GROUP BY s.saludable_salud
            ORDER BY s.saludable_salud";
    
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

// --- COMPARACIÓN ENTRE FACULTADES (OPTIMIZADO) ---
elseif ($tipo == "comparar_facultades_salud") {
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
    
    // OPTIMIZACIÓN: Solo traer lo necesario, evitar JOIN innecesario con carrera
    $sql = "SELECT 
                f.nombre_facultad,
                s.saludable_salud,
                COUNT(*) as cantidad
            FROM salud s
            INNER JOIN estilo_de_vida e ON s.id_cuestionario = e.id_cuestionario
            INNER JOIN alumnos a ON e.matricula_alum = a.matricula_alum
            INNER JOIN facultad f ON a.id_facultad = f.id_facultad
            $whereClause
            GROUP BY f.nombre_facultad, s.saludable_salud
            ORDER BY f.nombre_facultad, s.saludable_salud";
    
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