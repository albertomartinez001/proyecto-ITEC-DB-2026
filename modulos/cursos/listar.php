<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
requerirLogin(); // Todos pueden ver los cursos

$db = Database::getInstance()->getConnection();

$sql = "SELECT c.cod_cso, c.nombre AS curso, c.creditos, 
               ca.nombrecrra, p.a_vigencia
        FROM curso c
        JOIN pensum p ON p.id_psum = c.pensum_id_psum
        JOIN carrera ca ON ca.id_crra = p.carrera_id_crra
        ORDER BY ca.nombrecrra, c.nombre";

$cursos = $db->query($sql)->fetchAll();
?>

<h3 class="mb-3"><i class="bi bi-book"></i> Catálogo de Cursos</h3>

<?php if (rolActual() === 'administrador'): ?>
    <div class="alert alert-info py-2">
        <i class="bi bi-info-circle"></i> Para crear o eliminar cursos, ve a <strong>Carreras y Pensum</strong>.
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th>Código</th>
                    <th>Nombre del Curso</th>
                    <th>Créditos</th>
                    <th>Carrera</th>
                    <th>Vigencia</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($cursos): ?>
                <?php foreach ($cursos as $c): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($c['cod_cso']) ?></span></td>
                        <td><?= htmlspecialchars($c['curso']) ?></td>
                        <td><?= htmlspecialchars($c['creditos']) ?></td>
                        <td><?= htmlspecialchars($c['nombrecrra']) ?></td>
                        <td><?= htmlspecialchars($c['a_vigencia']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay cursos registrados.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>