<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/inventario.css">
</head>
<body>
    <div class="navbar">
        <h2>Gestión de Inventario</h2>
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
                    <th>Horneados / Próx. Tanda</th>
                    <th>Pérdidas</th>
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
                        <td class="td-horneados">
                            <button type="button" class="btn-hornear"
                                onclick="agregarTanda(<?= $prod['id'] ?>, <?= $prod['cantidad_por_tanda'] ?>)">
                                Hornear (+<?= $prod['cantidad_por_tanda'] ?>)
                            </button>
                            <span id="badge-tanda-<?= $prod['id'] ?>" class="badge-tanda" style="display:none;"></span>
                            <input type="time" class="input-proxima-tanda"
                                data-id="<?= $prod['id'] ?>"
                                data-original="<?= $prod['proxima_tanda'] ? date('H:i', strtotime($prod['proxima_tanda'])) : '' ?>"
                                value="<?= $prod['proxima_tanda'] ? date('H:i', strtotime($prod['proxima_tanda'])) : '' ?>"
                                onchange="actualizarBotonGuardar()">
                        </td>
                        <td class="td-mermas">
                            <div class="form-inline form-accion">
                                <input type="number" id="merma-input-<?= $prod['id'] ?>"
                                    min="1" max="<?= $prod['stock'] ?>"
                                    class="input-merma"
                                    <?= $prod['stock'] == 0 ? 'disabled' : '' ?>>
                                <button type="button" class="btn-merma"
                                    onclick="agregarMerma(<?= $prod['id'] ?>, <?= $prod['stock'] ?>)"
                                    <?= $prod['stock'] == 0 ? 'disabled' : '' ?>>Agregar</button>
                                <span id="badge-merma-<?= $prod['id'] ?>" class="badge-merma" style="display:none;"></span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="8" class="td-vacio">No hay productos registrados en el inventario.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        <div class="total-inventario">Valor Total del Inventario: <strong>Bs. <?= number_format($total_inventario, 2) ?></strong></div>
    </div>

    <div class="contenedor-guardar-batch">
        <form id="form-batch" action="/?ruta=batch_inventario" method="POST">
            <input type="hidden" name="back" value="inventario">
            <div id="hidden-inputs"></div>
            <button type="button" class="btn-guardar-batch" id="btn-guardar-batch" disabled onclick="guardarCambios()">
                Guardar Cambios
            </button>
        </form>
    </div>

    <script>
        let pendingHorneados = {};
        let pendingMermas = {};

        function agregarTanda(id, cantPorTanda) {
            if (!pendingHorneados[id]) pendingHorneados[id] = { tandas: 0, cantPorTanda: cantPorTanda };
            pendingHorneados[id].tandas++;
            const badge = document.getElementById('badge-tanda-' + id);
            badge.style.display = 'inline-block';
            const total = pendingHorneados[id].tandas * cantPorTanda;
            badge.textContent = pendingHorneados[id].tandas + ' tanda(s) (+' + total + ')';
            actualizarBotonGuardar();
        }

        function agregarMerma(id, stockActual) {
            const input = document.getElementById('merma-input-' + id);
            const cant = parseInt(input.value);
            if (!cant || cant <= 0) return;
            const totalMerma = (pendingMermas[id] || 0) + cant;
            if (totalMerma > stockActual) {
                alert('La merma total (' + totalMerma + ') supera el stock actual (' + stockActual + ').');
                return;
            }
            pendingMermas[id] = totalMerma;
            input.value = '';
            const badge = document.getElementById('badge-merma-' + id);
            badge.style.display = 'inline-block';
            badge.textContent = '\u2212' + pendingMermas[id] + ' uds.';
            actualizarBotonGuardar();
        }

        function actualizarBotonGuardar() {
            const btn = document.getElementById('btn-guardar-batch');
            const hasHorneados = Object.keys(pendingHorneados).length > 0;
            const hasMermas    = Object.keys(pendingMermas).length > 0;
            const hasProxima   = Array.from(document.querySelectorAll('.input-proxima-tanda'))
                                    .some(el => el.value !== el.dataset.original);
            btn.disabled = !(hasHorneados || hasMermas || hasProxima);
        }

        function guardarCambios() {
            const form      = document.getElementById('form-batch');
            const container = document.getElementById('hidden-inputs');
            container.innerHTML = '';

            for (let id in pendingHorneados) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'horneados[' + id + ']';
                inp.value = pendingHorneados[id].tandas;
                container.appendChild(inp);
            }

            for (let id in pendingMermas) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'mermas[' + id + ']';
                inp.value = pendingMermas[id];
                container.appendChild(inp);
            }

            document.querySelectorAll('.input-proxima-tanda').forEach(el => {
                if (el.value !== el.dataset.original) {
                    const inp = document.createElement('input');
                    inp.type  = 'hidden';
                    inp.name  = 'proxima_tanda[' + el.dataset.id + ']';
                    inp.value = el.value;
                    container.appendChild(inp);
                }
            });

            form.submit();
        }
    </script>
</body>
</html>