<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$productos = [];
try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    $productos = $pdo->query("SELECT * FROM productos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    
    $total_inventario = 0;
    foreach ($productos as $prod) {
        $total_inventario += ($prod['precio'] * $prod['stock']);
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Inventario</title>
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
</head>
<body>
    <div class="navbar">
        <h2>Gestión de Inventario</h2>
        <div>
            <a href="/?ruta=producto_form" style="background-color: #2980b9; margin-right: 10px;">Agregar Producto</a>
            <a href="/?ruta=panel_admin" style="background-color: #34495e; margin-right: 10px;">Volver al Panel</a>
            <a href="/?ruta=logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="contenedor-principal">
        <table>
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Precio Unit.</th>
                    <th>Stock Actual</th>
                    <th>Valor Total (Bs.)</th>
                    <th>Opciones</th>
                    <th>Acción: Horneado</th>
                    <th>Acción: Pérdidas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $prod): ?>
                    <tr>
                        <td>
                            <?php if ($prod['imagen_url']): ?>
                                <img src="<?= htmlspecialchars($prod['imagen_url']) ?>" alt="Img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #7f8c8d; font-size: 0.8em;">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($prod['nombre']) ?></td>
                        <td>Bs. <?= number_format($prod['precio'], 2) ?></td>
                        <td style="font-weight: bold; font-size: 1.1em;"><?= $prod['stock'] ?></td>
                        <td>Bs. <?= number_format($prod['precio'] * $prod['stock'], 2) ?></td>
                        <td>
                            <a href="/?ruta=producto_form&id=<?= $prod['id'] ?>" style="background-color: #f39c12; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold;">Editar</a>
                        </td>
                        <td>
                            <form action="/?ruta=horneados" method="POST" style="margin: 0;">
                                <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                <button type="submit" class="btn-hornear">Hornear (+<?= $prod['cantidad_por_tanda'] ?>)</button>
                            </form>
                        </td>
                        <td>
                            <form action="/?ruta=perdidas" method="POST" class="form-inline" style="margin: 0;">
                                <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                <input type="number" name="cantidad" min="1" max="<?= $prod['stock'] ?>" class="input-merma" required <?= $prod['stock'] == 0 ? 'disabled' : '' ?>>
                                <button type="submit" class="btn-merma" <?= $prod['stock'] == 0 ? 'disabled style="background-color: #95a5a6;"' : '' ?>>Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="8" style="text-align: center;">No hay productos registrados en el inventario.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <h3 style="margin-top: 20px;">Valor Total del Inventario: Bs. <?= number_format($total_inventario, 2) ?></h3>
    </div>
</body>
</html>