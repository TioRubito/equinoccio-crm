<?php
header('Content-Type: application/json');
session_start();
require '../../config/db.php';

// Sólo médicos y operadores
if(!isset($_SESSION['usuario']) || !in_array($_SESSION['nivel'], ['admin','operador','medico'])){
  echo json_encode(['error'=>'Acceso no autorizado']); exit;
}
$accion = $_POST['accion'] ?? '';

// 1) Listar citas de hoy con su atención (si existe)
if($accion==='listar'){
  $medId = $_SESSION['id_usuario']; // asumimos medico = usuario
  $hoy = date('Y-m-d');
  $sql = "
    SELECT c.id AS cita_id, p.nombre AS paciente, c.start, c.end,
           a.id AS atencion_id, a.inicio, a.fin, a.estado
    FROM citas c
    JOIN pacientes p ON p.id = c.paciente_id
    LEFT JOIN atenciones a ON a.cita_id = c.id
    join medicos m on c.medico_id = m.id
    join usuarios u on m.usuario_id =u.id
    WHERE u.id = ?
      AND DATE(c.start)=?
    ORDER BY c.start
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$medId, $hoy]);
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

/***************************************************************************************** */
  if($accion==='iniciar'){
      $stmt = $pdo->prepare("INSERT INTO atenciones (cita_id, inicio, estado) VALUES (?, NOW(), 'atendido')");
      $stmt->execute([$_POST['cita_id']]);
      echo json_encode(['success' => true]);
  }else 
  if ($accion==='finalizar'){
    $stmt = $pdo->prepare("UPDATE atenciones SET fin = NOW() WHERE id = ?");
    $stmt->execute([$_POST['atencion_id']]);

    // Calcular duración
    $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(MINUTE, inicio, fin) AS duracion FROM atenciones WHERE id = ?");
    $stmt->execute([$_POST['atencion_id']]);
    $minutos = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'duracion' => $minutos]);
  }else
  if($accion === 'ausente'){
    $stmt = $pdo->prepare("INSERT INTO atenciones (cita_id, estado) VALUES (?, 'ausente')");
    $stmt->execute([$_POST['cita_id']]);
    echo json_encode(['success' => true]);
  }



/*
// 2) Guardar o actualizar atención
if($accion==='guardar'){
  $cita_id = $_POST['cita_id'];
  $inicio  = $_POST['inicio'];
  $fin     = $_POST['fin'];
  $estado  = $_POST['estado'];
  $notas   = $_POST['notas'];
  $diag    = $_POST['diagnostico'];

  // Comprobar si ya existe
  $check = $pdo->prepare("SELECT id FROM atenciones WHERE cita_id=?");
  $check->execute([$cita_id]);
  if($id = $check->fetchColumn()){
    $upd = $pdo->prepare(
      "UPDATE atenciones SET inicio=?,fin=?,estado=?,notas=?,diagnostico=? WHERE id=?"
    );
    $upd->execute([$inicio,$fin,$estado,$notas,$diag,$id]);
  } else {
    $ins = $pdo->prepare(
      "INSERT INTO atenciones (cita_id,inicio,fin,estado,notas,diagnostico)
       VALUES (?,?,?,?,?,?)"
    );
    $ins->execute([$cita_id,$inicio,$fin,$estado,$notas,$diag]);
  }
  echo json_encode(['success'=>true]); exit;
}

// Acción desconocida
echo json_encode(['error'=>'Acción inválida']); exit;*/