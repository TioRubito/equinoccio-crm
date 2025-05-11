<?php
header('Content-Type: application/json');
require '../../config/db.php';

$medico_id = intval($_POST['medico_id'] ?? 0);
$out = ['businessHours'=>[], 'bloqueos'=>[]];

if ($medico_id) {
    /* --- horario_atencion → businessHours --- */
    $h = $pdo->prepare(
        "SELECT dia_semana, hora_inicio, hora_fin
           FROM horarios_atencion
          WHERE medico_id = ?");
    $h->execute([$medico_id]);
    foreach ($h as $row) {
        $out['businessHours'][] = [
            'daysOfWeek'=>[intval($row['dia_semana'])],
            'startTime' => substr($row['hora_inicio'],0,5),
            'endTime'   => substr($row['hora_fin']  ,0,5)
        ];
    }

    /* --- bloqueos de hoy hacia 1 mes adelante --- */
    $start = date('Y-m-d');
    $end   = date('Y-m-d', strtotime('+1 month'));

    $b = $pdo->prepare(
        "SELECT fecha_inicio AS start,
                fecha_fin    AS end,
                motivo       AS title
           FROM bloqueos
          WHERE medico_id = ?
            AND fecha_inicio < ?
            AND fecha_fin    > ?");
    $b->execute([$medico_id,$end,$start]);
    $out['bloqueos'] = array_map(function($r){
        $r['display']='background';
        return $r;
    }, $b->fetchAll());
}

echo json_encode($out);
