<?php

require_once "../../config/bootstrap.php";
require_once "../../controllers/ProductoController.php";
require_once "../../middleware/AdminMiddleware.php";

requireAdmin();

$db = new Database();
$conn = $db->getConnection();

$controller = new ProductoController($conn);

/*
|------------------------------------------------------------------
| FORM DATA
|------------------------------------------------------------------
*/
$data = $_POST;

/*
|------------------------------------------------------------------
| IMAGEN
|------------------------------------------------------------------
*/
if (isset($_FILES["imagen"])) {
    $data["imagen"] = $_FILES["imagen"];
}

/*
|------------------------------------------------------------------
| REQUEST
|------------------------------------------------------------------
*/
$result = $controller->editar($data);

/*
|------------------------------------------------------------------
| RESPONSE
|------------------------------------------------------------------
*/
jsonResponse(
    $result["success"],
    $result["message"],
    $result["data"] ?? null
);