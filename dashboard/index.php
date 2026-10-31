<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$db = Database::getInstance()->getConnection();
$idUser = $_SESSION['usuario']['iduser'];

$metricas = [];
$accesos  = [];

// Configuración según el Rol
if ($rol === 'administrador') {
    $metricas = [
        ['Estudiantes', 'SELECT COUNT(*) c FROM estudiante', 'bi-people-fill',  'primary', 'modulos/estudiantes/listar.php'],
        ['Docentes',    'SELECT COUNT(*) c FROM docente',    'bi-person-badge', 'success', 'modulos/docentes/listar.php'],
        ['Cursos',      'SELECT COUNT(*) c FROM curso',      'bi-book',         'warning', 'modulos/cursos/listar.php'],
        ['Carreras',    'SELECT COUNT(*) c FROM carrera',    'bi-mortarboard',  'info',    'modulos/carreras/listar.php'],
    ];
    $accesos = [
        ['Nuevo Estudiante', 'modulos/estudiantes/crear.php', 'bi-person-plus', 'outline-primary'],
        ['Nuevo Docente',    'modulos/docentes/crear.php',    'bi-person-plus', 'outline-success'],
        ['Nueva Sección',    'modulos/secciones/crear.php',   'bi-diagram-3',   'outline-info'],
        ['Registrar Notas',  'modulos/notas/registrar.php',   'bi-clipboard-plus', 'outline-warning'],
    ];
} elseif ($rol === 'docente') {
    // Buscar id del docente
    $stmt = $db->prepare("SELECT id_dte FROM docente WHERE usuario_iduser = :u");
    $stmt->execute([':u' => $idUser]);
    $idDocente = $stmt->fetch()['id_dte'] ?? 0;

    $metricas = [
        ['Mis Secciones', "SELECT COUNT(*) c FROM seccion WHERE docente_id_dte = $idDocente", 'bi-diagram-3', 'primary', 'modulos/secciones/listar.php'],
        ['Mis Alumnos',   "SELECT COUNT(*) c FROM asignacion_est a JOIN seccion s ON s.id_sec = a.seccion_id_sec WHERE s.docente_id_dte = $idDocente", 'bi-people', 'success', 'modulos/secciones/listar.php'],
    ];
    $accesos = [
        ['Pasar Asistencia', 'modulos/asistencia/registrar.php', 'bi-calendar-check', 'outline-primary'],
        ['Registrar Notas',  'modulos/notas/registrar.php',      'bi-clipboard-plus', 'outline-success'],
    ];
} else {
    // Estudiante
    $stmt = $db->prepare("SELECT id_est FROM estudiante WHERE usuario_iduser = :u");
    $stmt->execute([':u' => $idUser]);
    $idEst = $stmt->fetch()['id_est'] ?? 0;

    $metricas = [
        ['Cursos Asignados', "SELECT COUNT(*) c FROM asignacion_est WHERE estudiante_id_est = $idEst", 'bi-book', 'primary', 'modulos/notas/consultar.php'],
        ['Asistencias',      "SELECT COUNT(*) c FROM asistencia WHERE usuario_iduser = $idUser AND estado = 'Presente'", 'bi-calendar-check', 'success', 'modulos/asistencia/listar.php'],
    ];
    $accesos = [
        ['Ver Mis Notas',     'modulos/notas/consultar.php',   'bi-clipboard-data', 'outline-primary'],
        ['Ver Mi Asistencia', 'modulos/asistencia/listar.php', 'bi-calendar-check', 'outline-success'],
    ];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">¡Hola, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>! 👋</h2>
        <p class="text-muted mb-0">Bienvenido a tu panel de <?= ucfirst($rol) ?></p>
    </div>
    <span class="badge bg-primary fs-6 p-2"><i class="bi bi-calendar3"></i> <?= date('d/m/Y') ?></span>
</div>

<!-- Tarjetas de Métricas -->
<div class="row g-3 mb-4">
<?php foreach ($metricas as $m):
    $stmt = $db->query($m[1]);
    $valor = $stmt->fetch()['c'] ?? 0;
?>
    <div class="col-md-3">
        <a href="<?= BASE_URL . $m[4] ?>" class="text-decoration-none">
            <div class="card border-<?= $m[3] ?> shadow-sm h-100 metric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1"><?= $m[0] ?></h6>
                            <h2 class="mb-0 text-dark"><?= $valor ?></h2>
                        </div>
                        <i class="bi <?= $m[2] ?> fs-1 text-<?= $m[3] ?>"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
<?php endforeach; ?>
</div>

<!-- Accesos Rápidos -->
<h5 class="mb-3 text-secondary"><i class="bi bi-lightning-charge-fill text-warning"></i> Accesos Rápidos</h5>
<div class="row g-3">
    <?php foreach ($accesos as $a): ?>
        <div class="col-md-3">
            <a href="<?= BASE_URL . $a[1] ?>" class="btn btn-<?= $a[3] ?> w-100 py-3 shadow-sm">
                <i class="bi <?= $a[2] ?> fs-3 d-block mb-2"></i>
                <?= $a[0] ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .metric-card { transition: transform 0.2s ease-in-out; }
    .metric-card:hover { transform: translateY(-5px); }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>