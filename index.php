<?php
session_start();

$ruta = isset($_GET['ruta']) ? $_GET['ruta'] : 'login';

switch ($ruta) {
    case 'login':
        header("Location: app/views/login.php");
        exit();
        break;
        
    default:
        echo "<h1>Error 404</h1><p>La página que buscas no existe.</p>";
        break;
}
?>