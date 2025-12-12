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

// determinar categoría (GET al entrar, POST al enviar)
$categoria = '';
if (!empty($_GET['categoria'])) {
    $categoria = $_GET['categoria'];
} elseif (!empty($_POST['categoria'])) {
    $categoria = $_POST['categoria'];
}

$savedCount = 0;
$errorMsg = '';

// proceso de guardado: recibimos arrays item[] y precio[]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save')) {
    $items = isset($_POST['item']) && is_array($_POST['item']) ? $_POST['item'] : [];
    $precios = isset($_POST['precio']) && is_array($_POST['precio']) ? $_POST['precio'] : [];

    // preparar statements
    $insertStmt = $pdo->prepare("INSERT INTO productos (nombre, precio, categoria) VALUES (:nombre, :precio, :categoria)");
    $insertWithIdStmt = $pdo->prepare("INSERT INTO productos (id_producto, nombre, precio, categoria) VALUES (:id, :nombre, :precio, :categoria)");

    // calcular IDs existentes y huecos (missing) entre 1..maxId
    $existing = [];
    $idsStmt = $pdo->query("SELECT id_producto FROM productos ORDER BY id_producto ASC");
    $existing = $idsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $existingSet = [];
    foreach ($existing as $v) $existingSet[(int)$v] = true;
    $maxId = count($existing) ? (int)max($existing) : 0;

    $missing = [];
    for ($i = 1; $i <= $maxId; $i++) {
        if (!isset($existingSet[$i])) $missing[] = $i;
    }

    $count = 0;
    $max = max(count($items), count($precios));
    for ($i = 0; $i < $max; $i++) {
        $nombre = isset($items[$i]) ? trim($items[$i]) : '';
        $precio = isset($precios[$i]) ? trim($precios[$i]) : '';

        // solo insertar si ambos campos existen y no están vacíos
        if ($nombre !== '' && $precio !== '') {
            $precio_normalizado = str_replace(',', '.', $precio);
            try {
                if (!empty($missing)) {
                    // usar el primer ID faltante
                    $useId = array_shift($missing);
                    $insertWithIdStmt->execute([
                        ':id' => $useId,
                        ':nombre' => $nombre,
                        ':precio' => $precio_normalizado,
                        ':categoria' => $categoria
                    ]);
                    // mantener maxId actualizado en caso de que useId > maxId
                    if ($useId > $maxId) $maxId = $useId;
                } else {
                    // no hay huecos: insertar normal (auto-increment)
                    $insertStmt->execute([
                        ':nombre' => $nombre,
                        ':precio' => $precio_normalizado,
                        ':categoria' => $categoria
                    ]);
                }
                $count++;
            } catch (Exception $e) {
                $errorMsg = 'Error al insertar algunos productos.';
            }
        }
    }

    $savedCount = $count;

    // redirigir para limpiar POST y dejar inputs vacíos (mantener la categoría en GET)
    header("Location: 2RegistroProducto.php?categoria=" . urlencode($categoria) . "&saved=" . $savedCount);
    exit;
}

// mostrar contador tras redirección si existe
if (!empty($_GET['saved'])) {
    $savedCount = (int)$_GET['saved'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOFTDINNER-REGISTRAR PRODUCTOS</title>
    <link rel="stylesheet" href="REGISTRO_PRODUCTOS_D.css">
    <style>
        /* Alineación centrada y filas más compactas */
        header, section { display:flex; flex-direction:column; align-items:center; }
        #categoriaSeleccionada { margin-top:8px; }
        .form-contenedor { width: 560px; max-width: 95%; }
        .fila { display:flex; gap:10px; align-items:center; margin-bottom:8px; }
        .fila label { flex: 1; text-align:left; font-weight:bold; }
        .fila input[type="text"] { flex: 1; padding:8px; border-radius:6px; border:1px solid rgba(0,0,0,0.12); }
        .fila .precio { width: 140px; flex: 0 0 140px; }
        footer { display:flex; justify-content:center; gap:12px; margin-top:14px; }
        footer button { padding:10px 16px; background:#1976D2; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:bold; }
    </style>
</head>
<body id="pantallaRegistro">

    <header>
        <h1>PRODUCTOS</h1>
        <h2 id="categoriaSeleccionada"><?php echo htmlspecialchars($categoria ?: 'Sin categoría seleccionada'); ?></h2>
    </header>

    <section>
        <div class="form-contenedor">
            <h3 style="text-align:left;">REGISTRAR</h3>

            <?php if ($savedCount > 0): ?>
                <p style="color:green; font-weight:bold;"><?php echo $savedCount; ?> producto(s) guardado(s) correctamente.</p>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <p style="color:red;"><?php echo htmlspecialchars($errorMsg); ?></p>
            <?php endif; ?>

            <form method="post" action="2RegistroProducto.php">
                <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria); ?>">

                <div class="fila">
                    <label>ITEM</label>
                    <label style="width:140px; text-align:left;">PRECIO</label>
                </div>

                <div class="fila">
                    <input name="item[]" id="item1" type="text" placeholder="EDITAR">
                    <input class="precio" name="precio[]" id="precio1" type="text" placeholder="EDITAR">
                </div>

                <div class="fila">
                    <input name="item[]" id="item2" type="text" placeholder="EDITAR">
                    <input class="precio" name="precio[]" id="precio2" type="text" placeholder="EDITAR">
                </div>

                <div class="fila">
                    <input name="item[]" id="item3" type="text" placeholder="EDITAR">
                    <input class="precio" name="precio[]" id="precio3" type="text" placeholder="EDITAR">
                </div>

                <div class="fila">
                    <input name="item[]" id="item4" type="text" placeholder="EDITAR">
                    <input class="precio" name="precio[]" id="precio4" type="text" placeholder="EDITAR">
                </div>

                <div class="fila">
                    <input name="item[]" id="item5" type="text" placeholder="EDITAR">
                    <input class="precio" name="precio[]" id="precio5" type="text" placeholder="EDITAR">
                </div>

                <footer>
                    <button type="submit" id="btnGuardar">GUARDAR</button>

                    <!-- Botón Editar con mismo diseño que Guardar -->
                    <button type="button" style = "display: inline-block; margin: 38px auto 68px auto; background: transparent; color: var(--blanco); padding: 12px 30px; border-radius: 24px; border: 2px solid rgba(255,255,255,0.85); font-size: 16px; font-weight: 700; box-shadow: 0 6px 12px rgba(0,0,0,0.45); cursor: pointer; backdrop-filter: blur(2px);" id="btnEditar" onclick="window.location.href='3EdicionProducto.php?categoria=<?php echo urlencode($categoria); ?>'">EDITAR</button>

                    <button type="button" id="btnRegresar" onclick="window.location.href='1CategoriaProducto.php'">REGRESAR</button>
                </footer>
            </form>
        </div>
    </section>

</body>
</html>