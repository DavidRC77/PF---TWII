<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/conexion.php';

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    $stmt = $pdo->query("SELECT id, nombre, descripcion, precio, stock, imagen_url, proxima_tanda FROM productos ORDER BY id ASC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear proxima_tanda a ISO 8601 con offset de Bolivia (UTC-4) para JS
    foreach ($productos as &$p) {
        if ($p['proxima_tanda']) {
            $dt = new DateTime($p['proxima_tanda'], new DateTimeZone('America/La_Paz'));
            $p['proxima_tanda'] = $dt->format('Y-m-d\TH:i:sP'); // ej: 2026-03-09T17:00:00-04:00
        }
    }

    echo json_encode($productos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
}
?>