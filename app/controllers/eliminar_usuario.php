<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $id = (int)$_POST['id_usuario'];
    // No permitir que el admin se elimine a sí mismo
    if ($id !== (int)$_SESSION['usuario_id']) {
        try {
            $conexion = new Conexion();
            $pdo = $conexion->conectar();
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // No se puede eliminar si tiene registros relacionados (FK)
        }
    }
}

session_write_close();
header("Location: /?ruta=gestionar_usuarios_admin");
exit();
