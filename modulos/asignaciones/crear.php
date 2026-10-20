<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

// Recibir el ID de la sección
$idSeccion = (int)($_GET['seccion'] ?? 0);
if ($idSeccion <= 0) {
    header('Location: ../secciones/listar.php');
    exit;
}

// Datos de la sección
$stmt = $db->prepare(
    "SELECT s.id_sec, s.semestre, s.anio, c.nombre AS curso
     FROM seccion s
     JOIN curso c ON c.id_cso = s.curso_id_cso
     WHERE s.id_sec = :id"
);
$stmt->execute([':id' => $idSeccion]);
$seccion = $stmt->fetch();

if (!$seccion) {
    header('Location: ../secciones/listar.php');
    exit;
}

// POST → Asignar estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEstudiante = (int)($_POST['estudiante'] ?? 0);

    if ($idEstudiante <= 0) {
        $error = 'Seleccione un estudiante.';
    } else {
        try {
            // Verificar que no esté ya asignado
            $check = $db->prepare(
                "SELECT COUNT(*) c FROM asignacion_est
                 WHERE seccion_id_sec = :sec AND estudiante_id_est = :est"
            );
            $check->execute([':sec' => $idSeccion, ':est' => $idEstudiante]);

            if ($check->fetch()['c'] > 0) {
                throw new Exception('Este estudiante ya está asignado a esta sección.');
            }

            // Insertar asignación
            $sql = "INSERT INTO asignacion_est (id_asig, seccion_id_sec, estudiante_id_est)
                    VALUES (seq_asignacion.NEXTVAL, :sec, :est)";
            $db->prepare($sql)->execute([
                ':sec' => $idSeccion,
                ':est' => $idEstudiante,
            ]);

            header('Location: crear.php?seccion=' . $idSeccion . '&msg=creado');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Estudiantes no asignados a esta sección
$stmt = $db->prepare(
    "SELECT e.id_est, e.carnet, e.nombre, e.apellido
     FROM estudiante e
     WHERE e.id_est NOT IN (
         SELECT estudiante_id_est FROM asignacion_est WHERE seccion_id_sec = :sec
     )
     ORDER BY e.apellido, e.nombre"
);
$stmt->execute([':sec' => $idSeccion]);
$estudiantesDisponibles = $stmt->fetchAll();

// Estudiantes ya asignados
$stmt = $db->prepare(
    "SELECT a.id_asig, e.carnet, e.nombre, e.apellido
     FROM asignacion_est a
     JOIN estudiante e ON e.id_est = a.estudiante_id_est
     WHERE a.seccion_id_sec = :sec
     ORDER BY e.apellido, e.nombre"
);
$stmt->execute([':sec' => $idSeccion]);
$asignados = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../secciones/listar.php">Secciones</a></li>
        <li class="breadcrumb-item active">Asignar Estudiantes</li>
    </ol>
</nav>

<h3 class="mb-1"><i class="bi bi-person-plus"></i> Asignar Estudiantes a Sección</h3>
<p class="text-muted">
    <strong><?= htmlspecialchars($seccion['curso']) ?></strong> — 
    <?= htmlspecialchars($seccion['semestre']) ?> <?= $seccion['anio'] ?>
</p>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">
        <i class="bi bi-check-circle"></i> Estudiante asignado correctamente.
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger py-2">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Formulario para asignar -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle"></i> Agregar Estudiante
            </div>
            <div class="card-body">
                <?php if ($estudiantesDisponibles): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Estudiante</label>
                            <select name="estudiante" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($estudiantesDisponibles as $e): ?>
                                    <option value="<?= $e['id_est'] ?>">
                                        <?= htmlspecialchars($e['carnet'] . ' - ' . $e['apellido'] . ', ' . $e['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Asignar
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i> No hay estudiantes disponibles.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lista de asignados -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <i class="bi bi-people"></i> Estudiantes asignados (<?= count($asignados) ?>)
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Carnet</th>
                            <th>Nombre</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($asignados): ?>
                        <?php foreach ($asignados as $a): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($a['carnet']) ?></span></td>
                                <td><?= htmlspecialchars($a['apellido'] . ', ' . $a['nombre']) ?></td>
                                <td class="text-end">
                                    <a href="eliminar.php?id=<?= $a['id_asig'] ?>&seccion=<?= $idSeccion ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Quitar asignación?')">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay estudiantes asignados.
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