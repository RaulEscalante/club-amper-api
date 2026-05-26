<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

class Mailer {

    public static function enviarVerificacion(
        $correo,
        $nombre,
        $token
    ) {

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();

            $mail->Host =
                $_ENV["SMTP_HOST"];

            $mail->SMTPAuth = true;

            $mail->Username =
                $_ENV["SMTP_USER"];

            $mail->Password =
                $_ENV["SMTP_PASS"];

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;

            $mail->Port =
                $_ENV["SMTP_PORT"];

            $mail->CharSet = "UTF-8";

            // REMITENTE
            $mail->setFrom(
                $_ENV["SMTP_FROM"],
                "Club Amper"
            );

            // DESTINO
            $mail->addAddress(
                $correo,
                $nombre
            );

            // LINK
            $link =
                $_ENV["APP_URL"]
                . "/verificar?token="
                . $token;

            // CONTENIDO
            $mail->isHTML(true);

            $mail->Subject =
                "Verifica tu cuenta";

            $mail->Body = "
                <h2>Bienvenido a Club Amper</h2>

                <p>
                    Hola {$nombre},
                    gracias por registrarte.
                </p>

                <p>
                    Haz click aquí para verificar tu cuenta:
                </p>

                <a href='{$link}'
                   style='
                        background:#bb1818;
                        color:white;
                        padding:12px 18px;
                        text-decoration:none;
                        border-radius:8px;
                        display:inline-block;
                   '
                >
                    Verificar cuenta
                </a>
            ";

            $mail->send();

            return true;

        } catch (Exception $e) {

            error_log($mail->ErrorInfo);

            return false;
        }
    }
}