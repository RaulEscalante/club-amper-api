<?php

class Producto
{
    private $conn;
    private $table = "productos";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Crear producto
    public function crear($codigo_sku, $nombre, $marca, $puntos_requeridos, $imagen, $stock)
    {
        $sql = "INSERT INTO productos 
        (codigo_sku, nombre, marca, puntos_requeridos, imagen, stock)
        VALUES 
        (:codigo_sku, :nombre, :marca, :puntos_requeridos, :imagen, :stock)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":codigo_sku", $codigo_sku);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":marca", $marca);
        $stmt->bindParam(":puntos_requeridos", $puntos_requeridos);
        $stmt->bindParam(":imagen", $imagen);
        $stmt->bindParam(":stock", $stock);

        return $stmt->execute();
    }
    // Listar productos
    public function listar()
    {
        $sql = "SELECT * FROM productos where estado = 1 ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function listarAdmin()
    {
        $sql = "SELECT * FROM productos ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
    public function editar($id, $codigo_sku, $nombre, $marca, $puntos_requeridos, $imagen, $stock)
    {
        $sql = "UPDATE productos SET
        codigo_sku = :codigo_sku, nombre = :nombre,
        marca = :marca, puntos_requeridos = :puntos_requeridos,
        imagen = :imagen, stock = :stock WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":codigo_sku", $codigo_sku);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":marca", $marca);
        $stmt->bindParam(":puntos_requeridos", $puntos_requeridos);
        $stmt->bindParam(":imagen", $imagen);
        $stmt->bindParam(":stock", $stock);

        return $stmt->execute();
    }
    public function eliminar($id)
    {
        $sql = "DELETE FROM productos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    public function desactivar($id)
    {
        $sql = "UPDATE productos SET estado = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    public function reactivar($id)
    {
        $sql = "UPDATE productos 
            SET estado = 1 
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}