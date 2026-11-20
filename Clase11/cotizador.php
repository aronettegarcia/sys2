<?php
require_once 'config/conexion.php';

$mensaje = '';
$error = '';

// Cargar catálogo de productos activos para llenar la tabla del formulario
$stmt_prod = $pdo->query("SELECT p.id_producto, p.codigo, p.nombre, p.precio, c.nombre AS categoria 
                          FROM productos p 
                          INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
                          WHERE p.activo = 1 
                          ORDER BY p.nombre ASC");
$productos = $stmt_prod->fetchAll();

// PROCESAMIENTO DEL FORMULARIO DE COTIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_cotizacion'])) {
    
    $cliente = trim($_POST['cliente'] ?? '');
    $productos_seleccionados = $_POST['productos'] ?? []; // Arreglo [id_producto => cantidad]

    // ==============================================================================
    // TODO #1: VALIDACIÓN INICIAL
    // Valida qu $cliente no esté vaceío y que $productos_seleccionados tenga elementos.
    // Si falla, asigna un mensaje de error a la variable $error.
    // ==============================================================================
    if (empty($cliente)) {
        $error = "El nombre del cliente es obligatorio.";
    }
     elseif (empty($productos_seleccionados)) {
        $error = "Debe seleccionar al menos un producto.";
    }

    if (empty($error)) {
        try {
            // ==========================================================================
            // TODO #2: INICIO DE TRANSACCIÓN PDO
            // Inicia la transacción atómica con el método $pdo->beginTransaction().
            // ==========================================================================
            // [ESCRIBE TU CÓDIGO AQUÍ]

            $pdo->beginTransaction();

    // calculo del subtotal acumulado y preparación de los items a insertar
            $subtotal_acumulado = 0;
            $items_a_insertar = [];

            // Recorrer los productos e identificar las cantidades mayores a 0
            foreach ($productos_seleccionados as $id_prod => $cantidad) {
                $cantidad = (int)$cantidad;
                if ($cantidad > 0) {
                    // Consultar precio unitario en la BD para evitar alteración en frontend
                    $stmt_p = $pdo->prepare("SELECT precio FROM productos WHERE id_producto = :id AND activo = 1");
                    $stmt_p->execute([':id' => $id_prod]);
                    $prod_db = $stmt_p->fetch();

                    if ($prod_db) {
                        $precio_u = (float)$prod_db['precio'];
                        $subtotal_acumulado += ($precio_u * $cantidad);
                        
                        $items_a_insertar[] = [
                            'id_producto' => $id_prod,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio_u
                        ];
                    }
                }
            }

            if (empty($items_a_insertar)) {
                throw new Exception("Debe ingresar al menos una cantidad mayor a cero.");
            }

            // ==========================================================================
            // TODO #3: CÁLCULOS FISCALES (EL SALVADOR)
            // 1. Calcula el IVA del 13% sobre $subtotal_acumulado ($iva = $subtotal * 0.13).
            // 2. Calcula el Total final ($total = $subtotal + $iva).
            // ==========================================================================
            $iva = $subtotal_acumulado * 0.13;   // <-- [COMPLETA ESTE CÁLCULO]
            $total = $subtotal_acumulado + $iva; // <-- [COMPLETA ESTE CÁLCULO]


            // ==========================================================================
            // TODO #4: INSERTAR ENCABEZADO ('cotizaciones')
            // 1. Prepara la consulta INSERT para la tabla 'cotizaciones' (cliente, subtotal, iva, total).
            // 2. Ejecuta la sentencia pasando los parámetros.
            // 3. Obtén el ID generado con $pdo->lastInsertId() y guárdalo en $id_cotizacion.
            // ==========================================================================
            // [ESCRIBE TU CÓDIGO AQUÍ]
            
            $sql_cot = "INSERT INTO cotizaciones (cliente, subtotal, iva, total) VALUES (:cliente, :subtotal, :iva, :total)";
            $stmt_cot = $pdo->prepare($sql_cot);
            $stmt_cot->execute([
                ':cliente' => $cliente,
                ':subtotal' => $subtotal_acumulado,
                ':iva' => $iva,
                ':total' => $total
            ]);
            //obtener el id de la cotización recién insertada
            $id_cotizacion = $pdo->lastInsertId();


            // ==========================================================================
            // TODO #5: INSERTAR DETALLE EN BUCLE ('detalle_cotizacion')
            // 1. Prepara la consulta INSERT para la tabla 'detalle_cotizacion'
            //    (id_cotizacion, id_producto, cantidad, precio_unitario).
            // 2. Utiliza un bucle foreach sobre $items_a_insertar para ejecutar la inserción.
            // ==========================================================================
            // [ESCRIBE TU CÓDIGO AQUÍ]
            $sql_det = "INSERT INTO detalle_cotizacion (id_cotizacion, id_producto, cantidad, precio_unitario) VALUES (:id_cotizacion, :id_producto, :cantidad, :precio_unitario)";
            foreach ($items_a_insertar as $item) {
                $stmt_det = $pdo->prepare($sql_det);
                $stmt_det->execute([
                    ':id_cotizacion' => $id_cotizacion,
                    ':id_producto' => $item['id_producto'],
                    ':cantidad' => $item['cantidad'],
                    ':precio_unitario' => $item['precio_unitario']
                ]);
            }

            // ==========================================================================
            // TODO #6: CONFIRMAR TRANSACCIÓN
            // Confirma la transacción con $pdo->commit() y asigna un mensaje de éxito a $mensaje.
            // ==========================================================================
            // [ESCRIBE TU CÓDIGO AQUÍ]
            $pdo->commit();
            $mensaje = "Cotización procesada exitosamente. ID: {$id_cotizacion}, Total: $" . number_format($total, 2);

        } catch (Exception $e) {
            // ==========================================================================
            // TODO #7: REVERTIR TRANSACCIÓN
            // Si ocurre un error, revierte los cambios con $pdo->rollBack().
            // ==========================================================================
            // [ESCRIBE TU CÓDIGO AQUÍ]

            $pdo->rollBack();
            $error = "Error en la transacción: " . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PYME-Core | Cotizador Automático</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary">Cotizador Automático de Ventas</h1>
            <div>
                <a href="reporte_metricas.php" class="btn btn-outline-primary me-2">Ver Métricas</a>
                <a href="index.php" class="btn btn-outline-secondary">Volver al Catálogo</a>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="cotizador.php">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">1. Datos del Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cliente" class="form-label fw-bold">Nombre o Razón Social</label>
                        <input type="text" name="cliente" id="cliente" class="form-control" placeholder="Ej. Constructora El Roble S.A. de C.V." required>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">2. Seleccionar Productos</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-end">Precio Unit. ($)</th>
                                <th style="width: 150px;" class="text-center">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                                    <td class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($p['categoria']) ?></span></td>
                                    <td class="text-end">$<?= number_format($p['precio'], 2) ?></td>
                                    <td>
                                        <input type="number" name="productos[<?= $p['id_producto'] ?>]" value="0" min="0" class="form-control form-control-sm text-center">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" name="procesar_cotizacion" class="btn btn-success btn-lg w-100 shadow">
                Procesar Cotización (Con IVA 13%)
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>