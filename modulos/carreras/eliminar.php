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
    $stmt = $db->prepare("DELETE FROM carrera WHERE id_crra = :id");
    $stmt->execute([':id' => $id]);
    header('Location: listar.php?msg=borrado');
    exit;
} catch (Exception $e) {
    // Si falla (por FK, por ejemplo), mostramos mensaje
    header('Location: listar.php?msg=error');
    exit;
}