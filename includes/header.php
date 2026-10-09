<?php
require_once __DIR__ . '/auth.php';
requerirLogin();
$rol = rolActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .navbar-brand { font-weight: 600; }
        .sidebar {
            background: #ffffff;
            border-right: 1px solid #e3e6ea;
            min-height: calc(100vh - 56px);
        }
        .sidebar .nav-link {
            color: #333;
            border-radius: .5rem;
            padding: .5rem .75rem;
            margin-bottom: .15rem;
        }
        .sidebar .nav-link:hover {
            background: #eef3ff;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>dashboard/index.php">
            <i class="bi bi-mortarboard-fill"></i> <?= APP_NAME ?>
        </a>
        <div class="ms-auto d-flex align-items-center text-white">
            <span class="me-3">
                <i class="bi bi-person-circle"></i>
                <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                <span class="badge bg-light text-primary ms-1"><?= ucfirst($rol) ?></span>
            </span>
            <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-sm btn-outline-light">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">