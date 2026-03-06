<?php
session_start();
require_once '../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_producto = (int)$_POST['id'];

    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();

        $stmt = $pdo->prepare("UPDATE productos SET stock = stock + cantidad_por_tanda WHERE id = :id");
        $stmt->execute(['id' => $id_producto]);

    } catch (PDOException $e) {
        die("Error al registrar el horneado: " . $e->getMessage());
    }
}

header("Location: ../views/inventario.php");
exit();
?>
