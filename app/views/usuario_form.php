<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/usuario_form.css">
</head>
<body>
    <div class="login-box" style="max-width: 500px;">
        <h2><?= $titulo ?></h2>
        <form action="/?ruta=accion_usuario" method="POST">
            <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
            
            <label>Nombre Completo:</label>
            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($u['nombre_completo']) ?>" required>
            
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>DNI:</label>
                    <input type="text" name="dni" value="<?= htmlspecialchars($u['dni']) ?>" required>
                </div>
                <div style="flex: 1;">
                    <label>Celular:</label>
                    <input type="text" name="celular" value="<?= htmlspecialchars($u['celular']) ?>" required>
                </div>
            </div>

            <label>Teléfono (Opcional):</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($u['telefono']) ?>">
            
            <label>Correo Electrónico:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($u['correo']) ?>" required>
            
            <label>Contraseña <?= $u['id'] ? '(Dejar en blanco para no cambiar)' : '' ?>:</label>
            <input type="password" name="clave" <?= $u['id'] ? '' : 'required' ?>>
            
            <label>Rol del Usuario:</label>
            <select name="rol" style="width:100%; padding:10px; margin:10px 0; border-radius:4px; border:1px solid #ccc;">
                <option value="basico" <?= $u['rol'] == 'basico' ? 'selected' : '' ?>>Básico (Límite 20)</option>
                <option value="vip" <?= $u['rol'] == 'vip' ? 'selected' : '' ?>>VIP (Sin límite)</option>
                <option value="admin" <?= $u['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <button type="submit" name="accion" value="guardar" style="background-color: #27ae60; margin-top:10px;">Guardar Usuario</button>
            <a href="/?ruta=gestionar_usuarios" style="display:block; text-align:center; margin-top:15px; color:#7f8c8d; text-decoration:none;">Cancelar</a>
        </form>
    </div>
</body>
</html>