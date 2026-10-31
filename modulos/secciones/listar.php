<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// ¡Cambio 1! Permitimos que administradores y docentes entren.
requerirRol(['administrador', 'docente']);

$db = Database::getInstance()->getConnection();

$sql = "SELECT s.id_sec, s.semestre, s.anio, 
               c.nombre AS curso, c.cod_cso,
               d.nombre AS doc_nombre, d.apellido AS doc_apellido
        FROM seccion s
        JOIN curso c ON c.id_cso = s.curso_id_cso
        JOIN docente d ON d.id_dte = s.docente_id_dte";

// ¡Cambio 2! Si es docente, filtramos para que solo vea SUS secciones
$parametros = [];
if ($rol === 'docente') {
    $sql .= " WHERE d.usuario_iduser = :id_user";
    $parametros[':id_user'] = $_SESSION['usuario']['iduser'];
}

$sql .= " ORDER BY s.anio DESC, s.semestre, c.nombre";

$stmt = $db->prepare($sql);
$stmt->execute($parametros);
$secciones = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-diagram-3"></i> 
        <?= ($rol === 'docente') ? 'Mis Secciones' : 'Secciones' ?>
    </h3>
    <?php if ($rol === 'administrador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Sección
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">
        <i class="bi bi-check-circle"></i>
        <?php
            if ($_GET['msg'] === 'creado')  echo 'Sección creada correctamente.';
            if ($_GET['msg'] === 'borrado') echo 'Sección eliminada correctamente.';
        ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th>Curso</th>
                    <?php if ($rol === 'administrador'): ?>
                        <th>Docente</th>
                    <?php endif; ?>
                    <th>Semestre</th>
                    <th>Año</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($secciones): ?>
                <?php foreach ($secciones as $s): ?>
                    <tr>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($s['cod_cso']) ?></span>
                            <?= htmlspecialchars($s['curso']) ?>
                        </td>
                        <?php if ($rol === 'administrador'): ?>
                            <td><?= htmlspecialchars($s['doc_nombre'] . ' ' . $s['doc_apellido']) ?></td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($s['semestre']) ?></td>
                        <td><?= htmlspecialchars($s['anio']) ?></td>
                        <td class="text-end">
                            <?php if ($rol === 'administrador'): ?>
                                <a href="../asignaciones/crear.php?seccion=<?= $s['id_sec'] ?>" 
                                   class="btn btn-sm btn-outline-info" title="Asignar Estudiantes">
                                    <i class="bi bi-person-plus"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $s['id_sec'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('¿Eliminar esta sección?')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php else: ?>
                                <!-- Si es docente, le mostramos un botón para ir directo a poner notas o asistencia -->
                                <a href="../notas/registrar.php?seccion=<?= $s['id_sec'] ?>" 
                                   class="btn btn-sm btn-outline-success" title="Registrar Notas">
                                    <i class="bi bi-clipboard-plus"></i>
                                </a>
                                <a href="../asistencia/registrar.php?seccion=<?= $s['id_sec'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Pasar Asistencia">
                                    <i class="bi bi-calendar-check"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= ($rol === 'administrador') ? 5 : 4 ?>" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay secciones registradas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>