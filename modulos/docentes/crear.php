<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dpi      = trim($_POST['dpi']      ?? '');
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = $_POST['password']      ?? '';

    if ($nombre === '' || $apellido === '' || $dpi === '' || $usuario === '' || $password === '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        try {
            $db->beginTransaction();

            // 1) Verificar si el nombre de usuario ya existe
            $check = $db->prepare("SELECT COUNT(*) c FROM usuario WHERE nombre = :u");
            $check->execute([':u' => $usuario]);
            if ($check->fetch()['c'] > 0) {
                throw new Exception('El nombre de usuario ya está en uso.');
            }

            // 2) Insertar usuario (rol docente = 2)
            $sqlU = "INSERT INTO usuario (iduser, nombre, password, rol_id_rol)
                     VALUES (seq_usuario.NEXTVAL, :n, :p, 2)";
            $db->prepare($sqlU)->execute([':n' => $usuario, ':p' => $password]);

            // Obtener el ID recién creado
            $idUser = $db->query("SELECT seq_usuario.CURRVAL AS id FROM dual")->fetch()['id'];

            // 3) Insertar docente
            $sqlD = "INSERT INTO docente (id_dte, nombre, apellido, dpi, usuario_iduser)
                     VALUES (seq_docente.NEXTVAL, :nom, :ape, :dpi, :iduser)";
            $db->prepare($sqlD)->execute([
                ':nom'    => $nombre,
                ':ape'    => $apellido,
                ':dpi'    => $dpi,
                ':iduser' => $idUser,
            ]);

            $db->commit();
            header('Location: listar.php?msg=creado');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Docentes</a></li>
        <li class="breadcrumb-item active">Nuevo Docente</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-person-plus"></i> Nuevo Docente</h3>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" required autofocus>
            </div>
            <div class="col-md-6">
                <label class="form-label">Apellido <span class="text-danger">*</span></label>
                <input type="text" name="apellido" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">DPI <span class="text-danger">*</span></label>
                <input type="number" name="dpi" class="form-control" required
                       placeholder="Ej: 1234567890123">
            </div>

            <div class="col-md-6">
                <label class="form-label">Usuario <span class="text-danger">*</span></label>
                <input type="text" name="usuario" class="form-control" required
                       placeholder="Ej: jperez">
            </div>

            <div class="col-md-6">
                <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                <input type="text" name="password" class="form-control" required
                       placeholder="Contraseña inicial">
                <div class="form-text">Se almacena en texto plano (solo para este proyecto académico).</div>
            </div>

            <div class="col-12 border-top pt-3">
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
                <a href="listar.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>