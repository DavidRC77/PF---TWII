<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['cantidad'])) {
    $id_producto = (int)$_POST['id'];
    $cantidad    = (int)$_POST['cantidad'];

    if ($cantidad > 0) {
        try {
            $conexion = new Conexion();
            $pdo = $conexion->conectar();

            $stmtVerificar = $pdo->prepare("SELECT stock FROM productos WHERE id = :id");
            $stmtVerificar->execute(['id' => $id_producto]);
            $producto = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

            if ($producto && $producto['stock'] >= $cantidad) {
                $stmtUpdate = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :id");
                $stmtUpdate->execute(['cantidad' => $cantidad, 'id' => $id_producto]);
            }

        } catch (PDOException $e) {
            die("Error al registrar la merma: " . $e->getMessage());
        }
    }
}

session_write_close();
header("Location: /?ruta=inventario");
exit();
?>
