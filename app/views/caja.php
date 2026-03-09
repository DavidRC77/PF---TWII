<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="public/assets/img/Horno.png">
    <title>Caja Registradora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/caja.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <div class="navbar">
        <h2>Caja Registradora</h2>
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
                <h3>Detalle de Venta <?= $reserva_id ? "<span class='badge-ticket'>#$reserva_id</span>" : '' ?></h3>

                <form id="form-caja" action="/?ruta=procesar_venta" method="POST">
                    <input type="hidden" name="reserva_id" value="<?= $reserva_id ?>">

                    <div class="campo-caja">
                        <label class="label-caja">DNI / NIT</label>
                        <div class="grupo-dni">
                            <input type="text" name="cliente_dni" id="input-dni"
                                value="<?= htmlspecialchars($cliente_dni) ?>"
                                placeholder="Ej: 12345678"
                                <?= $reserva_id ? 'readonly' : 'oninput="limpiarBusqueda()"' ?>
                                class="input-caja <?= $reserva_id ? 'readonly' : '' ?>">
                            <?php if (!$reserva_id): ?>
                            <button type="button" class="btn-buscar-dni" onclick="buscarCliente()">Buscar</button>
                            <?php endif; ?>
                        </div>
                        <div id="msg-dni" class="slot-msg-dni"></div>
                    </div>

                    <div class="campo-caja">
                        <label class="label-caja">Nombre del Cliente</label>
                        <input type="text" name="cliente_nombre" id="input-nombre"
                            value="<?= htmlspecialchars($cliente_nombre) ?>"
                            placeholder="Ej: Juan Pérez"
                            <?= $reserva_id ? 'readonly' : '' ?>
                            class="input-caja <?= $reserva_id ? 'readonly' : '' ?>">
                    </div>

                    <hr class="separador">

                    <div id="items-carrito"></div>

                    <div id="total-carrito" class="total-pedido">Total: Bs. 0.00</div>

                    <button type="button" id="btn-cobrar" class="btn-cobrar" style="display:none;" onclick="registrarVenta()">Registrar Venta</button>
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

        function buscarCliente() {
            const dni = document.getElementById('input-dni').value.trim();
            const msg = document.getElementById('msg-dni');
            if (!dni) { msg.textContent = ''; return; }
            fetch(`/?ruta=buscar_cliente&dni=${encodeURIComponent(dni)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.encontrado) {
                        document.getElementById('input-nombre').value = data.nombre;
                        msg.className = 'slot-msg-dni msg-ok';
                        msg.textContent = '\u2713 Cliente encontrado';
                    } else {
                        document.getElementById('input-nombre').value = '';
                        msg.className = 'slot-msg-dni msg-error';
                        msg.textContent = 'Cliente no registrado — ingrese el nombre manualmente';
                    }
                })
                .catch(() => { msg.textContent = ''; });
        }

        function limpiarBusqueda() {
            document.getElementById('msg-dni').className = 'slot-msg-dni';
            document.getElementById('msg-dni').textContent = '';
        }

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

        function registrarVenta() {
            const { jsPDF } = window.jspdf;

            const clienteNombre = document.querySelector('[name="cliente_nombre"]').value.trim() || 'Cliente Mostrador';
            const clienteDni    = document.querySelector('[name="cliente_dni"]').value.trim() || '0';
            const reservaIdVal  = document.querySelector('[name="reserva_id"]').value;

            // Calcular altura dinámica del ticket
            const filas = Object.keys(carrito).length;
            const altoBase = 95;
            const altoPorFila = 7;
            const altoTotal = altoBase + filas * altoPorFila;

            const doc = new jsPDF({ unit: 'mm', format: [80, altoTotal], orientation: 'portrait' });
            const mx = 5;           // margen x
            const pw = 80;          // ancho papel
            let y = 10;

            // Encabezado
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(13);
            doc.text('PANADERÍA EL HORNO', pw / 2, y, { align: 'center' }); y += 6;
            doc.setFontSize(9);
            doc.text('TICKET DE VENTA', pw / 2, y, { align: 'center' }); y += 5;
            doc.setLineWidth(0.3);
            doc.line(mx, y, pw - mx, y); y += 5;

            // Datos cliente
            const ahora = new Date();
            const fecha = ahora.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const hora  = ahora.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text(`Fecha: ${fecha}   Hora: ${hora}`, mx, y); y += 5;
            doc.text(`Cliente: ${clienteNombre}`, mx, y); y += 5;
            doc.text(`DNI/NIT: ${clienteDni}`, mx, y); y += 5;
            if (esReserva && reservaIdVal) {
                doc.text(`N\u00b0 Reserva: #${reservaIdVal}`, mx, y); y += 5;
            }
            doc.line(mx, y, pw - mx, y); y += 5;

            // Cabecera de items
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text('PRODUCTO', mx, y);
            doc.text('CANT', 46, y, { align: 'center' });
            doc.text('SUBTOTAL', pw - mx, y, { align: 'right' });
            y += 3;
            doc.line(mx, y, pw - mx, y); y += 5;

            // Items
            doc.setFont('helvetica', 'normal');
            let totalVenta = 0;
            for (let id in carrito) {
                const item     = carrito[id];
                const subtotal = item.precio * item.cantidad;
                totalVenta    += subtotal;
                const nombre   = item.nombre.length > 22 ? item.nombre.substring(0, 21) + '.' : item.nombre;
                doc.text(nombre, mx, y);
                doc.text(String(item.cantidad), 46, y, { align: 'center' });
                doc.text(`Bs. ${subtotal.toFixed(2)}`, pw - mx, y, { align: 'right' });
                y += 6;
            }

            doc.line(mx, y, pw - mx, y); y += 6;

            // Total
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.text(`TOTAL: Bs. ${totalVenta.toFixed(2)}`, pw - mx, y, { align: 'right' }); y += 8;

            // Pie
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.text('\u00a1Gracias por su compra!', pw / 2, y, { align: 'center' }); y += 5;
            doc.text('Panader\u00eda El Horno', pw / 2, y, { align: 'center' });

            // Descargar PDF y luego enviar el form
            doc.save(`ticket_${ahora.getTime()}.pdf`);
            document.getElementById('form-caja').submit();
        }
    </script>
</body>
</html>