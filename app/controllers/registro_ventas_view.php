<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$ventas          = [];
$error_db        = '';
$busqueda        = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$total_filtrado  = 0;

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    $sql = "SELECT v.id, v.cliente_nombre, v.cliente_dni, v.total, v.fecha, v.reserva_id,
                   STRING_AGG(dv.cantidad || 'x ' || p.nombre || ' (Bs. ' || dv.precio_unitario || ')', '<br>') as detalles
            FROM ventas v
            LEFT JOIN detalle_ventas dv ON v.id = dv.venta_id
            LEFT JOIN productos p ON dv.producto_id = p.id";

    if ($busqueda !== '') {
        $sql .= " WHERE v.cliente_dni ILIKE :busqueda OR CAST(v.fecha AS TEXT) LIKE :busqueda";
    }

    $sql .= " GROUP BY v.id ORDER BY v.fecha DESC";

    $stmt = $pdo->prepare($sql);

    if ($busqueda !== '') {
        $stmt->execute(['busqueda' => '%' . $busqueda . '%']);
    } else {
        $stmt->execute();
    }

    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ventas as $v) {
        $total_filtrado += $v['total'];
    }
} catch (PDOException $e) {
    $error_db = "Error al consultar las ventas: " . $e->getMessage();
}

require_once __DIR__ . '/../views/registro_ventas.php';
