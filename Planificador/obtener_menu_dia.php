<?php
header("Content-Type: application/json; charset=utf-8");

require_once '../config/config.php';

try {
    $database = new Database();
    $conexion = $database->connect();

    $idMenuAlumno = isset($_GET['id_menu_alumno']) ? intval($_GET['id_menu_alumno']) : 0;
    $idDia = isset($_GET['id_dia']) ? intval($_GET['id_dia']) : 0;

    if ($idMenuAlumno <= 0 || $idDia <= 0) {
        throw new Exception("Datos incompletos.");
    }

    $sql = "
        SELECT
            md.id_detalle,
            tc.nombre_tiempo,
            p.id_platillo,
            p.nombre_platillo,
            p.calorias,
            p.descripcion,
            p.preparacion,
            p.imagen,
            md.id_tiempo
        FROM pm_menu_detalle md
        INNER JOIN pm_tiempos_comida tc
            ON md.id_tiempo = tc.id_tiempo
        INNER JOIN pm_platillos p
            ON md.id_platillo = p.id_platillo
        WHERE md.id_menu_alumno = ?
        AND md.id_dia = ?
        ORDER BY tc.orden ASC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $idMenuAlumno, $idDia);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $menu = [];
    $totalCalorias = 0;

    $sqlIngredientes = "
        SELECT
            i.nombre_ingrediente,
            pi.cantidad,
            ga.nombre_grupo,
            ga.color,
            ga.icono
        FROM pm_platillo_ingredientes pi
        INNER JOIN pm_ingredientes i
            ON pi.id_ingrediente = i.id_ingrediente
        LEFT JOIN pm_grupos_alimento ga
            ON i.id_grupo_alimento = ga.id_grupo_alimento
        WHERE pi.id_platillo = ?
        ORDER BY ga.nombre_grupo ASC, i.nombre_ingrediente ASC
    ";

    $stmtIngredientes = $conexion->prepare($sqlIngredientes);

    while ($fila = $resultado->fetch_assoc()) {
        $idPlatillo = intval($fila['id_platillo']);
        $totalCalorias += intval($fila['calorias']);

        $stmtIngredientes->bind_param("i", $idPlatillo);
        $stmtIngredientes->execute();
        $resIngredientes = $stmtIngredientes->get_result();

        $ingredientes = [];

        while ($ingrediente = $resIngredientes->fetch_assoc()) {
            $ingredientes[] = $ingrediente;
        }

        $fila['ingredientes'] = $ingredientes;
        $menu[] = $fila;
    }

    echo json_encode([
        "success" => true,
        "menu" => $menu,
        "total_calorias" => $totalCalorias
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
