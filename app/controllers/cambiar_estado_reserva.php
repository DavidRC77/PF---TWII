<?php
session_start();
require_once '../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['nuevo_estado'])) {
    $id_reserva = (int)$_POST['id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : 'Cancelado por administración sin detalles.';

    if (in_array($nuevo_estado, ['entregado', 'cancelado'])) {
        try {
            $conexion = new Conexion();
            $pdo = $conexion->conectar();
            
            $pdo->beginTransaction();

            $stmtCheck = $pdo->prepare("SELECT estado FROM reservas WHERE id = :id FOR UPDATE");
            $stmtCheck->execute(['id' => $id_reserva]);
            $reserva = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($reserva && $reserva['estado'] === 'pendiente') {
                if ($nuevo_estado === 'cancelado') {
                    $stmtUpdate = $pdo->prepare("UPDATE reservas SET estado = :estado, cancelado_por = 'admin', motivo_cancelacion = :motivo WHERE id = :id");
                    $stmtUpdate->execute(['estado' => $nuevo_estado, 'motivo' => $motivo, 'id' => $id_reserva]);

                    $stmtDetalles = $pdo->prepare("SELECT producto_id, cantidad FROM detalle_reservas WHERE reserva_id = :reserva_id");
                    $stmtDetalles->execute(['reserva_id' => $id_reserva]);
                    $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

                    $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :producto_id");
                    foreach ($detalles as $detalle) {
                        $stmtStock->execute([
                            'cantidad' => $detalle['cantidad'],
                            'producto_id' => $detalle['producto_id']
                        ]);
                    }
                } else {
                    $stmtUpdate = $pdo->prepare("UPDATE reservas SET estado = :estado WHERE id = :id");
                    $stmtUpdate->execute(['estado' => $nuevo_estado, 'id' => $id_reserva]);
                }
                
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            die("Error al procesar la solicitud: " . $e->getMessage());
        }
    }
}

header("Location: ../views/gestionar_reservas.php");
exit();
?>