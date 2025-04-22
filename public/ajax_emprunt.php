<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search']  ?? '');
$statut = trim($_GET['statut']  ?? '');

// Construction de la requête de base
$sql    = "SELECT
    emp.idEmp,
    etu.matEtu           AS matricule,
    CONCAT(etu.nom,' ',etu.prenoms) AS etudiant,
    l.titre              AS titre,
    l.genre              AS genre,
    emp.dateEmp,
    emp.dateRetour,
    emp.dateRendu
  FROM Emprunt AS emp
  INNER JOIN Etudiant AS etu ON emp.idEtu = etu.idEtu
  INNER JOIN Livre   AS l   ON emp.idLiv = l.idLiv
  WHERE 1=1";
$params = [];


if ($search !== '') {
    $sql .= " AND ( etu.matEtu    LIKE :search OR etu.nom    LIKE :search OR etu.prenoms LIKE :search OR l.titre    LIKE :search)";
    $params[':search'] = "%{$search}%";
}

// Filtrer par statut de rendu
if ($statut === 'retour') {
    $sql .= " AND emp.dateRendu IS NOT NULL";
} elseif ($statut === 'nonretour') {
    $sql .= " AND emp.dateRendu IS NULL";
}

$sql .="ORDER BY dateEmp DES";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'total' => count($data),
  'data'  => $data
]);
