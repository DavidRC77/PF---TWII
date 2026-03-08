<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'basico' && $_SESSION['rol'] !== 'vip')) {
    header("Location: /?ruta=login");
    exit();
}

$reservas = [];
$error_db = '';

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    $sql = "SELECT r.id, p.nombre AS producto, dr.precio_unitario, dr.cantidad, r.fecha_creacion, r.estado, r.total, r.cancelado_por, r.motivo_cancelacion
            FROM reservas r
            JOIN detalle_reservas dr ON r.id = dr.reserva_id
            JOIN productos p ON dr.producto_id = p.id
            WHERE r.usuario_id = :usuario_id
            ORDER BY r.fecha_creacion DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_db = "Error al consultar tus reservas: " . $e->getMessage();
}

require_once __DIR__ . '/../views/mis_reservas.php';
