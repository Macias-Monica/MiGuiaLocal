<?php
$serverName = "miguialocal.database.windows.net"; 
$dbname = "miGuiaLocal";
$username = "userMGL";
$password = "pwsMGL00";
//$password = "pwsMGL";
//en azure la pass es pwsMGL00
try {
    // Crear conexión usando PDO
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$dbname", $username, $password);
    // Establecer el modo de error de PDO para excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
