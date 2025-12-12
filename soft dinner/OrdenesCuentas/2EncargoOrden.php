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

// Verificar que exista la mesa seleccionada en sesión
if (!isset($_SESSION['selected_mesa'])) {
    header("Location: 1SeleccionOrdenMesa.php");
    exit;
}

$mesaId = (int)$_SESSION['selected_mesa'];
$mesaEstado = isset($_SESSION['selected_mesa_estado']) ? strtoupper($_SESSION['selected_mesa_estado']) : null;
$ordenIdEnSesion = isset($_SESSION['selected_orden_id']) ? $_SESSION['selected_orden_id'] : null;

$message = '';
$successMessage = '';
$orderEstadoMsg = ''; // estado para mostrar en etiqueta verde
$debug = ''; // información de depuración visible si algo sale mal

// Obtener lista de productos (id y nombre y precio)
$stmt = $pdo->query("SELECT id_producto, nombre, precio FROM productos");
$productosList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper para obtener precio por id_producto
function obtener_precio(PDO $pdo, $id_producto) {
    try {
        $s = $pdo->prepare("SELECT precio FROM productos WHERE id_producto = :id_producto LIMIT 1");
        $s->execute([':id_producto' => $id_producto]);
        $f = $s->fetch(PDO::FETCH_ASSOC);
        return $f ? (float)$f['precio'] : 0.0;
    } catch (Exception $e) {
        return 0.0;
    }
}

// Helper: obtiene id_usuario fiable desde la sesión
function obtener_id_usuario_sesion(PDO $pdo) {
    if (isset($_SESSION['id_usuario']) && (int)$_SESSION['id_usuario'] > 0) {
        return (int)$_SESSION['id_usuario'];
    }
    if (isset($_SESSION['user_correo'])) {
        $s = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1");
        $s->execute([':correo' => $_SESSION['user_correo']]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if ($r) return (int)$r['id_usuario'];
    }
    if (isset($_SESSION['correo'])) {
        $s = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1");
        $s->execute([':correo' => $_SESSION['correo']]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if ($r) return (int)$r['id_usuario'];
    }
    return null;
}

// Inicializar el arreglo de selects y flags conforme a la sesión/BD
$selects = [];    // cada elemento será id_producto seleccionado o '' para vacío
$registered = []; // cada elemento será 1 si ese select ya está registrado en la orden, 0 si es "por registrar"

// Si hay una orden pendiente vinculada a la mesa, cargarla y reconstruir selects (registrados)
if ($ordenIdEnSesion) {
    // Obtener detalle_orden (id_producto, cantidad)
    $stmtDO = $pdo->prepare("SELECT id_producto, cantidad FROM detalle_orden WHERE id_orden = :id_orden");
    $stmtDO->execute([':id_orden' => $ordenIdEnSesion]);
    $detalles = $stmtDO->fetchAll(PDO::FETCH_ASSOC);

    // Reconstruir selects repitiendo el producto tantas veces como su cantidad
    foreach ($detalles as $d) {
        $cantidad = (int)$d['cantidad'];
        for ($i = 0; $i < $cantidad; $i++) {
            $selects[] = $d['id_producto'];
            $registered[] = 1; // proviene de DB => ya registrado
        }
    }
    // Si por alguna razón no hay detalles, dejar un select vacío mínimo (no registrado)
    if (count($selects) === 0) {
        $selects[] = '';
        $registered[] = 0;
    }
} else {
    // Pantalla por defecto: un solo select vacío (no registrado)
    $selects[] = '';
    $registered[] = 0;
}

// Manejo de POST para agregar, eliminar o enviar orden
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Reconstruir selects desde lo enviado (si existe)
    if (isset($_POST['producto']) && is_array($_POST['producto'])) {
        $posted = array_values($_POST['producto']);
    } else {
        $posted = [];
    }
    // Reconstruir registered flags enviados desde el formulario
    if (isset($_POST['registered']) && is_array($_POST['registered'])) {
        // normalizar a valores 1/0
        $postedRegistered = array_map(function($v){ return ($v === '1' || $v === 1 || $v === 'true') ? 1 : 0; }, array_values($_POST['registered']));
    } else {
        $postedRegistered = [];
    }

    // Leer acción enviada
    $accionRaw = isset($_POST['accion']) ? $_POST['accion'] : '';
    $accion = $accionRaw;
    $eliminarIndex = null;
    // Soportar valor "eliminar:INDEX"
    if (strpos($accionRaw, 'eliminar:') === 0) {
        $accion = 'eliminar';
        $eliminarIndex = (int)substr($accionRaw, strlen('eliminar:'));
    }

    // Debug mínimo
    $debug .= "POST accion={$accionRaw}; producto=" . json_encode($posted) . "; registered=" . json_encode($postedRegistered) . "; ";

    if ($accion === 'agregar') {
        // Conservar los existentes y añadir un nuevo select vacío (no registrado)
        $selects = $posted;
        $registered = $postedRegistered;
        $selects[] = '';
        $registered[] = 0;
    } elseif ($accion === 'eliminar') {
        // Eliminar index tanto en selects como en registered
        if ($eliminarIndex === null && isset($_POST['eliminar_index'])) {
            $eliminarIndex = (int)$_POST['eliminar_index'];
        }
        $selects = $posted;
        $registered = $postedRegistered;
        if ($eliminarIndex !== null && isset($selects[$eliminarIndex])) {
            array_splice($selects, $eliminarIndex, 1);
            // mantener parity
            if (isset($registered[$eliminarIndex])) array_splice($registered, $eliminarIndex, 1);
        }
        if (count($selects) === 0) {
            $selects[] = '';
            $registered[] = 0;
        }
    } elseif ($accion === 'enviar') {
        // Guardar selects y registered
        $selects = $posted;
        $registered = $postedRegistered;
        if (count($selects) === 0) {
            $message = "No hay productos seleccionados.";
        } else {
            // Validar que no haya vacíos
            $hayVacio = false;
            foreach ($selects as $s) {
                if ($s === '' || $s === null) { $hayVacio = true; break; }
            }
            if ($hayVacio) {
                $message = "Debe seleccionar un producto en cada lista antes de enviar la orden.";
            } else {
                // Consolidar conteo por producto (nuevo estado deseado)
                $nuevosCounts = [];
                foreach ($selects as $s) {
                    $pid = (int)$s;
                    if ($pid <= 0) continue;
                    if (!isset($nuevosCounts[$pid])) $nuevosCounts[$pid] = 0;
                    $nuevosCounts[$pid]++;
                }

                // Obtener id_usuario fiable
                $id_usuario = obtener_id_usuario_sesion($pdo);
                $debug .= "id_usuario_resuelto=" . var_export($id_usuario, true) . "; ";

                if (!$id_usuario) {
                    $message = "No se pudo identificar el usuario. Inicie sesión nuevamente.";
                } else {
                    try {
                        $pdo->beginTransaction();

                        // Si no existe orden en sesión, crear una nueva
                        if (!$ordenIdEnSesion) {
                            $stmtIns = $pdo->prepare("INSERT INTO ordenes (id_mesa, id_usuario, fecha, estado) VALUES (:id_mesa, :id_usuario, NOW(), 'pendiente')");
                            $stmtIns->execute([':id_mesa' => $mesaId, ':id_usuario' => $id_usuario]);
                            $ordenIdEnSesion = (int)$pdo->lastInsertId();
                            $_SESSION['selected_orden_id'] = $ordenIdEnSesion;
                            $debug .= "orden_creada_id=" . $ordenIdEnSesion . "; ";
                        }

                        // Cargar detalle actual para comparar (id_producto => cantidad)
                        $stmtOld = $pdo->prepare("SELECT id_producto, cantidad FROM detalle_orden WHERE id_orden = :id_orden");
                        $stmtOld->execute([':id_orden' => $ordenIdEnSesion]);
                        $oldRows = $stmtOld->fetchAll(PDO::FETCH_ASSOC);
                        $oldCounts = [];
                        foreach ($oldRows as $r) $oldCounts[(int)$r['id_producto']] = (int)$r['cantidad'];

                        // Procesar nuevosCounts: actualizar/insertar
                        foreach ($nuevosCounts as $pid => $newQty) {
                            $price = obtener_precio($pdo, $pid);
                            $subtotal = $price * $newQty;

                            if (isset($oldCounts[$pid])) {
                                // actualizar cantidad y subtotal
                                $stmtUpd = $pdo->prepare("UPDATE detalle_orden SET cantidad = :cantidad, subtotal = :subtotal WHERE id_orden = :id_orden AND id_producto = :id_producto");
                                $stmtUpd->execute([
                                    ':cantidad' => $newQty,
                                    ':subtotal' => $subtotal,
                                    ':id_orden' => $ordenIdEnSesion,
                                    ':id_producto' => $pid
                                ]);
                                unset($oldCounts[$pid]); // procesado
                            } else {
                                // insertar nuevo
                                $stmtInsD = $pdo->prepare("INSERT INTO detalle_orden (id_orden, id_producto, cantidad, subtotal) VALUES (:id_orden, :id_producto, :cantidad, :subtotal)");
                                $stmtInsD->execute([
                                    ':id_orden' => $ordenIdEnSesion,
                                    ':id_producto' => $pid,
                                    ':cantidad' => $newQty,
                                    ':subtotal' => $subtotal
                                ]);
                            }
                        }

                        // Los que quedaron en oldCounts ya no están en la nueva selección: eliminarlos
                        foreach ($oldCounts as $pid => $oldQty) {
                            $stmtDel = $pdo->prepare("DELETE FROM detalle_orden WHERE id_orden = :id_orden AND id_producto = :id_producto");
                            $stmtDel->execute([':id_orden' => $ordenIdEnSesion, ':id_producto' => $pid]);
                        }

                        // Asegurar que la mesa quede OCUPADA
                        $stmtMesa = $pdo->prepare("UPDATE mesas SET estado_mesa = 'OCUPADA' WHERE id_mesa = :id_mesa");
                        $stmtMesa->execute([':id_mesa' => $mesaId]);
                        $_SESSION['selected_mesa_estado'] = 'OCUPADA';

                        // Obtener estado actual de la orden para mostrar
                        $stmtEstado = $pdo->prepare("SELECT estado FROM ordenes WHERE id_orden = :id_orden LIMIT 1");
                        $stmtEstado->execute([':id_orden' => $ordenIdEnSesion]);
                        $rEstado = $stmtEstado->fetch(PDO::FETCH_ASSOC);
                        $orderEstadoMsg = $rEstado ? $rEstado['estado'] : 'pendiente';

                        $pdo->commit();

                        $successMessage = "Orden Entregada: " . $orderEstadoMsg;

                        // IMPORTANTE: tras registrar la orden marcamos todas las selects actuales como registradas
                        // para que sus botones ELIMINAR se oculten (según tu requisito).
                        $registered = array_fill(0, count($selects), 1);
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = "Error al procesar la orden: " . $e->getMessage();
                        $debug .= "exception: " . $e->getMessage();
                    }
                }
            }
        }
    } else {
        // ninguna acción reconocida -> conservar lo enviado (y sus flags)
        $selects = $posted;
        $registered = $postedRegistered;
        if (count($selects) === 0) {
            $selects[] = '';
            $registered[] = 0;
        }
    }
}

// Para mostrar el número de mesa en la vista
$numeroMesaMostrar = $mesaId;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOFTDINNER-ORDENES</title>
    <link rel="stylesheet" href="ORDENES_MAIN_D.css">
</head>

<body>

    <!-- ------------------- TÍTULO PRINCIPAL ------------------- -->
    <header>
        <h1>ÓRDENES</h1>
    </header>

    <?php if ($message): ?>
        <p style="color:red; font-weight:bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <p style="color:green; font-weight:bold;"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <!-- ------------------- MESA SELECCIONADA ------------------- -->
    <section id="mesaInfo">
        <h2>
            MESA 
            <span id="numeroMesa"><?php echo htmlspecialchars($numeroMesaMostrar); ?></span>
        </h2>
    </section>

    <!-- ------------------- FORMULARIO DE ÓRDENES ------------------- -->
    <section id="ordenesContainer">
        <form method="post">
            <?php foreach ($selects as $index => $selectedPid): ?>
                <div class="grupo" style="margin-bottom:12px;">
                    <label class="tituloCampo">Producto <?php echo $index + 1; ?></label>
                    <select name="producto[]" style="width:60%; height:36px; font-size:16px;">
                        <option style="text-align: center;" value="">-- Seleccione --</option>
                        <?php foreach ($productosList as $p): ?>
                            <option value="<?php echo $p['id_producto']; ?>" <?php if ((string)$p['id_producto'] === (string)$selectedPid) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($p['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- input oculto que indica si este select ya está registrado (1) o es por registrar (0) -->
                    <input type="hidden" name="registered[]" value="<?php echo isset($registered[$index]) && $registered[$index] == 1 ? '1' : '0'; ?>">

                    <div class="botonesAccion" style="display:inline-block; margin-left:12px;">
                        

                        <!-- ELIMINAR solo visible cuando registered == 0 (producto por registrar) -->
                        <?php if (!isset($registered[$index]) || $registered[$index] == 0): ?>
                            <button type="submit" class="btnEditar" name="accion" value="<?php echo 'eliminar:' . $index; ?>">ELIMINAR</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top:18px;">
                <button type="submit" id="btnEnviarOrdenes"name="accion" value="agregar">Agregar Producto</button>
                <button type="submit" id="btnEnviarOrdenes" name="accion" value="enviar">ENVIAR ORDENES</button>
                <button type="button" id="btnEnviarOrdenes"onclick="window.location.href='3VerOrden.php'">VER ORDEN</button>
            </div>
        </form>
    </section>

    <!-- ------------------- BOTÓN REGRESAR ------------------- -->
    <footer>
        <button id ="btnRegresar" type="button" style = "display: inline-block; margin: 8px auto 68px auto; background: transparent; color: var(--blanco); padding: 12px 30px; border-radius: 24px; border: 2px solid rgba(255,255,255,0.85); font-size: 16px; font-weight: 700; box-shadow: 0 6px 12px rgba(0,0,0,0.45); cursor: pointer; backdrop-filter: blur(2px);" onclick="window.location.href='1SeleccionOrdenMesa.php'">REGRESAR</button>
    </footer>

</body>
</html>