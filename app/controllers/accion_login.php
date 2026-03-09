<?php
require_once __DIR__ . '/../models/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /?ruta=login");
    exit();
}

$correo = trim($_POST['correo']);
$clave  = trim($_POST['clave']);

if (empty($correo) || empty($clave)) {
        header("Location: /?ruta=login&error=" . urlencode("Por favor, complete todos los campos."));
    exit();
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    $stmt = $pdo->prepare(
        "SELECT id, nombre_completo, clave, rol FROM usuarios WHERE correo = :correo AND activo = true"
    );
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($clave, $usuario['clave'])) {
        require_once __DIR__ . '/../models/jwt_session.php';
        JwtSession::create([
            'usuario_id'      => $usuario['id'],
            'nombre_completo' => $usuario['nombre_completo'],
            'rol'             => $usuario['rol'],
        ]);

        if ($usuario['rol'] === 'admin' || $usuario['rol'] === 'empleado') {
            header("Location: /?ruta=panel_admin");
        } else {
            header("Location: /?ruta=catalogo");
        }
        exit();
    } else {
        header("Location: /?ruta=login&error=" . urlencode("Correo o contraseña incorrectos."));
        exit();
    }
} catch (PDOException $e) {
    header("Location: /?ruta=login&error=" . urlencode("Error de sistema: " . $e->getMessage()));
    exit();
}
?>
