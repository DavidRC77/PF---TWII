<?php
// Servir archivos estáticos directamente (CSS, JS, imágenes, etc.)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$static = dirname(__DIR__) . $uri;

if ($uri !== '/' && file_exists($static) && !is_dir($static)) {
    $ext = strtolower(pathinfo($static, PATHINFO_EXTENSION));
    $mime = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'webp'  => 'image/webp',
    ];
    if (isset($mime[$ext])) {
        header('Content-Type: ' . $mime[$ext]);
        readfile($static);
        exit;
    }
}

require_once dirname(__DIR__) . '/index.php';
