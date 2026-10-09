<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

// 1. PROCESAR EL FORMULARIO (antes de cualquier HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombrecrra'] ?? '');

    if ($nombre === '') {
        $error = 'El nombre de la carrera es obligatorio.';
    } else {
        try {
            $sql = "INSERT INTO carrera (id_crra, nombrecrra)
                    VALUES (seq_carrera.NEXTVAL, :nombre)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':nombre' => $nombre]);

            header('Location: listar.php?msg=creado');
            exit;
        } catch (Exception $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

// 2. AHORA SÍ mostramos el HTML
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Carreras</a></li>
        <li class="breadcrumb-item active">Nueva Carrera</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-plus-circle"></i> Nueva Carrera</h3>

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
                       placeholder="Ej: Ingeniería en Sistemas">
            </div>

            <div class="col-12 border-top pt-3">
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
                <a href="listar.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>