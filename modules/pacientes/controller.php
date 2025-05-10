<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../config/db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'listar') {
    $stmt = $pdo->query("SELECT * FROM pacientes ORDER BY id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($accion === 'guardar') {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $fecha = $_POST['fecha_nacimiento'] ?? null;
    $sexo = $_POST['sexo'] ?? 'M';

    if ($id) {
        $stmt = $pdo->prepare("UPDATE pacientes SET nombre=?, cedula=?, correo=?, telefono=?, fecha_nacimiento=?, sexo=? WHERE id=?");
        $stmt->execute([$nombre, $cedula, $correo, $telefono, $fecha, $sexo, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pacientes (nombre, cedula, correo, telefono, fecha_nacimiento, sexo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $cedula, $correo, $telefono, $fecha, $sexo]);
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($accion === 'cambiar_estado') {
    $id = $_POST['id'] ?? '';
    $nuevo_estado = $_POST['estado'] ?? 'inactivo';
    $stmt = $pdo->prepare("UPDATE pacientes SET estado=? WHERE id=?");
    $stmt->execute([$nuevo_estado, $id]);
    echo json_encode(['success' => true]);
    exit;
}
?>
