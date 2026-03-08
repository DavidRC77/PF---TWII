<?php
$ruta = $_GET['ruta'] ?? 'login';

switch ($ruta) {
    case 'login':
        require_once __DIR__ . '/app/views/login.php';
        break;
    case 'panel_admin':
        require_once __DIR__ . '/app/controllers/panel_admin.php';
        break;
    case 'catalogo':
        require_once __DIR__ . '/app/controllers/catalogo.php';
        break;
    case 'caja':
        require_once __DIR__ . '/app/controllers/caja_view.php';
        break;
    case 'gestionar_reservas':
        require_once __DIR__ . '/app/controllers/gestionar_reservas_view.php';
        break;
    case 'gestionar_usuarios':
        require_once __DIR__ . '/app/controllers/gestionar_usuarios_view.php';
        break;
    case 'historial_reservas':
        require_once __DIR__ . '/app/controllers/historial_reservas_view.php';
        break;
    case 'inventario':
        require_once __DIR__ . '/app/controllers/inventario_view.php';
        break;
    case 'mis_reservas':
        require_once __DIR__ . '/app/controllers/mis_reservas_view.php';
        break;
    case 'nuevo_producto':
    case 'producto_form':
        require_once __DIR__ . '/app/controllers/producto_form_view.php';
        break;
    case 'registro_ventas':
        require_once __DIR__ . '/app/controllers/registro_ventas_view.php';
        break;
    case 'usuario_form':
        require_once __DIR__ . '/app/controllers/usuario_form_view.php';
        break;
    case 'logout':
        require_once __DIR__ . '/app/controllers/logout.php';
        break;
    case 'horneados':
        require_once __DIR__ . '/app/controllers/horneados.php';
        break;
    case 'perdidas':
        require_once __DIR__ . '/app/controllers/perdidas.php';
        break;
    case 'accion_cliente_reserva':
        require_once __DIR__ . '/app/controllers/accion_cliente_reserva.php';
        break;
    case 'procesar_reserva':
        require_once __DIR__ . '/app/controllers/procesar_reserva.php';
        break;
    case 'procesar_venta':
        require_once __DIR__ . '/app/controllers/procesar_venta.php';
        break;
    case 'accion_login':
        require_once __DIR__ . '/app/controllers/accion_login.php';
        break;
    case 'accion_usuario':
        require_once __DIR__ . '/app/controllers/accion_usuario.php';
        break;
    case 'accion_producto':
        require_once __DIR__ . '/app/controllers/accion_producto.php';
        break;
    case 'cambiar_estado_reserva':
        require_once __DIR__ . '/app/controllers/cambiar_estado_reserva.php';
        break;
    case 'api_stock':
        require_once __DIR__ . '/app/controllers/api_stock.php';
        break;
    default:
        http_response_code(404);
        echo "<h1>Error 404</h1><p>La página que buscas no existe.</p>";
        break;
}
?>