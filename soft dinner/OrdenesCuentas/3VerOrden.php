<?php
session_start();

$imagen = "Imagenes/MantelImagen.jpg";
$usuario = "Usuario";

$bdhost="localhost";
$bduser="root";
$bdpass="";
$bdname="soft_dinner";
$conexion=mysqli_connect($bdhost,$bduser,$bdpass,$bdname);
$query_max = mysqli_query($conexion, "SELECT MAX(id_usuario) FROM usuarios");
$row = mysqli_fetch_array($query_max);
$max_value = $row[0];
$dsn = "mysql:host=localhost;dbname=soft_dinner;charset=utf8mb4";

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$pdo = new PDO($dsn, $bduser, $bdpass, $options);

// Verificar mesa/orden en sesión
if (!isset($_SESSION['selected_mesa'])) {
    header("Location: 1SeleccionOrdenMesa.php");
    exit;
}
$mesaId = (int)$_SESSION['selected_mesa'];
$ordenId = isset($_SESSION['selected_orden_id']) ? (int)$_SESSION['selected_orden_id'] : null;

$message = '';
$success = '';

// Si no hay orden pendiente asociada, mostrar vacío
$items = [];
$total = 0.0;

if ($ordenId) {
    $stmt = $pdo->prepare("
        SELECT d.id_producto, d.cantidad, d.subtotal, p.nombre, p.precio
        FROM detalle_orden d
        JOIN productos p ON d.id_producto = p.id_producto
        WHERE d.id_orden = :id_orden
    ");
    $stmt->execute([':id_orden' => $ordenId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $it) {
        $total += (float)$it['subtotal'];
    }
}

// Procesar pago (efectivo o tarjeta)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_method'])) {
    $payMethod = $_POST['pay_method'] === 'tarjeta' ? 'tarjeta' : 'efectivo';
    $pagarRaw = isset($_POST['pagar']) ? trim($_POST['pagar']) : '';
    // Normalizar número (acepta coma o punto)
    $pagarRaw = str_replace(',', '.', $pagarRaw);
    $pagar = is_numeric($pagarRaw) ? (float)$pagarRaw : null;

    if ($ordenId === null) {
        $message = "No hay orden asociada a la mesa.";
    } elseif ($pagar === null) {
        $message = "Ingrese una cantidad válida a pagar.";
    } elseif ($pagar < $total) {
        $message = "La cantidad pagada no puede ser menor al total (" . number_format($total,2) . ").";
    } else {
        try {
            $pdo->beginTransaction();

            // Obtener nombre del empleado que creó la orden (a partir de id_usuario en ordenes)
            $stmtEmp = $pdo->prepare("
                SELECT u.nombre
                FROM usuarios u
                JOIN ordenes o ON u.id_usuario = o.id_usuario
                WHERE o.id_orden = :id_orden
                LIMIT 1
            ");
            $stmtEmp->execute([':id_orden' => $ordenId]);
            $empRow = $stmtEmp->fetch(PDO::FETCH_ASSOC);
            $nombreEmpleado = $empRow ? $empRow['nombre'] : '';

            // Registrar recibo (incluye cantidad_pagada y nombre_empleado)
            $cambio = $pagar - $total;
            $stmtIns = $pdo->prepare("INSERT INTO recibos (id_orden, total, cantidad_pagada, metodo_pago, cambio, fecha, nombre_empleado) VALUES (:id_orden, :total, :cantidad_pagada, :metodo_pago, :cambio, NOW(), :nombre_empleado)");
            $stmtIns->execute([
                ':id_orden' => $ordenId,
                ':total' => $total,
                ':cantidad_pagada' => $pagar,
                ':metodo_pago' => $payMethod,
                ':cambio' => $cambio,
                ':nombre_empleado' => $nombreEmpleado
            ]);

            // Marcar orden como pagada
            $stmtUpdOrden = $pdo->prepare("UPDATE ordenes SET estado = 'pagado' WHERE id_orden = :id_orden");
            $stmtUpdOrden->execute([':id_orden' => $ordenId]);

            // Liberar mesa
            $stmtUpdMesa = $pdo->prepare("UPDATE mesas SET estado_mesa = 'LIBRE' WHERE id_mesa = :id_mesa");
            $stmtUpdMesa->execute([':id_mesa' => $mesaId]);

            $pdo->commit();

            // Limpiar sesión y redirigir
            unset($_SESSION['selected_orden_id']);
            unset($_SESSION['selected_mesa']);
            unset($_SESSION['selected_mesa_estado']);

            // Opcional: guardar mensaje en sesión antes de redirigir
            $_SESSION['recibo_success'] = "Recibo generado. Cantidad pagada: " . number_format($pagar, 2) . " - Cambio: " . number_format($cambio, 2);
            header("Location: 1SeleccionOrdenMesa.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error al generar recibo: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOFTDINNER-VER ORDEN</title>
    <link rel="stylesheet" href="ORDENES_MAIN_D.css">
</head>
<body>

<header>
    <h1>ÓRDENES</h1>
</header>

<?php if ($message): ?>
    <p style="color:red; font-weight:bold;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green; font-weight:bold;"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<section id="mesaInfo">
    <h2>
        MESA <span id="numeroMesa"><?php echo htmlspecialchars($mesaId); ?></span>
    </h2>
</section>

<section id="ordenesContainer">
    <div class="grupo">
        <label class="tituloCampo">Orden</label>

        <?php if (empty($items)): ?>
            <p>No hay orden pendiente para esta mesa.</p>
        <?php else: ?>
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:#eee;">
                        <th style="padding:8px; color:#b80000; text-align:left;">Producto</th>
                        <th style="padding:8px; color:#b80000; text-align:right;">Precio</th>
                        <th style="padding:8px; color:#b80000; text-align:right;">Cantidad</th>
                        <th style="padding:8px; color:#b80000; text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td style="padding:8px;"><?php echo htmlspecialchars($it['nombre']); ?></td>
                            <td style="padding:8px; text-align:right;"><?php echo number_format((float)$it['precio'], 2); ?></td>
                            <td style="padding:8px; text-align:right;"><?php echo (int)$it['cantidad']; ?></td>
                            <td style="padding:8px; text-align:right;"><?php echo number_format((float)$it['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="padding:8px; text-align:right; font-weight:bold;">EL PRECIO TOTAL ES DE:</td>
                        <td style="padding:8px; text-align:right; font-weight:bold;"><?php echo number_format($total, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>

    </div>
</section>

<form method="post" style="margin-top:16px;">
    <label class="tituloCampo">Pagar</label><br><br>
    <input type="text" name="pagar" placeholder="Cantidad a Pagar" class="campoTexto" value="<?php echo isset($_POST['pagar']) ? htmlspecialchars($_POST['pagar']) : ''; ?>">

    <section id="botoneraPrincipal" style="margin-top:12px;">
    <div style="margin-top:18px;">
        <button type="submit" id="btnEnviarOrdenes" name="pay_method" value="efectivo">Efectivo</button>
        <button type="submit" id="btnEnviarOrdenes" name="pay_method" value="tarjeta">Tarjeta</button>
    </div>
    </section>
</form>

<footer>
    <button id ="btnRegresar" type="button" style="display: inline-block; margin: 8px auto 68px auto; background: transparent; color: var(--blanco); padding: 12px 30px; border-radius: 24px; border: 2px solid rgba(255,255,255,0.85); font-size: 16px; font-weight: 700; box-shadow: 0 6px 12px rgba(0,0,0,0.45); cursor: pointer; backdrop-filter: blur(2px);" onclick="window.location.href='2EncargoOrden.php'">REGRESAR</button>
</footer>

</body>
</html>