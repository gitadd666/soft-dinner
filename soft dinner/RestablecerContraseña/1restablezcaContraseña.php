<?php 
session_start();

$imagen = "Imagenes/MantelImagen.jpg";
$usuario = "Usuario";
?>

<?php 

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

$errorEmail = false;
$correoValor = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoValor = isset($_POST['correo']) ? trim($_POST['correo']) : '';

    if ($correoValor === '') {
        $errorEmail = true;
    } else {
        // comprobar si el correo existe en la BD
        $sql = "SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correoValor]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            // correo no registrado
            $errorEmail = true;
        } else {
            // correo registrado: generar código y reenviar mediante POST a 2verificacion.php
            $codigo = random_int(100000, 999999);
            // crear formulario oculto que se auto-envía para mantener POST
            echo '<!DOCTYPE html><html><body>';
            echo '<form id="forwardForm" action="2verificacion.php" method="post">';
            echo '<input type="hidden" name="correo" value="'.htmlspecialchars($correoValor, ENT_QUOTES).'">';
            echo '<input type="hidden" name="codigo" value="'.htmlspecialchars($codigo, ENT_QUOTES).'">';
            echo '</form>';
            echo '<script>document.getElementById("forwardForm").submit();</script>';
            echo '</body></html>';
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>REESTABLECER CONTRASEÑA - SOFT DINNER</title>
    <link rel="stylesheet" href="restablezcaContraseñaBonito.css">
    <style>
      .error-label { color: red; display: block; margin-top: 8px; font-weight: bold; }
    </style>
  </head>


<body>
    <div class="cajaRestablecer">
    <h1>Reestablezca su contraseña</h1>

    <form action="1restablezcaContraseña.php" method="post">

      <label for="correo">Correo electrónico:</label>
      <input type="email" id="correo" name="correo" placeholder="Ingresa tu correo" value="<?php echo htmlspecialchars($correoValor); ?>" required> 
      <?php if ($errorEmail): ?>
        <label class="error-label">Esta correo no se encuentra registrado.</label>
      <?php endif; ?>     
      
      <button type="submit">Confirmar</button>

    </form>
  </div>
  </body>
</html>