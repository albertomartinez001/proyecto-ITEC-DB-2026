<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * ¿Hay sesión activa?
 */
function estaLogueado() {
    return isset($_SESSION['usuario']);
}

/**
 * Redirige al login si no hay sesión.
 */
function requerirLogin() {
    if (!estaLogueado()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

/**
 * Rol actual del usuario logueado.
 */
function rolActual() {
    return $_SESSION['usuario']['rol'] ?? null;
}

/**
 * Restringe el acceso a ciertos roles.
 */
function requerirRol($rolesPermitidos) {
    requerirLogin();
    if (!in_array(rolActual(), $rolesPermitidos, true)) {
        http_response_code(403);
        die('Acceso denegado: no tiene permisos para esta sección.');
    }
}

/**
 * Intenta autenticar un usuario.
 */
function autenticar($nombreUsuario, $password) {
    $db = Database::getInstance()->getConnection();

    $sql = "SELECT u.iduser, u.nombre, u.password, r.nombre_r AS rol
            FROM usuario u
            JOIN rol r ON r.id_rol = u.rol_id_rol
            WHERE u.nombre = :nombre";

    $stmt = $db->prepare($sql);
    $stmt->execute([':nombre' => $nombreUsuario]);
    $user = $stmt->fetch();

    if (!$user) return false;
    if ($password !== $user['password']) return false;

    $_SESSION['usuario'] = [
        'iduser' => $user['iduser'],
        'nombre' => $user['nombre'],
        'rol'    => strtolower($user['rol']),
    ];
    return true;
}