<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/historial_reservas.css">
</head>
<body>
    <div class="navbar">
        <h2>Historial Completo de Reservas</h2>
        <div>
            <a href="/?ruta=panel_admin" style="background-color: #34495e; margin-right: 10px;">Volver al Panel</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <form action="/?ruta=historial_reservas" method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 500px;">
                <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por N° Ticket o DNI..." style="margin: 0; flex: 1;">
                <button type="submit" style="width: auto; margin: 0; background-color: #2980b9;">Buscar</button>
                <?php if($busqueda !== ''): ?>
                    <a href="/?ruta=historial_reservas" style="padding: 10px; background-color: #95a5a6; color: white; text-decoration: none; border-radius: 4px;">Limpiar</a>
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
                        <th>Estado y Detalles</th>
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
                            <td style="font-weight: bold; color: #7f8c8d;"><?= $limite ?></td>
                            <td class="estado-<?= $res['estado'] ?>" style="<?= $res['estado'] === 'cancelado' ? 'color: #c0392b;' : '' ?>">
                                <strong><?= ucfirst($res['estado']) ?></strong>
                                <?php if ($res['estado'] === 'cancelado' && $res['cancelado_por']): ?>
                                    <br><small style="color: #7f8c8d; font-weight: normal; display: block; margin-top: 4px;">
                                        Por: <?= ucfirst($res['cancelado_por']) ?><br>
                                        Motivo: <?= htmlspecialchars($res['motivo_cancelacion']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservas)): ?>
                        <tr><td colspan="8" style="text-align: center;">No hay historial de reservas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>