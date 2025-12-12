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
    <title>SOFTDINNER-PRODUCTOS</title>
    <link rel="stylesheet" href="PRODUCTOS_CATEGORIAS_D.css">
</head>
<body>

    <header>
        <h1>PRODUCTOS</h1>
    </header>

    <section>
        <h2>SELECCIONAR CATEGORIAS</h2>

        <div>
            <!-- cada botón envía la categoría a 2RegistroProducto.php usando GET -->
            <form action="2RegistroProducto.php" method="get" style="display:inline;">
                <input type="hidden" name="categoria" value="Bebida">
                <button type="submit" id="btnBebidas">BEBIDAS</button>
            </form>

            <form action="2RegistroProducto.php" method="get" style="display:inline;">
                <input type="hidden" name="categoria" value="Comida">
                <button type="submit" id="btnComidas">COMIDAS</button>
            </form>
        </div>
    </section>

    <section id="imagenChef">
        <img src="CHEF.png" alt="Chef">
    </section>

    <footer>
        <!-- Regresa explícitamente a la pantalla principal fuera de la carpeta -->
        <button id="btnRegresar" onclick="window.location.href='../PantallaDeInicio.php'">REGRESAR</button>
    </footer>

</body>
</html>