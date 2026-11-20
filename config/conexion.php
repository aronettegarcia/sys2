<?php
//Cadena de conexión a la base de datos
$dns = 'mysql:host=localhost;dbname=bd_practica;charset=utf8';

//reglas de seguridad, esta matriz contiene las opciones de configuración para la conexión PDO
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
];
//Bloque try-catch para manejar errores de conexión a la base de datos
try {
    $pdo = new PDO($dns, 'root', '', $opciones);
    //echo 'Conexión exitosa a la base de datos';
} catch (PDOException $e) {
    die('Error crítico de conexión: ' . $e->getMessage());
}
?>