<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

try {
    $db->beginTransaction();

    // 1) Obtener el iduser asociado
    $stmt = $db->prepare("SELECT usuario_iduser FROM docente WHERE id_dte = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        throw new Exception('Docente no encontrado.');
    }
    $idUser = $row['usuario_iduser'];

    // 2) Borrar docente
    $db->prepare("DELETE FROM docente WHERE id_dte = :id")
       ->execute([':id' => $id]);

    // 3) Borrar usuario
    $db->prepare("DELETE FROM usuario WHERE iduser = :id")
       ->execute([':id' => $idUser]);

    $db->commit();
    header('Location: listar.php?msg=borrado');
    exit;
} catch (Exception $e) {
    $db->rollBack();
    header('Location: listar.php?msg=error');
    exit;
}