<?php
require_once "../../config/bootstrap.php";
require_once "../../controllers/ProductoController.php";
require_once "../../middleware/AdminMiddleware.php";

$db = new Database();
$conn = $db->getConnection();

$controller = new ProductoController($conn);

$result = $controller->listar();

jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);