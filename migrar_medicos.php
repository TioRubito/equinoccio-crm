<?php
require 'config/db.php';
session_start();

try {
    // 1️⃣ Obtener todos los médicos
    $stmt = $pdo->query("SELECT id, nombre FROM medicos WHERE usuario_id IS NULL");

    $insertUsuario = $pdo->prepare("
        INSERT INTO usuarios (nombre, usuario, clave, nivel) 
        VALUES (:nombre, :username, :password, :rol)
    ");

    $updateMedico = $pdo->prepare("
        UPDATE medicos SET usuario_id = :usuario_id WHERE id = :medico_id
    ");

    $contador = 0;

    while ($medico = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombre     = $medico['nombre'];
        $username   = 'medico_' . $medico['id'];
        $password   = password_hash('claveTemporal123', PASSWORD_DEFAULT);
        $rol        = 'medico';

        // 2️⃣ Crear usuario
        $insertUsuario->execute([
            ':nombre'   => $nombre,
            ':username' => $username,
            ':password' => $password,
            ':rol'      => $rol
        ]);

        $usuario_id = $pdo->lastInsertId();

        // 3️⃣ Actualizar médico con el usuario creado
        $updateMedico->execute([
            ':usuario_id' => $usuario_id,
            ':medico_id'  => $medico['id']
        ]);

        $contador++;
    }

    echo "✅ Se migraron $contador médicos a usuarios correctamente.";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
