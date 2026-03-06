<?php
session_start();
require_once '../models/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/login.php");
    exit();
}

$correo = trim($_POST['correo']);
$clave  = trim($_POST['clave']);

if (empty($correo) || empty($clave)) {
    header("Location: ../views/login.php?error=" . urlencode("Por favor, complete todos los campos."));
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
        $_SESSION['usuario_id']       = $usuario['id'];
        $_SESSION['nombre_completo']  = $usuario['nombre_completo'];
        $_SESSION['rol']              = $usuario['rol'];

        if ($usuario['rol'] === 'admin') {
            header("Location: ../views/panel_admin.php");
        } else {
            header("Location: ../views/catalogo.php");
        }
        exit();
    } else {
        header("Location: ../views/login.php?error=" . urlencode("Correo o contraseña incorrectos."));
        exit();
    }
} catch (PDOException $e) {
    header("Location: ../views/login.php?error=" . urlencode("Error de sistema: " . $e->getMessage()));
    exit();
}
?>
