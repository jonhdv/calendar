<?php

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreoSMTP($asunto, $cuerpo) {
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
        $mail->addAddress('juan@heyagencia.com');
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
?>
