<?php
// File: modules/citas/controller_citas.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
/*header('Content-Type: application/json');
session_start();*/
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['nivel'], ['admin', 'operador'])) {
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}
require '../../config/db.php';
try {
//$accion = $_POST['accion'] ?? '';
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';


/*if($accion === 'listar') {
  $stmt = $pdo->query("SELECT c.id, p.nombre as title, c.start, c.end, c.paciente_id, p.nombre AS paciente_nombre
                      FROM citas c
                      JOIN pacientes p ON p.id = c.paciente_id");
  $events = [];
  foreach($stmt->fetchAll() as $row) {
    $events[] = [
      'id'            => $row['id'],
      'title'         => $row['title'],
      'start'         => $row['start'],
      'end'           => $row['end'],
      'paciente_id'   => $row['paciente_id'],
      'extendedProps' => ['paciente_nombre' => $row['paciente_nombre']]
    ];
  }
  echo json_encode($events);
  exit;
}*/
/* ───────── LISTAR para FullCalendar ───────── */
if ($accion === 'listar') {

    // Rango que FullCalendar envía automáticamente
    $start = $_REQUEST['start'] ?? null;
    $end   = $_REQUEST['end']   ?? null;

    $sql = "
      SELECT
        c.id,
        p.nombre as title,
        c.start,
        c.end,
        c.paciente_id,
        p.nombre   AS paciente_nombre,
        p.cedula   AS paciente_cedula,
        c.medico_id,
        m.nombre   AS medico_nombre,
        s.nombre   AS servicio_nombre,
        m.servicio_id,
        s.color   AS servicio_color
      FROM citas c
      JOIN pacientes p ON p.id = c.paciente_id
      JOIN medicos   m ON m.id = c.medico_id
      JOIN servicios s ON s.id = m.servicio_id
      WHERE 1 = 1
    ";

    $params = [];

    /* filtra sólo el rango que pide FullCalendar */
    if ($start && $end) {
        $sql .= " AND c.start < :end AND c.end > :start";
        $params[':start'] = $start;
        $params[':end']   = $end;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $events = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $events[] = [
          'id'    => $row['id'],
          'title' => $row['title'] ?: $row['paciente_nombre'],
          // convierte “YYYY-MM-DD HH:MM:SS” → “YYYY-MM-DDTHH:MM:SS”
          'start' => str_replace(' ', 'T', $row['start']),
          'end'   => str_replace(' ', 'T', $row['end']),
          // lo que FullCalendar pinta como "backgroundColor"
          'backgroundColor' => $row['servicio_color'] ?? null,
          // la línea que manda el color
          //'color' => $row['servicio_color'],
          'extendedProps' => [
              'paciente_id'     => $row['paciente_id'],
              'paciente_nombre' => $row['paciente_nombre'],
              'paciente_cedula' => $row['paciente_cedula'],
              'medico_id'       => $row['medico_id'],
              'medico_nombre'   => $row['medico_nombre'],
              'servicio'        => $row['servicio_nombre'],
              'servicio_id'     => $row['servicio_id'],
              'servicio_colorx'   => $row['servicio_color']
          ],
        ];

    }

    echo json_encode($events);
    exit;
}




/* ---------- GUARDAR / ACTUALIZAR ---------- */
/*  ── controller_citas.php  ────────── */
if ($accion === 'guardar') {

    $id          = $_POST['id']          ?? '';
    $paciente_id = $_POST['paciente_id'] ?? '';
    $medico_id   = $_POST['medico_id']   ?? '';
    $start       = $_POST['start']       ?? '';
    $end         = $_POST['end']         ?? '';
    $title       = $_POST['title']       ?? '';

    /* ── 1) ¿hay otra cita que se cruce con este médico? ───────── */
    $sqlSolape = "
        SELECT 1
          FROM citas
         WHERE medico_id = ?
           AND start <  ?
           AND end   >  ?
           ".($id ? "AND id <> ?" : "")."
         LIMIT 1";
    $params = $id ? [$medico_id, $end, $start, $id]
                  : [$medico_id, $end, $start];

    $haySolape = $pdo->prepare($sqlSolape);
    $haySolape->execute($params);

    if ($haySolape->fetchColumn()){
        echo json_encode(['error'=>'Ese médico ya tiene una cita en ese intervalo.']);
        exit;
    }

    // 1) ¿está dentro de algún horario?
   /* $okHorario = $pdo->prepare("
      SELECT 1 FROM horarios_atencion
      WHERE medico_id = ?
        AND dia_semana = DAYOFWEEK(?) - 1          -- MySQL: domingo=1
        AND TIME(?) BETWEEN hora_inicio AND hora_fin
        AND TIME(?) BETWEEN hora_inicio AND hora_fin");
    $okHorario->execute([$medico_id, $start, $start, $end]);
    if(!$okHorario->fetchColumn()){
        echo json_encode(['error'=>'Fuera del horario de atención']); exit;
    }*/

    // 2) ¿choca con un bloqueo?
    $chocaBloqueo = $pdo->prepare("
      SELECT 1 FROM bloqueos
      WHERE medico_id = ?
        AND ? < fecha_fin  AND ? > fecha_inicio");
    $chocaBloqueo->execute([$medico_id, $start, $end]);
    if($chocaBloqueo->fetchColumn()){
        echo json_encode(['error'=>'El médico está bloqueado en ese rango']); exit;
    }



    /* ── 2) INSERT/UPDATE normal (ya no se solapan) ───────────── */
    if ($id){
        $sql = "UPDATE citas
                   SET paciente_id = ?, medico_id = ?,
                       servicio_id = (SELECT servicio_id FROM medicos WHERE id = ?),
                       start = ?, end = ?, title = ?
                 WHERE id = ?";
        $pdo->prepare($sql)
            ->execute([$paciente_id,$medico_id,$medico_id,$start,$end,$title,$id]);
    }else{
        $sql = "INSERT INTO citas
                  (paciente_id,medico_id,servicio_id,start,end,title)
                VALUES (?,?,(SELECT servicio_id FROM medicos WHERE id = ?),?,?,?)";
        $pdo->prepare($sql)
            ->execute([$paciente_id,$medico_id,$medico_id,$start,$end,$title]);
    }

    echo json_encode(['success'=>true]);
    exit;
}



if($accion === 'borrar') {
  $stmt = $pdo->prepare("DELETE FROM citas WHERE id=?");
  $stmt->execute([$_POST['id']]);
  echo json_encode(['success' => true]);
  exit;
}

echo json_encode(['error' => 'Acción desconocida']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>
