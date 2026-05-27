<?php
require_once __DIR__ . "/../models/Usuario.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/validator.php";

class UsuarioController
{
    private $usuarioModel;
    public function __construct($conn)
    {
        $this->usuarioModel = new Usuario($conn);
    }
    /*
    |--------------------------------------------------------------------------
    | Registrar
    |--------------------------------------------------------------------------
    */
    public function registrar()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );
        if (!$data) {
            return ["success" => false, "message" => "Datos incompletos", "status" => 400];
        }
        /*
        |--------------------------------------------------------------------------
        | Campos
        |--------------------------------------------------------------------------
        */
        $tipo_documento = trim(
            $data["tipo_documento"] ?? ""
        );
        $documento = trim(
            $data["documento"] ?? ""
        );
        $nombres = trim(
            $data["nombres"] ?? ""
        );
        $apellidos = trim(
            $data["apellidos"] ?? ""
        );
        $correo = trim(
            $data["correo"] ?? ""
        );
        $telefono = trim(
            $data["telefono"] ?? ""
        );
        $password = trim(
            $data["password"] ?? ""
        );
        /*
        |--------------------------------------------------------------------------
        | Validaciones
        |--------------------------------------------------------------------------
        */
        if (
            empty($tipo_documento) ||
            empty($documento) ||
            empty($nombres) ||
            empty($apellidos) ||
            empty($correo) ||
            empty($telefono) ||
            empty($password)
        ) {
            return ["success" => false, "message" => "Todos los campos son obligatorios", "status" => 400];
        }
        // DNI
        if (
            $tipo_documento === "dni" &&
            !isValidDNI($documento)
        ) {
            return ["success" => false, "message" => "El DNI debe tener 8 dígitos", "status" => 400];
        }
        // RUC
        if (
            $tipo_documento === "ruc" &&
            !isValidRUC($documento)
        ) {

            return ["success" => false, "message" => "El RUC debe tener 11 dígitos", "status" => 400];
        }
        // Correo
        if (!isValidEmail($correo)) {
            return ["success" => false, "message" => "Correo inválido", "status" => 400];
        }
        if (!preg_match('/^[0-9]{9}$/', $telefono)) {
            return ["success" => false, "message" => "El teléfono debe tener 9 dígitos", "status" => 400];
        }
        // Password
        if (strlen($password) < 6) {
            return ["success" => false, "message" => "La contraseña debe tener mínimo 6 caracteres", "status" => 400];
        }
        /*
        |--------------------------------------------------------------------------
        | Registrar
        |--------------------------------------------------------------------------
        */
        $token = bin2hex(random_bytes(32));

        $result = $this->usuarioModel->registrar(
            $tipo_documento,
            $documento,
            $nombres,
            $apellidos,
            $correo,
            $telefono,
            $password,
            $token
        );
        if ($result === "correo_existente") {
            return jsonResponse(false, "El correo ya está registrado", null, 409);
        }

        if ($result === "documento_existente") {
            return jsonResponse(false, "El documento ya está registrado", null, 409);
        }
        if (!$result) {
            return jsonResponse(
                false,
                "Error al registrar",
                null,
                500
            );
        }

        try {
            require_once __DIR__ . "/../helpers/Mailer.php";

            // IMPORTANTE: terminar respuesta HTTP
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            // LOG
            file_put_contents(
                "/tmp/mail_debug.txt",
                "Intentando enviar a: " . $correo . "\n",
                FILE_APPEND
            );

            // ENVIAR CORREO DESPUÉS
            Mailer::enviarVerificacion(
                $correo,
                $nombres,
                $token
            );

        } catch (Exception $e) {
            file_put_contents(
                "/tmp/mail_debug.txt",
                "Error: " . $e->getMessage() . "\n",
                FILE_APPEND
            );
        }

        return jsonResponse(true, "Usuario registrado");
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!$data) {

            return jsonResponse(
                false,
                "Datos incompletos",
                null,
                400
            );
        }

        $correo = trim(
            $data["correo"] ?? ""
        );

        $password = trim(
            $data["password"] ?? ""
        );

        if (
            empty($correo) ||
            empty($password)
        ) {

            return jsonResponse(
                false,
                "Correo y contraseña obligatorios",
                null,
                400
            );
        }

        $result = $this->usuarioModel->login(
            $correo,
            $password
        );

        if (!$result) {

            return jsonResponse(
                false,
                "Credenciales incorrectas",
                null,
                401
            );
        }

        return jsonResponse(
            true,
            "Login correcto",
            $result
        );
    }

    public function perfil($usuario)
    {
        $data = $this->usuarioModel->perfil($usuario["id"]);

        return [
            "success" => true,
            "message" => "Perfil obtenido",
            "data" => $data
        ];
    }
    public function actualizarTelefono(
        $usuario,
        $data
    ) {
        $telefono =
            trim($data["telefono"] ?? "");

        if (!preg_match('/^[0-9]{9}$/', $telefono)) {

            return [
                "success" => false,
                "message" => "Número inválido"
            ];
        }

        $result =
            $this->usuarioModel
                ->actualizarTelefono(
                    $usuario["id"],
                    $telefono
                );

        if (!$result) {

            return [
                "success" => false,
                "message" => "Error al actualizar"
            ];
        }

        return [
            "success" => true,
            "message" => "Teléfono actualizado"
        ];
    }

    /*
|--------------------------------------------------------------------------
| Verificar correo
|--------------------------------------------------------------------------
*/

    public function verificarCorreo($token)
    {
        if (empty($token)) {
            return ["success" => false, "message" => "Token inválido"];
        }

        $result = $this->usuarioModel->verificarEmail($token);

        if (!$result) {
            return ["success" => false, "message" => "Token inválido o expirado"];
        }

        return ["success" => true, "message" => "Correo verificado correctamente"];
    }
}