<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

$stmt = $db->prepare(
    "SELECT d.id_dte, d.nombre, d.apellido, d.dpi, u.nombre AS usuario
     FROM docente d
     JOIN usuario u ON u.iduser = d.usuario_iduser
     WHERE d.id_dte = :id"
);
$stmt->execute([':id' => $id]);
$docente = $stmt->fetch();

if (!$docente) {
    header('Location: listar.php');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Docentes</a></li>
        <li class="breadcrumb-item active">Ver Docente</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-person-badge"></i> Detalle del Docente</h3>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-info-circle"></i> Información
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th style="width: 160px;">Nombre completo:</th>
                        <td><?= htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']) ?></td>
                    </tr>
                    <tr>
                        <th>DPI:</th>
                        <td><?= htmlspecialchars($docente['dpi']) ?></td>
                    </tr>
                    <tr>
                        <th>Usuario:</th>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($docente['usuario']) ?></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a href="editar.php?id=<?= $docente['id_dte'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="listar.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>