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

            $mail->SMTPDebug = 0;

            $mail->Debugoutput = function ($str, $level) {
                error_log("SMTP DEBUG: $str");
            };

            $mail->Host = getenv("SMTP_HOST");

            $mail->SMTPAuth = true;

            $mail->Username = getenv("SMTP_USER");

            $mail->Password = getenv("SMTP_PASS");

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port = getenv("SMTP_PORT");

            $mail->CharSet = "UTF-8";

            $mail->setFrom(
                getenv("SMTP_FROM"),
                "Club Amper"
            );

            $mail->addAddress(
                $correo,
                $nombre
            );

            $link =
                getenv("APP_URL")
                . "/verificar?token="
                . $token;

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
            error_log("CORREO ENVIADO A: " . $correo);
            return true;

        } catch (Exception $e) {

            throw new Exception($mail->ErrorInfo);
        }
    }
}