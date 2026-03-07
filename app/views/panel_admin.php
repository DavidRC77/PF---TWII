<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="public/assets/css/estilos.css">
</head>
<body>
    <div class="navbar">
        <h2>Panel de Control - Panadería</h2>
        <div>
            <span class="saludo-usuario">Hola, <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <div class="grid">
            <div class="tarjeta-admin">
                <h3>📦 Inventario</h3>
                <p>Gestionar stock, mermas y productos.</p>
                <a href="/?ruta=inventario" class="btn-panel" style="background-color: #27ae60;">Ir a Inventario</a>
            </div>

            <div class="tarjeta-admin">
                <h3>⏱️ Reservas</h3>
                <p>Ver y entregar los pedidos del día.</p>
                <a href="/?ruta=gestionar_reservas" class="btn-panel" style="background-color: #f39c12;">Ver Reservas</a>
            </div>

            <div class="tarjeta-admin">
                <h3>🗄️ Historial Reservas</h3>
                <p>Registro completo de pedidos pasados.</p>
                <a href="/?ruta=historial_reservas" class="btn-panel" style="background-color: #d35400;">Ver Historial</a>
            </div>

            <div class="tarjeta-admin">
                <h3>👥 Usuarios</h3>
                <p>Administrar clientes y roles VIP.</p>
                <a href="/?ruta=gestionar_usuarios" class="btn-panel" style="background-color: #8e44ad;">Gestionar Usuarios</a>
            </div>

            <div class="tarjeta-admin">
                <h3>🛒 Caja</h3>
                <p>Punto de venta físico para el mostrador.</p>
                <a href="/?ruta=caja" class="btn-panel" style="background-color: #27ae60;">Abrir Caja</a>
            </div>

            <div class="tarjeta-admin">
                <h3>📊 Registro de Ventas</h3>
                <p>Historial contable de todos los ingresos.</p>
                <a href="/?ruta=registro_ventas" class="btn-panel" style="background-color: #2980b9;">Ver Reporte</a>
            </div>
        </div>
    </div>
</body>
</html>