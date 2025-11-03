
<?php 
$imagen = "Imagenes/MantelImagen.jpg";
$usuario = "Usuario"
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
<?php 
if (isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}

?>

<h1 style="color: white; text-align: center; font-size:50px;"> Bienvenido <?php echo $usuario; ?> </h1>

<form method="post">
<input type="text" name="usuario" placeholder="Escribe tu nombre">
<button type="submit" value="Enviar" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Iniciar Sesion</button>
</form>



<br>
<img src="Imagenes/IconoPlato.png" style="width: 200px; height: 200px; position: absolute; top: 120px; left: 660px;">

<div style="position: absolute; top: 350px; left: 675px;">
<button type="submit" style="margin:5px auto; width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Cuentas</button> <br>
<button type="submit" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Ordenes</button> <br>
<button type="submit" style="margin:5px auto; width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Historial</button> <br>
<button type="submit" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Ganancias</button> <br>
<button type="submit" style="margin:5px auto; width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Reportes</button> <br>
<button type="submit" style="width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Productos</button>
</div>

</body>
</html>





