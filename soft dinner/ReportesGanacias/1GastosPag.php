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

// Variables de formulario / mensajes
$messageError = '';
$messageSuccess = '';
$fecha = '';
$luz = $agua = $renta = $salarios = $insumos = '';

// Helper para obtener YYYY-MM desde fecha
function yyyymm($date) {
    return date('Y-m', strtotime($date));
}

// Función para obtener el menor id disponible (rellena huecos)
function siguienteIdDisponible(PDO $pdo) {
    $stmt = $pdo->query("SELECT id_gastos FROM gastos_extra ORDER BY id_gastos ASC");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $next = 1;
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id === $next) {
            $next++;
        } elseif ($id > $next) {
            break;
        }
    }
    return $next;
}

// Procesamiento de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer valores
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
    $luz = isset($_POST['luz']) ? trim($_POST['luz']) : '';
    $agua = isset($_POST['agua']) ? trim($_POST['agua']) : '';
    $renta = isset($_POST['renta']) ? trim($_POST['renta']) : '';
    $salarios = isset($_POST['salarios']) ? trim($_POST['salarios']) : '';
    $insumos = isset($_POST['insumos']) ? trim($_POST['insumos']) : '';

    // Validar fecha formato aceptado (HTML date produce YYYY-MM-DD)
    if ($fecha === '') {
        $messageError = "Favor de ingresar una Fecha";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $messageError = "Formato de fecha inválido. Use YYYY-MM-DD";
    } else {
        // action: guardar, consultar o ver ganancias
        $inputYM = yyyymm($fecha);
        $year = date('Y', strtotime($fecha));
        $month = date('n', strtotime($fecha)); // sin cero a la izquierda

        if ($action === 'verganacias') {
            // Verificar si existe registro en gastos_extra para el año y mes seleccionados
            $stmt = $pdo->prepare("SELECT id_gastos FROM gastos_extra WHERE YEAR(fecha)=:y AND MONTH(fecha)=:m LIMIT 1");
            $stmt->execute([':y' => $year, ':m' => $month]);
            $rowExist = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($rowExist) {
                // Redirigir a la página de ganancias pasando año y mes
                header('Location: 2GanaciasPag.php?y=' . intval($year) . '&m=' . intval($month));
                exit;
            } else {
                // No permitir navegación y mostrar mensaje de error
                $messageError = "No hay datos disponibles";
            }
        } elseif ($action === 'consultar') {
            // Buscar registro para mismo año-mes
            $stmt = $pdo->prepare("SELECT * FROM gastos_extra WHERE YEAR(fecha)=:y AND MONTH(fecha)=:m LIMIT 1");
            $stmt->execute([':y' => $year, ':m' => $month]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                // Poblar campos para mostrar
                $luz = $row['luz'];
                $agua = $row['agua'];
                $renta = $row['renta'];
                $salarios = $row['salarios'];
                $insumos = $row['insumos'];
                $messageSuccess = "Registro encontrado para " . $inputYM;
            } else {
                $messageError = "Registro Inexistente";
            }
        } elseif ($action === 'guardar') {
            // Validar campos numéricos (no vacíos)
            $campos = ['luz'=>$luz,'agua'=>$agua,'renta'=>$renta,'salarios'=>$salarios,'insumos'=>$insumos];
            $falta = false;
            foreach ($campos as $k => $v) {
                if ($v === '' || !is_numeric($v)) { $falta = true; break; }
            }
            if ($falta) {
                $messageError = "Complete todos los campos numéricos correctamente antes de guardar.";
            } else {
                // Revisar si ya existe registro para ese mes
                $stmtExist = $pdo->prepare("SELECT id_gastos FROM gastos_extra WHERE YEAR(fecha)=:y AND MONTH(fecha)=:m LIMIT 1");
                $stmtExist->execute([':y'=> $year, ':m'=> $month]);
                $existRow = $stmtExist->fetch(PDO::FETCH_ASSOC);

                if ($existRow) {
                    // Actualizar el registro existente (usar la misma fila)
                    $stmtUpd = $pdo->prepare("UPDATE gastos_extra SET luz=:luz, agua=:agua, renta=:renta, salarios=:salarios, insumos=:insumos, fecha=:fecha WHERE id_gastos = :id_gastos");
                    $stmtUpd->execute([
                        ':luz' => $luz,
                        ':agua' => $agua,
                        ':renta' => $renta,
                        ':salarios' => $salarios,
                        ':insumos' => $insumos,
                        ':fecha' => $fecha,
                        ':id_gastos' => $existRow['id_gastos']
                    ]);
                    $messageSuccess = "Registro actualizado para " . $inputYM;

                    // Vaciar campos después de actualizar
                    $fecha = $luz = $agua = $renta = $salarios = $insumos = '';
                } else {
                    // Decidir si se permite insertar nuevo mes según regla de contigüidad
                    // Obtener min y max mes existentes
                    $stmtMinMax = $pdo->query("SELECT MIN(fecha) AS minf, MAX(fecha) AS maxf FROM gastos_extra");
                    $mm = $stmtMinMax->fetch(PDO::FETCH_ASSOC);
                    $minf = $mm['minf'];
                    $maxf = $mm['maxf'];

                    if (!$minf) {
                        // Sin registros previos: permitir cualquier mes
                        $canInsert = true;
                    } else {
                        // calcular YYYY-MM
                        $minYM = yyyymm($minf);
                        $maxYM = yyyymm($maxf);

                        // calcular mes anterior a min y mes siguiente a max
                        $dtMin = new DateTime($minYM . '-01');
                        $dtMin->modify('-1 month');
                        $prevOfMin = $dtMin->format('Y-m');

                        $dtMax = new DateTime($maxYM . '-01');
                        $dtMax->modify('+1 month');
                        $nextOfMax = $dtMax->format('Y-m');

                        // Permitir únicamente si inputYM == prevOfMin OR inputYM == nextOfMax
                        if ($inputYM === $prevOfMin || $inputYM === $nextOfMax) {
                            $canInsert = true;
                        } else {
                            $canInsert = false;
                        }
                    }

                    if (!$canInsert) {
                        $messageError = "No se puede registrar ese mes. Debe registrar el mes contiguo al rango existente (añadir al inicio o al final).";
                    } else {
                        // Obtener primer id disponible (rellenar huecos)
                        $newId = siguienteIdDisponible($pdo);

                        // Insertar nuevo registro con id específico para rellenar huecos
                        $stmtIns = $pdo->prepare("INSERT INTO gastos_extra (id_gastos, luz, agua, renta, salarios, insumos, fecha) VALUES (:id_gastos, :luz, :agua, :renta, :salarios, :insumos, :fecha)");
                        $stmtIns->execute([
                            ':id_gastos' => $newId,
                            ':luz' => $luz,
                            ':agua' => $agua,
                            ':renta' => $renta,
                            ':salarios' => $salarios,
                            ':insumos' => $insumos,
                            ':fecha' => $fecha
                        ]);
                        $messageSuccess = "Registro creado para " . $inputYM . " con id " . $newId;

                        // Vaciar campos después de insertar
                        $fecha = $luz = $agua = $renta = $salarios = $insumos = '';
                    }
                }
            }
        } 
    }
}

// Si se cargó por consulta o actualización, queremos que el campo fecha mantenga el valor mostrado.
// Variables $fecha, $luz... ya contienen valores.

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Gastos - PC</title>
    <style>
        /* --- VARIABLES Y CONFIGURACIÓN --- */
        :root {
            --primary-red: #cc0000;
            --border-color: #000;
            --input-radius: 12px;
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
            justify-content: center;
            align-items: center;
            padding: 40px;

            /* FONDO PLAID (Igual que tus diseños anteriores) */
            background-color: var(--primary-red);
            background-image: 
                repeating-linear-gradient(45deg, rgba(0,0,0,0.15) 25%, transparent 25%, transparent 75%, rgba(0,0,0,0.15) 75%, rgba(0,0,0,0.15)),
                repeating-linear-gradient(45deg, rgba(0,0,0,0.15) 25%, transparent 25%, transparent 75%, rgba(0,0,0,0.15) 75%, rgba(0,0,0,0.15));
            background-position: 0 0, 10px 10px;
            background-size: 30px 30px;
            box-shadow: inset 0 0 150px rgba(0,0,0,0.7);
        }

        /* --- CONTENEDOR PRINCIPAL --- */
        .main-panel {
            background-color: rgba(100, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            width: 100%;
            max-width: 900px;
            border: 6px solid black;
            border-radius: 30px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 20px 20px 0 rgba(0,0,0,0.4);
            position: relative;
        }

        .header-oval {
            background-color: #ff0000;
            border: 4px solid black;
            border-radius: 50px;
            padding: 10px 60px;
            margin-top: -70px;
            margin-bottom: 20px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.3);
        }

        .header-text {
            color: white;
            font-size: 36px;
            font-weight: 900;
            -webkit-text-stroke: 1.5px black;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sub-title {
            font-size: 48px;
            color: white;
            font-weight: 900;
            -webkit-text-stroke: 2px black;
            text-shadow: 4px 4px 0 #000;
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px 40px;
            width: 100%;
            margin-bottom: 20px;
        }

        .full-width { grid-column: span 2; display:flex; flex-direction:column; align-items:center; }

        .input-group { display:flex; flex-direction:column; gap:8px; }

        .label-text {
            color: white;
            font-size: 18px;
            font-weight: 800;
            text-shadow: 2px 2px 0 #000;
            -webkit-text-stroke: 0.5px black;
        }

        .input-field {
            width: 100%;
            padding: 12px 15px;
            font-size: 18px;
            border: 3px solid black;
            border-radius: var(--input-radius);
            outline: none;
            transition: transform 0.2s, box-shadow 0.2s;
            background:white;
        }

        .input-field:focus {
            transform: scale(1.02);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }

        .actions-container { display:flex; gap:20px; width:100%; justify-content:center; margin-top:8px; }
        .action-btn {
            background-color: white;
            border: 3px solid black;
            border-radius: 20px;
            padding: 12px 26px;
            font-size: 18px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 6px 6px 0 rgba(0,0,0,0.3);
            transition: all 0.2s;
        }
        .action-btn:hover { transform: translateY(-3px); }

        .message-error { color:#ffdddd; background:#b80000; padding:8px 12px; border-radius:8px; font-weight:800; }
        .message-success { color:#084; background:#dff0df; padding:8px 12px; border-radius:8px; font-weight:800; }

        .back-btn { background: white; border: 3px solid black; padding: 12px 26px; border-radius: 20px; text-decoration: none; color: black; font-weight: 900; box-shadow: 6px 6px 0 rgba(0,0,0,0.3); }

    </style>
</head>
<body>

    <main class="main-panel">
        <div class="header-oval"><span class="header-text">Ganancias</span></div>
        <h1 class="sub-title">Gastos Extra</h1>

        <!-- Mensajes -->
        <?php if ($messageError): ?><div style="width:100%; margin-bottom:12px;" class="message-error"><?php echo htmlspecialchars($messageError); ?></div><?php endif; ?>
        <?php if ($messageSuccess): ?><div style="width:100%; margin-bottom:12px;" class="message-success"><?php echo htmlspecialchars($messageSuccess); ?></div><?php endif; ?>

        <form method="post" class="form-grid" novalidate>
            <div class="input-group full-width">
                <label class="label-text">Ingrese una Fecha</label>
                <!-- date input: user selects day but we use year-month for matching -->
                <input type="date" name="fecha" class="input-field" style="text-align:center; width:50%;" value="<?php echo htmlspecialchars($fecha); ?>" required>
            </div>

            <div class="input-group">
                <label class="label-text">Ingrese Luz Mensual:</label>
                <input type="number" step="0.01" min="0" name="luz" class="input-field" placeholder="$ 0.00" value="<?php echo htmlspecialchars($luz); ?>" required>
            </div>

            <div class="input-group">
                <label class="label-text">Ingrese Salarios Mensual:</label>
                <input type="number" step="0.01" min="0" name="salarios" class="input-field" placeholder="$ 0.00" value="<?php echo htmlspecialchars($salarios); ?>" required>
            </div>

            <div class="input-group">
                <label class="label-text">Ingrese Agua Mensual:</label>
                <input type="number" step="0.01" min="0" name="agua" class="input-field" placeholder="$ 0.00" value="<?php echo htmlspecialchars($agua); ?>" required>
            </div>

            <div class="input-group">
                <label class="label-text">Ingrese Insumos Mensual:</label>
                <input type="number" step="0.01" min="0" name="insumos" class="input-field" placeholder="$ 0.00" value="<?php echo htmlspecialchars($insumos); ?>" required>
            </div>

            <div class="input-group">
                <label class="label-text">Ingrese Renta Mensual:</label>
                <input type="number" step="0.01" min="0" name="renta" class="input-field" placeholder="$ 0.00" value="<?php echo htmlspecialchars($renta); ?>" required>
            </div>

            <!-- Acciones -->
            <div class="full-width" style="display:flex; justify-content:center;">
                <div class="actions-container" style="max-width:760px;">
                    <button class="action-btn" type="submit" name="action" value="verganacias">Ver Ganacias</button>
                    <button class="action-btn" type="submit" name="action" value="guardar">Guardar</button>
                    <button class="action-btn" type="submit" name="action" value="consultar">Consultar</button>
                    <a class="action-btn back-btn" href="../PantallaDeInicio.php">Regresar</a>
                </div>
            </div>
        </form>

    </main>

</body>
</html>
