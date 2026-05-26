<?php
require_once "../../config/bootstrap.php";
require_once "../../controllers/UsuarioController.php";

$db = new Database();
$conn = $db->getConnection();
$controller = new UsuarioController($conn);

$token = $_GET["token"] ?? "";

$result = $controller->verificarCorreo($token);

jsonResponse(
    $result["success"],
    $result["message"]
);