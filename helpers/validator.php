<?php
function isValidEmail($correo)
{
    return filter_var(
        $correo,
        FILTER_VALIDATE_EMAIL
    );
}
function isValidDNI($documento)
{
    return strlen($documento) === 8;
}
function isValidRUC($documento)
{
    return strlen($documento) === 11;
}