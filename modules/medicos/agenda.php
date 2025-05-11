<?php
// modules/medicos/agenda.php
header('Content-Type: application/json');
require '../../config/db.php';

$medico = $_POST['medico_id'] ?? 0;

// --- Horario semanal (businessHours) ----------------
$hq = $pdo->prepare("
  SELECT dia_semana, TIME_FORMAT(hora_inicio,'%H:%i') h1, TIME_FORMAT(hora_fin,'%H:%i') h2
  FROM horarios_atencion WHERE medico_id = ?");
$hq->execute([$medico]);
$business = [];
foreach ($hq as $row){
  $business[] = [
      'daysOfWeek'=>[(int)$row['dia_semana']], // [0] Dom … [6] Sáb
      'startTime' =>$row['h1'],
      'endTime'   =>$row['h2']
  ];
}

// --- Bloqueos puntuales --------------------------------
$bq = $pdo->prepare("
  SELECT fecha_inicio fi, fecha_fin ff, motivo
  FROM bloqueos WHERE medico_id = ?");
$bq->execute([$medico]);

$blocks = [];
foreach ($bq as $row){
  $blocks[] = [
      'start'     =>$row['fi'],
      'end'       =>$row['ff'],
      'title'     =>$row['motivo'],
      'rendering' =>'background',     // ← FullCalendar
      'overlap'   =>false,
      'color'     =>'#ffc107'          // ámbar
  ];
}

echo json_encode(['businessHours'=>$business,'bloqueos'=>$blocks]);
