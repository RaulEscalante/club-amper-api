<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, usuario");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Producto.php";

$db = new Database();
$conn = $db->getConnection();

$producto = new Producto($conn);

$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "No se recibieron datos"
    ]);
    exit;
}

$headers = getallheaders();

$usuarioHeader =
    $headers['usuario']
    ?? $headers['Usuario']
    ?? null;

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

if (!$usuario || (int)$usuario["rol_id"] !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "No autorizado"
    ]);
    exit;
}

$result = $producto->reactivar(
    $data["id"]
);

echo json_encode([
    "success" => $result,
    "message" => $result
        ? "Producto reactivado"
        : "Error al reactivar"
]);