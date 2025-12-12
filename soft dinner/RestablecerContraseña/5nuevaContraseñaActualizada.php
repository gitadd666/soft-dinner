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

?>

<!DOCTYPE html>
<html lang="es">

  <head>
    <meta charset="UTF-8">
    <title>REESTABLECER CONTRASEÑA - SOFT DINNER</title>
    <link rel="stylesheet" href="confirmacionNuevaContraseñaBonita.css">
  </head>

  <body>

    <div class="cajaConfirmacionNuevaContra">
    <h1>Nueva contraseña</h1>

    <p>Contraseña actualizada con éxito</p>

    </div>

    <button style="position: fixed; right: 20px; bottom: 20px; width: 170px; height: 50px; font-size:20px; font-weight:bold; color:#000; border:2px solid #000; border-radius:10px; text-align:center; line-height:50px; text-decoration:none; background:#F0F0F0;" onclick="window.location.href='../InicioSesion.php'">Iniciar Sesión</button>
  </body>
</html>