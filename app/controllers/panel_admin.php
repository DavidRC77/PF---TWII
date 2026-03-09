<?php
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'empleado')) {
    header("Location: /?ruta=login");
    exit();
}

require_once __DIR__ . '/../views/panel_admin.php';
