<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/conexion.php';

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    
    $stmt = $pdo->query("SELECT id, nombre, descripcion, precio, stock, imagen_url FROM productos ORDER BY id ASC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($productos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
}
?>