<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
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

session_write_close();
header("Location: /?ruta=inventario");
exit();
?>
