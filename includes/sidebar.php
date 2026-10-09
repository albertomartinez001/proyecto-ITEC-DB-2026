<?php
$menu = [];

if ($rol === 'administrador') {
    $menu = [
        ['dashboard/index.php',              'bi-speedometer2',    'Dashboard'],
        ['modulos/estudiantes/listar.php',   'bi-people-fill',     'Estudiantes'],
        ['modulos/docentes/listar.php',      'bi-person-badge',    'Docentes'],
        ['modulos/cursos/listar.php',        'bi-book',            'Cursos'],
        ['modulos/carreras/listar.php',      'bi-mortarboard',     'Carreras y Pensum'],
        ['modulos/secciones/listar.php',     'bi-diagram-3',       'Secciones'],
        ['modulos/asignaciones/listar.php',  'bi-link-45deg',      'Asignaciones'],
        ['modulos/asistencia/listar.php',    'bi-calendar-check',  'Asistencia'],
        ['modulos/notas/consultar.php',      'bi-clipboard-data',  'Calificaciones'],
        ['modulos/busqueda/index.php',       'bi-search',          'Búsqueda'],
        ['modulos/informativo/index.php',    'bi-info-circle',     'Institucional'],
    ];
} elseif ($rol === 'docente') {
    $menu = [
        ['dashboard/index.php',              'bi-speedometer2',    'Dashboard'],
        ['modulos/secciones/listar.php',     'bi-diagram-3',       'Mis Secciones'],
        ['modulos/asistencia/registrar.php', 'bi-calendar-check',  'Registrar Asistencia'],
        ['modulos/notas/registrar.php',      'bi-clipboard-plus',  'Registrar Notas'],
        ['modulos/informativo/index.php',    'bi-info-circle',     'Institucional'],
    ];
} elseif ($rol === 'estudiante') {
    $menu = [
        ['dashboard/index.php',              'bi-speedometer2',    'Dashboard'],
        ['modulos/notas/consultar.php',      'bi-clipboard-data',  'Mis Notas'],
        ['modulos/asistencia/listar.php',    'bi-calendar-check',  'Mi Asistencia'],
        ['modulos/cursos/listar.php',        'bi-book',            'Cursos'],
        ['modulos/informativo/index.php',    'bi-info-circle',     'Institucional'],
    ];
}
?>
<div class="col-md-2 sidebar p-3">
    <ul class="nav flex-column">
        <?php foreach ($menu as [$ruta, $icono, $texto]): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL . $ruta ?>">
                    <i class="bi <?= $icono ?>"></i> <?= $texto ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<div class="col-md-10 p-4"></div>