<?php
// config/auth.php

// 1. Iniciar la sesión únicamente si no existe una activa.
// Evita el error "Notice: session_start(): Ignoring session_start() because a session is already active".
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica que el usuario haya iniciado sesión.
 * Si no está autenticado, corta la ejecución y lo envía al login.
 */
function requerir_autenticacion(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?error=sesion_requerida');
        exit; // Detiene el script para evitar que se ejecute el resto de la página
    }
}

/**
 * Control de Acceso Basado en Roles (RBAC).
 * Valida que el usuario esté autenticado y que su rol esté dentro de los permitidos.
 *
 * @param array $roles_permitidos Ejemplo: ['admin'] o ['admin', 'vendedor']
 */
function requerir_rol(array $roles_permitidos = []): void
{
    // Primero garantizamos que exista una sesión válida
    requerir_autenticacion();

    $rol_actual = $_SESSION['usuario_rol'] ?? '';

    // Si el rol del usuario no está en la lista autorizada, bloqueamos el acceso
    if (!in_array($rol_actual, $roles_permitidos, true)) {
        header('Location: index.php?error=acceso_denegado');
        exit;
    }
}

/**
 * Helper visual para saber si el usuario actual es administrador.
 * Útil para mostrar u ocultar botones en la interfaz (Bootstrap).
 */
function es_admin(): bool
{
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}