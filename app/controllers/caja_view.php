<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$conexion = new Conexion();
$pdo = $conexion->conectar();

$productos_db = $pdo->query("SELECT * FROM productos WHERE stock > 0 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$reserva_id = isset($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : null;
$cliente_nombre = '';
$cliente_dni = '';
$items_reserva_json = '{}';
$es_reserva = 'false';

if ($reserva_id) {
    $stmt = $pdo->prepare("SELECT r.id, u.nombre_completo, u.dni FROM reservas r JOIN usuarios u ON r.usuario_id = u.id WHERE r.id = ? AND r.estado = 'pendiente'");
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reserva) {
        $cliente_nombre = $reserva['nombre_completo'];
        $cliente_dni    = $reserva['dni'];
        $es_reserva     = 'true';

        $stmtDet = $pdo->prepare("SELECT dr.producto_id, p.nombre, p.precio, dr.cantidad FROM detalle_reservas dr JOIN productos p ON dr.producto_id = p.id WHERE dr.reserva_id = ?");
        $stmtDet->execute([$reserva_id]);
        $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($detalles as $d) {
            $items[$d['producto_id']] = [
                'nombre'   => $d['nombre'],
                'precio'   => (float)$d['precio'],
                'cantidad' => (int)$d['cantidad'],
                'maxStock' => (int)$d['cantidad'],
            ];
        }
        $items_reserva_json = json_encode($items);
    } else {
        header("Location: /?ruta=caja");
        exit();
    }
}

require_once __DIR__ . '/../views/caja.php';
