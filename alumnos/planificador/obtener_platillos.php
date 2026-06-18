<?php
header("Content-Type: application/json; charset=utf-8");

require_once '../../config/config.php';

try {
    $database = new Database();
    $conexion = $database->connect();

    $idTiempo = isset($_GET['id_tiempo']) ? intval($_GET['id_tiempo']) : 0;

    if ($idTiempo <= 0) {
        throw new Exception("Tiempo de comida no válido.");
    }

    $sql = "
        SELECT
            p.id_platillo,
            p.nombre_platillo,
            p.descripcion,
            p.preparacion,
            p.calorias,
            p.imagen
        FROM pm_platillos p
        WHERE p.id_tiempo = ?
        AND p.activo = 1
        ORDER BY p.calorias ASC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idTiempo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $platillos = [];

    while ($platillo = $resultado->fetch_assoc()) {
        $idPlatillo = $platillo['id_platillo'];

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
        ";

        $stmtIngredientes = $conexion->prepare($sqlIngredientes);
        $stmtIngredientes->bind_param("i", $idPlatillo);
        $stmtIngredientes->execute();
        $resIngredientes = $stmtIngredientes->get_result();

        $ingredientes = [];

        while ($ing = $resIngredientes->fetch_assoc()) {
            $ingredientes[] = $ing;
        }

        $platillo['ingredientes'] = $ingredientes;
        $platillos[] = $platillo;
    }

    echo json_encode([
        "success" => true,
        "platillos" => $platillos
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
