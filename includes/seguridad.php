<?php
// includes/seguridad.php

// 1. Iniciar sesión con cookies endurecidas (HttpOnly + SameSite)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,  // Evita robo de cookie PHPSESSID vía JavaScript (XSS)
        'samesite' => 'Lax'  // Barrera adicional contra envío de cookies en peticiones cruzadas
    ]);
    session_start();
}

/**
 * Genera o devuelve el token CSRF único de la sesión actual.
 */
function generarTokenCSRF(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida en tiempo constante el token enviado por POST contra el de la sesión.
 */
function validarTokenCSRF(?string $tokenRecibido): void
{
    if (
        empty($_SESSION['csrf_token']) ||
        empty($tokenRecibido) ||
        !hash_equals($_SESSION['csrf_token'], $tokenRecibido)
    ) {
        http_response_code(403);
        die('Error de seguridad (403): Token CSRF inválido o expirado.');
    }
}