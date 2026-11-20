<?php
require_once 'config/conexion.php';
require_once 'config/auth.php';
requerir_rol(['admin']);
$errores = []; // arreglo para almacenar mensajes de error
$categorias = []; // arreglo para almacenar las categorías desde la base de datos

// ==============================================================================
// TODO #1: LECTURA DE CATEGORÍAS (READ)
// Utiliza un bloque try-catch para consultar la tabla 'categorias' con $pdo->query()
// y almacenar el resultado asociativo dentro del arreglo $categorias.

try {
    $sql_cat="SELECT * FROM categorias";
    $stmt_cat = $pdo->query($sql_cat);
    $categorias = $stmt_cat->fetchAll();
} catch (PDOException $e) {
    die('Error al leer categorías: ' . $e->getMessage());
}

// ==============================================================================


// PROCESAMIENTO DEL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================================================
    // TODO #2: CAPTURA Y SANITIZACIÓN DE DATOS
    // Captura los valores recibidos por POST ('codigo', 'nombre', 'precio', 'id_categoria').
    // Elimina espacios en blanco innecesarios utilizando trim().
    // ==========================================================================
    $codigo       = trim($_POST['codigo'] ?? ''); 
    $nombre       = trim($_POST['nombre'] ?? ''); 
    $precio       = trim($_POST['precio'] ?? ''); 
    $id_categoria = trim($_POST['id_categoria'] ?? ''); 

    // ==========================================================================
    // TODO #3: VALIDACIONES DE NEGOCIO
    // Valida que los campos no estén vacíos y que 'precio' sea un valor numérico válido.
    // Si una regla falla, agrega el mensaje de error correspondiente al arreglo $errores[].

    if ($codigo === '') {
        $errores[] = 'El código no puede estar vacío.';
    }
    if ($nombre === '') {
        $errores[] = 'El nombre no puede estar vacío.';
    }
    if ($precio === '' || !is_numeric($precio)) {
        $errores[] = 'El precio debe ser un número válido.';
    }
    if ($id_categoria === '') {
        $errores[] = 'La categoría no puede estar vacía.';
    }


    // ==========================================================================


    // ==========================================================================
    // TODO #4: INSERCIÓN EN BASE DE DATOS (CREATE)
    // Si el arreglo $errores está vacío:
    // 1. Escribe la sentencia SQL 'INSERT INTO productos...' estableciendo activo = 1.
    // 2. Prepara la sentencia con marcadores nombrados (:codigo, :nombre, :precio, :id_categoria).
    // 3. Ejecuta la sentencia pasando los parámetros.
    // 4. Redirige al usuario hacia 'index.php' usando header().
    // ==========================================================================
    if (empty($errores)) {
        try {
            $sql = "INSERT INTO productos (codigo, nombre, precio, id_categoria, activo) 
                    VALUES (:codigo, :nombre, :precio, :id_categoria, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':precio' => $precio,
                ':id_categoria' => $id_categoria
            ]);

            header('Location: index.php');
        } catch (PDOException $e) {
            die('Error al crear el producto: ' . $e->getMessage());
        }
    }
}
?>

<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <title>PYME-Core | Crear Producto</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-4" style="max-width: 600px;">
        <h1 class="text-center mb-4 text-primary">Registrar Nuevo Producto</h1>
        
        <?php if (!empty($errores)) : ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="codigo" class="form-label font-weight-bold">Código de Barra / SKU</label>
                <input type="text" class="form-control" id="codigo" name="codigo" value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label font-weight-bold">Nombre del Producto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label font-weight-bold">Precio Unitario ($)</label>
                <input type="text" class="form-control" id="precio" name="precio" value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>">
            </div>
                        
            <div class="mb-3">
                <label for="id_categoria" class="form-label font-weight-bold">Categoría</label>
                <select class="form-select" id="id_categoria" name="id_categoria">
                    <option value="">Seleccione una categoría</option>
                    <?php foreach ($categorias as $categoria) : ?>
                        <?php
                        // Convertir todas la llaves a minusculas por si la bd devuelve ID_CATEGORIA o NOMBRE en mayusc.
                        $cat_limpia = array_change_key_case($categoria, CASE_LOWER);
                        //Detectar si la categoría tiene 'id_categoria' o 'id' y si tiene 'nombre' o 'nombre_categoria'
                        $id_real = $cat_limpia['id_categoria'] ?? $cat_limpia['id'] ?? '';
                        //Detectar si la categoría tiene 'nombre' o 'nombre_categoria'
                        $nombre_real = $cat_limpia['nombre'] ?? $cat_limpia['nombre_categoria'] ?? 'Sin nombre';
                        
                        $selected = (isset($_POST['id_categoria']) && $_POST['id_categoria'] == $id_real) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($id_real) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($nombre_real) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>