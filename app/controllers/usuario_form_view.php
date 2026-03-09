<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'empleado')) {
    header("Location: /?ruta=login");
    exit();
}

$u     = ['id' => '', 'nombre_completo' => '', 'dni' => '', 'celular' => '', 'telefono' => '', 'correo' => '', 'rol' => 'basico'];
$titulo = "Registrar Nuevo Usuario";

if (isset($_GET['id'])) {
    $titulo = "Editar Usuario";
    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al cargar el usuario.");
    }
}

require_once __DIR__ . '/../views/usuario_form.php';
