<?php
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Conexión a base de datos
$conn = new mysqli("pdb1042.awardspace.net", "4528622_pisi", "sklike5522", "4528622_pisi");

if ($conn->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit();
}

// Establecer charset
$conn->set_charset("utf8");

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion === 'cerrar_todas') {
    // Cerrar todas las sesiones activas excepto la del administrador actual
    $usuarioActual = $conn->real_escape_string($_SESSION['usuario']);
    $fechaActual = date('Y-m-d H:i:s');
    
    // Primero, contar cuántas sesiones se cerrarán
    $sqlCount = "SELECT COUNT(*) as total FROM registro_ingresos 
                 WHERE fecha_salida IS NULL AND usuario != '$usuarioActual'";
    $resultCount = $conn->query($sqlCount);
    $countRow = $resultCount->fetch_assoc();
    $total = $countRow['total'];
    
    if ($total == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'No hay sesiones activas para cerrar'
        ]);
        $conn->close();
        exit();
    }
    
    // Actualizar las sesiones
    $sql = "UPDATE registro_ingresos 
            SET fecha_salida = '$fechaActual' 
            WHERE fecha_salida IS NULL 
            AND usuario != '$usuarioActual'";
    
    if ($conn->query($sql)) {
        $sesiones_cerradas = $conn->affected_rows;
        
        echo json_encode([
            'success' => true, 
            'message' => "Se cerraron $sesiones_cerradas sesión(es) activa(s)",
            'sesiones_cerradas' => $sesiones_cerradas
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al cerrar sesiones: ' . $conn->error
        ]);
    }
    
} elseif ($accion === 'cerrar_una') {
    // Cerrar una sesión específica
    $id_registro = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id_registro == 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'ID de registro inválido'
        ]);
        $conn->close();
        exit();
    }
    
    $fechaActual = date('Y-m-d H:i:s');
    
    // Verificar que la sesión no sea la del usuario actual
    $sqlCheck = "SELECT usuario FROM registro_ingresos WHERE id = $id_registro";
    $resultCheck = $conn->query($sqlCheck);
    
    if ($resultCheck && $resultCheck->num_rows > 0) {
        $rowCheck = $resultCheck->fetch_assoc();
        if ($rowCheck['usuario'] == $_SESSION['usuario']) {
            echo json_encode([
                'success' => false, 
                'message' => 'No puedes cerrar tu propia sesión'
            ]);
            $conn->close();
            exit();
        }
    }
    
    $sql = "UPDATE registro_ingresos 
            SET fecha_salida = '$fechaActual' 
            WHERE id = $id_registro 
            AND fecha_salida IS NULL";
    
    if ($conn->query($sql)) {
        if ($conn->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Sesión cerrada correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'La sesión ya estaba cerrada o no existe'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al cerrar la sesión: ' . $conn->error
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Acción no válida. Acción recibida: ' . $accion
    ]);
}

$conn->close();
?>