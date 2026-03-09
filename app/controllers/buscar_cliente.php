<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'empleado')) {
    echo json_encode(['encontrado' => false]);
    exit();
}

$dni = trim($_GET['dni'] ?? '');

if ($dni === '') {
    echo json_encode(['encontrado' => false]);
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();
    $stmt = $pdo->prepare("SELECT nombre_completo FROM usuarios WHERE dni = :dni LIMIT 1");
    $stmt->execute(['dni' => $dni]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode(['encontrado' => true, 'nombre' => $usuario['nombre_completo']]);
    } else {
        echo json_encode(['encontrado' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['encontrado' => false]);
}
?>
