<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, usuario");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Producto.php";

$db = new Database();
$conn = $db->getConnection();

$producto = new Producto($conn);

// obtener headers
$headers = getallheaders();

// obtener usuario del header
$usuarioHeader =
    $headers['usuario']
    ?? $headers['Usuario']
    ?? null;

// convertir a array
$usuario = $usuarioHeader
    ? json_decode(
        mb_convert_encoding(
            $usuarioHeader,
            'UTF-8',
            'UTF-8'
        ),
        true
    )
    : null;


// SOLO UNA ASIGNACIÓN
if ($usuario && $usuario["rol_id"] === 1) {
    $productos = $producto->listarAdmin();
} else {
    $productos = $producto->listar();
}

echo json_encode([
    "success" => true,
    "data" => $productos
]);