<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$ventas = [];
$error_db = '';
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$total_filtrado = 0;

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    $sql = "SELECT v.id, v.cliente_nombre, v.cliente_dni, v.total, v.fecha, v.reserva_id, 
                   STRING_AGG(dv.cantidad || 'x ' || p.nombre || ' (Bs. ' || dv.precio_unitario || ')', '<br>') as detalles 
            FROM ventas v 
            LEFT JOIN detalle_ventas dv ON v.id = dv.venta_id 
            LEFT JOIN productos p ON dv.producto_id = p.id ";
            
    if ($busqueda !== '') {
        $sql .= " WHERE v.cliente_dni ILIKE :busqueda OR CAST(v.fecha AS TEXT) LIKE :busqueda ";
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Ventas</title>
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
</head>
<body>
    <div class="navbar">
        <h2>Registro Contable de Ventas</h2>
        <div>
            <a href="/?ruta=panel_admin" style="background-color: #34495e; margin-right: 10px;">Volver al Panel</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <form action="/?ruta=registro_ventas" method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 500px;">
                <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por DNI o Fecha (YYYY-MM-DD)..." style="margin: 0; flex: 1;">
                <button type="submit" style="width: auto; margin: 0; background-color: #2980b9;">Buscar</button>
                <?php if($busqueda !== ''): ?>
                    <a href="/?ruta=registro_ventas" style="padding: 10px; background-color: #95a5a6; color: white; text-decoration: none; border-radius: 4px;">Limpiar</a>
                <?php endif; ?>
            </form>
            
            <div style="background-color: #27ae60; color: white; padding: 10px 20px; border-radius: 4px; font-size: 1.2em; font-weight: bold;">
                Total Reporte: Bs. <?= number_format($total_filtrado, 2) ?>
            </div>
        </div>

        <?php if ($error_db): ?>
            <div class="error"><?= $error_db ?></div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>N° Venta</th>
                        <th>Cliente</th>
                        <th>DNI</th>
                        <th>Origen</th>
                        <th>Detalle de Productos</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Total (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v): ?>
                        <?php 
                            $tiempo = strtotime($v['fecha']);
                            $fecha = date('Y-m-d', $tiempo);
                            $hora = date('H:i:s', $tiempo);
                        ?>
                        <tr>
                            <td><strong>#<?= $v['id'] ?></strong></td>
                            <td><?= htmlspecialchars($v['cliente_nombre']) ?></td>
                            <td><?= htmlspecialchars($v['cliente_dni']) ?></td>
                            <td>
                                <?php if ($v['reserva_id']): ?>
                                    <span style="color: #f39c12; font-weight: bold;">Reserva #<?= $v['reserva_id'] ?></span>
                                <?php else: ?>
                                    <span style="color: #2980b9; font-weight: bold;">Mostrador</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.9em; line-height: 1.4; color: #34495e;">
                                <?= $v['detalles'] ? $v['detalles'] : '<span style="color:red;">Error al cargar detalle</span>' ?>
                            </td>
                            <td><?= $fecha ?></td>
                            <td><?= $hora ?></td>
                            <td style="font-weight: bold; color: #27ae60;">Bs. <?= number_format($v['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ventas)): ?>
                        <tr><td colspan="8" style="text-align: center;">No hay ventas registradas que coincidan con la búsqueda.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>