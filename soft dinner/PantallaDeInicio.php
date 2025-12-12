<?php 
session_start();

$imagen = "Imagenes/MantelImagen.jpg";
$usuario = "Usuario";

// Si hay sesión de usuario, usar el nombre guardado en la sesión.
// Si no existe, mantener el valor por defecto o el valor enviado por POST (compatibilidad).
if (isset($_SESSION['user_nombre']) && $_SESSION['user_nombre'] !== '') {
    $usuario = $_SESSION['user_nombre'];
} elseif (isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Restaurante</title>
	<style> body {
            margin: 0;
            padding: 0;
            background-image: url('<?php echo $imagen; ?>');
            background-size: cover; /* ajusta la imagen a toda la pantalla */
            background-position: center; /* centra la imagen */
            background-repeat: no-repeat; /* evita que se repita */
        }
    </style>
</head>
<body> 

<h1 style="color: white; text-align: center; font-size:50px;"> Bienvenido <?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?> </h1>

<br>
<img src="Imagenes/IconoPlato.png" style="width: 200px; height: 200px; position: absolute; top: 120px; left: 660px;">

<div style="position: absolute; top: 350px; left: 675px;">
<a href="OrdenesCuentas/Cuentas.php" style="text-decoration:none;"><button type="button" style="margin:5px auto; width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Cuentas</button></a> <br>
<a href="OrdenesCuentas/1SeleccionOrdenMesa.php" style="text-decoration:none;"><button type="button" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Ordenes</button></a> <br>
<!-- Historial ya redirige a ReportesGanacias/Historial.php -->
<a href="ReportesGanacias/Historial.php" style="text-decoration:none;"><button type="button" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Historial</button></a> <br>
<!-- Ganancias ahora redirige a ReportesGanacias/1GastosPag.php -->
<a href="ReportesGanacias/1GastosPag.php" style="text-decoration:none;"><button type="button" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Ganancias</button></a> <br>
<!-- Botón Productos ahora dirige a la pantalla de categorías -->
<a href="OrdenesCuentas/1CategoriaProducto.php" style="text-decoration:none;"><button type="button" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Productos</button></a>
</div>

<a href="Index.php"style="position: absolute; top: 650px; left: 40px; width: 170px; height: 50px; font-size:20px; font-weight:bold; color:#000; border:2px solid #000; border-radius:10px; text-align:center; line-height:50px; text-decoration:none; background:#F0F0F0;"> Cerrar Sesion </a>

</body>
</html>





