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

// Variables de salida
$ganancia_mes = '';
$ganancia_semana = '';
$ganancia_dia = '';
$messageError = '';

// Leer parámetros year y month enviados por 1GastosPag.php
$y = isset($_GET['y']) ? intval($_GET['y']) : null;
$m = isset($_GET['m']) ? intval($_GET['m']) : null;

if ($y && $m && $m >= 1 && $m <= 12) {
    // Buscar registro en gastos_extra para ese mes
    $stmt = $pdo->prepare("SELECT total FROM gastos_extra WHERE YEAR(fecha)=:y AND MONTH(fecha)=:m LIMIT 1");
    $stmt->execute([':y' => $y, ':m' => $m]);
    $gasto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gasto) {
        // Si por alguna razón no existe registro, mostrar error (aunque 1GastosPag evita la navegación)
        $messageError = "No hay datos disponibles";
    } else {
        $totalRegistro = floatval($gasto['total']);

        // Sumar totales de recibos en ese mes
        $stmt2 = $pdo->prepare("SELECT SUM(total) AS total_recibos FROM recibos WHERE YEAR(fecha)=:y AND MONTH(fecha)=:m");
        $stmt2->execute([':y' => $y, ':m' => $m]);
        $sumRow = $stmt2->fetch(PDO::FETCH_ASSOC);
        $totalRecibos = floatval($sumRow['total_recibos'] ?? 0);

        // Calculos
        $ganancia_mes_val = $totalRecibos - $totalRegistro;
        $ganancia_semana_val = $ganancia_mes_val / 4;
        // dias del mes
        $dias_mes = cal_days_in_month(CAL_GREGORIAN, $m, $y);
        $ganancia_dia_val = $dias_mes > 0 ? ($ganancia_mes_val / $dias_mes) : 0;

        // Formatear con 2 decimales y punto decimal
        $ganancia_mes = number_format($ganancia_mes_val, 2, '.', '');
        $ganancia_semana = number_format($ganancia_semana_val, 2, '.', '');
        $ganancia_dia = number_format($ganancia_dia_val, 2, '.', '');
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganancias y Promedios - PC</title>
    <style>
        /* --- CONFIGURACIÓN BASE --- */
        :root {
            --primary-red: #cc0000; /* Rojo base de la imagen */
            --input-bg: #ffffff;
            --text-stroke-color: #000;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif; /* Fuente sans-serif simple como en la imagen */
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center; /* Centrado vertical */
            padding: 20px;

            /* FONDO PLAID (Cuadros Escoceses) - Ajustado para parecerse a la imagen */
            background-color: #990000;
            background-image: 
                linear-gradient(90deg, rgba(200,0,0,0.5) 50%, transparent 50%),
                linear-gradient(rgba(200,0,0,0.5) 50%, transparent 50%);
            background-size: 80px 80px;
            box-shadow: inset 0 0 100px rgba(0,0,0,0.8); /* Viñeta oscura en los bordes */
        }

        /* --- CONTENEDOR PRINCIPAL CENTRADO EN PANTALLA --- */
        .main-dashboard {
            width: 100vw;
            height: 100vh; /* ocupar toda la altura para centrar en el medio exacto */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center; /* centra todo el contenido en el medio de la pantalla */
            gap: 20px;
            padding: 20px;
        }

        /* --- HEADER OVALADO (Flotante al centro) --- */
        .header-oval-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            width: 100%;
        }

        .header-oval {
            background-color: #ff0000;
            border: 3px solid black;
            border-radius: 50%; /* Forma ovalada */
            padding: 10px 60px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 70px;
        }

        .header-text {
            color: white;
            font-size: 32px;
            font-weight: 900;
            -webkit-text-stroke: 1.5px black; 
            letter-spacing: 1px;
            font-family: 'Verdana', sans-serif;
        }

        /* --- COLUMNAS (PANEL) --- */
        .column {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
            width: 100%;
            max-width: 520px; /* limita ancho para que quede centrado visualmente */
        }

        .section-title {
            color: white;
            font-size: 32px;
            font-weight: 900;
            text-align: center;
            -webkit-text-stroke: 1.5px black;
            text-shadow: 3px 3px 0 #000;
            margin-bottom: 15px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
            align-items: center;
        }

        .label-text {
            color: white; /* Texto blanco */
            font-size: 18px;
            font-weight: 900;
            -webkit-text-stroke: 0.8px black; 
            text-shadow: 2px 2px 0 black;
            text-align: center;
            width: 100%;
        }

        .custom-input {
            display: block;
            margin: 0;
            width: 100%;
            background-color: white;
            border-radius: 12px; /* Bordes redondeados como en la imagen */
            padding: 15px;
            font-size: 16px;
            border: none;
            outline: none;
            box-shadow: 0 4px 0 rgba(0,0,0,0.2);
            color: #333;
            text-align: center;
        }

        .message-error { color:#ffdddd; background:#b80000; padding:8px 12px; border-radius:8px; font-weight:800; text-align:center; max-width:520px; width:100%; }

    </style>
</head>
<body>

    <div class="main-dashboard">

        <div class="header-oval-container">
            <div class="header-oval">
                <span class="header-text">Ganancias</span>
            </div>
        </div>

        <?php if ($messageError): ?>
            <div class="message-error"><?php echo htmlspecialchars($messageError); ?></div>
        <?php endif; ?>

        <div class="column">
        
            

            <div class="input-group">
                <label class="label-text">Ganancias en el día:</label>
                <input type="text" class="custom-input" placeholder="Ganancia total en el dia" value="<?php echo htmlspecialchars($ganancia_dia); ?>">
            </div>

            <div class="input-group">
                <label class="label-text">Ganancias en la semana:</label>
                <input type="text" class="custom-input" placeholder="Ganancia total en la semana" value="<?php echo htmlspecialchars($ganancia_semana); ?>">
            </div>

            <div class="input-group">
                <label class="label-text">Ganancias en el mes:</label>
                <input type="text" class="custom-input" placeholder="Ganancia total en el mes" value="<?php echo htmlspecialchars($ganancia_mes); ?>">
            </div>

        </div>

        
        <div style="text-align:center; margin-top:16px;">
            <a href="1GastosPag.php" style="background:#ffffff; border:3px solid #000; padding:10px 18px; border-radius:12px; text-decoration:none; color:#000; font-weight:700; box-shadow:3px 3px 0 rgba(0,0,0,0.2);">Regresar</a>
        </div>

    </div>

</body>
</html>