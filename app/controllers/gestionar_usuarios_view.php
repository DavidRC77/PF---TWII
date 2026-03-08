<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login");
    exit();
}

$usuarios = [];

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    $usuarios = $pdo->query("SELECT *, CASE WHEN penalizacion > CURRENT_TIMESTAMP THEN 1 ELSE 0 END as castigado FROM usuarios ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

require_once __DIR__ . '/../views/gestionar_usuarios.php';
