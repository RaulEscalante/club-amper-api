<?php

class Canje
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | Crear cabecera canje
    |--------------------------------------------------------------------------
    */

    public function crearCabecera(
        $usuario_id,
        $total_puntos
    ) {

        $sql = "
            INSERT INTO canjes
            (
                usuario_id,
                total_puntos
            )
            VALUES
            (
                :usuario_id,
                :total_puntos
            )
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ":usuario_id" => $usuario_id,
            ":total_puntos" => $total_puntos
        ]);

        return $this->conn->lastInsertId();
    }

    /*
    |--------------------------------------------------------------------------
    | Insertar detalle
    |--------------------------------------------------------------------------
    */

    public function insertarDetalle(
        $canje_id,
        $producto
    ) {

        $sql = "
            INSERT INTO detalle_canje
            (
                canje_id,
                producto_id,
                nombre_producto,
                cantidad,
                puntos_unitarios,
                subtotal_puntos
            )
            VALUES
            (
                :canje_id,
                :producto_id,
                :nombre_producto,
                :cantidad,
                :puntos_unitarios,
                :subtotal_puntos
            )
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":canje_id" =>
                $canje_id,

            ":producto_id" =>
                $producto["producto_id"],

            ":nombre_producto" =>
                $producto["nombre_producto"],

            ":cantidad" =>
                $producto["cantidad"],

            ":puntos_unitarios" =>
                $producto["puntos_unitarios"],

            ":subtotal_puntos" =>
                $producto["subtotal_puntos"]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Descontar stock
    |--------------------------------------------------------------------------
    */

    public function descontarStock(
        $producto_id,
        $cantidad = 1
    ) {

        $sql = "
            UPDATE productos
            SET stock = stock - :cantidad
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":cantidad" => $cantidad,
            ":id" => $producto_id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Descontar puntos usuario
    |--------------------------------------------------------------------------
    */

    public function descontarPuntos(
        $usuario_id,
        $puntos
    ) {

        $sql = "
            UPDATE usuarios
            SET puntos = puntos - :puntos
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":puntos" => $puntos,
            ":id" => $usuario_id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Historial usuario
    |--------------------------------------------------------------------------
    */

    public function historial($usuario_id)
    {
        $sql = "
            SELECT
                c.id,
                c.total_puntos,
                c.fecha,
                c.estado,           
                
                d.nombre_producto,
                d.cantidad,
                d.subtotal_puntos

            FROM canjes c

            INNER JOIN detalle_canje d
                ON d.canje_id = c.id

            WHERE c.usuario_id = :usuario_id

            ORDER BY c.id DESC
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ":usuario_id" => $usuario_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos()
    {
        $sql = "
        SELECT
            c.id,
            c.total_puntos,
            c.estado,
            c.fecha,

            u.nombres,
            u.apellidos,
            u.documento,

            d.nombre_producto,
            d.cantidad,
            d.subtotal_puntos

        FROM canjes c

        INNER JOIN usuarios u
            ON u.id = c.usuario_id

        INNER JOIN detalle_canje d
            ON d.canje_id = c.id

        ORDER BY c.id DESC
    ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function actualizarEstado(
        $canje_id,
        $estado
    ) {
        $sql = "
        UPDATE canjes
        SET estado = :estado
        WHERE id = :id
    ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":estado" => $estado,
            ":id" => $canje_id
        ]);
    }
}