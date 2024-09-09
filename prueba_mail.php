<?php

require __DIR__ . '/db/PHPMailer/PHPMailer.php';
require __DIR__ . '/db/PHPMailer/Exception.php';
require __DIR__ . '/db/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

function enviarCorreoSMTP($destinatario, $asunto, $cuerpo) {
    // Configuración de PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Host = 'smtp.ionos.es';
        $mail->SMTPAuth = true;
        $mail->Username = 'calendario@heyagencia.com';
        $mail->Password = 'calendario_hey_2024';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('no-reply@heyagencia.com', 'Calendario HEY');
        $mail->addAddress($destinatario);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

echo enviarCorreoSMTP('juan@heyagencia.com', 'Prueba email', 'Esto es solo una prueba de email');
?>
