<?php
require_once __DIR__ . '/../../includes/auth.php';
requerirRol(['administrador']);

$db = Database::getInstance()->getConnection();

$id         = (int)($_GET['id']      ?? 0);
$idSeccion  = (int)($_GET['seccion'] ?? 0);

if ($id <= 0 || $idSeccion <= 0) {
    header('Location: ../secciones/listar.php');
    exit;
}

try {
    $db->prepare("DELETE FROM asignacion_est WHERE id_asig = :id")
       ->execute([':id' => $id]);
    header('Location: crear.php?seccion=' . $idSeccion . '&msg=borrado');
    exit;
} catch (Exception $e) {
    header('Location: crear.php?seccion=' . $idSeccion . '&msg=error');
    exit;
}