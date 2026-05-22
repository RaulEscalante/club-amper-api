<?php

require_once "../../config/bootstrap.php";
require_once "../../controllers/CanjeController.php";

$usuario = getUsuarioAuth();

if (!$usuario) {

    jsonResponse(
        false,
        "No autorizado",
        null,
        401
    );
}

$db = new Database();
$conn = $db->getConnection();

$controller = new CanjeController($conn);

$result = $controller->historial($usuario);

jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);