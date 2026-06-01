<?php
/**/
function obtenerClientesERP($offset = 0)
{
    $url = $_ENV["ERP_URL"] ?? null;

    $token = $_ENV["ERP_TOKEN"] ?? null;
    
    if (empty($url) || empty($token)) {
        return [
            "success" => false,
            "message" => "ERP_URL o ERP_TOKEN no configurados"
        ];
    }

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
        $error = curl_error($ch);
        curl_close($ch);

        return [
            "success" => false,
            "message" => $error
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