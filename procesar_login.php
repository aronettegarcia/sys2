<?php
   // procesar_login.php
   require_once 'config/conexion.php';
   require_once 'includes/seguridad.php';

   // Si ya tiene sesión activa, redirigir al catálogo
   if (isset($_SESSION['usuario_id'])) {
       header('Location: index.php');
       exit;
   }

   // Solo aceptar peticiones POST
   if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
       header('Location: login.php');
       exit;
   }

   // 1. Validar token CSRF antes de tocar la base de datos
   validarTokenCSRF($_POST['csrf_token'] ?? null);

   // 2. Capturar y validar formato de credenciales
   $email    = trim($_POST['email'] ?? '');
   $password = $_POST['password'] ?? '';

   if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
       header('Location: login.php?error=credenciales');
       exit;
   }

   try {
       // 3. Consulta preparada sobre la tabla usuarios de bd_practica
       $sql = 'SELECT id_usuario, nombre, email, password_hash, rol
               FROM usuarios
               WHERE email = :email AND activo = 1
               LIMIT 1';
       $stmt = $pdo->prepare($sql);
       $stmt->execute([':email' => $email]);
       $usuario = $stmt->fetch();

       // 4. Verificación criptográfica con Bcrypt
       if ($usuario && password_verify($password, $usuario['password_hash'])) {
           session_regenerate_id(true); // Mitiga Session Fixation

           $_SESSION['usuario_id']     = $usuario['id_usuario'];
           $_SESSION['usuario_nombre'] = $usuario['nombre'];
           $_SESSION['usuario_email']  = $usuario['email'];
           $_SESSION['usuario_rol']    = $usuario['rol'];

           header('Location: index.php');
           exit;
       } else {
           header('Location: login.php?error=credenciales');
           exit;
       }
   } catch (PDOException $e) {
       header('Location: login.php?error=servidor');
       exit;
   }