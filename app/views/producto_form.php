<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/producto_form.css">
</head>
<body>
    <div class="login-box" style="max-width: 500px;">
        <h2><?= $titulo ?></h2>
        <form action="/?ruta=accion_producto" method="POST">
            <input type="hidden" name="id_producto" value="<?= $p['id'] ?>">
            
            <label>Nombre del Pan:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>" required>
            
            <label>Descripción (Opcional):</label>
            <input type="text" name="descripcion" value="<?= htmlspecialchars($p['descripcion']) ?>">
            
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>Precio Unitario (Bs.):</label>
                    <input type="number" step="0.10" name="precio" value="<?= htmlspecialchars($p['precio']) ?>" required>
                </div>
                <div style="flex: 1;">
                    <label>Cantidad por Tanda:</label>
                    <input type="number" name="cantidad_por_tanda" value="<?= htmlspecialchars($p['cantidad_por_tanda']) ?>" required>
                </div>
            </div>
            
            <label>URL de la Imagen (Opcional):</label>
            <input type="text" name="imagen_url" value="<?= htmlspecialchars($p['imagen_url']) ?>">

            <button type="submit" name="accion" value="guardar" style="background-color: #27ae60; margin-top:15px;">Guardar Producto</button>
            <a href="/?ruta=inventario" style="display:block; text-align:center; margin-top:15px; color:#7f8c8d;">Cancelar</a>
        </form>
    </div>
</body>
</html>