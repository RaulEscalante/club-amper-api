<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

class Mailer {

    public static function enviarVerificacion($correo, $token) {

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'TU_CORREO@gmail.com';
            $mail->Password = 'TU_APP_PASSWORD';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('TU_CORREO@gmail.com', 'Club Amper');
            $mail->addAddress($correo);

            $link = "https://club-amper.vercel.app/verificar?token=$token";

            $mail->isHTML(true);
            $mail->Subject = "Verifica tu cuenta";
            $mail->Body = "
                <h3>Bienvenido a Club Amper</h3>
                <p>Haz click para verificar tu cuenta:</p>
                <a href='$link'>Verificar cuenta</a>
            ";

            $mail->send();

            return true;

        } catch (Exception $e) {

            return false;
        }
    }
}