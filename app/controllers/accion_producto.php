<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $id = !empty($_POST['id_producto']) ? (int)$_POST['id_producto'] : null;
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $cantidad_por_tanda = (int)$_POST['cantidad_por_tanda'];
    $imagen_url = trim($_POST['imagen_url']);

    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();

        if ($id) {
            $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, cantidad_por_tanda = ?, imagen_url = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $precio, $cantidad_por_tanda, $imagen_url, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, cantidad_por_tanda, imagen_url, stock) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->execute([$nombre, $descripcion, $precio, $cantidad_por_tanda, $imagen_url]);
        }
    } catch (PDOException $e) {
        die("Error al guardar el producto: " . $e->getMessage());
    }
}

header("Location: /?ruta=inventario");
exit();
?>