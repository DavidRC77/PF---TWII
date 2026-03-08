<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $id = !empty($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;

    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();

        if ($accion === 'guardar') {
            $nombre = trim($_POST['nombre_completo']);
            $dni = trim($_POST['dni']);
            $celular = trim($_POST['celular']);
            $telefono = trim($_POST['telefono']) !== '' ? trim($_POST['telefono']) : null;
            $correo = trim($_POST['correo']);
            $rol = $_POST['rol'];
            $pass = !empty($_POST['clave']) ? password_hash($_POST['clave'], PASSWORD_BCRYPT) : null;

            if ($id) {
                if ($pass) {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo=?, dni=?, celular=?, telefono=?, correo=?, rol=?, clave=? WHERE id=?");
                    $stmt->execute([$nombre, $dni, $celular, $telefono, $correo, $rol, $pass, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo=?, dni=?, celular=?, telefono=?, correo=?, rol=? WHERE id=?");
                    $stmt->execute([$nombre, $dni, $celular, $telefono, $correo, $rol, $id]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, dni, celular, telefono, correo, rol, clave) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$nombre, $dni, $celular, $telefono, $correo, $rol, $pass]);
            }
        } 
        elseif ($accion === 'toggle_activo') {
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
            $stmt->execute([$id]);
        }
        elseif ($accion === 'quitar_penalizacion') {
            $stmt = $pdo->prepare("UPDATE usuarios SET penalizacion = NULL WHERE id = ?");
            $stmt->execute([$id]);
        }
    } catch (PDOException $e) { die("Error: " . $e->getMessage()); }
}

header("Location: /?ruta=gestionar_usuarios");
exit();
?>