<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

class Mailer
{

    public static function enviarVerificacion(
        $correo,
        $nombre,
        $token
    ) {

        $mail = new PHPMailer(true);   

        try {

            $mail->isSMTP();

            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;

            $mail->Username = "raulescalantem14@gmail.com";
            $mail->Password = "lrpb pnju eils bizq";

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->CharSet = "UTF-8";

            // REMITENTE
            $mail->setFrom(
                getenv("SMTP_FROM"),
                "Club Amper"
            );

            // DESTINO
            $mail->addAddress(
                $correo,
                $nombre
            );

            // LINK
            $link =
                getenv("APP_URL")
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
            error_log("MAIL ERROR: " . $mail->ErrorInfo);

            error_log($mail->ErrorInfo);

            return false;
        }
    }
}