     <?php 
$imagen = "Imagenes/MantelImagen.jpg";
$usuario = "Usuario"
?>


<?php 

$bdhost="localhost";
$bduser="root";
$bdpass="";
$bdname="restaurante";
$conexion=mysqli_connect($bdhost,$bduser,$bdpass,$bdname);
$query_max = mysqli_query($conexion, "SELECT MAX(id) FROM usuario");
$row = mysqli_fetch_array($query_max);
$max_value = $row[0];
$dsn = "mysql:host=localhost;dbname=restaurante;charset=utf8mb4";

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

$pdo = new PDO($dsn, $bduser, $bdpass, $options);

$mensaje = "";
$correo = "nothing";
if (isset($_POST['correo']) && isset($_POST['contrasena'])) {
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];

$sql = "SELECT * FROM usuario WHERE correo = :correo LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([':correo' => $correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) 
{
    if($contrasena == $usuario['contrasena'])
    {
        header("Location: PantallaDeInicio.php");
        exit(); // <- muy importante para detener el script
        echo "Bienvenido: ", $usuario['nombre'], "<br>";
        echo "Su correo es: ", $usuario['correo'], "<br>";
        echo "Y su contraseña es: ", $usuario['contrasena'];
    }
    else
    {
        $mensaje = "Contraseña Incorrecta";
    }
} 
else 
{
    $mensaje = "No Existe el Correo";
    $usuario['correo'] = "Correo no encontrado";
}

}

// Consulta preparada para buscar por nombre
//$sql = "SELECT * FROM usuario WHERE nombre = :nombre LIMIT 1";
//$sql = "SELECT id, nombre, contrasena FROM usuario WHERE nombre = :nombre LIMIT 1";






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
<img src="Imagenes/CirculoMarron.png" style="width: 390px; height: 130px; position: absolute; top: 30px; left: 565px;">
<h1 style="position: absolute; top: 35px; left: 610px;color: black; text-align: center; font-size:45px;"> Inicio de Sesion </h1>
<br>
<!--<img src="IconoPlato.png" style="width: 200px; height: 200px; position: absolute; top: 300px; left: 660px;"> -->
<div style="position: absolute; top: 180px; left: 650px; text-align: center;">
<p style="margin:5px auto; color: Red; font-size:18px; font-weight:bold;"><?php echo $mensaje; ?></p>
<form method="post">
<p style="margin:5px auto; text-align: left; font-size:20px; font-weight:bold;">Correo</p>
<input type="email" style="margin-bottom:20px; width: 230px; height: 50px; font-size:18px; font-weight:bold; border-radius:10px; text-align: center;" name="correo" placeholder="Escriba su Correo" minlength="12" maxlenght="60" required> <br>

<p style="margin:5px auto; text-align: left; font-size:20px; font-weight:bold;">Contraseña</p>
<input type="password" style="margin-bottom:10px; width: 230px; height: 50px; font-size:18px; font-weight:bold; border-radius:10px; text-align: center;" name="contrasena" placeholder="Ingrese su Contraseña" minlength="6" maxlenght="60" required> <br>
<a href="#" style="">¿Olvidaste la Contraseña?</a> <br>

<button type="submit" value="Enviar" style="margin-top:30px; width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Iniciar Sesion</button>
</form>
<img src="Imagenes/RamenIcono.png" style="width: 240px; height: 170px; position: absolute; top: 330px; left: -10px;">
<!-- <button type="submit" style="margin:5px auto; width: 170px; height: 50px; font-size:20px; font-weight:bold; border-radius:10px;">Cuentas</button> <br> -->
</div>
<img src="Imagenes/MedioCirculo.png" style="width: 450px; height: 150px; position: absolute; top: 700px; left: 545px;">
<a href="CrearCuenta.php" style="text-align: center; position: absolute; top: 760px; left: 680px;">¿No tienes una Cuenta? <br><br> ¡Crea Una! </a> <br>

</body>
</html>





