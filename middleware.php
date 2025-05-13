<?php
session_start();

// 1) Verifica que esté logueado
function requireLogin() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
}

// 2) Verifica que su rol (o cualquiera de sus roles) esté en el arreglo permitido
function requireRole(array $allowedRoles) {
    requireLogin();
    // Supondremos que en $_SESSION['usuario']['roles'] tienes un array de roles, p. ej. ['medico','recepcionista']
    $userRoles = $_SESSION['usuario']['roles'] ?? [];
    // ¿Intersección vacía?
    if (count(array_intersect($userRoles, $allowedRoles)) === 0) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Acceso denegado.';
        exit;
    }
}

// 3) (Opcional) Si quieres verificar permisos más finos, podrías tener
//    una función requirePermission('citas.crear') que mire en otro array.
function requirePermission(string $permiso) {
    requireLogin();
    $permisos = $_SESSION['usuario']['permisos'] ?? [];
    if (!in_array($permiso, $permisos)) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Permiso insuficiente.';
        exit;
    }
}
