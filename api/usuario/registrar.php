<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../controllers/UsuarioController.php";

$db = new Database();

$conn = $db->getConnection();

$controller = new UsuarioController($conn);

$controller->registrar();