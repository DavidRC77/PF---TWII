<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="public/assets/img/Horno.png">
    <title>Gestión de Usuarios — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/gestionar_usuarios.css">
</head>
<body>
    <div class="navbar">
        <h2>Gestión de Usuarios (Admin)</h2>
        <div>
            <a href="/?ruta=usuario_form" class="btn-nuevo">+ Nuevo Usuario</a>
            <a href="/?ruta=panel_admin" class="btn-volver">Volver</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <div class="tabla-scroll">
        <table>
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Contacto (Cel / Correo)</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr <?= $u['activo'] ? '' : 'class="fila-suspendida"' ?>>
                        <td><strong><?= htmlspecialchars($u['dni']) ?></strong></td>
                        <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                        <td>
                            <?= htmlspecialchars($u['celular']) ?><br>
                            <small><?= htmlspecialchars($u['correo']) ?></small>
                        </td>
                        <td><?php
                            $rol = strtolower($u['rol']);
                            $claseRol = $rol === 'admin' ? 'badge-admin' : ($rol === 'empleado' ? 'badge-empleado' : ($rol === 'vip' ? 'badge-vip' : 'badge-basico'));
                            echo '<span class="badge-rol ' . $claseRol . '">' . strtoupper($u['rol']) . '</span>';
                        ?></td>
                        <td>
                            <?= $u['activo'] ? '<span class="badge-activo">✔ Activo</span>' : '<span class="badge-suspendido">✘ Suspendido</span>' ?>
                            <?= $u['castigado'] ? '<br><span class="badge-penalizado">⚠ Penalizado</span>' : '' ?>
                        </td>
                        <td>
                            <div class="acciones-usuario">
                                <a href="/?ruta=usuario_form&id=<?= $u['id'] ?>" class="btn-mis-reservas">Editar</a>
                                
                                <form action="/?ruta=accion_usuario" method="POST">
                                    <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
                                    <button type="submit" name="accion" value="toggle_activo" class="btn-toggle-activo <?= $u['activo'] ? 'btn-desactivar' : 'btn-activar' ?>">
                                        <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                    <?php if ($u['castigado']): ?>
                                        <button type="submit" name="accion" value="quitar_penalizacion" class="btn-perdonar">Perdonar</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                            <form action="/?ruta=eliminar_usuario" method="POST" class="form-accion"
                                  onsubmit="return confirm('¿Eliminar permanentemente al usuario «<?= htmlspecialchars(addslashes($u['nombre_completo'])) ?>»? Esta acción no se puede deshacer.')">
                                <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-eliminar-admin">🗑</button>
                            </form>
                            <?php else: ?>
                                <span style="color:#aaa; font-size:0.78rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</body>
</html>
