<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$p     = ['id' => '', 'nombre' => '', 'descripcion' => '', 'precio' => '', 'cantidad_por_tanda' => '', 'imagen_url' => ''];
$titulo = "Agregar Nuevo Producto";

if (isset($_GET['id'])) {
    $titulo = "Editar Producto";
    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al cargar el producto.");
    }
}

require_once __DIR__ . '/../views/producto_form.php';
