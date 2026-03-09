<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/inventario.css">
</head>
<body>
    <div class="navbar">
        <h2>Gestión de Inventario (Admin)</h2>
        <div>
            <a href="/?ruta=producto_form" class="btn-agregar-inv">Agregar Producto</a>
            <a href="/?ruta=panel_admin" class="btn-volver-inv">Volver al Panel</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <div class="tabla-scroll">
        <table>
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Precio Unit.</th>
                    <th>Stock Actual</th>
                    <th>Valor Total (Bs.)</th>
                    <th>Opciones</th>
                    <th>Horneados</th>
                    <th>Pérdidas</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $prod): ?>
                    <tr>
                        <td>
                            <?php if ($prod['imagen_url']): ?>
                                <img src="<?= htmlspecialchars($prod['imagen_url']) ?>" alt="Img" class="img-producto">
                            <?php else: ?>
                                <span class="sin-imagen">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($prod['nombre']) ?></td>
                        <td>Bs. <?= number_format($prod['precio'], 2) ?></td>
                        <td class="td-stock"><?= $prod['stock'] ?></td>
                        <td>Bs. <?= number_format($prod['precio'] * $prod['stock'], 2) ?></td>
                        <td>
                            <a href="/?ruta=producto_form&id=<?= $prod['id'] ?>" class="btn-editar">Editar</a>
                        </td>
                        <td>
                            <form action="/?ruta=horneados" method="POST" class="form-accion">
                                <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                <button type="submit" class="btn-hornear">Hornear (+<?= $prod['cantidad_por_tanda'] ?>)</button>
                            </form>
                        </td>
                        <td>
                            <form action="/?ruta=perdidas" method="POST" class="form-inline form-accion">
                                <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                <input type="number" name="cantidad" min="1" max="<?= $prod['stock'] ?>" class="input-merma" required <?= $prod['stock'] == 0 ? 'disabled' : '' ?>>
                                <button type="submit" class="btn-merma" <?= $prod['stock'] == 0 ? 'disabled' : '' ?>>Eliminar</button>
                            </form>
                        </td>
                        <td>
                            <form action="/?ruta=eliminar_producto" method="POST" class="form-accion"
                                  onsubmit="return confirm('¿Eliminar permanentemente el producto «<?= htmlspecialchars(addslashes($prod['nombre'])) ?>»? Esta acción no se puede deshacer.')">
                                <input type="hidden" name="id_producto" value="<?= $prod['id'] ?>">
                                <button type="submit" class="btn-eliminar-admin">🗑</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="9" class="td-vacio">No hay productos registrados en el inventario.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        <div class="total-inventario">Valor Total del Inventario: <strong>Bs. <?= number_format($total_inventario, 2) ?></strong></div>
    </div>
</body>
</html>
