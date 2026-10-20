<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador', 'docente']);

$db = Database::getInstance()->getConnection();
$error = '';
$msg = '';

$idSeccion = (int)($_GET['seccion'] ?? 0);

// Si es docente, solo puede ver sus secciones
$esDocente = (rolActual() === 'docente');
$idDocente = null;

if ($esDocente) {
    $stmt = $db->prepare("SELECT id_dte FROM docente WHERE usuario_iduser = :u");
    $stmt->execute([':u' => $_SESSION['usuario']['iduser']]);
    $idDocente = $stmt->fetch()['id_dte'] ?? 0;
}

// Listar secciones para el combo
if ($esDocente) {
    $stmt = $db->prepare(
        "SELECT s.id_sec, s.semestre, s.anio, c.nombre AS curso
         FROM seccion s JOIN curso c ON c.id_cso = s.curso_id_cso
         WHERE s.docente_id_dte = :d
         ORDER BY s.anio DESC, c.nombre"
    );
    $stmt->execute([':d' => $idDocente]);
} else {
    $stmt = $db->query(
        "SELECT s.id_sec, s.semestre, s.anio, c.nombre AS curso
         FROM seccion s JOIN curso c ON c.id_cso = s.curso_id_cso
         ORDER BY s.anio DESC, c.nombre"
    );
}
$secciones = $stmt->fetchAll();

// Si se envía el formulario de notas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_asig'])) {
    $idAsig  = (int)$_POST['id_asig'];
    $zona    = (float)($_POST['zona']   ?? 0);
    $examF   = (float)($_POST['exam_f'] ?? 0);
    $total   = $zona + $examF; // Regla simple: zona + examen

    try {
        // Verificar si ya existe registro para esta asignación
        $check = $db->prepare("SELECT id_nota FROM notas WHERE asignacion_est_id_asig = :id");
        $check->execute([':id' => $idAsig]);
        $nota = $check->fetch();

        if ($nota) {
            // Update
            $sql = "UPDATE notas SET zona=:z, exam_f=:e, nota_total=:t WHERE id_nota=:id";
            $db->prepare($sql)->execute([':z'=>$zona, ':e'=>$examF, ':t'=>$total, ':id'=>$nota['id_nota']]);
        } else {
            // Insert
            $sql = "INSERT INTO notas (id_nota, zona, exam_f, nota_total, asignacion_est_id_asig)
                    VALUES (seq_notas.NEXTVAL, :z, :e, :t, :id)";
            $db->prepare($sql)->execute([':z'=>$zona, ':e'=>$examF, ':t'=>$total, ':id'=>$idAsig]);
        }
        header('Location: registrar.php?seccion=' . $idSeccion . '&msg=ok');
        exit;
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Cargar alumnos y notas de la sección seleccionada
$alumnos = [];
if ($idSeccion > 0) {
    $sql = "SELECT a.id_asig, e.carnet, e.nombre, e.apellido, n.zona, n.exam_f, n.nota_total
            FROM asignacion_est a
            JOIN estudiante e ON e.id_est = a.estudiante_id_est
            LEFT JOIN notas n ON n.asignacion_est_id_asig = a.id_asig
            WHERE a.seccion_id_sec = :sec
            ORDER BY e.apellido, e.nombre";
    $stmt = $db->prepare($sql);
    $stmt->execute([':sec' => $idSeccion]);
    $alumnos = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<h3 class="mb-3"><i class="bi bi-clipboard-plus"></i> Registro de Calificaciones</h3>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2"><i class="bi bi-check-circle"></i> Notas guardadas correctamente.</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Selector de sección -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <select name="seccion" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Seleccione una sección --</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= $s['id_sec'] ?>" <?= ($s['id_sec'] == $idSeccion) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['curso']) ?> (<?= $s['semestre'] ?> <?= $s['anio'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Cargar Alumnos</button>
            </div>
        </form>
    </div>
</div>

<?php if ($idSeccion > 0): ?>
    <?php if ($alumnos): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-light">Alumnos de la sección</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Carnet</th>
                            <th>Estudiante</th>
                            <th style="width: 100px;">Zona</th>
                            <th style="width: 100px;">Exam. Final</th>
                            <th style="width: 100px;">Total</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($alumnos as $a): ?>
                        <form method="POST">
                            <input type="hidden" name="id_asig" value="<?= $a['id_asig'] ?>">
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($a['carnet']) ?></span></td>
                                <td><?= htmlspecialchars($a['apellido'] . ', ' . $a['nombre']) ?></td>
                                <td><input type="number" step="0.01" name="zona" class="form-control form-control-sm" value="<?= $a['zona'] ?? '0' ?>" required></td>
                                <td><input type="number" step="0.01" name="exam_f" class="form-control form-control-sm" value="<?= $a['exam_f'] ?? '0' ?>" required></td>
                                <td><strong><?= $a['nota_total'] ?? '0' ?></strong></td>
                                <td class="text-end">
                                    <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                </td>
                            </tr>
                        </form>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Esta sección no tiene estudiantes asignados.</div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>