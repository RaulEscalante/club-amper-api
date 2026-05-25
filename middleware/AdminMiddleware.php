<?php
require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/response.php";

function requireAdmin()
{
    $usuario = getUsuarioAuth();

    if (
        !$usuario ||
        (int)$usuario["rol_id"] !== 1
    ) {

        jsonResponse(
            false,
            "No autorizado",
            null,
            403
        );
    }

    return $usuario;
}