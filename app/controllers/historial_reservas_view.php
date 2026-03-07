<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$reservas = [];
$error_db = '';
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    $sql = "SELECT r.id, u.nombre_completo AS cliente, u.dni,
                   STRING_AGG(dr.cantidad || 'x ' || p.nombre, '<br>') AS detalle_productos,
                   r.fecha_creacion, r.fecha_expiracion, r.estado, r.cancelado_por, r.motivo_cancelacion
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN detalle_reservas dr ON r.id = dr.reserva_id
            JOIN productos p ON dr.producto_id = p.id";

    if ($busqueda !== '') {
        $sql .= " WHERE CAST(r.id AS TEXT) LIKE :busqueda OR u.dni ILIKE :busqueda";
    }

    $sql .= " GROUP BY r.id, u.nombre_completo, u.dni, r.fecha_creacion, r.fecha_expiracion, r.estado, r.cancelado_por, r.motivo_cancelacion
              ORDER BY r.id DESC";

    $stmt = $pdo->prepare($sql);

    if ($busqueda !== '') {
        $stmt->execute(['busqueda' => '%' . $busqueda . '%']);
    } else {
        $stmt->execute();
    }

    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_db = "Error al consultar las reservas: " . $e->getMessage();
}

require_once __DIR__ . '/../views/historial_reservas.php';
