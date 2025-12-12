<?php
session_start();

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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

$mensajeEnviado = "";
$errorCodigo = false;

// función para enviar el correo de verificación
function enviarCodigoPorCorreo($correoDestino, $codigo) {
    global $mensajeEnviado;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'eduardogarcia2024actualizado@gmail.com';
        $mail->Password   = 'abysrgcyjrpsdede';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('eduardogarcia2024actualizado@gmail.com', 'Cena Suave');
        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $mail->Subject = "Codigo de Verificacion";
        $mail->Body    = nl2br($codigo);

        $mail->send();
        $mensajeEnviado = "Correo enviado correctamente";
        return ['success' => true, 'message' => $mensajeEnviado];
    } catch (Exception $e) {
        $mensajeEnviado = "Error al enviar correo: {$mail->ErrorInfo}";
        return ['success' => false, 'message' => $mensajeEnviado];
    }
}

// flujo de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // caso inicial: llega desde 1restablezcaContraseña.php con correo + codigo
    if (isset($_POST['correo']) && isset($_POST['codigo']) && !isset($_POST['verify']) && !isset($_POST['action'])) {
        // almacenar en sesión para reenvíos y verificación posterior
        $_SESSION['reset_correo'] = $_POST['correo'];
        $_SESSION['reset_codigo'] = $_POST['codigo'];
        // enviar correo la primera vez
        enviarCodigoPorCorreo($_SESSION['reset_correo'], $_SESSION['reset_codigo']);
    }

    // caso reenvío (AJAX fetch desde el enlace "Reenviar")
    if (isset($_POST['action']) && $_POST['action'] === 'resend') {
        if (!empty($_SESSION['reset_correo']) && !empty($_SESSION['reset_codigo'])) {
            $res = enviarCodigoPorCorreo($_SESSION['reset_correo'], $_SESSION['reset_codigo']);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res);
            exit;
        } else {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'No hay datos de reenvío en la sesión.']);
            exit;
        }
    }

    // caso verificación: el usuario envía el código que ingresó
    if (isset($_POST['verify'])) {
        $codigoUsuario = isset($_POST['codigo_usuario']) ? trim($_POST['codigo_usuario']) : '';
        if (!empty($_SESSION['reset_codigo']) && $codigoUsuario === $_SESSION['reset_codigo']) {
            // código correcto: redirigir a 3nuevaContraseña.php
            header("Location: 3nuevaContraseña.php");
            exit;
        } else {
            // código incorrecto: mostrar error debajo del input
            $errorCodigo = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>REESTABLECER CONTRASEÑA - SOFT DINNER</title>
    <link rel="stylesheet" href="verificacionBonita.css">
    <style>
      /* estilo mínimo para el label de error */
      .error-label { color: red; display: block; margin-top: 8px; }
      .small-msg { font-size: 0.9rem; color: #333; margin-top: 8px; }
    </style>
  </head>

  <body>
    <div class="cajaVerificar">
      <h1>Código de verificación</h1>

      <form action="2verificacion.php" method="post" id="verifyForm">
        <label for="codigo">Se envió un código de verificación a su correo:</label>
        <input type="text" id="codigo" name="codigo_usuario" maxlength="6" pattern="\d{6}" inputmode="numeric" placeholder="Ingrese el código" required>
        <?php if ($errorCodigo): ?>
          <label class="error-label">Codigo Incorrecto, Intentelo denuevo.</label>
        <?php endif; ?>
        <input type="hidden" name="verify" value="1">
        <button type="submit">Confirmar código</button>
        <p class="small-msg">¿No recibiste el código? <a href="#" id="reenviar">Reenviar</a></p>
      </form>
    </div>

    <script>
      (function(){
        const reenviar = document.getElementById('reenviar');
        reenviar.addEventListener('click', function(e){
          e.preventDefault();
          // petición POST para reenvío usando Fetch
          const formData = new FormData();
          formData.append('action', 'resend');

          fetch('2verificacion.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
          })
          .then(response => response.json())
          .then(json => {
            // mostrar feedback simple al usuario
            if (json.success) {
              reenviar.textContent = 'Reenviado';
              setTimeout(() => reenviar.textContent = 'Reenviar', 3000);
            } else {
              reenviar.textContent = 'Error al reenviar';
              setTimeout(() => reenviar.textContent = 'Reenviar', 3000);
            }
          })
          .catch(() => {
            reenviar.textContent = 'Error al reenviar';
            setTimeout(() => reenviar.textContent = 'Reenviar', 3000);
          });
        });
      })();
    </script>
  </body>
</html>