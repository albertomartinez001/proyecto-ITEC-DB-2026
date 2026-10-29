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
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dpi      = trim($_POST['dpi']      ?? '');

    if ($nombre === '' || $apellido === '' || $dpi === '') {
        $error = 'Nombre, apellido y DPI son obligatorios.';
    } else {
        try {
            $sql = "UPDATE docente
                    SET nombre = :nom, apellido = :ape, dpi = :dpi
                    WHERE id_dte = :id";
            $db->prepare($sql)->execute([
                ':nom' => $nombre,
                ':ape' => $apellido,
                ':dpi' => $dpi,
                ':id'  => $id,
            ]);
            header('Location: listar.php?msg=editado');
            exit;
            } catch (Exception $e) {
            // Atrapar el error de DPI duplicado (ORA-00001)
            if (strpos($e->getMessage(), 'ORA-00001') !== false) {
                $error = 'El DPI ingresado ya está registrado en otro docente. Por favor, verifica el número.';
            } else {
                $error = 'Error al actualizar: ' . $e->getMessage();
            }
        }
    }
}

// Cargar datos
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
        <li class="breadcrumb-item active">Editar Docente</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-pencil"></i> Editar Docente</h3>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" required
                       value="<?= htmlspecialchars($docente['nombre']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Apellido <span class="text-danger">*</span></label>
                <input type="text" name="apellido" class="form-control" required
                       value="<?= htmlspecialchars($docente['apellido']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">DPI <span class="text-danger">*</span></label>
                <input type="number" name="dpi" class="form-control" required
                       value="<?= htmlspecialchars($docente['dpi']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" disabled
                       value="<?= htmlspecialchars($docente['usuario']) ?>">
                <div class="form-text">El nombre de usuario no se puede modificar.</div>
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