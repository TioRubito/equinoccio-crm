<?php
header('Content-Type: application/json');
require '../../config/db.php';

$accion    = $_POST['accion']     ?? '';
$medico_id = intval($_POST['medico_id'] ?? 0);

if ($accion === 'listar' && $medico_id) {
    $stmt = $pdo->prepare(
      "SELECT dia_semana, hora_inicio, hora_fin
         FROM horarios_atencion
        WHERE medico_id = ?"
    );
    $stmt->execute([$medico_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

echo json_encode([]);
