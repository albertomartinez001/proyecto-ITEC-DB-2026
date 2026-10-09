<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

// Buscador
$buscar = trim($_GET['q'] ?? '');
$sql = "SELECT id_crra, nombrecrra FROM carrera";
if ($buscar !== '') {
    $sql .= " WHERE LOWER(nombrecrra) LIKE :q";
}
$sql .= " ORDER BY nombrecrra";

$stmt = $db->prepare($sql);
if ($buscar !== '') {
    $stmt->bindValue(':q', '%' . strtolower($buscar) . '%');
}
$stmt->execute();
$carreras = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-mortarboard"></i> Carreras</h3>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva Carrera
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">
        <i class="bi bi-check-circle"></i>
        <?php
            if ($_GET['msg'] === 'creado')  echo 'Carrera creada correctamente.';
            if ($_GET['msg'] === 'editado') echo 'Carrera actualizada correctamente.';
            if ($_GET['msg'] === 'borrado') echo 'Carrera eliminada correctamente.';
        ?>
    </div>
<?php endif; ?>

<form class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" class="form-control"
               placeholder="Buscar por nombre..."
               value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">
            <i class="bi bi-search"></i> Buscar
        </button>
        <?php if ($buscar !== ''): ?>
            <a href="listar.php" class="btn btn-outline-danger">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Nombre de la Carrera</th>
                    <th style="width: 200px;" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($carreras): ?>
                <?php foreach ($carreras as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id_crra']) ?></td>
                        <td><?= htmlspecialchars($c['nombrecrra']) ?></td>
                        <td class="text-end">
                            <a href="pensum.php?id=<?= $c['id_crra'] ?>"
                               class="btn btn-sm btn-outline-info" title="Ver Pensum">
                                <i class="bi bi-list-ul"></i> Pensum
                            </a>
                            <a href="editar.php?id=<?= $c['id_crra'] ?>"
                               class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="eliminar.php?id=<?= $c['id_crra'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar esta carrera?')" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay carreras registradas todavía.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>