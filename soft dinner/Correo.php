<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mensajeEnviado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correoDestino = $_POST['correo'];
    $asunto = $_POST['asunto'];
    $mensaje = $_POST['mensaje'];

    $mail = new PHPMailer(true);
    $codigo = random_int(100000, 999999);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'eduardogarcia2024actualizado@gmail.com';
        $mail->Password   = 'abysrgcyjrpsdede';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('eduardogarcia2024actualizado@gmail.com', 'Codigo de Verificacion');
        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = nl2br($mensaje);

        $mail->send();
        $mensajeEnviado = "Correo enviado correctamente";
    } catch (Exception $e) {
        $mensajeEnviado = "Error al enviar correo: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar correo</title>
</head>
<body>

<h2>Enviar Correo</h2>

<?php 
if ($mensajeEnviado != "") {
    echo "<p><strong>$mensajeEnviado</strong></p>";
}
?>

<form method="POST">
    <label>Correo destino:</label><br>
    <input type="email" name="correo" required><br><br>

    <label>Asunto:</label><br>
    <input type="text" name="asunto" required><br><br>

    <label>Mensaje:</label><br>
    <textarea name="mensaje" rows="5" required></textarea><br><br>

    <button type="submit">Enviar correo</button>
</form>

</body>
</html>