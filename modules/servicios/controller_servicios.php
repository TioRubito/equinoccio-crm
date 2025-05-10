<?php
header('Content-Type: application/json');
require '../../config/db.php';
session_start();

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['nivel'], ['admin','operador'])) {
    echo json_encode(['error'=>'Acceso no autorizado']);
    exit;
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    /* ────────── LISTAR ────────── */
    case 'listar':
        $stmt = $pdo->query("SELECT id, nombre,color,activo FROM servicios ORDER BY nombre");
        echo json_encode($stmt->fetchAll());
        break;

    /* ────────── GUARDAR ───────── */
    case 'guardar':
        $id     = $_POST['id']     ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $color = $_POST['color'] ?? '#0d6efd';
        $activo = $_POST['activo'] ?? '1';

        if ($id) {
            $sql = "UPDATE servicios SET nombre = ?,color = ?, activo = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$nombre,$color,$activo,$id]);
        } else {
            $sql = "INSERT INTO servicios (nombre, color,activo) VALUES (?,?,?)";
            $pdo->prepare($sql)->execute([$nombre,$color,$activo]);
        }
        echo json_encode(['success'=>true]);
        break;

    /* ────────── BORRAR ────────── */
    case 'borrar':
        $id = $_POST['id'] ?? '';
        $pdo->prepare("DELETE FROM servicios WHERE id = ?")->execute([$id]);
        echo json_encode(['success'=>true]);
        break;

    default:
        echo json_encode(['error'=>'Acción inválida']);
}