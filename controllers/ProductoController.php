<?php
require_once __DIR__ . "/../models/Producto.php";

class ProductoController
{
    private $producto;
    public function __construct($db)
    {
        $this->producto = new Producto($db);
    }
    /*
    |--------------------------------------------------------------------------
    | Crear producto
    |--------------------------------------------------------------------------
    */

    public function crear($data)
    {
        $codigo_sku = trim($data["codigo_sku"] ?? "");
        $nombre = trim($data["nombre"] ?? "");
        $marca = trim($data["marca"] ?? "");
        $puntos = trim($data["puntos_requeridos"] ?? "");
        $imagen = "";
        $stock = trim($data["stock"] ?? "");

        // Validaciones
        if (
            empty($codigo_sku) || empty($nombre) || empty($marca)
        ) {
            return [
                "success" => false,
                "message" => "Campos obligatorios incompletos"
            ];
        }

        if (!is_numeric($puntos) || $puntos < 0) {
            return [
                "success" => false,
                "message" => "Puntos inválidos"
            ];
        }

        if (!is_numeric($stock) || $stock < 0) {
            return [
                "success" => false,
                "message" => "Stock inválido"
            ];
        }

        if (
            isset($data["imagen"]) &&
            $data["imagen"]["error"] === 0
        ) {
            $archivo = $data["imagen"];
            /*
            |--------------------------------------------------------------------------
            | VALIDAR MIME
            |--------------------------------------------------------------------------
            */
            $permitidos = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo === false) {

                return [
                    "success" => false,
                    "message" => "Error al validar archivo"
                ];
            }
            $mimeReal = finfo_file(
                $finfo,
                $archivo["tmp_name"]
            );

            finfo_close($finfo);

            if (!in_array($mimeReal, $permitidos)) {

                return [
                    "success" => false,
                    "message" => "Formato de imagen inválido"
                ];
            }
            /*
            |--------------------------------------------------------------------------
            | VALIDAR TAMAÑO
            |--------------------------------------------------------------------------
            */
            if ($archivo["size"] > 2 * 1024 * 1024) {
                return [
                    "success" => false,
                    "message" => "La imagen excede 2MB"
                ];
            }
            /*
            |--------------------------------------------------------------------------
            | GENERAR NOMBRE SEGURO
            |--------------------------------------------------------------------------
            */
            $extensionesPermitidas = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            $extension = strtolower(
                pathinfo(
                    $archivo["name"],
                    PATHINFO_EXTENSION
                )
            );

            if (
                !in_array(
                    $extension,
                    $extensionesPermitidas
                )
            ) {

                return [
                    "success" => false,
                    "message" => "Extensión inválida"
                ];
            }
            $nombreImagen =
                uniqid() . "." . $extension;
            $rutaDestino =
                __DIR__ .
                "/../uploads/productos/" .
                $nombreImagen;
            /*
            |--------------------------------------------------------------------------
            | SUBIR
            |--------------------------------------------------------------------------
            */
            if (
                !move_uploaded_file(
                    $archivo["tmp_name"],
                    $rutaDestino
                )
            ) {

                return [
                    "success" => false,
                    "message" => "Error al subir imagen"
                ];
            }

            $imagen = $nombreImagen;
        }

        $result = $this->producto->crear(
            $codigo_sku,
            $nombre,
            $marca,
            $puntos,
            $imagen,
            $stock
        );

        if ($result) {
            return [
                "success" => true,
                "message" => "Producto creado"
            ];
        }

        return [
            "success" => false,
            "message" => "Error al crear producto"
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Listar productos
    |--------------------------------------------------------------------------
    */
    public function listar()
    {
        $usuario = getUsuarioAuth();

        if (
            $usuario &&
            (int) $usuario["rol_id"] === 1
        ) {
            $productos = $this->producto->listarAdmin();
        } else {
            $productos = $this->producto->listar();
        }
        return [
            "success" => true,
            "message" => "Productos obtenidos",
            "data" => $productos
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Editar producto
    |--------------------------------------------------------------------------
    */
    public function editar($data)
    {
        $id = $data["id"] ?? null;

        if (!$id) {
            return [
                "success" => false,
                "message" => "ID requerido"
            ];
        }

        /*
        |------------------------------------------------------------------
        | CAMPOS
        |------------------------------------------------------------------
        */
        $codigo_sku = trim($data["codigo_sku"] ?? "");
        $nombre = trim($data["nombre"] ?? "");
        $marca = trim($data["marca"] ?? "");
        $puntos = trim($data["puntos_requeridos"] ?? "");
        $stock = trim($data["stock"] ?? "");

        /*
        |------------------------------------------------------------------
        | IMAGEN ACTUAL
        |------------------------------------------------------------------
        */
        $imagen = $data["imagen_actual"] ?? "";

        /*
        |------------------------------------------------------------------
        | VALIDACIONES
        |------------------------------------------------------------------
        */
        if (
            empty($codigo_sku) ||
            empty($nombre) ||
            empty($marca)
        ) {
            return [
                "success" => false,
                "message" => "Campos obligatorios incompletos"
            ];
        }

        /*
        |------------------------------------------------------------------
        | NUEVA IMAGEN
        |------------------------------------------------------------------
        */
        if (
            isset($data["imagen"]) &&
            $data["imagen"]["error"] === 0
        ) {

            $archivo = $data["imagen"];

            /*
            |--------------------------------------------------------------
            | VALIDAR MIME
            |--------------------------------------------------------------
            */
            $permitidos = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo === false) {

                return [
                    "success" => false,
                    "message" => "Error al validar archivo"
                ];
            }

            $mimeReal = finfo_file(
                $finfo,
                $archivo["tmp_name"]
            );

            finfo_close($finfo);

            if (!in_array($mimeReal, $permitidos)) {

                return [
                    "success" => false,
                    "message" => "Formato inválido"
                ];
            }

            /*
            |--------------------------------------------------------------
            | VALIDAR TAMAÑO
            |--------------------------------------------------------------
            */
            if ($archivo["size"] > 2 * 1024 * 1024) {

                return [
                    "success" => false,
                    "message" => "Imagen excede 2MB"
                ];
            }

            /*
            |--------------------------------------------------------------
            | EXTENSIÓN
            |--------------------------------------------------------------
            */
            $extension = strtolower(
                pathinfo(
                    $archivo["name"],
                    PATHINFO_EXTENSION
                )
            );

            $extensionesPermitidas = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (
                !in_array(
                    $extension,
                    $extensionesPermitidas
                )
            ) {

                return [
                    "success" => false,
                    "message" => "Extensión inválida"
                ];
            }

            /*
            |--------------------------------------------------------------
            | NUEVO NOMBRE
            |--------------------------------------------------------------
            */
            $nombreImagen =
                uniqid() . "." . $extension;

            $rutaDestino =
                __DIR__ .
                "/../uploads/productos/" .
                $nombreImagen;

            /*
            |--------------------------------------------------------------
            | SUBIR
            |--------------------------------------------------------------
            */
            if (
                !move_uploaded_file(
                    $archivo["tmp_name"],
                    $rutaDestino
                )
            ) {

                return [
                    "success" => false,
                    "message" => "Error al subir imagen"
                ];
            }

            /*
            |--------------------------------------------------------------
            | ELIMINAR IMAGEN ANTERIOR
            |--------------------------------------------------------------
            */
            if (!empty($imagen)) {

                $rutaAnterior =
                    __DIR__ .
                    "/../uploads/productos/" .
                    $imagen;

                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            /*
            |--------------------------------------------------------------
            | NUEVA IMAGEN
            |--------------------------------------------------------------
            */
            $imagen = $nombreImagen;
        }

        /*
        |------------------------------------------------------------------
        | UPDATE
        |------------------------------------------------------------------
        */
        $result = $this->producto->editar(
            $id,
            $codigo_sku,
            $nombre,
            $marca,
            $puntos,
            $imagen,
            $stock
        );

        if ($result) {
            return [
                "success" => true,
                "message" => "Producto actualizado"
            ];
        }

        return [
            "success" => false,
            "message" => "Error al actualizar producto"
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Desactivar
    |--------------------------------------------------------------------------
    */
    public function desactivar($id)
    {
        $result = $this->producto->desactivar($id);

        if ($result) {
            return [
                "success" => true,
                "message" => "Producto desactivado"
            ];
        }

        return [
            "success" => false,
            "message" => "Error al desactivar producto"
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Reactivar
    |--------------------------------------------------------------------------
    */
    public function reactivar($id)
    {
        $result = $this->producto->reactivar($id);

        if ($result) {
            return [
                "success" => true,
                "message" => "Producto reactivado"
            ];
        }

        return [
            "success" => false,
            "message" => "Error al reactivar producto"
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Eliminar
    |--------------------------------------------------------------------------
    */
    public function eliminar($id)
    {
        return $this->producto->eliminar($id);
    }
}