<?php
 $conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

if (isset($_GET['matricula_alum'])) {
    $matricula = $_GET['matricula_alum'];

    $query = "SELECT * FROM historial_alumnos WHERE matricula_alum = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(new stdClass()); // objeto vacío en lugar de mensaje de error
}


    $stmt->close();
    $conn->close();
} else {
    echo json_encode(new stdClass());
}
?>