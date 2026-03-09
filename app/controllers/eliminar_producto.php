<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $id = (int)$_POST['id_producto'];
    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        // No se puede eliminar si tiene registros relacionados (FK)
    }
}

session_write_close();
header("Location: /?ruta=inventario_admin");
exit();
