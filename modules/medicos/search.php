<?php
/*
require '../../config/db.php';
$q = $_GET['q']??'';
$s = $pdo->prepare("SELECT id, nombre FROM medicos
                    WHERE nombre LIKE ? ORDER BY nombre LIMIT 10");
$s->execute(["%$q%"]);
echo json_encode($s->fetchAll(PDO::FETCH_ASSOC));*/
// modules/medicos/search.php
require '../../config/db.php';

$q = $_GET['q'] ?? '';

$sql = "
  SELECT  m.id,
          m.nombre                 AS medico,
          s.nombre                 AS servicio
  FROM    medicos   m
  JOIN    servicios s ON s.id = m.servicio_id
  WHERE   m.activo = 1
      AND 
    (m.nombre  LIKE :q  OR  s.nombre LIKE :q)
  LIMIT  20
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => "%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
