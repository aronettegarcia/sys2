<?php
require_once 'config/conexion.php';
require_once 'config/auth.php';
requerir_autenticacion();

// ==============================================================================
// TODO #1: CAPTURA DE PARÁMETROS GET
// Captura el término de búsqueda enviado por el formulario ($_GET['q']).
// Si no viene ningún término, asigna una cadena vacía por defecto.
// ==============================================================================
$q = trim($_GET['q'] ?? ''); 

// ==============================================================================
// TODO #2: SENTENCIA PREPARADA PDO (READ Y BÚSQUEDA)
// 1. Escribe la consulta SQL para seleccionar (id_producto, codigo, nombre, precio)
//    filtrando los productos que tengan 'activo = 1' y cuyo nombre coincida con :q (LIKE).
$sql = "SELECT id_producto, codigo, nombre, precio 
        FROM productos 
        WHERE activo = 1 AND nombre LIKE :q 
        ORDER BY nombre ASC"; 

// 2. Prepara la consulta usando $pdo->prepare().
$stmt = $pdo->prepare($sql);

// 3. Ejecuta la consulta pasando el parámetro ':q' rodeado de comodines (%...%).
$stmt->execute([':q' => "%$q%"]); 

// 4. Guarda todos los resultados en la variable $productos usando fetchAll().
$productos = $stmt->fetchAll();
// ==============================================================================


?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <title>PYME-Core | Catálogo Digital</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-3 d-flex justify-content-between align-items-center bg-light p-3 mb-3 rounded shadow-sm">
        <h1 class="m-0 h3 text-primary">Catálogo Digital</h1>
        <a href="crear.php" class="btn btn-success">+ Crear producto</a>
    </div>

    <div class="container mt-3">
        <!-- Formulario de búsqueda vía GET -->
        <form method="get" class="d-flex mb-3">
            <input name="q" class="form-control me-2" placeholder="Buscar producto..." value="<?= htmlspecialchars($q) ?>">
            <button class="btn btn-primary">Buscar</button>
        </form>
    </div>

    <div class="container">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th class="text-end">Precio ($)</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $p) : ?>
                        <tr>
                            <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td class="text-end">$<?= number_format($p['precio'], 2) ?></td>
                            
                            <td class="text-center">
                                <?php if (es_admin()) : ?>
                                <a href="editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <?php else : ?>
                            <!-- Visible para el rol 'vendedor' (mantiene alineada la columna) -->
                            <span class="badge bg-secondary">Solo lectura</span>
                        <?php endif; ?>
                                <!-- Formulario seguro POST para eliminar -->
                                <form action="eliminar.php" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de deshabilitar este producto?');">
                                    <input type="hidden" name="id" value="<?= $p['id_producto'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>       
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-3 text-muted">No se encontraron productos en el catálogo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>