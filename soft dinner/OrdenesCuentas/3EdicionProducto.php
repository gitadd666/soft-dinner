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

// determinar categor�a (GET al entrar, POST al enviar)
$categoria = '';
if (!empty($_GET['categoria'])) {
    $categoria = $_GET['categoria'];
} elseif (!empty($_POST['categoria'])) {
    $categoria = $_POST['categoria'];
}

// Variables para renderizado
$rows_display = array_fill(0, 5, ['id_producto' => '', 'nombre' => '', 'precio' => '']);
$message = '';

// Acci�n: eliminar -> detectamos bot�n con name="delete_id"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    if ($delId > 0) {
        $delStmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = :id");
        $delStmt->execute([':id' => $delId]);
    }
    // recargar la p�gina para evitar repost
    header("Location: 3EdicionProducto.php?categoria=" . urlencode($categoria));
    exit;
}

// Acci�n: consultar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'consult') {
    $num = isset($_POST['consulta_num']) ? (int)$_POST['consulta_num'] : 0;
    if ($num <= 0) {
        $message = 'Introduce un numero valido.';
    } else {
        // obtener id_producto m�ximo existente
        $maxStmt = $pdo->query("SELECT MAX(id_producto) AS maxid FROM productos");
        $maxRow = $maxStmt->fetch(PDO::FETCH_ASSOC);
        $maxId = (int)($maxRow['maxid'] ?? 0);

        // determinar ventana de 5 registros: si num <=5 -> [1..num], si num>5 -> [num-4..num]
        $end = min($num, $maxId);
        if ($end < 1) {
            // no hay registros
            $rows_display = array_fill(0,5,['id_producto'=>'','nombre'=>'','precio'=>'']);
        } else {
            $start = max(1, $end - 4);
            if ($start > $end) $start = $end;

            $stmt = $pdo->prepare("SELECT id_producto, nombre, precio FROM productos WHERE id_producto BETWEEN :start AND :end ORDER BY id_producto ASC");
            $stmt->execute([':start' => $start, ':end' => $end]);
            $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

            for ($i = 0; $i < count($fetched) && $i < 5; $i++) {
                $rows_display[$i] = [
                    'id_producto' => $fetched[$i]['id_producto'],
                    'nombre' => $fetched[$i]['nombre'],
                    'precio' => $fetched[$i]['precio']
                ];
            }
        }
    }
}

// Acci�n: guardar (actualizar registros mostrados)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $ids = isset($_POST['id_producto']) && is_array($_POST['id_producto']) ? $_POST['id_producto'] : [];
    $items = isset($_POST['item']) && is_array($_POST['item']) ? $_POST['item'] : [];
    $precios = isset($_POST['precio']) && is_array($_POST['precio']) ? $_POST['precio'] : [];

    $updateStmt = $pdo->prepare("UPDATE productos SET nombre = :nombre, precio = :precio WHERE id_producto = :id");
    $updated = 0;
    for ($i = 0; $i < count($ids); $i++) {
        $id = trim($ids[$i]);
        $nombre = isset($items[$i]) ? trim($items[$i]) : '';
        $precio = isset($precios[$i]) ? trim($precios[$i]) : '';

        // solo actualizar si hay id y ambos campos no vac�os
        if ($id !== '' && $nombre !== '' && $precio !== '') {
            try {
                $updateStmt->execute([':nombre' => $nombre, ':precio' => $precio, ':id' => $id]);
                $updated++;
            } catch (Exception $e) {
                // ignorar por fila y continuar
            }
        }
    }

    $message = ($updated > 0) ? "$updated registro(s) actualizado(s)." : "No hay cambios para actualizar.";
    // recargar para mostrar mensaje limpio (evita repost)
    header("Location: 3EdicionProducto.php?categoria=" . urlencode($categoria) . "&msg=" . urlencode($message));
    exit;
}

// Si venimos de redirecci�n con mensaje
if (!empty($_GET['msg'])) {
    $message = $_GET['msg'];
}

// Helper para mostrar valor seguro en input
function esc($v) { return htmlspecialchars($v); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOFTDINNER-EDICI�N PRODUCTOS</title>
    <link rel="stylesheet" href="REGISTRO_PRODUCTOS_D.css">
    <style>
        /* Igualar dise�o a 2RegistroProducto.php */
        header, section { display:flex; flex-direction:column; align-items:center; }
        .form-contenedor { width: 560px; max-width:95%; }
        .fila { display:flex; gap:10px; align-items:center; margin-bottom:8px; }
        .fila label { flex: 1; text-align:left; font-weight:bold; }
        .fila input[type="text"] { flex: 1; padding:8px; border-radius:6px; border:1px solid rgba(0,0,0,0.12); }
        .precio { width:140px; flex:0 0 140px; }
        .row-actions { width:110px; display:flex; justify-content:flex-end; gap:8px; }
        .btn-small { padding:8px 10px; background:#e55353; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
        footer { display:flex; justify-content:center; gap:12px; margin-top:14px; }
        footer button { padding:10px 16px; background:#1976D2; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:bold; }
        .controls { display:flex; gap:8px; align-items:center; margin-bottom:10px; justify-content:flex-start; }
        .controls input[type="number"] { width:120px; padding:6px; border-radius:6px; border:1px solid rgba(0,0,0,0.12); }
        .message { margin:8px 0; color:green; font-weight:bold; text-align:center; }
        /* centrar filas */
        .form-contenedor .fila { justify-content: center; }
        .label-col { width:70%; text-align:left; }
    </style>
</head>
<body id="pantallaRegistro">

    <header>
        <h1>EDICION PRODUCTOS</h1>
        <h2 id="categoriaSeleccionada"><?php echo esc($categoria ?: 'Sin categor�a seleccionada'); ?></h2>
    </header>

    <section>
        <div class="form-contenedor">
            <?php if ($message): ?><p class="message"><?php echo esc($message); ?></p><?php endif; ?>

            <!-- Un �nico formulario para todas las acciones -->
            <form method="post" action="3EdicionProducto.php">
                <input type="hidden" name="categoria" value="<?php echo esc($categoria); ?>">

                <!-- N�mero de consulta -->
                <div class="controls" style="margin-bottom:12px;">
                    <label for="consulta_num">Numero:</label>
                    <input type="number" id="consulta_num" name="consulta_num" min="1" placeholder="Ej: 5">
                </div>

                <div class="fila">
                    <label class="label-col">ITEM</label>
                    <label style="width:140px; text-align:left;">PRECIO</label>
                    <div class="row-actions"></div>
                </div>

                <?php for ($i = 0; $i < 5; $i++):
                    $r = $rows_display[$i];
                    $hasRecord = !empty($r['id_producto']);
                ?>
                <div class="fila">
                    <input name="item[]" class="item-input" type="text" value="<?php echo esc($r['nombre']); ?>" placeholder="Item <?php echo $i+1; ?>">
                    <input name="precio[]" class="precio" type="text" value="<?php echo esc($r['precio']); ?>" placeholder="Precio">
                    <input type="hidden" name="id_producto[]" value="<?php echo esc($r['id_producto']); ?>">
                    <div class="row-actions">
                        <?php if ($hasRecord): ?>
                            <!-- bot�n de eliminar como parte del mismo formulario -->
                            <button class="btn-small" type="submit" name="delete_id" value="<?php echo esc($r['id_producto']); ?>" onclick="return confirm('Eliminar registro id <?php echo esc($r['id_producto']); ?>?')">Eliminar</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endfor; ?>

                <!-- Footer con los botones (mismo dise�o que 2RegistroProducto.php) -->
                <footer>
                    <button type="submit" style = "display: inline-block; margin: 38px auto 68px auto; background: transparent; color: var(--blanco); padding: 12px 30px; border-radius: 24px; border: 2px solid rgba(255,255,255,0.85); font-size: 16px; font-weight: 700; box-shadow: 0 6px 12px rgba(0,0,0,0.45); cursor: pointer; backdrop-filter: blur(2px);" name="action" value="save">GUARDAR</button>
                    <button type="submit" style = "display: inline-block; margin: 38px auto 68px auto; background: transparent; color: var(--blanco); padding: 12px 30px; border-radius: 24px; border: 2px solid rgba(255,255,255,0.85); font-size: 16px; font-weight: 700; box-shadow: 0 6px 12px rgba(0,0,0,0.45); cursor: pointer; backdrop-filter: blur(2px);" name="action" value="consult">CONSULTAR</button>
                    <button type="button" style = "display: inline-block; margin: 38px auto 68px auto; background: transparent; color: var(--blanco); padding: 12px 30px; border-radius: 24px; border: 2px solid rgba(255,255,255,0.85); font-size: 16px; font-weight: 700; box-shadow: 0 6px 12px rgba(0,0,0,0.45); cursor: pointer; backdrop-filter: blur(2px);" onclick="window.location.href='2RegistroProducto.php?categoria=<?php echo urlencode($categoria); ?>'">REGRESAR</button>
                </footer>
            </form>
        </div>
    </section>

</body>
</html>


