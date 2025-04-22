<?php
session_start();
require_once __DIR__ . '/../config/database.php';
if ($_SESSION['user']['roles'] !== 'Admin') {
  header('Location: library.php?error=accès_interdit');
  exit;
}

$idDoc = (int)($_GET['idDoc'] ?? 0);
if (!$idDoc) { header('Location: library.php?error=id_invalide'); exit; }

// Récupère le nom de fichier pour suppression FS
$stmt = $pdo->prepare("SELECT fichier FROM Document WHERE idDoc = ?");
$stmt->execute([$idDoc]);
if ($f = $stmt->fetchColumn()) {
  @unlink(__DIR__.'/../documents/'.$f);
}

// Supprime en BDD
$stmt = $pdo->prepare("DELETE FROM Document WHERE idDoc = ?");
$stmt->execute([$idDoc]);

header('Location: library.php?action=delete&status=success');
exit;
