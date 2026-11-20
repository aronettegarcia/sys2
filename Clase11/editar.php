<?php
require_once 'config/conexion.php';
require_once 'config/auth.php';
requerir_rol(['admin']);

// ==============================================================================
// TODO #1: VALIDACIÓN DEL PARÁMETRO GET
// Valida que el parámetro 'id' recibido por GET sea un número entero válido mediante filter_input().
// Si no es un entero válido, redirige a 'index.php'.
// ==============================================================================
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

// ==============================================================================
// TODO #2: CONSULTA DE PRECARGA (READ BY ID)
// 1. Prepara la consulta SQL para obtener el producto correspondiente al :id.
// 2. Ejecuta la consulta pasando el ID validado.
// 3. Usa fetch() para guardar el registro en $producto. Si no existe, interrumpe con die().
// ==============================================================================
$stmt = $pdo->prepare('SELECT * FROM productos WHERE id_producto = :id');
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$producto) {
    die('Producto no encontrado.');
}

// PROCESAMIENTO DE LA ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================================================
    // TODO #3: SENTENCIA PREPARADA DE ACTUALIZACIÓN (UPDATE)
    // 1. Escribe la consulta UPDATE para modificar 'nombre' y 'precio' según el 'id_producto'.
    // 2. Prepara la consulta y ejecútala pasando los valores recibidos del formulario POST.
    // 3. Redirige al usuario hacia 'index.php'.
    // ==========================================================================

    $sql = "UPDATE productos SET nombre = :nombre, precio = :precio WHERE id_producto = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $_POST['nombre'],
        ':precio' => $_POST['precio'],
        ':id' => $id
    ]);
    header('Location: index.php');
}
?>

<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <title>PYME-Core | Editar Producto</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">Editar Producto #<?= htmlspecialchars($producto['id_producto'] ?? '') ?></h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-bold">Nombre del Producto</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="precio" class="form-label fw-bold">Precio ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" value="<?= htmlspecialchars($producto['precio'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>