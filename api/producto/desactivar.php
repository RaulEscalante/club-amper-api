<?php
require_once "../../config/bootstrap.php";
require_once "../../controllers/ProductoController.php";
require_once "../../middleware/AdminMiddleware.php";

requireAdmin();

$db = new Database();
$conn = $db->getConnection();

$controller = new ProductoController($conn);

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$result = $controller->desactivar($data["id"]);

jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);
