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
        $telefono,
        $password,
        $token
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
                    telefono,
                    password,
                    rol_id,
                    token_verificacion,
                    email_verificado
                )
                VALUES (
                    :tipo_documento,
                    :documento,
                    :nombres,
                    :apellidos,
                    :correo,
                    :telefono,
                    :password,
                    2,
                    :token_verificacion,
                    0
                )";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":tipo_documento", $tipo_documento);
        $stmt->bindParam(":documento", $documento);
        $stmt->bindParam(":nombres", $nombres);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":token_verificacion", $token);

        $resultado = $stmt->execute();

        return $resultado;
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
            u.id,
            u.tipo_documento,
            u.documento,
            u.nombres,
            u.apellidos,
            u.correo,
            u.telefono,
            COALESCE(pc.puntos, 0) AS puntos,
            u.rol_id,
            u.estado
        FROM usuarios u
        LEFT JOIN puntos_cache pc
            ON u.documento = pc.documento
        WHERE u.id = :id
        LIMIT 1
    ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarTelefono(
        $id,
        $telefono
    ) {
        $sql = "
        UPDATE usuarios
        SET telefono = :telefono
        WHERE id = :id
    ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":telefono" => $telefono,
            ":id" => $id
        ]);
    }

    /*
|--------------------------------------------------------------------------
| Verificar email
|--------------------------------------------------------------------------
*/
    public function verificarEmail($token)
    {
        $sql = "
        SELECT id 
        FROM usuarios
        WHERE token_verificacion = :token
        LIMIT 1
    ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":token", $token);

        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return false;
        }

        $updateSql = "
        UPDATE usuarios
        SET 
            email_verificado = 1,
            token_verificacion = NULL
        WHERE id = :id
    ";

        $updateStmt = $this->conn->prepare($updateSql);

        $updateStmt->bindParam(":id", $usuario["id"]);

        return $updateStmt->execute();
    }
}