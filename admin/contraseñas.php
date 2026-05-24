<?php
// 1. AUMENTAR EL TIEMPO DE EJECUCIÓN
// Permitir que el script corra por 300 segundos (5 minutos) en lugar de 30.
set_time_limit(300);

$servername = "pdb1042.awardspace.net";
$username = "4528622_pisi";
$password = "sklike5522";
$database = "4528622_pisi";
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$database;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "Conexión exitosa. Reanudando actualización...<br>";

    // 2. SELECCIONAR SOLO LOS ALUMNOS PENDIENTES
    // Buscamos alumnos donde el password sea NULL, esté vacío, 
    // o NO empiece con '$2y$' (que es la firma de Bcrypt).
    $sql = "SELECT matricula_alum, fe_nacimiento_alum 
            FROM alumnos 
            WHERE password IS NULL 
               OR password = '' 
               OR password NOT LIKE '$2y$%'";
               
    $stmt = $pdo->query($sql);

    // Contar cuántos faltan
    $pendientes = $stmt->rowCount();
    echo "Se encontraron $pendientes alumnos pendientes de actualizar.<br>";

    $contador = 0;

    // 3. RECORRER Y ACTUALIZAR
    while ($row = $stmt->fetch()) {
        $matricula = $row['matricula_alum'];
        $fechaNac  = $row['fe_nacimiento_alum'];

        // Validar que la fecha no venga vacía para evitar errores
        if (empty($fechaNac)) {
            echo "Saltando matrícula $matricula: Sin fecha de nacimiento.<br>";
            continue;
        }

        // A. Crear la contraseña plana
        // Aseguramos tomar solo los primeros 10 caracteres de la fecha (AAAA-MM-DD)
        $passwordPlana = "Salud" . substr($fechaNac, 0, 10); 

        // B. Encriptar
        $passwordHash = password_hash($passwordPlana, PASSWORD_BCRYPT);

        // C. Actualizar
        $updateSql = "UPDATE alumnos SET password = :pass WHERE matricula_alum = :mat";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute(['pass' => $passwordHash, 'mat' => $matricula]);

        $contador++;
        
        // Opcional: Imprimir un punto cada 50 registros para ver que avanza
        if ($contador % 50 == 0) {
            echo ". ";
            flush(); // Forzar al navegador a mostrar el avance
        }
    }

    echo "<br>¡Proceso terminado! Se actualizaron $contador alumnos en esta tanda.";

} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>