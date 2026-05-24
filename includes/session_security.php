<?php
date_default_timezone_set('America/Mexico_City');

/**
 * Genera un token único de sesión
 */
function generarTokenSesion() {
    return bin2hex(random_bytes(32));
}

/**
 * Valida si la sesión actual es la única activa
 * Retorna true si es válida, false si fue invalidada por otra sesión
 */
function validarSesionUnica($conn) {
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['session_token'])) {
        return false;
    }

    $usuario = $_SESSION['usuario'];
    $tokenActual = $_SESSION['session_token'];

    // Verificar token en la base de datos
    $stmt = $conn->prepare("SELECT session_token FROM administradores WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $tokenDB = $row['session_token'];
        $stmt->close();
        
        // Si los tokens no coinciden, otra sesión se inició
        if ($tokenActual !== $tokenDB) {
            return false;
        }
        return true;
    }
    
    $stmt->close();
    return false;
}

/**
 * Cierra la sesión actual y limpia los registros
 */
function cerrarSesionCompleta($conn, $usuario) {
    // Cerrar registro en BD
    $sql = "UPDATE registro_ingresos SET fecha_salida = NOW() WHERE usuario = ? AND fecha_salida IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->close();
    
    // Limpiar token de sesión
    $sql = "UPDATE administradores SET session_token = NULL, session_timestamp = NULL WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->close();
    
    // Destruir sesión PHP
    session_unset();
    session_destroy();
}

/**
 * Verifica timeout de inactividad (30 minutos por defecto)
 */
function verificarTimeout($tiempo_limite = 1800) {
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
        
        if ($tiempo_transcurrido > $tiempo_limite) {
            return true; // Sesión expirada
        }
    }
    
    // Actualizar último acceso
    $_SESSION['ultimo_acceso'] = time();
    return false;
}
?>