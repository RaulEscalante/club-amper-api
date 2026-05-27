<?php

header('Content-Type: application/json');

echo json_encode([
    "smtp_host" => getenv("SMTP_HOST"),
    "smtp_port" => getenv("SMTP_PORT"),
    "smtp_user" => getenv("SMTP_USER"),
    "smtp_from" => getenv("SMTP_FROM"),
    "app_url" => getenv("APP_URL")
]);