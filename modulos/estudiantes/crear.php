<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carnet   = trim($_POST['carnet']   ?? '');
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = $_POST['password']      ?? '';

    if ($carnet === '' || $nombre === '' || $apellido === '' || $usuario === '' || $password === '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        try {
            $db->beginTransaction();

            // 1. Verificar usuario único
            $check = $db->prepare("SELECT COUNT(*) c FROM usuario WHERE nombre = :u");
            $check->execute([':u' => $usuario]);
            if ($check->fetch()['c'] > 0) {
                throw new Exception('El nombre de usuario ya está en uso.');
            }

            // 2. Insertar usuario (rol estudiante = 3)
            $sqlU = "INSERT INTO usuario (iduser, nombre, password, rol_id_rol)
                     VALUES (seq_usuario.NEXTVAL, :n, :p, 3)";
            $db->prepare($sqlU)->execute([':n' => $usuario, ':p' => $password]);

            $idUser = $db->query("SELECT seq_usuario.CURRVAL AS id FROM dual")->fetch()['id'];

            // 3. Insertar estudiante
            $sqlE = "INSERT INTO estudiante (id_est, carnet, nombre, apellido, usuario_iduser)
                     VALUES (seq_estudiante.NEXTVAL, :car, :nom, :ape, :iduser)";
            $db->prepare($sqlE)->execute([
                ':car'    => $carnet,
                ':nom'    => $nombre,
                ':ape'    => $apellido,
                ':iduser' => $idUser,
            ]);

            $db->commit();
            header('Location: listar.php?msg=creado');
            exit;

        } catch (Exception $e) {
            $db->rollBack(); // <--- ESTA LÍNEA FALTABA
            
            // Atrapar el error de carnet duplicado (ORA-00001)
            if (strpos($e->getMessage(), 'ORA-00001') !== false) {
                $error = 'El carnet ingresado ya está registrado. Por favor, verifica el número.';
            } else {
                $error = $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="listar.php">Estudiantes</a></li>
        <li class="breadcrumb-item active">Nuevo Estudiante</li>
    </ol>
</nav>

<h3 class="mb-3"><i class="bi bi-person-plus"></i> Nuevo Estudiante</h3>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Carnet <span class="text-danger">*</span></label>
                <input type="number" name="carnet" class="form-control" required autofocus
                       placeholder="Ej: 20240001">
            </div>
            <div class="col-md-6">
                <label class="form-label">Usuario <span class="text-danger">*</span></label>
                <input type="text" name="usuario" class="form-control" required
                       placeholder="Ej: mgarcia">
            </div>

            <div class="col-md-6">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Apellido <span class="text-danger">*</span></label>
                <input type="text" name="apellido" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                <input type="text" name="password" class="form-control" required
                       placeholder="Contraseña inicial">
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