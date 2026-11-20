<?php
// Inicializar la sesión existente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Limpiar todas las variables almacenadas en el arreglo superglobal $_SESSION
$_SESSION = array();

// 2. Destruir la cookie de sesión en el navegador si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor completamente
session_destroy();

// 4. Redirigir al usuario al formulario de inicio de sesión
header('Location: login.php?msj=sesion_cerrada');
exit();
?>