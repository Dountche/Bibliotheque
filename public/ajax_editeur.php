<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search']  ?? '');


$sql    = "SELECT * FROM Editeur  WHERE 1";


$params = [];

if ($search !== '') {
  $sql .= " AND (nom LIKE :search OR  siteweb LIKE :search)";
  $params[':search'] = "%{$search}%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'total' => count($data),
  'data'  => $data
]);
