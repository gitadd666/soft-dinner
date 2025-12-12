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

// Obtener órdenes pendientes por mesa (1..3)
$mesas = [];
for ($m = 1; $m <= 3; $m++) {
    // Buscar una orden pendiente (no pagada) para la mesa
    $stmt = $pdo->prepare("SELECT id_orden FROM ordenes WHERE id_mesa = :id_mesa AND estado <> 'pagado' ORDER BY fecha DESC LIMIT 1");
    $stmt->execute([':id_mesa' => $m]);
    $ordenRow = $stmt->fetch(PDO::FETCH_ASSOC);

    $items = [];
    $total = 0.0;
    if ($ordenRow && isset($ordenRow['id_orden'])) {
        $idOrden = (int)$ordenRow['id_orden'];
        $stmt2 = $pdo->prepare("
            SELECT d.cantidad, d.subtotal, p.nombre, p.precio
            FROM detalle_orden d
            JOIN productos p ON d.id_producto = p.id_producto
            WHERE d.id_orden = :id_orden
        ");
        $stmt2->execute([':id_orden' => $idOrden]);
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as $it) {
            $total += (float)$it['subtotal'];
        }
    }

    $mesas[$m] = [
        'items' => $items,
        'total' => $total
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOFTDINNER - CUENTAS</title>
    <link rel="stylesheet" href="CUENTAS_D.css">
    <style>
        /* --- Estilo del "card container" tomado de ReportesGanacias/Historial.php
           adaptado a las dimensiones del contenedor actual de Cuentas.php --- */
        .cuentas-card {
            background-color: rgba(255, 255, 255, 0.95);
            width: 100%;
            max-width: 900px; /* mantiene un ancho razonable igual que Historial */
            margin: 18px auto; /* centrado dentro del <main> */
            border: 5px solid #000;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            box-shadow: 15px 15px 0 rgba(0,0,0,0.3);
        }

        /* Mantener estilos del list-box y elementos internos igual que antes */
        .list-box {
            width: 100%;
            min-height: 140px;
            background-color: white;
            border: 4px solid black;
            border-radius: 15px;
            padding: 14px;
            overflow-y: auto;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .order-header {
            display:flex;
            justify-content:space-between;
            gap:12px;
            font-weight:800;
            color:#555;
            padding:2px 4px;
            font-size:13px;
        }

        .order-item {
            display:flex;
            justify-content:space-between;
            gap:12px;
            padding:6px 4px;
            font-size:15px;
            border-bottom:1px dashed #ddd;
            align-items:center;
        }

        .order-item:last-child { border-bottom: none; }

        .order-total {
            margin-top:6px;
            text-align:right;
            font-weight:900;
            font-size:15px;
        }

        .campo-nombre { flex: 1; }
        .campo-precio { width:110px; text-align:right; }
        .campo-cantidad { width:80px; text-align:right; }
        .campo-subtotal { width:120px; text-align:right; }

        /* Ajuste responsivo para pantallas pequeñas */
        @media (max-width: 700px) {
            .cuentas-card { padding: 12px; max-width: 100%; margin: 12px; }
            .campo-precio { width:90px; }
            .campo-subtotal { width:100px; }
        }
    </style>
</head>

<body>

    <header>
        <h1>CUENTAS</h1>
    </header>

    <main id="contenedorCuentas">

        <?php for ($i = 1; $i <= 3; $i++):
            $items = $mesas[$i]['items'];
            $total = $mesas[$i]['total'];
            if (empty($items)) {
                // No mostrar sección si no hay orden pendiente para la mesa
                continue;
            }
        ?>
        <section class="tarjeta-mesa" id="seccionMesa<?php echo $i; ?>">
            
            <h3 class="titulo-mesa">LA MESA <?php echo $i; ?> HA PEDIDO:</h3>

            <div class="caja-detalles-pedidos" id="listaMesa<?php echo $i; ?>">
                <div class="list-box" role="group" aria-label="Detalles Mesa <?php echo $i; ?>">

                    <div class="order-header">
                        <div class="campo-nombre">Producto</div>
                        <div class="campo-precio">Precio</div>
                        <div class="campo-cantidad">Cantidad</div>
                        <div class="campo-subtotal">Subtotal</div>
                    </div>

                    <?php foreach ($items as $it): ?>
                        <div class="order-item">
                            <div class="campo-nombre"><?php echo htmlspecialchars($it['nombre']); ?></div>
                            <div class="campo-precio">$ <?php echo number_format((float)$it['precio'], 2); ?></div>
                            <div class="campo-cantidad"><?php echo (int)$it['cantidad']; ?></div>
                            <div class="campo-subtotal">$ <?php echo number_format((float)$it['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="order-total">EL PRECIO TOTAL ES DE: $ <?php echo number_format($total, 2); ?></div>
                </div>
            </div>

        </section>
        <?php endfor; ?>

    </main>

    <footer>
        <a href="../PantallaDeInicio.php" style="text-decoration:none;"><button type="button" id="btnRegresar">REGRESAR</button></a>
    </footer>

</body>
</html>