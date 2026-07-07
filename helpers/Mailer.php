<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

class Mailer
{
    public static function enviarVerificacion($correo, $nombre, $token, $tipo = "registro")
    {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        try {

            $mail->isSMTP();

            /*
            |--------------------------------------------------------------------------
            | CONFIG SMTP (DESDE .ENV)
            |--------------------------------------------------------------------------
            */
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->CharSet = "UTF-8";

            /*
            |--------------------------------------------------------------------------
            | REMITENTE
            |--------------------------------------------------------------------------
            */
            $mail->setFrom(
                $_ENV['SMTP_FROM'],
                "Club Amper"
            );

            /*
            |--------------------------------------------------------------------------
            | DESTINATARIO
            |--------------------------------------------------------------------------
            */
            $mail->addAddress($correo, $nombre);

            /*
            |--------------------------------------------------------------------------
            | LINK FRONTEND
            |--------------------------------------------------------------------------
            */
            $link = $_ENV['APP_URL'] . "/verificar?token=" . $token;

            if ($tipo === "registro") {

                $titulo =
                    "Bienvenido a Club Amper";

                $asunto =
                    "Verifica tu cuenta";

                $mensaje = "
                    <p>
                        Hola {$nombre},
                        gracias por registrarte.
                    </p>

                    <p>
                        Haz click aquí para verificar tu cuenta:
                    </p>                    
                ";
                $textoBoton = "Verificar cuenta";

            } else {

                $titulo =
                    "Confirma tu nuevo correo electrónico";

                $asunto =
                    "Confirma tu nuevo correo";

                $mensaje = "
                    <p>
                        Hola {$nombre},
                    </p>

                    <p>
                        Hemos recibido una solicitud para cambiar el correo asociado a tu cuenta.
                    </p>

                    <p>
                        Confirma el nuevo correo haciendo click en el siguiente botón:
                    </p>
                ";
                $textoBoton = "Confirmar correo";             

            }
            /*
            |--------------------------------------------------------------------------
            | CONTENIDO
            |--------------------------------------------------------------------------
            */
            $mail->isHTML(true);
            $mail->Subject = $asunto;

            $mail->Body = "
                <h2>{$titulo}</h2>

                {$mensaje}

                <a href='{$link}'
                style='
                        background:#bb1818;
                        color:white;
                        padding:12px 18px;
                        text-decoration:none;
                        border-radius:8px;
                        display:inline-block;
                '>
                    {$textoBoton}
                </a>
            ";

            $mail->send();

            return true;

        } catch (Exception $e) {

            error_log("MAIL ERROR: " . $mail->ErrorInfo);
            error_log("EXCEPTION: " . $e->getMessage());

            return false;
        }
    }

    public static function enviarRecuperacion(
        $correo,
        $nombre,
        $token
    ) {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        try {

            $mail->isSMTP();

            $mail->Host = $_ENV['SMTP_HOST'];

            $mail->SMTPAuth = true;

            $mail->Username = $_ENV['SMTP_USER'];

            $mail->Password = $_ENV['SMTP_PASS'];

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;

            $mail->Port =
                $_ENV['SMTP_PORT'];

            $mail->setFrom(
                $_ENV['SMTP_FROM'],
                "Club Amper"
            );

            $mail->addAddress(
                $correo,
                $nombre
            );

            $link =
                $_ENV['APP_URL']
                . "/reset-password?token="
                . $token;

            $mail->isHTML(true);

            $mail->Subject =
                "Recuperación de contraseña";

            $mail->Body = "
            <h2>Recuperación de contraseña</h2>

            <p>
                Hola {$nombre}
            </p>

            <p>
                Hemos recibido una solicitud para cambiar tu contraseña.
            </p>

            <p>
                Haz click en el siguiente botón:
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
                Restablecer contraseña
            </a>

            <p>
                Este enlace expirará en 1 hora.
            </p>
        ";

            $mail->send();

            return true;

        } catch (Exception $e) {

            error_log(
                "MAIL RESET ERROR: "
                . $mail->ErrorInfo
            );

            return false;
        }
    }

    public static function enviarNotificacionCanje(
        $canje_id,
        $usuario,
        $productos,
        $total_puntos
    ) {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        try {

            $mail->isSMTP();

            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->CharSet = "UTF-8";

            $mail->setFrom(
                $_ENV['SMTP_FROM'],
                "Club Amper"
            );

            /*
            |------------------------------------------------------------
            | CORREO DEL ENCARGADO
            |------------------------------------------------------------
            */
            $mail->addAddress($_ENV['MAIL_CANJES']);

            $productosHtml = "";

            foreach ($productos as $producto) {

                $productosHtml .= "
                <tr>
                    <td>{$producto['nombre_producto']}</td>
                    <td>{$producto['cantidad']}</td>
                    <td>{$producto['subtotal_puntos']}</td>
                </tr>
            ";
            }

            $mail->isHTML(true);

            $mail->Subject =
                "Nuevo canje #{$canje_id}";

            $mail->Body = "
            <h2>Nuevo Canje Registrado</h2>

            <p>
                <strong>Ticket:</strong>
                #{$canje_id}
            </p>

            <p>
                <strong>Cliente:</strong>
                {$usuario['nombres']} {$usuario['apellidos']}
            </p>

            <p>
                <strong>Documento:</strong>
                {$usuario['documento']}
            </p>

            <p>
                <strong>Correo:</strong>
                {$usuario['correo']}
            </p>

            <table
                border='1'
                cellpadding='8'
                cellspacing='0'
                width='100%'
            >
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Pun tos</th>
                    </tr>
                </thead>
                <tbody>
                    {$productosHtml}
                </tbody>
            </table>

            <br>

            <p>
                <strong>Total puntos:</strong>
                {$total_puntos}
            </p>
        ";

            $mail->send();

            return true;

        } catch (Exception $e) {

            error_log(
                "MAIL CANJE ERROR: "
                . $mail->ErrorInfo
            );

            return false;
        }
    }
}