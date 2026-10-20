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
    $db->prepare("DELETE FROM seccion WHERE id_sec = :id")
       ->execute([':id' => $id]);
    header('Location: listar.php?msg=borrado');
    exit;
} catch (Exception $e) {
    header('Location: listar.php?msg=error');
    exit;
}