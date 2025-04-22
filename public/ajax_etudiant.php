<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search']  ?? '');
$school = trim($_GET['school']  ?? '');
$levels  = trim($_GET['levels']   ?? '');

// Construire la requête
$sql    = "SELECT * FROM Etudiant WHERE 1";
$params = [];

if ($search !== '') {
  $sql .= " AND (nom LIKE :search OR prenoms LIKE :search OR matEtu LIKE :search)";
  $params[':search'] = "%{$search}%";
}
if ($school !== '') {
  $sql .= " AND ecole = :school";
  $params[':school'] = $school;
}
if ($levels !== '') {
  $sql .= " AND niveau = :levels";
  $params[':levels'] = $levels;
}
$sql .= "ORDER BY prenoms ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'total' => count($data),
  'data'  => $data
]);
