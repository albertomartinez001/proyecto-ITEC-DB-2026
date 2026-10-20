<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
requerirRol(['administrador']); // Solo el admin ve todo el historial

$db = Database::getInstance()->getConnection();

$sql = "SELECT a.id_asig, a.fecha, 
               e.carnet, e.nombre AS est_nombre, e.apellido AS est_apellido,
               c.nombre AS curso, c.cod_cso,
               s.semestre, s.anio
        FROM asignacion_est a
        JOIN estudiante e ON e.id_est = a.estudiante_id_est
        JOIN seccion s ON s.id_sec = a.seccion_id_sec
        JOIN curso c ON c.id_cso = s.curso_id_cso
        ORDER BY a.fecha DESC, e.apellido";

$asignaciones = $db->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-link-45deg"></i> Historial de Asignaciones</h3>
</div>

<div class="alert alert-info py-2">
    <i class="bi bi-info-circle"></i> Para asignar un estudiante a una sección, ve al módulo de <strong>Secciones</strong> y haz clic en el botón <i class="bi bi-person-plus"></i>.
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th>Fecha</th>
                    <th>Estudiante</th>
                    <th>Curso</th>
                    <th>Ciclo</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($asignaciones): ?>
                <?php foreach ($asignaciones as $a): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($a['fecha'])) ?></td>
                        <td>
                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($a['carnet']) ?></span>
                            <?= htmlspecialchars($a['est_apellido'] . ', ' . $a['est_nombre']) ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($a['cod_cso']) ?></span>
                            <?= htmlspecialchars($a['curso']) ?>
                        </td>
                        <td><?= htmlspecialchars($a['semestre'] . ' ' . $a['anio']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay asignaciones registradas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>