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

$message = '';

// Manejo del clic en una mesa (envío por POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mesa_id'])) {
    $mesaId = (int)$_POST['mesa_id'];

    // Consultar estado real en la tabla `mesas`
    $stmt = $pdo->prepare("SELECT estado_mesa FROM mesas WHERE id_mesa = :id_mesa LIMIT 1");
    $stmt->execute([':id_mesa' => $mesaId]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        $estado = strtoupper(trim($fila['estado_mesa']));
        // Guardar mesa y estado en sesión para uso en 2EncargoOrden.php
        $_SESSION['selected_mesa'] = $mesaId;
        $_SESSION['selected_mesa_estado'] = $estado;

        // Si la mesa está OCUPADA, buscar si existe una orden pendiente vinculada
        $_SESSION['selected_orden_id'] = null;
        if ($estado === 'OCUPADA') {
            $stmt2 = $pdo->prepare("SELECT id_orden FROM ordenes WHERE id_mesa = :id_mesa AND estado = 'pendiente' LIMIT 1");
            $stmt2->execute([':id_mesa' => $mesaId]);
            $ordenFila = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($ordenFila) {
                $_SESSION['selected_orden_id'] = (int)$ordenFila['id_orden'];
            }
        }

        // Redirigir a la página de encargo (misma carpeta)
        header("Location: 2EncargoOrden.php");
        exit;
    } else {
        $message = "Mesa no encontrada en la base de datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOFTDINNER-SELECCIONAR MESA</title>
    <link rel="stylesheet" href="ORDENES_MESAS_D.css">
</head>

<body>

    <header>
        <h1>ORDENES</h1>
        <h2>MESAS</h2>
    </header>

    <?php if ($message): ?>
        <p style="color:red; font-weight:bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <section id="contenedorMesas">
        <!-- Cada botón es ahora un formulario que envía por POST el id de la mesa -->
        <form method="post" style="display:inline-block; margin:6px;">
            <input type="hidden" name="mesa_id" value="1">
            <button class="btnMesaOpcion" type="submit" style="padding:20px 48px; font-size:20px; min-width:220px; min-height:72px; border-radius:12px;">MESA 1</button>
        </form>

        <form method="post" style="display:inline-block; margin:6px;">
            <input type="hidden" name="mesa_id" value="2">
            <button class="btnMesaOpcion" type="submit" style="padding:20px 48px; font-size:20px; min-width:220px; min-height:72px; border-radius:12px;">MESA 2</button>
        </form>

        <form method="post" style="display:inline-block; margin:6px;">
            <input type="hidden" name="mesa_id" value="3">
            <button class="btnMesaOpcion" type="submit" style="padding:20px 48px; font-size:20px; min-width:220px; min-height:72px; border-radius:12px;">MESA 3</button>
        </form>
    </section>

    <footer>
    <button id ="btnRegresar" type="button" style = "display: inline-block; margin: 8px auto 68px auto; background: transparent; color: var(--blanco); padding: 12px 30px; border-radius: 24px; border: 2px solid rgba(255,255,255,0.85); font-size: 16px; font-weight: 700; box-shadow: 0 6px 12px rgba(0,0,0,0.45); cursor: pointer; backdrop-filter: blur(2px);" onclick="window.location.href='../PantallaDeInicio.php'">REGRESAR</button>
    </footer>

</body>
</html>