<?php
function getUsuarioAuth()
{
    $headers = getallheaders();

    $usuarioHeader =
        $headers['usuario']
        ?? $headers['Usuario']
        ?? null;

    if (!$usuarioHeader) {
        return null;
    }

    return json_decode(
        mb_convert_encoding(
            $usuarioHeader,
            'UTF-8',
            'UTF-8'
        ),
        true
    );
}