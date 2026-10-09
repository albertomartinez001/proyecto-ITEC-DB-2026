<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

$buscar = trim($_GET['q'] ?? '');

$sql = "SELECT e.id_est, e.carnet, e.nombre, e.apellido, u.nombre AS usuario
        FROM estudiante e
        JOIN usuario u ON u.iduser = e.usuario_iduser";
if ($buscar !== '') {
    $sql .= " WHERE LOWER(e.nombre) LIKE :q
              OR LOWER(e.apellido) LIKE :q
              OR TO_CHAR(e.carnet) LIKE :q";
}
$sql .= " ORDER BY e.apellido, e.nombre";

$stmt = $db->prepare($sql);
if ($buscar !== '') {
    $stmt->bindValue(':q', '%' . strtolower($buscar) . '%');
}
$stmt->execute();
$estudiantes = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-people-fill"></i> Estudiantes</h3>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Estudiante
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">
        <i class="bi bi-check-circle"></i>
        <?php
            if ($_GET['msg'] === 'creado')  echo 'Estudiante registrado correctamente.';
            if ($_GET['msg'] === 'editado') echo 'Estudiante actualizado correctamente.';
            if ($_GET['msg'] === 'borrado') echo 'Estudiante eliminado correctamente.';
        ?>
    </div>
<?php endif; ?>

<form class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" class="form-control"
               placeholder="Buscar por nombre, apellido o carnet..."
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
                    <th>Carnet</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Usuario</th>
                    <th style="width: 200px;" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($estudiantes): ?>
                <?php foreach ($estudiantes as $e): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($e['carnet']) ?></span></td>
                        <td><?= htmlspecialchars($e['nombre']) ?></td>
                        <td><?= htmlspecialchars($e['apellido']) ?></td>
                        <td><?= htmlspecialchars($e['usuario']) ?></td>
                        <td class="text-end">
                            <a href="ver.php?id=<?= $e['id_est'] ?>"
                               class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="editar.php?id=<?= $e['id_est'] ?>"
                               class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="eliminar.php?id=<?= $e['id_est'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Eliminar este estudiante?')" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay estudiantes registrados todavía.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>