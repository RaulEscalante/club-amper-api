<?php

require_once "../../config/bootstrap.php";
require_once "../../controllers/CanjeController.php";
require_once "../../middleware/AdminMiddleware.php";

requireAdmin();

$db = new Database();
$conn = $db->getConnection();

$controller =
    new CanjeController($conn);

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$result =
    $controller->actualizarEstado($data);

jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);