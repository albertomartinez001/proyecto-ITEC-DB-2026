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
    "SELECT e.id_est, e.carnet, e.nombre, e.apellido, u.nombre AS usuario
     FROM estudiante e
     JOIN usuario u ON u.iduser = e.usuario_iduser
     WHERE e.id_est = :id"
);
$stmt->execute([':id' => $id]);
$estudiante = $stmt->fetch();

if (!$estudiante) {
    header('Location: listar.php');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Estudiantes</a></li>
        <li class="breadcrumb-item active">Ver Estudiante</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-person-vcard"></i> Detalle del Estudiante</h3>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-info-circle"></i> Información
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th style="width: 180px;">Carnet:</th>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($estudiante['carnet']) ?></span></td>
                    </tr>
                    <tr>
                        <th>Nombre completo:</th>
                        <td><?= htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']) ?></td>
                    </tr>
                    <tr>
                        <th>Usuario:</th>
                        <td><?= htmlspecialchars($estudiante['usuario']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <a href="editar.php?id=<?= $estudiante['id_est'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="listar.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>