<?php
session_start();
require_once '../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../views/login.php");
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
            JOIN productos p ON dr.producto_id = p.id 
            WHERE DATE(r.fecha_creacion) = CURRENT_DATE ";
            
    if ($busqueda !== '') {
        $sql .= " AND (CAST(r.id AS TEXT) LIKE :busqueda OR u.dni ILIKE :busqueda) ";
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas de Hoy</title>
<link rel="stylesheet" href="../../public/assets/css/estilos.css"></head>
<body>
    <div class="navbar">
        <h2>Reservas de Hoy</h2>
        <div>
            <a href="../views/panel_admin.php" style="background-color: #34495e; margin-right: 10px;">Volver al Panel</a>
            <a href="../controllers/logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <form action="../views/gestionar_reservas.php" method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 500px;">
                <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por N° Ticket o DNI..." style="margin: 0; flex: 1;">
                <button type="submit" style="width: auto; margin: 0; background-color: #2980b9;">Buscar</button>
                <?php if($busqueda !== ''): ?>
                    <a href="../views/gestionar_reservas.php" style="padding: 10px; background-color: #95a5a6; color: white; text-decoration: none; border-radius: 4px;">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($error_db): ?>
            <div class="error"><?= $error_db ?></div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>N° Ticket</th>
                        <th>Cliente</th>
                        <th>DNI</th>
                        <th>Detalle de Productos</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Límite de Recojo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $res): ?>
                        <?php 
                            $tiempo_creacion = strtotime($res['fecha_creacion']);
                            $tiempo_expiracion = strtotime($res['fecha_expiracion']);
                            
                            $fecha = date('Y-m-d', $tiempo_creacion);
                            $hora = date('H:i:s', $tiempo_creacion);
                            $limite = date('H:i:s', $tiempo_expiracion);
                        ?>
                        <tr>
                            <td><?= $res['id'] ?></td>
                            <td><?= htmlspecialchars($res['cliente']) ?></td>
                            <td><?= htmlspecialchars($res['dni']) ?></td>
                            <td style="line-height: 1.4; color: #34495e; font-weight: bold;"><?= $res['detalle_productos'] ?></td>
                            <td><?= $fecha ?></td>
                            <td><?= $hora ?></td>
                            <td style="font-weight: bold; color: #f39c12;"><?= $limite ?></td>
                            <td class="estado-<?= $res['estado'] ?>" style="<?= $res['estado'] === 'cancelado' ? 'color: #c0392b;' : '' ?>">
                                <strong><?= ucfirst($res['estado']) ?></strong>
                                <?php if ($res['estado'] === 'cancelado' && $res['cancelado_por']): ?>
                                    <br><small style="color: #7f8c8d; font-weight: normal; display: block; margin-top: 4px;">
                                        Por: <?= ucfirst($res['cancelado_por']) ?><br>
                                        Motivo: <?= htmlspecialchars($res['motivo_cancelacion']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($res['estado'] === 'pendiente'): ?>
                                    <form action="../controllers/cambiar_estado_reserva.php" method="POST" style="display: inline-flex; gap: 5px;" onsubmit="return procesarAccionAdmin(this);">
                                        <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                        <input type="hidden" name="nuevo_estado" value="">
                                        <input type="hidden" name="motivo" value="">
                                        
                                        <a href="../views/caja.php?reserva_id=<?= $res['id'] ?>" style="background-color: #27ae60; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; display: flex; align-items: center;">Cobrar Ticket</a>
                                        <button type="submit" onclick="this.form.nuevo_estado.value='cancelado';" style="margin: 0; padding: 5px 10px; background-color: #c0392b; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: <?= $res['estado'] === 'entregado' ? '#27ae60' : '#c0392b' ?>; font-weight: bold;">
                                        <?= ucfirst($res['estado']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservas)): ?>
                        <tr><td colspan="9" style="text-align: center;">No hay reservas registradas el día de hoy.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function procesarAccionAdmin(form) {
            if (form.nuevo_estado.value === 'cancelado') {
                let motivo = prompt("Escribe el motivo de la cancelación (El cliente verá este mensaje):");
                if (motivo === null || motivo.trim() === "") {
                    return false;
                }
                form.motivo.value = motivo;
            }
            return true;
        }
    </script>
</body>
</html>