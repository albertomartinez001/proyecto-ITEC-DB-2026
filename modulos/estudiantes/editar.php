<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

// POST → actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carnet   = trim($_POST['carnet']   ?? '');
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');

    if ($carnet === '' || $nombre === '' || $apellido === '') {
        $error = 'Carnet, nombre y apellido son obligatorios.';
    } else {
        try {
            $sql = "UPDATE estudiante
                    SET carnet = :car, nombre = :nom, apellido = :ape
                    WHERE id_est = :id";
            $db->prepare($sql)->execute([
                ':car' => $carnet,
                ':nom' => $nombre,
                ':ape' => $apellido,
                ':id'  => $id,
            ]);
            header('Location: listar.php?msg=editado');
            exit;
        } catch (Exception $e) {
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}

// Cargar datos
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
        <li class="breadcrumb-item active">Editar Estudiante</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-pencil"></i> Editar Estudiante</h3>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Carnet <span class="text-danger">*</span></label>
                <input type="number" name="carnet" class="form-control" required
                       value="<?= htmlspecialchars($estudiante['carnet']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" disabled
                       value="<?= htmlspecialchars($estudiante['usuario']) ?>">
                <div class="form-text">El nombre de usuario no se puede modificar.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" required
                       value="<?= htmlspecialchars($estudiante['nombre']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Apellido <span class="text-danger">*</span></label>
                <input type="text" name="apellido" class="form-control" required
                       value="<?= htmlspecialchars($estudiante['apellido']) ?>">
            </div>

            <div class="col-12 border-top pt-3">
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar cambios
                </button>
                <a href="listar.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>