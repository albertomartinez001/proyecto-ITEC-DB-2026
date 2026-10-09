<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

// ID de la carrera
$idCarrera = (int)($_GET['id'] ?? 0);
if ($idCarrera <= 0) {
    header('Location: listar.php');
    exit;
}

// Datos de la carrera
$stmt = $db->prepare("SELECT id_crra, nombrecrra FROM carrera WHERE id_crra = :id");
$stmt->execute([':id' => $idCarrera]);
$carrera = $stmt->fetch();

if (!$carrera) {
    header('Location: listar.php');
    exit;
}

// Crear pensum (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vigencia = (int)($_POST['vigencia'] ?? 0);
    if ($vigencia < 1900 || $vigencia > 2100) {
        $error = 'El año de vigencia no es válido.';
    } else {
        try {
            $sql = "INSERT INTO pensum (id_psum, a_vigencia, carrera_id_crra)
                    VALUES (seq_pensum.NEXTVAL, :v, :c)";
            $db->prepare($sql)->execute([':v' => $vigencia, ':c' => $idCarrera]);
            header('Location: pensum.php?id=' . $idCarrera . '&msg=creado');
            exit;
        } catch (Exception $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

// Listar pensums de la carrera
$stmt = $db->prepare(
    "SELECT p.id_psum, p.a_vigencia,
            (SELECT COUNT(*) FROM curso c WHERE c.pensum_id_psum = p.id_psum) AS total_cursos
     FROM pensum p
     WHERE p.carrera_id_crra = :id
     ORDER BY p.a_vigencia DESC"
);
$stmt->execute([':id' => $idCarrera]);
$pensums = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Carreras</a></li>
        <li class="breadcrumb-item active">Pensum</li>
    </ol>
</nav>

<h3 class="mb-1"><i class="bi bi-list-ul"></i> Pensum de la Carrera</h3>
<p class="text-muted"><?= htmlspecialchars($carrera['nombrecrra']) ?></p>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">
        <i class="bi bi-check-circle"></i>
        <?php
            if ($_GET['msg'] === 'creado')  echo 'Pensum creado correctamente.';
            if ($_GET['msg'] === 'borrado') echo 'Pensum eliminado correctamente.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger py-2">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Formulario para crear pensum -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle"></i> Nuevo Pensum
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Año de vigencia</label>
                        <input type="number" name="vigencia" class="form-control"
                               min="1900" max="2100" required
                               placeholder="Ej: 2024">
                    </div>
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Crear Pensum
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de pensums -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <i class="bi bi-list-ul"></i> Pensums registrados
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Vigencia</th>
                            <th>Cursos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pensums): ?>
                        <?php foreach ($pensums as $p): ?>
                            <tr>
                                <td><?= $p['id_psum'] ?></td>
                                <td><span class="badge bg-secondary"><?= $p['a_vigencia'] ?></span></td>
                                <td><?= $p['total_cursos'] ?></td>
                                <td class="text-end">
                                    <a href="pensum_cursos.php?id=<?= $p['id_psum'] ?>"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-book"></i> Ver Cursos
                                    </a>
                                    <a href="pensum_eliminar.php?id=<?= $p['id_psum'] ?>&carrera=<?= $idCarrera ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Eliminar este pensum? Se eliminarán también sus cursos.')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay pensums registrados todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>