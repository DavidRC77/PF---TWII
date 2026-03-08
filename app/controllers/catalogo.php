<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'basico' && $_SESSION['rol'] !== 'vip')) {
    header("Location: /?ruta=login");
    exit();
}

$reserva_activa = null;
$detalles_reserva = [];
$esta_penalizado = false;
$fecha_fin_penalizacion = '';
$alerta_cancelado_admin = false;
$motivo_cancelado_admin = '';
$limite_panes = ($_SESSION['rol'] === 'vip') ? 9999 : 20;
$es_vip = ($_SESSION['rol'] === 'vip') ? 'true' : 'false';

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    $stmtUser = $pdo->prepare("SELECT penalizacion, CASE WHEN penalizacion > CURRENT_TIMESTAMP THEN 1 ELSE 0 END as castigado FROM usuarios WHERE id = :uid");
    $stmtUser->execute(['uid' => $_SESSION['usuario_id']]);
    $usuario_data = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($usuario_data && $usuario_data['castigado'] == 1) {
        $esta_penalizado = true;
        $fecha_fin_penalizacion = $usuario_data['penalizacion'];
    }

    $stmtUltimo = $pdo->prepare("SELECT estado, cancelado_por, motivo_cancelacion FROM reservas WHERE usuario_id = :uid ORDER BY id DESC LIMIT 1");
    $stmtUltimo->execute(['uid' => $_SESSION['usuario_id']]);
    $ultimo_pedido = $stmtUltimo->fetch(PDO::FETCH_ASSOC);

    if ($ultimo_pedido && $ultimo_pedido['estado'] === 'cancelado' && $ultimo_pedido['cancelado_por'] === 'admin') {
        $alerta_cancelado_admin = true;
        $motivo_cancelado_admin = $ultimo_pedido['motivo_cancelacion'];
    }

    $sql = "SELECT r.*, EXTRACT(EPOCH FROM (r.fecha_expiracion - CURRENT_TIMESTAMP)) AS segundos
            FROM reservas r
            WHERE r.usuario_id = :uid AND r.estado = 'pendiente'
            ORDER BY r.id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $_SESSION['usuario_id']]);
    $reserva_activa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reserva_activa) {
        $stmtDet = $pdo->prepare("SELECT dr.*, p.nombre FROM detalle_reservas dr JOIN productos p ON dr.producto_id = p.id WHERE dr.reserva_id = :rid");
        $stmtDet->execute(['rid' => $reserva_activa['id']]);
        $detalles_reserva = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}

require_once __DIR__ . '/../views/catalogo.php';
