
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
    <style>
        body { background-color: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow-x: hidden; }
        .navbar-brand { font-weight: 600; }
        
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: calc(100vh - 56px);
        }

        .sidebar {
            background: #ffffff;
            border-right: 1px solid #e3e6ea;
            flex: 0 0 250px;
            min-height: calc(100vh - 56px);
            transition: all 0.3s;
        }
        
        .sidebar.collapsed {
            margin-left: -250px;
        }

        .sidebar .nav-link {
            color: #495057;
            border-radius: .5rem;
            padding: .6rem .75rem;
            margin-bottom: .2rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover {
            background: #eef3ff;
            color: #0d6efd;
            transform: translateX(5px);
        }

        /* Contenido de la página */
        .content {
            flex: 1 1 auto;
            min-width: 0;
            padding: 1.5rem;
            min-height: calc(100vh - 56px);
            display: flex;
            flex-direction: column;
        }

        .content-body {
            flex: 1 0 auto;
            min-width: 0;
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            background-color: #ffffff;
        }
        .table th { text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; color: #6c757d; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top" style="height: 56px;">
    <div class="container-fluid">
        <button class="btn btn-primary border-0 me-2 shadow-none" type="button" id="sidebarCollapse">
            <i class="bi bi-list fs-4"></i>
        </button>
        
        <a class="navbar-brand" href="<?= BASE_URL ?>dashboard/index.php">
            <i class="bi bi-mortarboard-fill"></i> <?= APP_NAME ?>
        </a>
        
        <div class="ms-auto d-flex align-items-center text-white">
            <span class="me-3 d-none d-md-inline">
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

<div class="wrapper">
