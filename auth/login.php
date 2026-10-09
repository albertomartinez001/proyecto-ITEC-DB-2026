<?php
require_once __DIR__ . '/../includes/auth.php';

// Si ya está logueado, lo mandamos al dashboard
if (estaLogueado()) {
    header('Location: ' . BASE_URL . 'dashboard/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Debe ingresar usuario y contraseña.';
    } elseif (autenticar($usuario, $password)) {
        header('Location: ' . BASE_URL . 'dashboard/index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            min-height: 100vh;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.3);
        }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card login-card" style="width: 26rem;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
                <h3 class="mt-2 mb-0"><?= APP_NAME ?></h3>
                <p class="text-muted small mb-0"><?= UNIVERSIDAD ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person"></i> Usuario</label>
                    <input type="text" name="usuario" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-lock"></i> Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>