<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/registro_ventas.css">
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
        <div class="barra-superior">
            <form class="barra-busqueda" action="/?ruta=registro_ventas" method="GET">
                <input type="hidden" name="ruta" value="registro_ventas">
                <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por DNI o Fecha (YYYY-MM-DD)...">
                <button type="submit">Buscar</button>
                <?php if($busqueda !== ''): ?>
                    <a href="/?ruta=registro_ventas">Limpiar</a>
                <?php endif; ?>
            </form>
            <div class="badge-total">Total Reporte: Bs. <?= number_format($total_filtrado, 2) ?></div>
        </div>

        <?php if ($error_db): ?>
            <div class="error"><?= $error_db ?></div>
        <?php else: ?>
            <div class="tabla-scroll">
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
            </div>
        <?php endif; ?>
    </div>
</body>
</html>