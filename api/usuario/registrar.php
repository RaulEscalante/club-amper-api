<?php
require_once "../../config/bootstrap.php";
require_once __DIR__ . "/../../controllers/UsuarioController.php";

$db = new Database();

$conn = $db->getConnection();

$controller = new UsuarioController($conn);

$controller->registrar();