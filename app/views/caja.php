<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja Registradora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/caja.css">
</head>
<body>
    <div class="navbar">
        <h2>Punto de Venta (POS)</h2>
        <div>
            <a href="/?ruta=panel_admin" style="background-color: #34495e; margin-right: 10px;">Volver al Panel</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <?php if(isset($_GET['exito'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 8px; text-align: center; font-weight: bold;">
            ¡Venta registrada correctamente!
        </div>
    <?php endif; ?>

    <div class="layout-catalogo">
        <div class="columna-sidebar">
            <div class="tarjeta-pedido">
                <h3>Detalle de Venta <?= $reserva_id ? "(Ticket #$reserva_id)" : '' ?></h3>
                
                <form id="form-caja" action="/?ruta=procesar_venta" method="POST">
                    <input type="hidden" name="reserva_id" value="<?= $reserva_id ?>">
                    
                    <label>DNI Cliente:</label>
                    <input type="text" name="cliente_dni" value="<?= htmlspecialchars($cliente_dni) ?>" placeholder="Opcional (Mostrador)" <?= $reserva_id ? 'readonly style="background-color: #eee;"' : '' ?>>
                    
                    <label>Nombre Cliente:</label>
                    <input type="text" name="cliente_nombre" value="<?= htmlspecialchars($cliente_nombre) ?>" placeholder="Opcional (Mostrador)" <?= $reserva_id ? 'readonly style="background-color: #eee;"' : '' ?>>
                    
                    <div id="items-carrito" style="margin-top: 15px; border-top: 2px solid #34495e; padding-top: 15px;"></div>
                    
                    <h2 id="total-carrito" class="total-pedido" style="text-align: right; font-size: 1.8em; color: #27ae60;">Total: Bs. 0.00</h2>
                    
                    <button type="submit" id="btn-cobrar" class="btn-reservar" style="font-size: 1.2em; padding: 15px; display: none;">Confirmar Pago y Vender</button>
                </form>
            </div>
        </div>

        <div class="columna-productos">
            <h3><?= $reserva_id ? 'Productos Reservados (Solo lectura)' : 'Catálogo de Mostrador' ?></h3>
            <div class="grid">
                <?php foreach ($productos_db as $prod): ?>
                    <?php $imgUrl = !empty($prod['imagen_url']) ? htmlspecialchars($prod['imagen_url']) : 'https://via.placeholder.com/300x150?text=Sin+Imagen'; ?>
                    <div class="card" style="<?= $reserva_id ? 'opacity: 0.5; pointer-events: none;' : '' ?>">
                        <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                        <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                        <h4>Bs. <?= number_format($prod['precio'], 2) ?></h4>
                        <p>Stock: <b><?= $prod['stock'] ?></b></p>
                        <button onclick="agregarAlCarrito(<?= $prod['id'] ?>, '<?= addslashes($prod['nombre']) ?>', <?= $prod['precio'] ?>, <?= $prod['stock'] ?>)" class="btn-reservar" style="margin-top: 5px;">Agregar</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        const esReserva = <?= $es_reserva ?>;
        let carrito = <?= $items_reserva_json ?>;

        function agregarAlCarrito(id, nombre, precio, maxStock) {
            if (esReserva) return;
            if (carrito[id]) {
                if (carrito[id].cantidad < maxStock) carrito[id].cantidad++;
                else alert("Stock máximo alcanzado");
            } else {
                carrito[id] = { nombre: nombre, precio: parseFloat(precio), cantidad: 1, maxStock: maxStock };
            }
            actualizarCarritoUI();
        }

        function sumarDelCarrito(id) {
            if (esReserva) return;
            if (carrito[id].cantidad < carrito[id].maxStock) {
                carrito[id].cantidad++;
                actualizarCarritoUI();
            }
        }

        function restarDelCarrito(id) {
            if (esReserva) return;
            carrito[id].cantidad--;
            if (carrito[id].cantidad < 1) delete carrito[id];
            actualizarCarritoUI();
        }

        function actualizarCarritoUI() {
            const items = document.getElementById('items-carrito');
            const total = document.getElementById('total-carrito');
            const btnCobrar = document.getElementById('btn-cobrar');
            
            items.innerHTML = '';
            let suma = 0;
            let count = 0;

            for (let id in carrito) {
                count++;
                let item = carrito[id];
                let subtotal = item.precio * item.cantidad;
                suma += subtotal;
                
                let controles = esReserva ? `<b>${item.cantidad}</b>` : `
                    <button type="button" class="btn-cant btn-sumar" onclick="sumarDelCarrito(${id})">+</button>
                    <button type="button" class="btn-cant btn-restar" onclick="restarDelCarrito(${id})">-</button>
                `;

                items.innerHTML += `
                    <div class="item-carrito">
                        <span class="nombre-item-carrito">${item.cantidad}x ${item.nombre}</span>
                        <span class="precio-item">Bs. ${subtotal.toFixed(2)}</span>
                        <div class="controles-cantidad">${controles}</div>
                        <input type="hidden" name="productos[${id}]" value="${item.cantidad}">
                    </div>
                `;
            }

            total.innerHTML = `Total: Bs. ${suma.toFixed(2)}`;
            btnCobrar.style.display = count > 0 ? 'block' : 'none';
        }

        actualizarCarritoUI();
    </script>
</body>
</html>