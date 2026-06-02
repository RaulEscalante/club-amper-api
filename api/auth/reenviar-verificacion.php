<?php
require_once "../../config/bootstrap.php";
require_once "../../controllers/UsuarioController.php";

$db = new Database();
$conn = $db->getConnection();

$controller =
    new UsuarioController($conn);

$result =
    $controller->reenviarVerificacion();

jsonResponse(
    $result["success"],
    $result["message"]
);