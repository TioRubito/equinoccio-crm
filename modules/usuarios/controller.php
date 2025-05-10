<?php
header('Content-Type: application/json');
session_start();
header('Content-Type: application/json; charset=UTF-8');

require '../../config/db.php';


if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}
$raw = json_decode(file_get_contents('php://input'), true);
$_POST = array_merge($_POST, is_array($raw) ? $raw : []);


$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        try {
            $stmt = $pdo->query("SELECT id, nombre, usuario, nivel, estado FROM usuarios");
            $usuarios = $stmt->fetchAll();
            echo json_encode($usuarios);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al listar: ' . $e->getMessage()]);
        }
        break;

    case 'guardar':
        $id = $_POST['id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $nivel = $_POST['nivel'] ?? 'operador';
        $clave = $_POST['clave'] ?? '';

        try {
            if ($id) {
                if ($clave) {
                    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=?, clave=?, nivel=? WHERE id=?");
                    $stmt->execute([$nombre, $usuario, $claveHash, $nivel, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=?, nivel=? WHERE id=?");
                    $stmt->execute([$nombre, $usuario, $nivel, $id]);
                }
            } else {
                $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, clave, nivel, estado) VALUES (?, ?, ?, ?, 'activo')");
                $stmt->execute([$nombre, $usuario, $claveHash, $nivel]);
            }

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
        break;

    case 'cambiar_estado':
        $id = $_POST['id'] ?? '';
        $nuevo_estado = $_POST['estado'] ?? 'inactivo';

        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET estado=? WHERE id=?");
            $stmt->execute([$nuevo_estado, $id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al cambiar estado: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
