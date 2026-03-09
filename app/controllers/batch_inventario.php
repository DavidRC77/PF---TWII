<?php
require_once __DIR__ . '/../models/conexion.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'empleado')) {
    header("Location: /?ruta=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion = new Conexion();
        $pdo = $conexion->conectar();
        $pdo->beginTransaction();

        // Horneados: producto_id => número de tandas
        if (!empty($_POST['horneados']) && is_array($_POST['horneados'])) {
            $stmtHornear = $pdo->prepare("UPDATE productos SET stock = stock + (cantidad_por_tanda * :tandas) WHERE id = :id");
            foreach ($_POST['horneados'] as $id => $tandas) {
                $tandas = (int)$tandas;
                if ($tandas > 0) {
                    $stmtHornear->execute(['tandas' => $tandas, 'id' => (int)$id]);
                }
            }
        }

        // Mermas: producto_id => cantidad a restar (piso en 0)
        if (!empty($_POST['mermas']) && is_array($_POST['mermas'])) {
            $stmtMerma = $pdo->prepare("UPDATE productos SET stock = GREATEST(stock - :cantidad, 0) WHERE id = :id");
            foreach ($_POST['mermas'] as $id => $cantidad) {
                $cantidad = (int)$cantidad;
                if ($cantidad > 0) {
                    $stmtMerma->execute(['cantidad' => $cantidad, 'id' => (int)$id]);
                }
            }
        }

        // Próxima tanda: producto_id => HH:MM (vacío = limpiar)
        if (!empty($_POST['proxima_tanda']) && is_array($_POST['proxima_tanda'])) {
            date_default_timezone_set('America/La_Paz');
            $hoy = date('Y-m-d');
            $stmtSet  = $pdo->prepare("UPDATE productos SET proxima_tanda = :ts  WHERE id = :id");
            $stmtNull = $pdo->prepare("UPDATE productos SET proxima_tanda = NULL WHERE id = :id");
            foreach ($_POST['proxima_tanda'] as $id => $ts) {
                $ts = trim($ts);
                if ($ts !== '') {
                    $stmtSet->execute(['ts' => $hoy . ' ' . $ts . ':00', 'id' => (int)$id]);
                } else {
                    $stmtNull->execute(['id' => (int)$id]);
                }
            }
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

$back = (isset($_POST['back']) && $_POST['back'] === 'inventario_admin') ? 'inventario_admin' : 'inventario';
session_write_close();
header("Location: /?ruta=" . $back);
exit();
?>
