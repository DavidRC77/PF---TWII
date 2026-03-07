<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}
// Esta vista fue reemplazada por producto_form.php
header("Location: /?ruta=producto_form");
exit();
?>
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