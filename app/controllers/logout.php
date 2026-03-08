<?php
require_once __DIR__ . '/../models/jwt_session.php';
JwtSession::destroy();
header("Location: /?ruta=login");
exit();
?>