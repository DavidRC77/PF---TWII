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
    <title>Nuevo Producto</title>
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
</head>
<body>
    <div class="navbar">
        <h2>Agregar Nuevo Pan</h2>
        <a href="/?ruta=inventario">Volver al Inventario</a>
    </div>
    
    <div class="login-box">
        <form action="/?ruta=accion_producto" method="POST">
            <input type="hidden" name="id_producto" value="">
            <input type="text" name="nombre" placeholder="Nombre del Pan" required>
            <input type="text" name="descripcion" placeholder="Descripción">
            <input type="number" name="precio" step="0.01" placeholder="Precio Unitario (Bs.)" required>
            <input type="number" name="cantidad_por_tanda" placeholder="Cantidad por Tanda" required>
            <input type="url" name="imagen_url" placeholder="URL de la imagen (opcional)">
            <button type="submit" name="accion" value="guardar">Guardar Producto</button>
        </form>
    </div>
</body>
</html>