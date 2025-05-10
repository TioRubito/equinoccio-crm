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

// dentro de "guardar" ANTES del INSERT/UPDATE
$solapa = $pdo->prepare("
  SELECT COUNT(*) FROM citas
  WHERE medico_id = ? AND id <> ?
    AND start < ? AND end   > ?
");


/* ---------- GUARDAR / ACTUALIZAR ---------- */
if ($accion === 'guardar') {

    // ←‑‑  aquí traemos todos los datos del formulario
    $id          = $_POST['id']          ?? '';   //  ←  AHORA SÍ existe
    $paciente_id = $_POST['paciente_id'] ?? null;
    $medico_id   = $_POST['medico_id']   ?? null;
    $start       = $_POST['start']       ?? null;
    $end         = $_POST['end']         ?? null;
    $title       = $_POST['title']       ?? '';

    $solapa->execute([$medico_id, $id ?: 0, $end, $start]);
    if ($solapa->fetchColumn()){
        echo json_encode(['error'=>'El médico ya tiene una cita en ese horario.']);
        exit;
    }

    if (!$paciente_id || !$medico_id) {
        echo json_encode(['error'=>'Faltan paciente o médico']);
        exit;
    }

    if ($id) {
        // actualizar
        $sql = "UPDATE citas
                   SET paciente_id = ?,
                       medico_id   = ?,
                       servicio_id = (SELECT servicio_id FROM medicos WHERE id = ?),
                       start = ?,  end = ?,  title = ?
                 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $paciente_id, $medico_id, $medico_id,
            $start, $end, $title,
            $id
        ]);
    } else {
        // insertar
        $sql = "INSERT INTO citas
                  (paciente_id, medico_id, servicio_id, start, end, title)
                VALUES
                  (?,?,(SELECT servicio_id FROM medicos WHERE id = ?),?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $paciente_id, $medico_id, $medico_id,
            $start, $end, $title
        ]);
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
