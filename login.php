<?php
// login.php
require_once 'includes/seguridad.php';

if(isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PYME-Core | Iniciar Sesión</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    />
</head>
<body class="bg-light d-flex align-items-center vh-100">

    <div class="container" style="max-width: 420px;">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0 fw-bold">PYME-Core</h4>
                <small>Acceso al Sistema de Gestión</small>
            </div>

            <div class="card-body p-4">
                <!-- Alertas de error de autenticación -->
                <?php if (isset($_GET['error']) && $_GET['error'] === 'credenciales') : ?>
         <div class="alert alert-danger alert-dismissible fade show small" role="alert">
             Credenciales de acceso incorrectas o cuenta inactivada.
             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
         </div>
     <?php endif; ?>

     <?php if (isset($_GET['error']) && $_GET['error'] === 'servidor') : ?>
         <div class="alert alert-danger small" role="alert">
             Error del servidor al procesar el inicio de sesión.
         </div>
     <?php endif; ?>

                <!-- Alerta cuando se intenta entrar a una página privada sin sesión -->
                <?php if (isset($_GET['error']) && $_GET['error'] === 'sesion_requerida') : ?>
                    <div class="alert alert-warning small" role="alert">
                        Debes iniciar sesión para acceder al sistema.
                    </div>
                <?php endif; ?>

                <!-- Alerta tras cerrar sesión correctamente -->
                <?php if (isset($_GET['msj']) && $_GET['msj'] === 'sesion_cerrada') : ?>
                    <div class="alert alert-success small" role="alert">
                        Has cerrado sesión correctamente.
                    </div>
                <?php endif; ?>

                <form method="POST" action="procesar_login.php">
         <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="ejemplo@pyme.sv"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                            autofocus
                        />
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Contraseña</label>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                        />
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        Ingresar al Sistema
                    </button>
                </form>
            </div>

            <div class="card-footer bg-white text-center py-2 text-muted small">
                Módulo 2 — Control de Acceso por Roles
            </div>
        </div>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"
    ></script>
</body>
</html>