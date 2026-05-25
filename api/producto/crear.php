<?php
require_once "../../config/bootstrap.php";
require_once "../../controllers/ProductoController.php";
require_once "../../middleware/AdminMiddleware.php";

$usuario = requireAdmin();

$db = new Database();
$conn = $db->getConnection();

$controller = new ProductoController($conn);

$data = $_POST;

if (isset($_FILES["imagen"])) {
    $data["imagen"] = $_FILES["imagen"];
}

$result = $controller->crear($data);

jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);