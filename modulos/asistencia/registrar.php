<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador', 'docente']);

$db = Database::getInstance()->getConnection();
$error = '';

$idSeccion = (int)($_GET['seccion'] ?? 0);
$fecha     = $_GET['fecha'] ?? date('Y-m-d');

// Cargar secciones
$esDocente = (rolActual() === 'docente');
$idDocente = null;
if ($esDocente) {
    $stmt = $db->prepare("SELECT id_dte FROM docente WHERE usuario_iduser = :u");
    $stmt->execute([':u' => $_SESSION['usuario']['iduser']]);
    $idDocente = $stmt->fetch()['id_dte'] ?? 0;
}

if ($esDocente) {
    $stmt = $db->prepare("SELECT s.id_sec, s.semestre, s.anio, c.nombre AS curso
                          FROM seccion s JOIN curso c ON c.id_cso = s.curso_id_cso
                          WHERE s.docente_id_dte = :d ORDER BY s.anio DESC");
    $stmt->execute([':d' => $idDocente]);
} else {
    $stmt = $db->query("SELECT s.id_sec, s.semestre, s.anio, c.nombre AS curso
                        FROM seccion s JOIN curso c ON c.id_cso = s.curso_id_cso
                        ORDER BY s.anio DESC");
}
$secciones = $stmt->fetchAll();

// Procesar Asistencia (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado'])) {
    try {
        $db->beginTransaction();
        
        foreach ($_POST['estado'] as $idUserEstudiante => $estado) {
            // 1. Verificar si ya existe
            $sqlCheck = "SELECT id_asis FROM asistencia 
                         WHERE seccion_id_sec = :sec1 
                           AND usuario_iduser = :user1 
                           AND TRUNC(fecha) = TO_DATE(:fecha1, 'YYYY-MM-DD')";
            $check = $db->prepare($sqlCheck);
            $check->execute([
                ':sec1' => $idSeccion, 
                ':user1' => $idUserEstudiante, 
                ':fecha1' => $fecha
            ]);
            $existe = $check->fetch();

            if ($existe) {
                // 2. Actualizar
                $sqlUpdate = "UPDATE asistencia SET estado = :estado_upd WHERE id_asis = :id_upd";
                $db->prepare($sqlUpdate)->execute([
                    ':estado_upd' => $estado, 
                    ':id_upd' => $existe['id_asis']
                ]);
            } else {
                // 3. Insertar
                $sqlInsert = "INSERT INTO asistencia (id_asis, fecha, estado, seccion_id_sec, usuario_iduser)
                              VALUES (seq_asistencia.NEXTVAL, TO_DATE(:fecha_ins, 'YYYY-MM-DD'), :estado_ins, :sec_ins, :user_ins)";
                $db->prepare($sqlInsert)->execute([
                    ':fecha_ins'  => $fecha,
                    ':estado_ins' => $estado,
                    ':sec_ins'    => $idSeccion,
                    ':user_ins'   => $idUserEstudiante
                ]);
            }
        }
        
        $db->commit();
        header("Location: registrar.php?seccion=$idSeccion&fecha=$fecha&msg=ok");
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error al guardar asistencia: ' . $e->getMessage();
    }
}

// Cargar alumnos
$alumnos = [];
if ($idSeccion > 0) {
    $sql = "SELECT e.usuario_iduser, e.carnet, e.nombre, e.apellido, a.estado
            FROM asignacion_est asig
            JOIN estudiante e ON e.id_est = asig.estudiante_id_est
            LEFT JOIN asistencia a ON a.usuario_iduser = e.usuario_iduser 
                 AND a.seccion_id_sec = :sec_alumno 
                 AND TRUNC(a.fecha) = TO_DATE(:fecha_alumno, 'YYYY-MM-DD')
            WHERE asig.seccion_id_sec = :sec_asig
            ORDER BY e.apellido, e.nombre";
    $stmt = $db->prepare($sql);
    // Usamos nombres de variables distintos para cada :parametro
    $stmt->execute([
        ':sec_alumno'   => $idSeccion, 
        ':fecha_alumno' => $fecha, 
        ':sec_asig'     => $idSeccion
    ]);
    $alumnos = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<h3 class="mb-3"><i class="bi bi-calendar-check"></i> Registrar Asistencia</h3>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2"><i class="bi bi-check-circle"></i> Asistencia guardada correctamente.</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Sección</label>
                <select name="seccion" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($secciones as $s): ?>
                        <option value="<?= $s['id_sec'] ?>" <?= ($s['id_sec'] == $idSeccion) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['curso']) ?> (<?= $s['semestre'] ?> <?= $s['anio'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= $fecha ?>" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Cargar Alumnos</button>
            </div>
        </form>
    </div>
</div>

<?php if ($idSeccion > 0): ?>
    <?php if ($alumnos): ?>
        <form method="POST">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span>Lista de Asistencia (<?= date('d/m/Y', strtotime($fecha)) ?>)</span>
                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-save"></i> Guardar Todo</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>Carnet</th>
                                <th>Estudiante</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($alumnos as $a): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($a['carnet']) ?></span></td>
                                <td><?= htmlspecialchars($a['apellido'] . ', ' . $a['nombre']) ?></td>
                                <td class="text-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="estado[<?= $a['usuario_iduser'] ?>]" value="Presente" <?= ($a['estado'] === 'Presente' || $a['estado'] === null) ? 'checked' : '' ?>>
                                        <label class="form-check-label text-success">Presente</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="estado[<?= $a['usuario_iduser'] ?>]" value="Ausente" <?= ($a['estado'] === 'Ausente') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-danger">Ausente</label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">No hay alumnos asignados a esta sección.</div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>