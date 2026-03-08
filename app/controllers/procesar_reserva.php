<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'basico' && $_SESSION['rol'] !== 'vip')) {
    header("Location: /?ruta=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productos'])) {
    $productos = $_POST['productos'];
    $usuario_id = $_SESSION['usuario_id'];
    $limite_panes = ($_SESSION['rol'] === 'vip') ? 9999 : 20;
    
    $total_items_solicitados = 0;
    foreach ($productos as $cantidad) {
        $total_items_solicitados += (int)$cantidad;
    }

    if ($total_items_solicitados > $limite_panes) {
        session_write_close();
        header("Location: /?ruta=catalogo");
        exit();
    }
    
    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();
        $pdo->beginTransaction();

        $total = 0;
        $detalles_validos = [];

        $stmtValidar = $pdo->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = :id FOR UPDATE");
        
        foreach ($productos as $id => $cantidad) {
            $cantidad = (int)$cantidad;
            $stmtValidar->execute(['id' => $id]);
            $prod_db = $stmtValidar->fetch(PDO::FETCH_ASSOC);

            if ($prod_db && $prod_db['stock'] >= $cantidad && $cantidad > 0) {
                $subtotal = $prod_db['precio'] * $cantidad;
                $total += $subtotal;
                $detalles_validos[] = [
                    'producto_id' => $id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $prod_db['precio']
                ];
            } else {
                throw new Exception("Stock insuficiente.");
            }
        }

        if (empty($detalles_validos)) {
            throw new Exception("Pedido vacío.");
        }

        $stmtReserva = $pdo->prepare("INSERT INTO reservas (usuario_id, total, fecha_expiracion) VALUES (:uid, :total, CURRENT_TIMESTAMP + INTERVAL '30 minutes') RETURNING id");
        $stmtReserva->execute(['uid' => $usuario_id, 'total' => $total]);
        $id_reserva = $stmtReserva->fetchColumn();

        $stmtInsertDetalle = $pdo->prepare("INSERT INTO detalle_reservas (reserva_id, producto_id, cantidad, precio_unitario) VALUES (:rid, :pid, :cant, :precio)");
        $stmtUpdateStock = $pdo->prepare("UPDATE productos SET stock = stock - :cant WHERE id = :pid");

        foreach ($detalles_validos as $det) {
            $stmtInsertDetalle->execute([
                'rid' => $id_reserva,
                'pid' => $det['producto_id'],
                'cant' => $det['cantidad'],
                'precio' => $det['precio_unitario']
            ]);
            
            $stmtUpdateStock->execute([
                'cant' => $det['cantidad'],
                'pid' => $det['producto_id']
            ]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

session_write_close();
header("Location: /?ruta=catalogo");
exit();
?>