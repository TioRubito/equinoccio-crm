<?php
// File: modules/pacientes/search.php
// Busca pacientes para el select2 via AJAX

require '../../config/db.php';

$q = $_GET['q'] ?? '';
header('Content-Type: application/json; charset=utf-8');

// Consulta con LIKE en nombre o cédula, máximo 10 resultados
$stmt = $pdo->prepare(
    "SELECT id, nombre, cedula
     FROM pacientes
     WHERE estado = 'activo'
       AND (nombre LIKE :q OR cedula LIKE :q)
     ORDER BY nombre
     LIMIT 10"
);
$stmt->execute([':q' => "%{$q}%"]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($pacientes);
exit;
