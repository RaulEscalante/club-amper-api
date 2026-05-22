<?php

require_once "../../config/bootstrap.php";
require_once "../../controllers/UsuarioController.php";

$usuario = getUsuarioAuth();

if (!$usuario) {
    jsonResponse(false, "No autorizado", null, 401);
}

$db = new Database();
$conn = $db->getConnection();

$controller = new UsuarioController($conn);

$result = $controller->perfil($usuario);

jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);