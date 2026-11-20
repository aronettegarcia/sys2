<?php
require_once 'config/conexion.php';
require_once 'config/auth.php';
requerir_rol(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // nunca borrar con un enlace GET
  $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
  if ($id) {
    // Baja lógica: el producto deja de verse pero el historial de cotizaciones se conserva
    $stmt = $pdo->prepare('UPDATE productos SET activo = 0 WHERE id_producto = :id');
    $stmt->execute([':id' => $id]);
    // $stmt->rowCount() dice cuántas filas cambiaron (0 = no existía)
  }
}
header('Location: index.php'); exit;

?>

