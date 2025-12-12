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

$errorMinLength = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevaContrasena1'])) {
    $pass = trim($_POST['nuevaContrasena1']);
    if (mb_strlen($pass) < 6) {
        $errorMinLength = true;
    } else {
        // guardar temporalmente la contraseña en sesión para su confirmación en el siguiente paso
        $_SESSION['new_password_plain'] = $pass;
        // redirigir al formulario de repetir contraseña
        header("Location: 4repitaNuevaContraseña.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

  <head>
    <meta charset="UTF-8">
    <title>REESTABLECER CONTRASEÑA - SOFT DINNER</title>
    <link rel="stylesheet" href="nuevaContraseñaBonita.css">
    <style>
      .error-label { color: red; display: block; margin-top: 8px; }
    </style>
  </head>

  <body>
    <div class="cajaNuevaContra">
    <h1>Nueva contraseña</h1>

    <form action="3nuevaContraseña.php" method="post">

      <label for="nuevaContra">Ingrese la nueva contraseña:</label>
      <input type="password" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 10px; font-size: 14px; box-sizing: border-box;" id="nuevaContra" name="nuevaContrasena1" placeholder="Ingrese la nueva contraseña" required>
      <?php if ($errorMinLength): ?>
        <label class="error-label">Contraseña minimo de 6 caracteres</label>
      <?php endif; ?>

      <button type="submit">Añadir nueva contraseña</button>

    </form>
  </div>
  </body>
</html>