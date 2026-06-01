<?php

require_once __DIR__ . "/../models/Canje.php";
require_once __DIR__ . "/../models/Producto.php";

class CanjeController
{
    private $canje;
    private $producto;
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->canje = new Canje($db);
        $this->producto = new Producto($db);
    }
    /*
    |--------------------------------------------------------------------------
    | CREAR CANJE (FLUJO SIMPLE - 1 USUARIO, MÚLTIPLES PRODUCTOS)
    |--------------------------------------------------------------------------
    */
    public function crear($data, $usuario)
    {
        try {

            $productos = $data["productos"] ?? [];

            if (empty($productos)) {
                return [
                    "success" => false,
                    "message" => "No hay productos para canjear"
                ];
            }

            $this->conn->beginTransaction();

            $total_puntos = 0;
            $productos_final = [];

            /*
            |--------------------------------------------------------------------------
            | VALIDAR PRODUCTOS
            |--------------------------------------------------------------------------
            */
            foreach ($productos as $item) {

                $producto_id = $item["producto_id"] ?? null;
                $cantidad = $item["cantidad"] ?? 1;

                if (!$producto_id || $cantidad <= 0) {
                    $this->conn->rollBack();

                    return [
                        "success" => false,
                        "message" => "Producto inválido"
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | OBTENER PRODUCTO REAL
                |--------------------------------------------------------------------------
                */
                $stmt = $this->conn->prepare("
                    SELECT * FROM productos 
                    WHERE id = :id 
                    FOR UPDATE
                ");

                $stmt->bindParam(":id", $producto_id);
                $stmt->execute();

                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    $this->conn->rollBack();

                    return [
                        "success" => false,
                        "message" => "Producto no existe"
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | VALIDAR STOCK
                |--------------------------------------------------------------------------
                */
                if ($producto["stock"] < $cantidad) {
                    $this->conn->rollBack();

                    return [
                        "success" => false,
                        "message" => "Stock insuficiente en " . $producto["nombre"]
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | CALCULAR PUNTOS
                |--------------------------------------------------------------------------
                */
                $subtotal = $producto["puntos_requeridos"] * $cantidad;
                $total_puntos += $subtotal;

                $productos_final[] = [
                    "producto_id" => $producto_id,
                    "nombre_producto" => $producto["nombre"],
                    "cantidad" => $cantidad,
                    "puntos_unitarios" => $producto["puntos_requeridos"],
                    "subtotal_puntos" => $subtotal
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDAR PUNTOS USUARIO
            |--------------------------------------------------------------------------
            */
            $stmtUser = $this->conn->prepare("
                SELECT * FROM usuarios 
                WHERE id = :id 
                FOR UPDATE
            ");

            $stmtUser->bindParam(":id", $usuario["id"]);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user["puntos"] < $total_puntos) {
                $this->conn->rollBack();
                return [
                    "success" => false,
                    "message" => "No tienes puntos suficientes"
                ];
            }
            /*
            |--------------------------------------------------------------------------
            | CREAR CANJE
            |--------------------------------------------------------------------------
            */
            $canje_id = $this->canje->crearCabecera(
                $usuario["id"],
                $total_puntos
            );
            /*
            |--------------------------------------------------------------------------
            | INSERTAR DETALLE + DESCONTAR STOCK
            |--------------------------------------------------------------------------
            */
            foreach ($productos_final as $producto) {

                $this->canje->insertarDetalle(
                    $canje_id,
                    $producto
                );

                $this->canje->descontarStock(
                    $producto["producto_id"],
                    $producto["cantidad"]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DESCONTAR PUNTOS USUARIO
            |--------------------------------------------------------------------------
            */
            $this->canje->descontarPuntos(
                $usuario["id"],
                $total_puntos
            );

            $puntos_restantes =
                $user["puntos"] - $total_puntos;

            /*
            |--------------------------------------------------------------------------
            | CONFIRMAR TRANSACCIÓN
            |--------------------------------------------------------------------------
            */
            $this->conn->commit();

            Mailer::enviarNotificacionCanje(
                $canje_id,
                $user,
                $productos_final,
                $total_puntos
            );

            /*
            |--------------------------------------------------------------------------
            | RESPUESTA FINAL
            |--------------------------------------------------------------------------
            */

            $mensaje = "Hola, acabo de realizar un canje.\n\n";

            $mensaje .= "Ticket: #{$canje_id}\n";
            $mensaje .= "Cliente: {$user['nombres']} {$user['apellidos']}\n";
            $mensaje .= "Documento: {$user['documento']}\n\n";

            $mensaje .= "Productos:\n";

            foreach ($productos_final as $producto) {

                $mensaje .=
                    "- {$producto['nombre_producto']} x{$producto['cantidad']}\n";
            }

            $mensaje .= "\nTotal puntos: {$total_puntos}";

            $whatsapp_url = "https://wa.me/51933686366?text=" . urlencode($mensaje);

            return [
                "success" => true,
                "message" => "Canje realizado correctamente",
                "data" => [
                    "canje_id" => $canje_id,
                    "total_puntos" => $total_puntos,
                    "whatsapp_url" => $whatsapp_url
                ]
            ];

        } catch (Exception $e) {

            $this->conn->rollBack();

            return [
                "success" => false,
                "message" => "Error en canje: " . $e->getMessage()
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HISTORIAL
    |--------------------------------------------------------------------------
    */
    public function historial($usuario)
    {
        $data = $this->canje->historial($usuario["id"]);

        return [
            "success" => true,
            "message" => "Historial obtenido",
            "data" => $data
        ];
    }

    public function obtenerTodos()
    {
        $data = $this->canje->obtenerTodos();

        return [
            "success" => true,
            "message" => "Canjes obtenidos",
            "data" => $data
        ];
    }
    public function actualizarEstado($data)
    {
        $id = $data["id"] ?? null;
        $estado = $data["estado"] ?? null;

        if (!$id || !$estado) {

            return [
                "success" => false,
                "message" => "Datos incompletos"
            ];
        }

        $estadosValidos = [
            "pendiente",
            "entregado",
            "cancelado"
        ];

        if (!in_array($estado, $estadosValidos)) {

            return [
                "success" => false,
                "message" => "Estado inválido"
            ];
        }

        $result =
            $this->canje->actualizarEstado(
                $id,
                $estado
            );

        if ($result) {

            return [
                "success" => true,
                "message" => "Estado actualizado"
            ];
        }

        return [
            "success" => false,
            "message" => "Error al actualizar"
        ];
    }
}