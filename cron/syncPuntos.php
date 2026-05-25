<?php

require_once __DIR__ . "/../config/bootstrap.php";
require_once __DIR__ . "/../helpers/ERPHelper.php";

/*
|----------------------------------------------------------------------
| DB
|----------------------------------------------------------------------
*/
$db = new Database();

$conn = $db->getConnection();

/*
|----------------------------------------------------------------------
| CONFIG
|----------------------------------------------------------------------
*/
$offset = 0;

$limit = 1000;

$totalProcesados = 0;

/*
|----------------------------------------------------------------------
| LOOP SEGURO
|----------------------------------------------------------------------
*/
while (true) {

    /*
    |------------------------------------------------------------------
    | CONSULTAR ERP
    |------------------------------------------------------------------
    */
    $response = obtenerClientesERP($offset);

    if (!$response["success"]) {

        echo "Error ERP: " .
            $response["message"];

        exit;
    }

    /*
    |------------------------------------------------------------------
    | DATA
    |------------------------------------------------------------------
    */
    $clientes =
        $response["message"]["arrayDataFetch"] ?? [];

    $receivedCount =
        $response["message"]["receivedCount"] ?? 0;

    /*
    |------------------------------------------------------------------
    | SI NO HAY MÁS DATOS
    |------------------------------------------------------------------
    */
    if ($receivedCount === 0) {

        break;
    }

    /*
    |------------------------------------------------------------------
    | RECORRER CLIENTES
    |------------------------------------------------------------------
    */
    foreach ($clientes as $cliente) {

        $documento =
            trim(
                $cliente["setCustomerDocumentNumber"] ?? ""
            );

        $puntos =
            (int) (
                $cliente["setCustomerScore"] ?? 0
            );

        /*
        |--------------------------------------------------------------
        | IGNORAR SIN DOCUMENTO
        |--------------------------------------------------------------
        */
        if (empty($documento)) {
            continue;
        }

        /*
        |--------------------------------------------------------------
        | UPSERT
        |--------------------------------------------------------------
        */
        $sql = "
            INSERT INTO puntos_cache
            (
                documento,
                puntos
            )
            VALUES
            (
                :documento,
                :puntos
            )
            ON DUPLICATE KEY UPDATE
                puntos = VALUES(puntos)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ":documento" => $documento,
            ":puntos" => $puntos
        ]);

        $totalProcesados++;
    }

    /*
    |------------------------------------------------------------------
    | SEGURIDAD ANTI LOOP
    |------------------------------------------------------------------
    */
    if ($receivedCount < $limit) {

        break;
    }

    /*
    |------------------------------------------------------------------
    | SIGUIENTE BLOQUE
    |------------------------------------------------------------------
    */
    $offset += $limit;
}

/*
|----------------------------------------------------------------------
| FINAL
|----------------------------------------------------------------------
*/
echo
    "Sincronización completada. " .
    $totalProcesados .
    " registros procesados.";