<?php
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Horno - inicio de sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/login.css">
</head>
<body class="login-page d-flex flex-column justify-content-center align-items-center vh-100">
    
    <div class="hero text-center mb-4 z-2">
        <h1 class="display-4 hero-title">El Horno</h1>
        <p class="subtitle text-uppercase fw-bold m-0">pan del horno a tu mesa</p>
    </div>

    <div class="login-box w-100 px-3 z-2">
        <div class="overlay position-relative d-flex flex-column justify-content-center align-items-center">
            
            <h2 class="text-center w-100 login-title">Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger p-2 text-center w-100 error-alert" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/?ruta=accion_login" class="w-100">
                <input type="email" name="correo" class="form-control mb-3" placeholder="Correo electrónico" required>
                <input type="password" name="clave" class="form-control mb-3" placeholder="Contraseña" required>
                <button type="submit" class="btn w-100 text-white mt-2 login-btn">Ingresar</button>
            </form>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>