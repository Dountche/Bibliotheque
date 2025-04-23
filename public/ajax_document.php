<?php
// public/ajax_document.php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

// Récupération des filtres
$searchRaw = trim($_GET['search']   ?? '');
$catRaw    = trim($_GET['category'] ?? '');
if ($catRaw === 'other') {
    $catRaw = trim($_GET['other'] ?? '');
}

// Construction dynamique de la requête
$sql    = "SELECT *
           FROM Document
           WHERE 1";
$params = [];

if ($searchRaw !== '') {
    $sql .= " AND titre LIKE :search";
    $params[':search'] = "%{$searchRaw}%";
}
if ($catRaw !== '') {
    $sql   .= " AND categorie = :cat";
    $params[':cat'] = $catRaw;
}

$sql .= " ORDER BY dateUpload DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Renvoi JSON
echo json_encode([
    'total' => count($docs),
    'docs'  => $docs
]);
