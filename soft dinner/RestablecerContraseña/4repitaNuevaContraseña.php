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

$errorMismatch = false;
$errorNoSession = false;

if (empty($_SESSION['new_password_plain'])) {
    $errorNoSession = true; // no hay contraseña previa (usuario saltó pasos)
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevaContrasena2']) && !$errorNoSession) {
    $pass2 = trim($_POST['nuevaContrasena2']);
    $pass1 = $_SESSION['new_password_plain'];

    if ($pass1 !== $pass2) {
        $errorMismatch = true;
    } else {
        // comprobar que exista correo en sesión desde el proceso de verificación
        if (empty($_SESSION['reset_correo'])) {
            // no hay correo, no podemos actualizar
            $errorNoSession = true;
        } else {
            // actualizar la contraseña en la base de datos (SEGURO: guardado en texto plano solicitado por el usuario)
            $correo = $_SESSION['reset_correo'];
            $sql = "UPDATE usuarios SET contrasena = :contrasena WHERE correo = :correo";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':contrasena' => $pass1, ':correo' => $correo]);

            // limpiar sesión de datos sensibles
            unset($_SESSION['new_password_plain']);
            unset($_SESSION['reset_codigo']);
            // redirigir a confirmación
            header("Location: 5nuevaContraseñaActualizada.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

  <head>
    <meta charset="UTF-8">
    <title>REESTABLECER CONTRASEÑA - SOFT DINNER</title>
    <link rel="stylesheet" href="repitaNuevaContraseñaBonita.css">
    <style>
      .error-label { color: red; display: block; margin-top: 8px; }
      .info-label { color: #333; display: block; margin-top: 8px; }
    </style>
  </head>

  <body>
    <div class="cajaRepitaNuevaContra">
    <h1>Nueva contraseña</h1>

    <?php if ($errorNoSession): ?>
      <p class="error-label">Error: falta información del proceso de restablecimiento. Vuelva a iniciar el proceso.</p>
    <?php else: ?>

    <form action="4repitaNuevaContraseña.php" method="post">

      <label for="nuevaContra">Ingrese nuevamente la nueva contraseña:</label>
      <input type="password" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 10px; font-size: 14px; box-sizing: border-box;" id="nuevaContra" name="nuevaContrasena2" placeholder="Ingrese la nueva contraseña" required>
      <?php if ($errorMismatch): ?>
        <label class="error-label">Las contraseñas no Coinciden.</label>
      <?php endif; ?>

      <button type="submit">Confirmar nueva contraseña</button>

    </form>

    <?php endif; ?>
  </div>
  </body>
</html>