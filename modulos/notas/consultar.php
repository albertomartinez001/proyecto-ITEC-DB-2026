<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador', 'estudiante', 'docente']);

$db = Database::getInstance()->getConnection();
$esEstudiante = (rolActual() === 'estudiante');
$idUser = $_SESSION['usuario']['iduser'];

// Si es estudiante, buscar sus notas
if ($esEstudiante) {
    $sql = "SELECT c.nombre AS curso, s.semestre, s.anio, n.zona, n.exam_f, n.nota_total
            FROM asignacion_est a
            JOIN estudiante e ON e.id_est = a.estudiante_id_est
            JOIN seccion s ON s.id_sec = a.seccion_id_sec
            JOIN curso c ON c.id_cso = s.curso_id_cso
            LEFT JOIN notas n ON n.asignacion_est_id_asig = a.id_asig
            WHERE e.usuario_iduser = :u
            ORDER BY s.anio DESC, c.nombre";
    $stmt = $db->prepare($sql);
    $stmt->execute([':u' => $idUser]);
    $notas = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<h3 class="mb-3"><i class="bi bi-clipboard-data"></i> Mis Calificaciones</h3>

<?php if ($esEstudiante): ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>Curso</th>
                        <th>Ciclo</th>
                        <th class="text-center">Zona</th>
                        <th class="text-center">Examen</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($notas): ?>
                    <?php foreach ($notas as $n): ?>
                        <tr>
                            <td><?= htmlspecialchars($n['curso']) ?></td>
                            <td><?= htmlspecialchars($n['semestre'] . ' ' . $n['anio']) ?></td>
                            <td class="text-center"><?= $n['zona'] ?? '-' ?></td>
                            <td class="text-center"><?= $n['exam_f'] ?? '-' ?></td>
                            <td class="text-center">
                                <?php if ($n['nota_total'] !== null): ?>
                                    <span class="badge bg-<?= ($n['nota_total'] >= 61) ? 'success' : 'danger' ?> fs-6">
                                        <?= $n['nota_total'] ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No tienes calificaciones registradas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Como administrador o docente, debes usar el módulo de <strong>Registrar Notas</strong>.
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>