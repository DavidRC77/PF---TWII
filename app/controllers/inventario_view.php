<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$productos = [];
$total_inventario = 0;

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    $productos = $pdo->query("SELECT * FROM productos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productos as $prod) {
        $total_inventario += ($prod['precio'] * $prod['stock']);
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

require_once __DIR__ . '/../views/inventario.php';
