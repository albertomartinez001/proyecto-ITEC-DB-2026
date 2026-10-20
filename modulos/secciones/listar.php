<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

$sql = "SELECT s.id_sec, s.semestre, s.anio, 
               c.nombre AS curso, c.cod_cso,
               d.nombre AS doc_nombre, d.apellido AS doc_apellido
        FROM seccion s
        JOIN curso c ON c.id_cso = s.curso_id_cso
        JOIN docente d ON d.id_dte = s.docente_id_dte
        ORDER BY s.anio DESC, s.semestre, c.nombre";

$secciones = $db->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-diagram-3"></i> Secciones</h3>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva Sección
    </a>
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
                    <th>Docente</th>
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
                        <td><?= htmlspecialchars($s['doc_nombre'] . ' ' . $s['doc_apellido']) ?></td>
                        <td><?= htmlspecialchars($s['semestre']) ?></td>
                        <td><?= htmlspecialchars($s['anio']) ?></td>
                        <td class="text-end">
                            <!-- Botón para asignar estudiantes (lo haremos luego) -->
                            <a href="../asignaciones/crear.php?seccion=<?= $s['id_sec'] ?>" 
                               class="btn btn-sm btn-outline-info" title="Asignar Estudiantes">
                                <i class="bi bi-person-plus"></i>
                            </a>
                            <a href="eliminar.php?id=<?= $s['id_sec'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar esta sección?')" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay secciones registradas todavía.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>