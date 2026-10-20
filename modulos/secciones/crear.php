<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

// Si es POST → guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCurso   = (int)($_POST['curso']    ?? 0);
    $idDocente = (int)($_POST['docente']  ?? 0);
    $semestre  = trim($_POST['semestre']  ?? '');
    $anio      = (int)($_POST['anio']     ?? 0);

    if ($idCurso <= 0 || $idDocente <= 0 || $semestre === '' || $anio <= 0) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        try {
            $sql = "INSERT INTO seccion (id_sec, semestre, anio, curso_id_cso, docente_id_dte)
                    VALUES (seq_seccion.NEXTVAL, :sem, :anio, :curso, :docente)";
            $db->prepare($sql)->execute([
                ':sem'     => $semestre,
                ':anio'    => $anio,
                ':curso'   => $idCurso,
                ':docente' => $idDocente,
            ]);
            header('Location: listar.php?msg=creado');
            exit;
        } catch (Exception $e) {
            $error = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

// Cargar cursos y docentes para los selectores
$cursos = $db->query(
    "SELECT c.id_cso, c.cod_cso, c.nombre, ca.nombrecrra
     FROM curso c
     JOIN pensum p ON p.id_psum = c.pensum_id_psum
     JOIN carrera ca ON ca.id_crra = p.carrera_id_crra
     ORDER BY ca.nombrecrra, c.nombre"
)->fetchAll();

$docentes = $db->query(
    "SELECT id_dte, nombre, apellido FROM docente ORDER BY apellido, nombre"
)->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Secciones</a></li>
        <li class="breadcrumb-item active">Nueva Sección</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-plus-circle"></i> Nueva Sección</h3>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Curso <span class="text-danger">*</span></label>
                <select name="curso" class="form-select" required>
                    <option value="">-- Seleccione un curso --</option>
                    <?php foreach ($cursos as $c): ?>
                        <option value="<?= $c['id_cso'] ?>">
                            [<?= htmlspecialchars($c['cod_cso']) ?>] 
                            <?= htmlspecialchars($c['nombre']) ?> 
                            (<?= htmlspecialchars($c['nombrecrra']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label">Docente <span class="text-danger">*</span></label>
                <select name="docente" class="form-select" required>
                    <option value="">-- Seleccione un docente --</option>
                    <?php foreach ($docentes as $d): ?>
                        <option value="<?= $d['id_dte'] ?>">
                            <?= htmlspecialchars($d['apellido'] . ', ' . $d['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Semestre <span class="text-danger">*</span></label>
                <select name="semestre" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    <option value="Primer Semestre">Primer Semestre</option>
                    <option value="Segundo Semestre">Segundo Semestre</option>
                    <option value="Vacaciones">Vacaciones</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Año <span class="text-danger">*</span></label>
                <input type="number" name="anio" class="form-control" required
                       min="2020" max="2100" value="<?= date('Y') ?>">
            </div>

            <div class="col-12 border-top pt-3">
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Sección
                </button>
                <a href="listar.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>