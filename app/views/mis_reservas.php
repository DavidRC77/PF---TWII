<?php
session_start();
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas</title>
    <link rel="stylesheet" href="public/assets/css/estilos.css">
</head>
<body>
    <div class="navbar">
        <h2>Mis Pedidos</h2>
        <div>
            <a href="/?ruta=catalogo" style="background-color: #34495e; margin-right: 10px;">Volver al Catálogo</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <?php if ($error_db): ?>
            <div class="error"><?= $error_db ?></div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>N° Ticket</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Subtotal Producto</th>
                        <th>Fecha de Reserva</th>
                        <th>Estado y Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $res): ?>
                        <tr>
                            <td><?= $res['id'] ?></td>
                            <td><?= htmlspecialchars($res['producto']) ?></td>
                            <td><?= $res['cantidad'] ?></td>
                            <td>Bs. <?= number_format($res['precio_unitario'] * $res['cantidad'], 2) ?></td>
                            <td><?= $res['fecha_creacion'] ?></td>
                            <td class="<?= $res['estado'] === 'pendiente' ? 'estado-pendiente' : 'estado-completado' ?>" style="<?= $res['estado'] === 'cancelado' ? 'color: #c0392b;' : '' ?>">
                                <strong><?= ucfirst($res['estado']) ?></strong>
                                <?php if ($res['estado'] === 'cancelado' && $res['cancelado_por']): ?>
                                    <br><small style="color: #7f8c8d; font-weight: normal;">
                                        Cancelado por: <?= ucfirst($res['cancelado_por']) ?><br>
                                        Motivo: <?= htmlspecialchars($res['motivo_cancelacion']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservas)): ?>
                        <tr><td colspan="6" style="text-align: center;">Aún no has hecho ninguna reserva.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>