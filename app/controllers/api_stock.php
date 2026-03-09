<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/conexion.php';

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    $stmt = $pdo->query("SELECT id, nombre, descripcion, precio, stock, imagen_url, proxima_tanda FROM productos ORDER BY id ASC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear proxima_tanda a ISO 8601 para que JS lo parsee correctamente
    foreach ($productos as &$p) {
        if ($p['proxima_tanda']) {
            $p['proxima_tanda'] = str_replace(' ', 'T', substr($p['proxima_tanda'], 0, 16));
        }
    }

    echo json_encode($productos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
}
?>