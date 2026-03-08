<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'basico' && $_SESSION['rol'] !== 'vip')) {
    header("Location: /?ruta=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['accion'])) {
    $id_reserva = (int)$_POST['id'];
    $accion = $_POST['accion'];
    $usuario_id = $_SESSION['usuario_id'];
    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : 'Cancelado por el cliente.';

    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();
        $pdo->beginTransaction();

        $stmtCheck = $pdo->prepare("SELECT estado, tiempo_ampliado FROM reservas WHERE id = :id AND usuario_id = :uid FOR UPDATE");
        $stmtCheck->execute(['id' => $id_reserva, 'uid' => $usuario_id]);
        $reserva = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($reserva && $reserva['estado'] === 'pendiente') {
            
            if ($accion === 'cancelar') {
                $stmtUpdate = $pdo->prepare("UPDATE reservas SET estado = 'cancelado', cancelado_por = 'cliente', motivo_cancelacion = :motivo WHERE id = :id");
                $stmtUpdate->execute(['motivo' => $motivo, 'id' => $id_reserva]);

                $stmtDetalles = $pdo->prepare("SELECT producto_id, cantidad FROM detalle_reservas WHERE reserva_id = :rid");
                $stmtDetalles->execute(['rid' => $id_reserva]);
                $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

                $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock + :cant WHERE id = :pid");
                foreach ($detalles as $detalle) {
                    $stmtStock->execute([
                        'cant' => $detalle['cantidad'],
                        'pid' => $detalle['producto_id']
                    ]);
                }
            } 
            elseif ($accion === 'extender' && !$reserva['tiempo_ampliado']) {
                $stmtExtend = $pdo->prepare("UPDATE reservas SET fecha_expiracion = fecha_expiracion + INTERVAL '15 minutes', tiempo_ampliado = true WHERE id = :id");
                $stmtExtend->execute(['id' => $id_reserva]);
            }

            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

header("Location: /?ruta=catalogo");
exit();
?>