<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->getConnection(); // ✔ correcto

if ($conn) {
    echo "CONEXIÓN OK";
} else {
    echo "ERROR";
}