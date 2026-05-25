<?php

require_once "../../config/bootstrap.php";
require_once "../../controllers/UsuarioController.php";

$database = new Database();

$conn = $database->getConnection();

$controller =
    new UsuarioController($conn);

$usuario = getUsuarioAuth();
if (!$usuario) {
    jsonResponse(
        false,
        "No autorizado",
        null,
        401
    );
}

$data = json_decode(
    file_get_contents("php://input"),
    true
);

echo json_encode(
    $controller->actualizarTelefono(
        $usuario,
        $data
    )
);