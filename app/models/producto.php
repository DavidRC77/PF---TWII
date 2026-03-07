<?php
class Producto {
    private $id;
    private $nombre;
    private $descripcion;
    private $precio;
    private $stock;
    private $proxima_tanda;
    private $cantidad_por_tanda;
    private $imagen_url;

    public function __construct($id, $nombre, $descripcion, $precio, $stock, $proxima_tanda = null, $cantidad_por_tanda = 0, $imagen_url = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->proxima_tanda = $proxima_tanda;
        $this->cantidad_por_tanda = $cantidad_por_tanda;
        $this->imagen_url = $imagen_url;
    }

    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getPrecio() { return $this->precio; }
    public function getStock() { return $this->stock; }
    public function getProximaTanda() { return $this->proxima_tanda; }
    public function getCantidadPorTanda() { return $this->cantidad_por_tanda; }
    public function getImagenUrl() { return $this->imagen_url; }

    public function setId($id) { $this->id = $id; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setPrecio($precio) { $this->precio = $precio; }
    public function setStock($stock) { $this->stock = $stock; }
    public function setProximaTanda($proxima_tanda) { $this->proxima_tanda = $proxima_tanda; }
    public function setCantidadPorTanda($cantidad) { $this->cantidad_por_tanda = $cantidad; }
    public function setImagenUrl($imagen_url) { $this->imagen_url = $imagen_url; }

    public function calcularValorTotal() {
        return $this->precio * $this->stock;
    }

    public function hornear() {
        $this->stock += $this->cantidad_por_tanda;
        return $this->stock;
    }

    public function registrarMerma($cantidad_quemada) {
        if ($cantidad_quemada <= $this->stock) {
            $this->stock -= $cantidad_quemada;
            return true;
        }
        return false;
    }
}
?>
