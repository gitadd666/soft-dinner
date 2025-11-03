

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
$contrasenaMensaje = "";
$nombre = "";
$correo = "";
$contrasena = "";
$contrasenaConfirmar = "";


if (isset($_POST['correo']) && isset($_POST['contrasena']) && isset($_POST['confirmar']) && isset($_POST['nombre'])) {
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];
$contrasenaConfirmar = $_POST['confirmar'];



$sql = "SELECT * FROM usuario WHERE correo = :correo LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([':correo' => $correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) 
{
    $mensaje = "Este correo ya esta en uso";
} 
else if ($contrasenaConfirmar !== $contrasena)
{
    $contrasenaMensaje = "Las contraseñas no coinciden";
}
else
{
    $sql = mysqli_query($conexion, "INSERT INTO usuario (nombre, correo, contrasena) VALUES ('$nombre', '$correo', '$contrasena')");
    header("Location: PantallaDeInicio.php");
    exit();
}

}






?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Restaurante</title>
        <style>
            :root { --yellow: #F0AD69; --card-bg: #ffffff; --accent: #1976D2; }
            html,body { height:100%; margin:0; }
            body {
                background: var(--yellow);
                font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
                display:flex;
                align-items:flex-start;
                justify-content:center;
                padding:40px 16px;
                box-sizing:border-box;
                color:#111;
            }
            .card {
                background: var(--card-bg);
                width:100%;
                max-width:420px;
                border-radius:10px;
                padding:28px;
                box-shadow:0 6px 18px rgba(0,0,0,0.08);
            }
            h1 {
                margin:0 0 18px 0;
                font-size:34px;
                text-align:center;
                letter-spacing:0.2px;
            }
            form { display:flex; flex-direction:column; gap:12px; }
            label { font-size:14px; margin-bottom:6px; color:black; font-weight:bold; }
            .field { display:flex; flex-direction:column; }
            input[type="text"], input[type="password"], input[type="email"] {
                padding:10px 12px;
                font-size:15px;
                border:1px solid rgba(0,0,0,0.12);
                border-radius:6px;
            }
            .note { font-size:13px; color:rgba(0,0,0,0.6); }
            .actions { margin-top:6px; display:flex; justify-content:center; }
            button {
                background: var(--accent);
                color:#fff;
                border:none;
                padding:10px 18px;
                font-size:16px;
                border-radius:8px;
                cursor:pointer;
            }
            .error { color:#b00020; font-size:13px; display:none; }
            @media (max-width:420px){ h1{ font-size:28px } }
        </style>
    </head>
    <body>
        
        <main class="card" role="main">
            <h1>Crear cuenta</h1>

            <form method="post">
                <div class="field">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" placeholder="Escriba su nombre" value="<?php echo $nombre; ?>" required>
                </div>

                <div class="field">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" name="correo" type="email" placeholder="usuario@ejemplo.com" value="<?php echo $correo; ?>" required>
                    <p style="margin:5px auto; color: Red; font-size:16px; font-weight:bold;"><?php echo $mensaje; ?></p>
                </div>

                <div class="field">
                    <label for="contrasena">Contraseña</label>
                    <input id="contrasena" name="contrasena" type="password" placeholder="Mínimo 6 caracteres" minlength="6" value="<?php echo $contrasena; ?>" required>
                </div>

                <div class="field">
                    <label for="confirmar">Confirmar contraseña</label>
                    <input id="confirmar" name="confirmar" type="password" placeholder="Repite la contraseña" minlength="6" value="<?php echo $contrasenaConfirmar; ?>" required>
                </div>

                <p style="margin:5px auto; color: Red; font-size:16px; font-weight:bold;"><?php echo $contrasenaMensaje; ?></p>

                <div class="actions">
                    <button type="submit">Enviar</button>
                </div>
            </form>
        </main>
        <a href="InicioSesion.php" style="text-align: center; position: absolute; top: 585px; left: 680px;">¿Ya tienes una Cuenta? <br><br> ¡Inicia Sesion! </a> <br>
    </body>
</html>