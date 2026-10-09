<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

$id      = (int)($_GET['id']      ?? 0);
$idCarr  = (int)($_GET['carrera'] ?? 0);

if ($id <= 0 || $idCarr <= 0) {
    header('Location: listar.php');
    exit;
}

try {
    // Primero eliminamos los cursos del pensum (por la FK)
    $db->prepare("DELETE FROM curso WHERE pensum_id_psum = :id")
       ->execute([':id' => $id]);

    // Luego eliminamos el pensum
    $db->prepare("DELETE FROM pensum WHERE id_psum = :id")
       ->execute([':id' => $id]);

    header('Location: pensum.php?id=' . $idCarr . '&msg=borrado');
    exit;
} catch (Exception $e) {
    header('Location: pensum.php?id=' . $idCarr . '&msg=error');
    exit;
}