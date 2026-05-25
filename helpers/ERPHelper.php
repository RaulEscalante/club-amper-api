<?php

function obtenerClientesERP($offset = 0)
{
    $url = getenv("ERP_URL");

    $token = getenv("ERP_TOKEN");

    $payload = [
        "apiPOS_getCustomers" => 1,
        "setToken" => $token,
        "limit" => 1000,
        "offset" => $offset
    ];

    $ch = curl_init($url);

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POST => true,

        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],

        CURLOPT_POSTFIELDS =>
            json_encode($payload),

        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    /*
    |----------------------------------------------------------------------
    | ERROR CURL
    |----------------------------------------------------------------------
    */
    if (curl_errno($ch)) {

        curl_close($ch);

        return [
            "success" => false,
            "message" => curl_error($ch)
        ];
    }

    curl_close($ch);

    $data = json_decode($response, true);

    /*
    |----------------------------------------------------------------------
    | VALIDAR RESPONSE
    |----------------------------------------------------------------------
    */
    if (
        !$data ||
        !isset($data["success"])
    ) {

        return [
            "success" => false,
            "message" => "Respuesta inválida ERP"
        ];
    }

    return $data;
}