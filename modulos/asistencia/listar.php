<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador', 'estudiante', 'docente']);

$db = Database::getInstance()->getConnection();
$esEstudiante = (rolActual() === 'estudiante');
$idUser = $_SESSION['usuario']['iduser'];

$asistencias = [];
if ($esEstudiante) {
    $sql = "SELECT a.fecha, a.estado, c.nombre AS curso, s.semestre, s.anio
            FROM asistencia a
            JOIN seccion s ON s.id_sec = a.seccion_id_sec
            JOIN curso c ON c.id_cso = s.curso_id_cso
            WHERE a.usuario_iduser = :u
            ORDER BY a.fecha DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([':u' => $idUser]);
    $asistencias = $stmt->fetchAll();
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<h3 class="mb-3"><i class="bi bi-calendar-check"></i> Mi Historial de Asistencia</h3>

<?php if ($esEstudiante): ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>Fecha</th>
                        <th>Curso</th>
                        <th>Ciclo</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($asistencias): ?>
                    <?php foreach ($asistencias as $a): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($a['fecha'])) ?></td>
                            <td><?= htmlspecialchars($a['curso']) ?></td>
                            <td><?= htmlspecialchars($a['semestre'] . ' ' . $a['anio']) ?></td>
                            <td class="text-center">
                                <?php if ($a['estado'] === 'Presente'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Presente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ausente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No tienes asistencias registradas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Usa el menú para <a href="registrar.php" class="alert-link">Registrar Asistencia</a>.
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>