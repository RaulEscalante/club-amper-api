<?php

if (php_sapi_name() === 'cli') {
    return;
}

$allowedOrigins = [
    "https://club-amper.vercel.app",
    "https://clubamper.com"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? "";

if (in_array($origin, $allowedOrigins)) {

    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, usuario");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {

    http_response_code(200);
    exit;
}