<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="public/assets/img/Horno.png">
    <title><?= $titulo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/usuario_form.css">
</head>
<body>
    <div class="login-box">
        <h2><?= $titulo ?></h2>
        <form action="/?ruta=accion_usuario" method="POST">
            <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
            
            <label>Nombre Completo:</label>
            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($u['nombre_completo']) ?>" required>
            
            <div class="fila-dos-campos">
                <div>
                    <label>DNI:</label>
                    <input type="text" name="dni" value="<?= htmlspecialchars($u['dni']) ?>" required>
                </div>
                <div>
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
            <select name="rol">
                <option value="basico" <?= $u['rol'] == 'basico' ? 'selected' : '' ?>>Básico (Límite 20)</option>
                <option value="vip" <?= $u['rol'] == 'vip' ? 'selected' : '' ?>>VIP (Límite 200)</option>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <option value="empleado" <?= $u['rol'] == 'empleado' ? 'selected' : '' ?>>Empleado</option>
                    <option value="admin" <?= $u['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                <?php endif; ?>
            </select>

            <button type="submit" name="accion" value="guardar">Guardar Usuario</button>
            <a href="/?ruta=gestionar_usuarios" class="enlace-cancelar">Cancelar</a>
        </form>
    </div>
</body>
</html>