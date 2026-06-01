<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restaurante - Cena Suave</title>
    <style>
        /* Importar una fuente moderna desde Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #F0AD69 0%, #D88B43 100%);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #2D251E;
        }

        /* Contenedor Principal (Tarjeta estilo Glassmorphism) */
        .main-container {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 24px;
            padding: 40px 30px;
            width: 90%;
            max-width: 480px;
            text-align: center;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15);
            box-sizing: border-box;
            position: relative;
        }

        /* Encabezado */
        h1 {
            font-size: 36px;
            font-weight: 700;
            color: #2D251E;
            margin-top: 0;
            margin-bottom: 35px;
            line-height: 1.2;
        }

        /* Formulario y Botones */
        .menu-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-items: center;
        }

        .btn {
            display: block;
            width: 100%;
            max-width: 280px;
            height: 55px;
            line-height: 55px;
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        /* Botón de Iniciar Sesión (Principal) */
        .btn-login {
            background: #2D251E;
            border: none;
        }

        .btn-login:hover {
            background: #45392F;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Botón de Registro (Secundario) */
        .btn-register {
            background: rgba(255, 255, 255, 0.8);
            color: #2D251E;
            border: 2px solid #2D251E;
        }

        .btn-register:hover {
            background: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        /* Texto informativo */
        .info-text {
            font-size: 14px;
            font-weight: 500;
            color: #4A3E3D;
            margin-top: 10px;
            margin-bottom: 0;
        }

        /* Imagen Decorativa del Platillo */
        .img-platillo {
            width: 120px;
            height: auto;
            margin-top: 25px;
            filter: drop-shadow(0 8px 12px rgba(0,0,0,0.15));
        }
    </style>
</head>

<body> 

<?php 
if (isset($_POST['usuario'])) {
    $usuario = $_POST['usuario'];
}
?>

<div class="main-container">
    <h1>Bienvenido a<br>Cena Suave</h1>

    <form method="post" class="menu-form">
        <a href="InicioSesion.php" class="btn btn-login">Iniciar Sesión</a>
        
        <a href="CrearCuenta.php" class="btn btn-register">Registrarse</a>     
        
        <p class="info-text">¿No tienes una cuenta aún? ¡Regístrate!</p>
    </form>

    <img src="Imagenes/Platillo.png" alt="Platillo" class="img-platillo">
</div>

</body>
</html>