<?php
session_start();

// Mostrar errores para depuración (quítalo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$messageError = '';
$fechaSeleccionada = '';
$orders = []; // array de órdenes con sus items
$totalAcumulado = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fechaSeleccionada = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';

    if ($fechaSeleccionada === '') {
        $messageError = "Favor de ingresar una Fecha";
    } else {
        // validar formato YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaSeleccionada)) {
            $messageError = "Formato de fecha inválido. Use YYYY-MM-DD";
        } else {
            // Buscar ordenes cuya fecha coincida (solo fecha, ignorar hora).
            // Usamos LEFT(fecha,10) = :fecha para ser tolerantes con el formato almacenado.
            $stmtOrd = $pdo->prepare("SELECT id_orden FROM ordenes WHERE LEFT(fecha,10) = :fecha ORDER BY id_orden ASC");
            $stmtOrd->execute([':fecha' => $fechaSeleccionada]);
            $ordenes = $stmtOrd->fetchAll(PDO::FETCH_ASSOC);

            $orderIndex = 0;
            $totalAcumulado = 0.0;

            foreach ($ordenes as $orden) {
                $orderIndex++;
                $idOrden = (int)$orden['id_orden'];

                // Obtener detalles de la orden: producto, precio, cantidad, subtotal
                $stmtDet = $pdo->prepare("
                    SELECT d.id_producto, d.cantidad, d.subtotal, p.nombre, p.precio
                    FROM detalle_orden d
                    LEFT JOIN productos p ON d.id_producto = p.id_producto
                    WHERE d.id_orden = :id_orden
                ");
                $stmtDet->execute([':id_orden' => $idOrden]);
                $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

                $items = [];
                $orderTotal = 0.0;
                foreach ($detalles as $det) {
                    $items[] = [
                        'nombre' => $det['nombre'],
                        'precio' => (float)$det['precio'],
                        'cantidad' => (int)$det['cantidad'],
                        'subtotal' => (float)$det['subtotal']
                    ];
                    $orderTotal += (float)$det['subtotal'];
                    $totalAcumulado += (float)$det['subtotal'];
                }

                $orders[] = [
                    'numero' => $orderIndex,
                    'id_orden' => $idOrden,
                    'items' => $items,
                    'order_total' => $orderTotal
                ];
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Detallado - PC</title>
    <style>
        /* --- CONFIGURACIÓN BASE --- */
        :root {
            --primary-red: #cc0000;
            --border-color: #000;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;

            /* Fondo de cuadros (Plaid) */
            background-color: var(--primary-red);
            background-image: 
                repeating-linear-gradient(45deg, rgba(0,0,0,0.15) 25%, transparent 25%, transparent 75%, rgba(0,0,0,0.15) 75%, rgba(0,0,0,0.15)),
                repeating-linear-gradient(45deg, rgba(0,0,0,0.15) 25%, transparent 25%, transparent 75%, rgba(0,0,0,0.15) 75%, rgba(0,0,0,0.15));
            background-position: 0 0, 10px 10px;
            background-size: 30px 30px;
        }

        /* --- HEADER --- */
        .header-oval {
            background-color: #ff1a1a;
            border: 4px solid var(--border-color);
            border-radius: 50px;
            padding: 10px 80px;
            margin-bottom: 30px;
            box-shadow: 6px 6px 0 rgba(0,0,0,0.4);
            z-index: 2;
        }

        .header-title {
            color: white;
            font-size: 40px;
            font-weight: 900;
            -webkit-text-stroke: 2px black;
            text-shadow: 3px 3px 0 #000;
            letter-spacing: 1px;
        }

        /* --- CONTENEDOR CENTRAL (DASHBOARD) --- */
        .dashboard-card {
            background-color: rgba(255, 255, 255, 0.95);
            width: 100%;
            max-width: 900px;
            border: 5px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centramos todo verticalmente */
            gap: 18px;
            box-shadow: 15px 15px 0 rgba(0,0,0,0.3);
        }

        /* --- CONTROL DE FECHA --- */
        .date-btn {
            background-color: white;
            border: 3px solid black;
            border-radius: 15px;
            padding: 10px 30px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 0;
            box-shadow: 4px 4px 0 rgba(0,0,0,0.2);
        }

        /* --- ETIQUETAS DE TEXTO (Labels) --- */
        .text-label {
            font-size: 20px;
            font-weight: 800; /* Letra gruesa como en la imagen */
            color: #000;
            width: 100%;
            text-align: left; /* Alineado a la izquierda según la imagen */
            text-shadow: 1px 1px 0 rgba(255,255,255,0.5); /* Pequeño brillo para legibilidad */
        }

        /* Span para resaltar la fecha en el texto */
        .date-highlight {
            color: #cc0000; /* Rojo oscuro */
        }

        /* --- CAJA BLANCA CON LISTA (El cuadro que pediste) --- */
        .list-box {
            width: 100%;
            height: 360px; /* Altura fija */
            background-color: white;
            border: 4px solid black; /* Borde grueso negro */
            border-radius: 15px;
            padding: 20px;
            overflow-y: auto; /* Scroll si hay muchos productos */
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1); /* Sombra interna sutil */
            
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .order-block {
            border: 2px solid #eee;
            padding: 8px;
            border-radius: 8px;
            background:#fafafa;
        }

        .order-title {
            font-weight:900;
            margin-bottom:6px;
        }

        .order-header {
            display:flex;
            justify-content:space-between;
            gap:12px;
            font-weight:800;
            color:#555;
            padding:4px 2px;
            font-size:13px;
        }

        .order-item {
            display:flex;
            justify-content:space-between;
            gap:12px;
            padding:6px 4px;
            font-size:15px;
            border-bottom:1px dashed #ddd;
        }

        .order-item:last-child { border-bottom: none; }

        .order-total {
            margin-top:8px;
            text-align:right;
            font-weight:900;
            font-size:16px;
        }

        /* --- SECCIÓN INGRESO TOTAL --- */
        .total-container {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between; /* Texto a la izq, Input a la der */
            margin-top: 10px;
        }

        .total-input {
            border: 3px solid black;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 18px;
            width: 220px;
            text-align: center;
            outline: none;
            font-weight: bold;
        }

        .error-label {
            color: #b80000;
            font-weight: 800;
            margin-bottom:8px;
        }

        .controls-row { width:100%; display:flex; gap:12px; align-items:center; }

        input[type="date"] {
            border:3px solid black;
            border-radius:10px;
            padding:8px 12px;
            font-size:16px;
            background:white;
        }

        .back-btn {
            background: #ffffff;
            border: 3px solid black;
            padding: 8px 18px;
            border-radius: 12px;
            text-decoration: none;
            color: black;
            font-weight: 700;
            box-shadow: 3px 3px 0 rgba(0,0,0,0.2);
        }

    </style>
</head>
<body>

    <div class="header-oval">
        <h1 class="header-title">Historial</h1>
    </div>

    <main class="dashboard-card">

        <!-- Botón regresar a PantallaDeInicio.php -->
        <div style="width:100%; display:flex; justify-content:flex-end;">
            <a class="back-btn" href="../PantallaDeInicio.php">Regresar</a>
        </div>

        <?php if ($messageError): ?>
            <div class="error-label"><?php echo htmlspecialchars($messageError); ?></div>
        <?php endif; ?>

        <form method="post" class="controls-row" style="justify-content:flex-start;">
            <!-- Campo fecha: formato YYYY-MM-DD -->
            <input type="date" name="fecha" value="<?php echo htmlspecialchars($fechaSeleccionada); ?>" required>
            <button class="date-btn" type="submit">Seleccionar Fecha</button>
        </form>

        <div class="text-label">
            El dia <span class="date-highlight"><?php echo $fechaSeleccionada ? htmlspecialchars($fechaSeleccionada) : '[Fecha]'; ?></span> se vendio:
        </div>

        <div class="list-box" aria-live="polite">
            <?php if (empty($orders)): ?>
                <div class="order-block" style="color:#666;">No se encontraron órdenes para la fecha seleccionada.</div>
            <?php else: ?>
                <?php foreach ($orders as $ord): ?>
                    <div class="order-block">
                        <div class="order-title">Orden <?php echo htmlspecialchars($ord['numero']); ?> (ID: <?php echo htmlspecialchars($ord['id_orden']); ?>)</div>

                        <!-- Encabezados para Precio / Cantidad / Subtotal -->
                        <div class="order-header">
                            <div style="flex:1;"></div>
                            <div style="width:110px; text-align:right;">Precio</div>
                            <div style="width:80px; text-align:right;">Cantidad</div>
                            <div style="width:120px; text-align:right;">Subtotal</div>
                        </div>

                        <?php if (empty($ord['items'])): ?>
                            <div class="order-item">Sin productos</div>
                        <?php else: ?>
                            <?php foreach ($ord['items'] as $it): ?>
                                <div class="order-item">
                                    <div style="flex:1;"><?php echo htmlspecialchars($it['nombre']); ?></div>
                                    <div style="width:110px; text-align:right;">$ <?php echo number_format($it['precio'],2); ?></div>
                                    <div style="width:80px; text-align:right;"><?php echo $it['cantidad']; ?></div>
                                    <div style="width:120px; text-align:right;">$ <?php echo number_format($it['subtotal'],2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Total de la orden -->
                        <div class="order-total">Total Orden: $ <?php echo number_format($ord['order_total'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="total-container">
            <div class="text-label" style="width:auto;">
                El ingreso total es de:
            </div>
            <input type="text" class="total-input" value="<?php echo '$ ' . number_format($totalAcumulado,2); ?>" readonly>
        </div>

    </main>

</body>
</html>