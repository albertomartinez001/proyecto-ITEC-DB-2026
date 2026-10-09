<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$db = Database::getInstance()->getConnection();

// Métricas según rol
$metricas = [];
$titulo   = '';

if ($rol === 'administrador') {
    $titulo = 'Panel de Administración';
    $metricas = [
        ['Estudiantes', 'SELECT COUNT(*) c FROM estudiante', 'bi-people-fill',  'primary'],
        ['Docentes',    'SELECT COUNT(*) c FROM docente',    'bi-person-badge', 'success'],
        ['Cursos',      'SELECT COUNT(*) c FROM curso',      'bi-book',         'warning'],
        ['Carreras',    'SELECT COUNT(*) c FROM carrera',    'bi-mortarboard',  'info'],
    ];
} elseif ($rol === 'docente') {
    $titulo = 'Panel del Docente';
    $metricas = [
        ['Mis Secciones', 'SELECT COUNT(*) c FROM seccion', 'bi-diagram-3', 'primary'],
        ['Mis Cursos',    'SELECT COUNT(DISTINCT curso_id_cso) c FROM seccion', 'bi-book', 'success'],
    ];
} else {
    $titulo = 'Panel del Estudiante';
    $metricas = [
        ['Mis Cursos',   'SELECT COUNT(*) c FROM asignacion_est', 'bi-book',           'primary'],
        ['Asistencias',  'SELECT COUNT(*) c FROM asistencia',     'bi-calendar-check', 'success'],
    ];
}
?>

<h2 class="mb-1"><i class="bi bi-speedometer2"></i> <?= $titulo ?></h2>
<p class="text-muted">Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></strong></p>

<div class="row g-3 mt-2">
<?php foreach ($metricas as $m):
    $stmt = $db->query($m[1]);
    $valor = $stmt->fetch()['c'] ?? 0;
?>
    <div class="col-md-3">
        <div class="card border-<?= $m[3] ?> shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1"><?= $m[0] ?></h6>
                    <h3 class="mb-0"><?= $valor ?></h3>
                </div>
                <i class="bi <?= $m[2] ?> fs-1 text-<?= $m[3] ?>"></i>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>