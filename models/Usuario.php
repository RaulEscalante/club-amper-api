<?php
class Usuario
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function registrar(
        $tipo_documento,
        $documento,
        $nombres,
        $apellidos,
        $correo,
        $password
    ) {
        // Validar correo repetido
        $checkSql = "SELECT id FROM usuarios WHERE correo = :correo LIMIT 1";

        $checkStmt = $this->conn->prepare($checkSql);

        $checkDocSql = "
            SELECT id 
            FROM usuarios 
            WHERE documento = :documento 
            LIMIT 1
        ";

        $checkDocStmt = $this->conn->prepare($checkDocSql);

        $checkDocStmt->bindParam(
            ":documento",
            $documento
        );

        $checkDocStmt->execute();

        if ($checkDocStmt->fetch()) {
            return "documento_existente";
        }

        $checkStmt->bindParam(":correo", $correo);

        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            return "correo_existente";
        }
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO usuarios (
                    tipo_documento,
                    documento,
                    nombres,
                    apellidos,
                    correo,
                    password,
                    rol_id
                )
                VALUES (
                    :tipo_documento,
                    :documento,
                    :nombres,
                    :apellidos,
                    :correo,
                    :password,
                    2
                )";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":tipo_documento", $tipo_documento);
        $stmt->bindParam(":documento", $documento);
        $stmt->bindParam(":nombres", $nombres);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":password", $passwordHash);

        return $stmt->execute();
    }

    public function login($correo, $password)
    {
        $correo = trim($correo);
        $password = trim($password);

        $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return false;
        }

        if (!password_verify($password, $usuario["password"])) {
            return false;
        }

        unset($usuario["password"]);

        return $usuario;
    }

    public function perfil($id)
    {
        $sql = "
        SELECT
            id,
            tipo_documento,
            documento,
            nombres,
            apellidos,
            correo,
            puntos,
            rol_id,
            estado
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}