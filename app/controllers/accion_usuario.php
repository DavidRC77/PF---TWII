<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'empleado')) {
    header("Location: /?ruta=login"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $id = !empty($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;

    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();

        if ($accion === 'guardar') {
            $nombre   = mb_substr(trim($_POST['nombre_completo']), 0, 150);
            $dni      = mb_substr(trim($_POST['dni']), 0, 20);
            $celular  = mb_substr(trim($_POST['celular']), 0, 20);
            $telefono = trim($_POST['telefono']) !== '' ? mb_substr(trim($_POST['telefono']), 0, 20) : null;
            $correo   = mb_substr(trim($_POST['correo']), 0, 150);
            $rol = $_POST['rol'];
            // Empleado solo puede asignar roles de cliente
            if ($_SESSION['rol'] === 'empleado' && !in_array($rol, ['basico', 'vip'])) {
                $rol = 'basico';
            }
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

session_write_close();
$destino = $_SESSION['rol'] === 'admin' ? 'gestionar_usuarios_admin' : 'gestionar_usuarios';
header("Location: /?ruta=$destino");
exit();
?>