 <?php 
$imagen = "Imagenes/MantelImagen.jpg";
$usuario = "Usuario"
?>


<?php 

?>



<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Restaurante</title>
	<style> body {
        background-color: #F0AD69;
                 }
    </style>
</head>


<body> 
<?php 
if (isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}
?>
<img src="Imagenes/CirculoMarron.png" style="width: 590px; height: 130px; position: absolute; top: 30px; left: 470px;">
<h1 style="position: absolute; top: 35px; left: 520px;color: black; text-align: center; font-size:45px;"> Bienvenido a Cena Suave </h1>
<br>

<!--<img src="IconoPlato.png" style="width: 200px; height: 200px; position: absolute; top: 300px; left: 660px;"> -->
<div style="position: absolute; top: 250px; left: 630px; text-align: center;">
<form method="post">
<a href="CrearCuenta.php"style="display:inline-block; margin-bottom:40px; width:270px; height:60px; font-size:20px; font-weight:bold; font-color:black; color:#000; border:2px solid #000; border-radius:10px; text-align:center; line-height:60px; text-decoration:none; background:#F0F0F0;"> Registrarse </a>    

<br>


<a href="InicioSesion.php"style="display:inline-block; margin-bottom:10px; width:270px; height:60px; font-size:20px; font-weight:bold; font-color:black; color:#000; border:2px solid #000; border-radius:10px; text-align:center; line-height:60px; text-decoration:none; background:#F0F0F0;"> Iniciar Sesión </a>

<p style="margin:10px auto; font-size:17px; font-weight:bold;">¿Ya cuentas con una cuenta? ¡Registrate!</p>
</form>

<img src="Imagenes/Platillo.png" style="width: 210px; height: 170px; position: absolute; top: 270px; left: 30px;">
<!-- <button type="submit" style="margin:5px auto; width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Cuentas</button> <br> -->
</div>

</body>
</html>