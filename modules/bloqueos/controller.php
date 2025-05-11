<?php
header('Content-Type: application/json');
require '../../config/db.php';

$accion    = $_POST['accion']     ?? '';
$medico_id = intval($_POST['medico_id'] ?? 0);
$start     = $_POST['start'] ?? '';
$end       = $_POST['end']   ?? '';

if ($accion === 'listar' && $medico_id) {
    $stmt = $pdo->prepare(
      "SELECT fecha_inicio AS start,
              fecha_fin    AS end,
              motivo       AS title
         FROM bloqueos
        WHERE medico_id = ?
          AND fecha_inicio < ?
          AND fecha_fin    > ?"
    );
    $stmt->execute([$medico_id, $end, $start]);
    $rows = $stmt->fetchAll();

    /* se envían como background events */
    foreach ($rows as &$r) $r['display'] = 'background';
    echo json_encode($rows);
    exit;
}

echo json_encode([]);
