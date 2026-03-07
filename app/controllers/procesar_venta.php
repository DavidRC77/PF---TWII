<?php
session_start();
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /?ruta=login"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productos'])) {
    $cliente_nombre = trim($_POST['cliente_nombre']) !== '' ? trim($_POST['cliente_nombre']) : 'Cliente Mostrador';
    $cliente_dni = trim($_POST['cliente_dni']) !== '' ? trim($_POST['cliente_dni']) : '0';
    $reserva_id = !empty($_POST['reserva_id']) ? (int)$_POST['reserva_id'] : null;
    $productos = $_POST['productos'];

    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();
        $pdo->beginTransaction();

        $total = 0;
        $detalles = [];

        foreach ($productos as $id => $cantidad) {
            $stmtP = $pdo->prepare("SELECT precio, stock FROM productos WHERE id = ? FOR UPDATE");
            $stmtP->execute([$id]);
            $prod = $stmtP->fetch(PDO::FETCH_ASSOC);

            if ($prod) {
                $subtotal = $prod['precio'] * $cantidad;
                $total += $subtotal;
                $detalles[] = ['id' => $id, 'cant' => $cantidad, 'precio' => $prod['precio']];

                if (!$reserva_id) {
                    if ($prod['stock'] < $cantidad) {
                        throw new Exception("Stock insuficiente para venta física.");
                    }
                    $stmtUpd = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                    $stmtUpd->execute([$cantidad, $id]);
                }
            }
        }

        if (empty($detalles)) throw new Exception("Venta vacía.");

        $stmtVenta = $pdo->prepare("INSERT INTO ventas (cliente_nombre, cliente_dni, total, reserva_id) VALUES (?, ?, ?, ?) RETURNING id");
        $stmtVenta->execute([$cliente_nombre, $cliente_dni, $total, $reserva_id]);
        $venta_id = $stmtVenta->fetchColumn();

        $stmtDetVenta = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        foreach ($detalles as $d) {
            $stmtDetVenta->execute([$venta_id, $d['id'], $d['cant'], $d['precio']]);
        }

        if ($reserva_id) {
            $stmtRes = $pdo->prepare("UPDATE reservas SET estado = 'entregado' WHERE id = ?");
            $stmtRes->execute([$reserva_id]);
        }

        $pdo->commit();
        header("Location: /?ruta=caja&exito=1");
        exit();

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Error al registrar la venta: " . $e->getMessage());
    }
}

header("Location: /?ruta=caja");
exit();
?>