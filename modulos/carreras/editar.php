<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

// Validar ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

// Si es POST → actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombrecrra'] ?? '');
    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } else {
        try {
            $stmt = $db->prepare("UPDATE carrera SET nombrecrra = :n WHERE id_crra = :id");
            $stmt->execute([':n' => $nombre, ':id' => $id]);
            header('Location: listar.php?msg=editado');
            exit;
        } catch (Exception $e) {
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}

// Cargar datos actuales
$stmt = $db->prepare("SELECT id_crra, nombrecrra FROM carrera WHERE id_crra = :id");
$stmt->execute([':id' => $id]);
$carrera = $stmt->fetch();

if (!$carrera) {
    header('Location: listar.php');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Carreras</a></li>
        <li class="breadcrumb-item active">Editar Carrera</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-pencil"></i> Editar Carrera</h3>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nombre de la Carrera <span class="text-danger">*</span></label>
                <input type="text" name="nombrecrra" class="form-control" required autofocus
                       value="<?= htmlspecialchars($carrera['nombrecrra']) ?>">
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