<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

$id       = (int)($_GET['id']     ?? 0);
$idPensum = (int)($_GET['pensum'] ?? 0);

if ($id <= 0 || $idPensum <= 0) {
    header('Location: listar.php');
    exit;
}

try {
    $db->prepare("DELETE FROM curso WHERE id_cso = :id")
       ->execute([':id' => $id]);

    header('Location: pensum_cursos.php?id=' . $idPensum . '&msg=borrado');
    exit;
} catch (Exception $e) {
    header('Location: pensum_cursos.php?id=' . $idPensum . '&msg=error');
    exit;
}