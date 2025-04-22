<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search']  ?? '');


$sql    = "SELECT
    l.idLiv AS idLiv,
    l.titre,
    l.idAut AS idAut,
    l.idEdit AS idEdit,
    CONCAT(a.nom, ' ', a.prenom) AS auteur,
    e.nom AS editeur,
    l.genre,
    l.datepub,
    l.disponible,
    l.nbExemplaire
  FROM Livre l
  INNER JOIN Auteur a ON l.idAut = a.idAut
  INNER JOIN Editeur e ON l.idEdit = e.idEdit
  WHERE 1";


$params = [];

if ($search !== '') {
  $sql .= " AND (titre LIKE :search OR  genre LIKE :search)";
  $params[':search'] = "%{$search}%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'total' => count($data),
  'data'  => $data
]);
