<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/gestionar_usuarios.css">
</head>
<body>
    <div class="navbar">
        <h2>Control Total de Usuarios</h2>
        <div>
            <a href="/?ruta=usuario_form" style="background-color: #27ae60; margin-right: 10px;">+ Nuevo Usuario</a>
            <a href="/?ruta=panel_admin" style="background-color: #34495e; margin-right: 10px;">Volver</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <table>
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Contacto (Cel / Correo)</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr style="opacity: <?= $u['activo'] ? '1' : '0.5' ?>;">
                        <td><strong><?= htmlspecialchars($u['dni']) ?></strong></td>
                        <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                        <td>
                            <?= htmlspecialchars($u['celular']) ?><br>
                            <small><?= htmlspecialchars($u['correo']) ?></small>
                        </td>
                        <td><strong><?= strtoupper($u['rol']) ?></strong></td>
                        <td>
                            <?= $u['activo'] ? '<span style="color:green">✔ Activo</span>' : '<span style="color:red">✘ Suspendido</span>' ?>
                            <?= $u['castigado'] ? '<br><small style="color:orange">⚠ Penalizado</small>' : '' ?>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <a href="/?ruta=usuario_form&id=<?= $u['id'] ?>" class="btn-mis-reservas" style="padding:5px 10px; font-size:12px;">Editar</a>
                                
                                <form action="/?ruta=accion_usuario" method="POST" style="margin:0;">
                                    <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
                                    <button type="submit" name="accion" value="toggle_activo" style="padding:5px 10px; font-size:12px; border:none; border-radius:4px; cursor:pointer; color:white; background-color: <?= $u['activo'] ? '#e67e22' : '#27ae60' ?>;">
                                        <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                    <?php if ($u['castigado']): ?>
                                        <button type="submit" name="accion" value="quitar_penalizacion" style="padding:5px 10px; font-size:12px; border:none; border-radius:4px; cursor:pointer; color:white; background-color: #2ecc71;">Perdonar</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>