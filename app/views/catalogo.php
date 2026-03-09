<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Panadería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/catalogo.css">
</head>
<body>
    <div class="navbar">
        <h2>Panadería EL Horno</h2>
        <div>
            <span class="saludo-usuario">Bienvenido(a), <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
            <a href="/?ruta=mis_reservas" class="btn-mis-reservas">Mis Reservas</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <?php if ($esta_penalizado): ?>
        <div class="alerta-penalizacion">
            <strong>¡Cuenta Penalizada!</strong> No recogiste un pedido a tiempo. No podrás realizar nuevas reservas hasta el: <?= $fecha_fin_penalizacion ?>.
        </div>
    <?php endif; ?>

    <div id="banner-cerrado" class="alerta-penalizacion" style="display:none; background-color:#fef9e7; color:#c0392b; border-left-color:#c0392b;">
        <strong>¡Panadería Cerrada!</strong> Nuestro horario de atención es de <strong>06:00 a 22:00</strong>. No se pueden realizar pedidos en este momento.
    </div>

    <?php if ($alerta_cancelado_admin && !$reserva_activa): ?>
        <div class="alerta-penalizacion" style="background-color: #fef9e7; color: #d35400; border-left-color: #d35400;">
            <strong>Aviso Importante:</strong> Su pedido anterior fue cancelado por la panadería. Lamentamos las molestias.<br>
            <em>Motivo: <?= htmlspecialchars($motivo_cancelado_admin) ?></em>
        </div>
    <?php endif; ?>

    <div class="layout-catalogo">
        <div class="columna-sidebar">
            <?php if ($reserva_activa): ?>
                <div class="tarjeta-pedido">
                    <h3>Tu Pedido(#<?= $reserva_activa['id'] ?>)</h3>
                    <ul class="lista-detalles">
                        <?php foreach ($detalles_reserva as $det): ?>
                            <li><?= $det['cantidad'] ?>x <?= htmlspecialchars($det['nombre']) ?> (Bs. <?= number_format($det['precio_unitario'] * $det['cantidad'], 2) ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                    <h4 class="total-pedido">Total: Bs. <?= number_format($reserva_activa['total'], 2) ?></h4>
                    
                    <div class="temporizador-caja">
                        <p>Tiempo restante para recoger:</p>
                        <h2 id="reloj">00:00</h2>
                    </div>

                    <form action="/?ruta=accion_cliente_reserva" method="POST" id="form-accion-reserva" class="formulario-acciones">
                        <input type="hidden" name="id" value="<?= $reserva_activa['id'] ?>">
                        <input type="hidden" name="accion" value="">
                        <input type="hidden" name="motivo" value="">
                        <?php if (!$reserva_activa['tiempo_ampliado']): ?>
                            <button type="button" onclick="document.getElementById('form-accion-reserva').elements['accion'].value='extender'; document.getElementById('form-accion-reserva').submit();" class="btn-extender">Extender Reserva (+15 min)</button>
                        <?php endif; ?>
                        <button type="button" onclick="cancelarPorCliente()" class="btn-cancelar-pedido">Cancelar Pedido</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="tarjeta-pedido">
                    <h3>Tu Pedido</h3>
                    <div id="carrito-vacio">
                        <p>No tienes ningún pan en tu pedido aún.</p>
                    </div>
                    <form id="form-carrito" action="/?ruta=procesar_reserva" method="POST" style="display: none;">
                        <div id="items-carrito"></div>
                        <h4 id="total-carrito" class="total-pedido">Total: Bs. 0.00</h4>
                        <button type="submit" class="btn-reservar">Confirmar Pedido</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="columna-productos">
            <h3>Nuestros Panes Recién Horneados</h3>
            <div class="grid" id="contenedor-productos">
                <p>Cargando catálogo...</p>
            </div>
        </div>
    </div>

    <script>
        const tieneReservaActiva = <?= $reserva_activa ? 'true' : 'false' ?>;
        const estaPenalizado = <?= $esta_penalizado ? 'true' : 'false' ?>;
        const limitePanes = <?= $limite_panes ?>;
        const esVip = <?= $es_vip ?>;
        let carrito = {};

        function cargarCatalogo() {
            fetch('/?ruta=api_stock')
                .then(response => response.json())
                .then(productos => {
                    const contenedor = document.getElementById('contenedor-productos');
                    contenedor.innerHTML = '';

                    productos.forEach(prod => {
                        const card = document.createElement('div');
                        card.className = 'card';
                        const imgUrl = prod.imagen_url ? prod.imagen_url : 'https://via.placeholder.com/300x150?text=Sin+Imagen';
                        const stockInfo = prod.stock > 0 ? `<p>Stock disponible: <b>${prod.stock}</b></p>` : `<p class="agotado">¡Agotado!</p>`;

                        // Próxima tanda: mostrar countdown si hay fecha futura (dentro de 4 horas)
                        let proximaTandaHtml = '';
                        if (prod.proxima_tanda) {
                            const tandasDate = new Date(prod.proxima_tanda);
                            const ahora = new Date();
                            const diffMs = tandasDate - ahora;
                            if (diffMs > 0 && diffMs < 4 * 60 * 60 * 1000) {
                                proximaTandaHtml = `<p class="proxima-tanda">🔥 Nuevos panes en <span class="countdown" data-target="${tandasDate.toISOString()}">...</span></p>`;
                            }
                        }
                        
                        let btnHtml = '';
                        if (prod.stock > 0) {
                            if (estaPenalizado) {
                                btnHtml = `<button disabled class="btn-agotado">Cuenta Restringida</button>`;
                            } else if (tieneReservaActiva) {
                                btnHtml = `<button disabled class="btn-agotado">Pedido en curso</button>`;
                            } else {
                                btnHtml = `<button onclick="agregarAlCarrito(${prod.id}, '${prod.nombre.replace(/'/g, "\\'")}', ${prod.precio}, ${prod.stock})" class="btn-reservar">Agregar al Pedido</button>`;
                            }
                        } else {
                            btnHtml = `<button disabled class="btn-agotado">Sin stock</button>`;
                        }

                        card.innerHTML = `
                            <img src="${imgUrl}" alt="${prod.nombre}" class="producto-img">
                            <h3>${prod.nombre}</h3>
                            <p class="desc-prod">${prod.descripcion || 'Sin descripción'}</p>
                            <h4>Bs. ${parseFloat(prod.precio).toFixed(2)}</h4>
                            ${stockInfo}
                            ${proximaTandaHtml}
                            ${btnHtml}
                        `;
                            ${stockInfo}
                            ${btnHtml}
                        `;
                        contenedor.appendChild(card);
                    });
                });
        }

        function obtenerTotalEnCarrito() {
            let total = 0;
            for (let id in carrito) {
                total += carrito[id].cantidad;
            }
            return total;
        }

        function agregarAlCarrito(id, nombre, precio, maxStock) {
            if (obtenerTotalEnCarrito() >= limitePanes) {
                alert("Límite máximo de " + limitePanes + " panes alcanzado." + (!esVip ? " ¡La cuenta VIP no tiene límites!" : ""));
                return;
            }
            if (carrito[id]) {
                if (carrito[id].cantidad < maxStock) {
                    carrito[id].cantidad++;
                } else {
                    alert("No hay suficiente stock para agregar más.");
                    return;
                }
            } else {
                carrito[id] = { nombre: nombre, precio: parseFloat(precio), cantidad: 1, maxStock: maxStock };
            }
            actualizarCarritoUI();
        }

        function sumarDelCarrito(id) {
            if (obtenerTotalEnCarrito() >= limitePanes) {
                alert("Límite máximo de " + limitePanes + " panes alcanzado." + (!esVip ? " Mejora tu cuenta " : ""));
                return;
            }
            if (carrito[id].cantidad < carrito[id].maxStock) {
                carrito[id].cantidad++;
                actualizarCarritoUI();
            } else {
                alert("Has alcanzado el límite de stock disponible.");
            }
        }

        function restarDelCarrito(id) {
            carrito[id].cantidad--;
            if (carrito[id].cantidad < 1) {
                delete carrito[id];
            }
            actualizarCarritoUI();
        }

        function actualizarCarritoUI() {
            const form = document.getElementById('form-carrito');
            const vacio = document.getElementById('carrito-vacio');
            const items = document.getElementById('items-carrito');
            const total = document.getElementById('total-carrito');
            
            items.innerHTML = '';
            let suma = 0;
            let count = 0;

            for (let id in carrito) {
                count++;
                let item = carrito[id];
                let subtotal = item.precio * item.cantidad;
                suma += subtotal;
                
                items.innerHTML += `
                    <div class="item-carrito">
                        <span class="nombre-item-carrito">${item.cantidad}x ${item.nombre}</span>
                        <span class="precio-item">Bs. ${subtotal.toFixed(2)}</span>
                        <div class="controles-cantidad">
                            <button type="button" class="btn-cant btn-sumar" onclick="sumarDelCarrito(${id})">+</button>
                            <button type="button" class="btn-cant btn-restar" onclick="restarDelCarrito(${id})">-</button>
                        </div>
                        <input type="hidden" name="productos[${id}]" value="${item.cantidad}">
                    </div>
                `;
            }

            if (count > 0) {
                vacio.style.display = 'none';
                form.style.display = 'block';
                total.innerHTML = `Total: Bs. ${suma.toFixed(2)}`;
                // Bloquear confirmación si está fuera de horario
                const btnConfirmar = form.querySelector('button[type="submit"]');
                if (estaFueraDeHorario()) {
                    btnConfirmar.disabled = true;
                    btnConfirmar.textContent = 'Fuera de horario (06:00–22:00)';
                } else {
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Confirmar Pedido';
                }
            } else {
                vacio.style.display = 'block';
                form.style.display = 'none';
            }
        }

        function cancelarPorCliente() {
            let motivo = prompt("¿Por qué deseas cancelar el pedido?");
            if (motivo !== null && motivo.trim() !== "") {
                let form = document.getElementById('form-accion-reserva');
                form.elements['accion'].value = 'cancelar';
                form.elements['motivo'].value = motivo;
                form.submit();
            }
        }

        if (tieneReservaActiva) {
            let tiempoRestante = <?= $reserva_activa ? max(0, (int)$reserva_activa['segundos']) : 0 ?>;
            const reloj = document.getElementById('reloj');
            
            setInterval(() => {
                if (tiempoRestante > 0) {
                    tiempoRestante--;
                    let minutos = Math.floor(tiempoRestante / 60);
                    let segundos = tiempoRestante % 60;
                    reloj.innerText = `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
                } else {
                    reloj.innerText = "00:00 (Expirado)";
                    reloj.style.color = "#c0392b";
                }
            }, 1000);
        }

        cargarCatalogo();
        if (!tieneReservaActiva && !estaPenalizado) setInterval(cargarCatalogo, 10000);

        // ── Horario de atención ──────────────────────────────────────────────
        function estaFueraDeHorario() {
            const ahora = new Date();
            const h = ahora.getHours();
            const m = ahora.getMinutes();
            const totalMin = h * 60 + m;
            return totalMin >= 21 * 60 + 30 || totalMin < 6 * 60;
        }

        function verificarHorario() {
            const banner = document.getElementById('banner-cerrado');
            if (banner) banner.style.display = estaFueraDeHorario() ? 'block' : 'none';
        }

        verificarHorario();
        setInterval(verificarHorario, 60000);

        // ── Countdown próxima tanda ──────────────────────────────────────────
        function actualizarCountdowns() {
            document.querySelectorAll('.countdown[data-target]').forEach(el => {
                const target = new Date(el.dataset.target);
                const diffMs = target - new Date();
                if (diffMs <= 0) {
                    el.textContent = '¡ya están listos!';
                    el.style.color = '#27ae60';
                } else {
                    const s = Math.ceil(diffMs / 1000);
                    const min = Math.floor(s / 60);
                    const seg = s % 60;
                    el.textContent = min > 0
                        ? min + ' min ' + seg.toString().padStart(2, '0') + 's'
                        : seg + 's';
                }
            });
        }

        setInterval(actualizarCountdowns, 1000);
    </script>
</body>
</html>