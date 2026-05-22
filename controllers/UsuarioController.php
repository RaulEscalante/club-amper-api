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
            jsonResponse(false, "Datos incompletos", null, 400);
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
            empty($password)
        ) {
            jsonResponse(false, "Todos los campos son obligatorios", null, 400);
        }
        // DNI
        if (
            $tipo_documento === "dni" &&
            !isValidDNI($documento)
        ) {
            jsonResponse(false, "El DNI debe tener 8 dígitos", null, 400);
        }
        // RUC
        if (
            $tipo_documento === "ruc" &&
            !isValidRUC($documento)
        ) {

            jsonResponse(false, "El RUC debe tener 11 dígitos", null, 400);
        }
        // Correo
        if (!isValidEmail($correo)) {
            jsonResponse(false, "Correo inválido", null, 400);
        }
        // Password
        if (strlen($password) < 6) {
            jsonResponse(false, "La contraseña debe tener mínimo 6 caracteres", null, 400);
        }
        /*
        |--------------------------------------------------------------------------
        | Registrar
        |--------------------------------------------------------------------------
        */
        $result = $this->usuarioModel->registrar(
            $tipo_documento,
            $documento,
            $nombres,
            $apellidos,
            $correo,
            $password
        );
        if ($result === "correo_existente") {
            jsonResponse(false, "El correo ya está registrado", null, 409);
        }

        if ($result === "documento_existente") {
            jsonResponse(false, "El documento ya está registrado", null, 409);
        }
        if (!$result) {
            jsonResponse(
                false,
                "Error al registrar",
                null,
                500
            );
        }

        jsonResponse(
            true,
            "Usuario registrado"
        );
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

            jsonResponse(
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

            jsonResponse(
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

            jsonResponse(
                false,
                "Credenciales incorrectas",
                null,
                401
            );
        }

        jsonResponse(
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
}