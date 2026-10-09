<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

// ID del pensum
$idPensum = (int)($_GET['id'] ?? 0);
if ($idPensum <= 0) {
    header('Location: listar.php');
    exit;
}

// Datos del pensum + carrera
$stmt = $db->prepare(
    "SELECT p.id_psum, p.a_vigencia, c.id_crra, c.nombrecrra
     FROM pensum p
     JOIN carrera c ON c.id_crra = p.carrera_id_crra
     WHERE p.id_psum = :id"
);
$stmt->execute([':id' => $idPensum]);
$pensum = $stmt->fetch();

if (!$pensum) {
    header('Location: listar.php');
    exit;
}

// Crear curso (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo   = trim($_POST['cod_cso']   ?? '');
    $nombre   = trim($_POST['nombre']    ?? '');
    $creditos = (int)($_POST['creditos'] ?? 0);

    if ($codigo === '' || $nombre === '') {
        $error = 'El código y el nombre son obligatorios.';
    } elseif ($creditos < 0) {
        $error = 'Los créditos no pueden ser negativos.';
    } else {
        try {
            $sql = "INSERT INTO curso (id_cso, cod_cso, nombre, creditos, pensum_id_psum)
                    VALUES (seq_curso.NEXTVAL, :cod, :nom, :cred, :pensum)";
            $db->prepare($sql)->execute([
                ':cod'    => $codigo,
                ':nom'    => $nombre,
                ':cred'   => $creditos,
                ':pensum' => $idPensum,
            ]);
            header('Location: pensum_cursos.php?id=' . $idPensum . '&msg=creado');
            exit;
        } catch (Exception $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

// Listar cursos del pensum
$stmt = $db->prepare(
    "SELECT id_cso, cod_cso, nombre, creditos
     FROM curso
     WHERE pensum_id_psum = :id
     ORDER BY cod_cso"
);
$stmt->execute([':id' => $idPensum]);
$cursos = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Carreras</a></li>
        <li class="breadcrumb-item">
            <a href="pensum.php?id=<?= $pensum['id_crra'] ?>">
                <?= htmlspecialchars($pensum['nombrecrra']) ?>
            </a>
        </li>
        <li class="breadcrumb-item active">Pensum <?= $pensum['a_vigencia'] ?></li>
    </ol>
</nav>

<h3 class="mb-1"><i class="bi bi-book"></i> Cursos del Pensum</h3>
<p class="text-muted">
    <strong><?= htmlspecialchars($pensum['nombrecrra']) ?></strong>
    — Vigencia <span class="badge bg-secondary"><?= $pensum['a_vigencia'] ?></span>
</p>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">
        <i class="bi bi-check-circle"></i>
        <?php
            if ($_GET['msg'] === 'creado')  echo 'Curso agregado correctamente.';
            if ($_GET['msg'] === 'borrado') echo 'Curso eliminado correctamente.';
        ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger py-2">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Formulario nuevo curso -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle"></i> Nuevo Curso
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="number" name="cod_cso" class="form-control"
                               required placeholder="Ej: 101">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control"
                               required placeholder="Ej: Programación I">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Créditos</label>
                        <input type="number" name="creditos" class="form-control"
                               value="4" min="0" required>
                    </div>
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Agregar Curso
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de cursos -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <i class="bi bi-book"></i> Cursos registrados (<?= count($cursos) ?>)
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th class="text-center">Créditos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($cursos): ?>
                        <?php foreach ($cursos as $c): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($c['cod_cso']) ?></span></td>
                                <td><?= htmlspecialchars($c['nombre']) ?></td>
                                <td class="text-center"><?= $c['creditos'] ?></td>
                                <td class="text-end">
                                    <a href="curso_eliminar.php?id=<?= $c['id_cso'] ?>&pensum=<?= $idPensum ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Eliminar este curso?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Este pensum aún no tiene cursos.
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