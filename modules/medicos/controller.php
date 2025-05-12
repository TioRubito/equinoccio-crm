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
        $stmt = $pdo->query("SELECT 
                                    m.id,
                                    m.nombre AS medico,
                                    s.nombre AS servicio,
                                    m.servicio_id,
                                    u.usuario AS usuario
                                FROM medicos m
                                JOIN servicios s ON s.id = m.servicio_id
                                JOIN usuarios u ON u.id = m.usuario_id
                                ORDER BY m.nombre");
        echo json_encode($stmt->fetchAll());
        break;

    /* ───── GUARDAR ───── */
    case 'guardar':
        $id          = $_POST['id']          ?? '';
        $nombre      = $_POST['nombre']      ?? '';
        $servicio_id = $_POST['servicio_id'] ?? '';
        $usuario     = $_POST['usuario']     ?? '';
        $clave       = $_POST['clave']     ?? '';

        if ($id) {
            // Buscar ID del usuario primero
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $usuario_id = $stmt->fetchColumn();

            if ($usuario_id) {
                $sql = "UPDATE medicos SET nombre = ?, servicio_id = ?, usuario_id = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$nombre, $servicio_id, $usuario_id, $id]);
                            // Solo actualiza la contraseña si se ingresó una nueva
                if (!empty($clave)) {
                    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET clave = ? WHERE id = ?");
                    $stmt->execute([$claveHash, $usuario_id]);
                }

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => '❌ Usuario no encontrado.']);
            }
        } else {
           /* $sql = "INSERT INTO medicos (nombre, servicio_id) VALUES (?,?)";
            $pdo->prepare($sql)->execute([$nombre, $servicio_id]);*/

           /* $usuario = $_POST['usuario'] ?? '';
            $clave   = $_POST['clave']   ?? '';
            $nivel   = 'medico';

            try {
                $pdo->beginTransaction();

                // Crear usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, clave, nivel) VALUES (?,?,?,?)");
                $stmt->execute([$nombre, $usuario, password_hash($clave, PASSWORD_DEFAULT), $nivel]);
                $usuario_id = $pdo->lastInsertId();

                // Crear médico
                $stmt = $pdo->prepare("INSERT INTO medicos (nombre, servicio_id, usuario_id) VALUES (?,?,?)");
                $stmt->execute([$nombre, $servicio_id, $usuario_id]);

                $pdo->commit();
                echo json_encode(['success'=>true]);
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
            }*/
            $usuario = $_POST['usuario'] ?? '';
            $clave   = $_POST['clave']   ?? '';
            $nivel   = 'medico';

            try {
                // Verificar si el usuario ya existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                $stmt->execute([$usuario]);
                if ($stmt->fetch()) {
                    echo json_encode(['success' => false, 'error' => '❌ El nombre de usuario ya está en uso.']);
                    exit;
                }

                $pdo->beginTransaction();

                // Crear usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, clave, nivel) VALUES (?,?,?,?)");
                $stmt->execute([$nombre, $usuario, password_hash($clave, PASSWORD_DEFAULT), $nivel]);
                $usuario_id = $pdo->lastInsertId();

                // Crear médico
                $stmt = $pdo->prepare("INSERT INTO medicos (nombre, servicio_id, usuario_id) VALUES (?,?,?)");
                $stmt->execute([$nombre, $servicio_id, $usuario_id]);

                $pdo->commit();
                ob_clean();
                echo json_encode(['success' => true]);
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }   


        }
       // echo json_encode(['success'=>true]);
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