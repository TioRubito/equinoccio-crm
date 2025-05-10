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
    /* ───── LISTAR ───── */
    case 'listar':
        $stmt = $pdo->query("SELECT m.id,
                                    m.nombre   AS medico,
                                    s.nombre   AS servicio,
                                    m.servicio_id
                             FROM   medicos   m
                             JOIN   servicios s ON s.id = m.servicio_id
                             ORDER BY m.nombre");
        echo json_encode($stmt->fetchAll());
        break;

    /* ───── GUARDAR ───── */
    case 'guardar':
        $id          = $_POST['id']          ?? '';
        $nombre      = $_POST['nombre']      ?? '';
        $servicio_id = $_POST['servicio_id'] ?? '';
        if ($id) {
            $sql = "UPDATE medicos SET nombre=?, servicio_id=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nombre, $servicio_id, $id]);
        } else {
            $sql = "INSERT INTO medicos (nombre, servicio_id) VALUES (?,?)";
            $pdo->prepare($sql)->execute([$nombre, $servicio_id]);
        }
        echo json_encode(['success'=>true]);
        break;

    /* ───── BORRAR ───── */
    case 'borrar':
        $id = $_POST['id'] ?? '';
        $pdo->prepare("DELETE FROM medicos WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]);
        break;

    default:
        echo json_encode(['error'=>'Acción inválida']);
}