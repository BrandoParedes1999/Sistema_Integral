<?php
// Evitar que se muestre cualquier error HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Limpiar cualquier salida previa
ob_start();

$matricula = isset($_GET['matricula_alum']) ? $_GET['matricula_alum'] : '';

// Limpiar buffer antes de enviar JSON
ob_end_clean();
header("Content-Type: application/json; charset=UTF-8");

if (!$matricula) {
    http_response_code(400);
    echo json_encode(["error" => "El parametro 'matricula_alum' es requerido."]);
    exit;
}

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";

try {
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        throw new Exception("Error de conexion a la base de datos: " . $conn->connect_error);
    }

    // --- CONSULTA ---
    $sql = "SELECT
        a.nombres_alum, a.ape_paterno_alum, a.ape_materno_alum, a.matricula_alum, a.edad_alum, a.correo_alum,
        f.nombre_facultad,
        c.nombre_carrera,
        df.fecha, df.imc, df.peso, df.talla, df.clasificacion_imc, df.icc, df.clasificacion_de_icc, df.masa_magra, df.ice, df.porcentaje_masa_grasa,
        df.clasificacion_porcentaje_grasa, df.get1, df.porcentaje_agua_total, df.agua_total, df.glucosa, df.clasificacion_glucosa, df.colesterol,
        df.clasificacion_colesterol, df.trigliceridos, df.clasificacion_trigliceridos,
        df.tension_arterial, df.clasificacion_tension_arterial,
        ev.total AS total_estilo_vida, ev.estado_saludable AS estado_estilo_vida,
        nut.total_nutricion, nut.saludable AS saludable_nutricion,
        eje.total_ejercicio, eje.saludable_ejercicio,
        sal.total_salud, sal.saludable_salud,
        sop.total_soporte, sop.saludable_soporte,
        mes.total_manejoestres, mes.saludable_manejo,
        aut.total_autoactualizacion, aut.saludable_autoactualizacion,
        dass.total_depresion, dass.total_ansiedad, dass.total_estres, dass.total_general,
        da.severidad AS severidad_ansiedad,
        dd.severidad AS severidad_depresion,
        de.severidad AS severidad_estres
    FROM datos_fisicos_alumnos df
    LEFT JOIN alumnos a ON df.matricula_alum = a.matricula_alum
    LEFT JOIN carrera c ON a.id_carrera = c.id_carrera
    LEFT JOIN facultad f ON a.id_facultad = f.id_facultad
    LEFT JOIN estilo_de_vida ev ON df.matricula_alum = ev.matricula_alum
    LEFT JOIN nutricion nut ON ev.id_cuestionario = nut.id_cuestionario
    LEFT JOIN ejercicio eje ON ev.id_cuestionario = eje.id_cuestionario
    LEFT JOIN salud sal ON ev.id_cuestionario = sal.id_cuestionario
    LEFT JOIN soporte_interpersonal sop ON ev.id_cuestionario = sop.id_cuestionario
    LEFT JOIN manejo_de_estres mes ON ev.id_cuestionario = mes.id_cuestionario
    LEFT JOIN autoactualizacion aut ON ev.id_cuestionario = aut.id_cuestionario
    LEFT JOIN dass ON df.matricula_alum = dass.matricula_alum
    LEFT JOIN dass_ansiedad da ON dass.id_cuestionario = da.id_cuestionario
    LEFT JOIN dass_depresion dd ON dass.id_cuestionario = dd.id_cuestionario
    LEFT JOIN dass_estres de ON dass.id_cuestionario = de.id_cuestionario
    WHERE df.matricula_alum = ?
    ORDER BY df.fecha DESC
    LIMIT 1;";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $matricula);  
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $datos = $result->fetch_assoc();
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "No se encontraron datos consolidados para la matricula proporcionada."]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    error_log("Error en resultadospdf.php: " . $e->getMessage());
}
?>