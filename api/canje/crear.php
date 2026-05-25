<?php

require_once "../../config/bootstrap.php";
require_once "../../controllers/CanjeController.php";

$usuario = getUsuarioAuth();

if (!$usuario) {

    jsonResponse(
        false,
        "No autorizado",
        null,
        403
    );
}

$db = new Database();
$conn = $db->getConnection();

$controller =
    new CanjeController($conn);

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$result =
    $controller->crear(
        $data,
        $usuario
    );

jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);