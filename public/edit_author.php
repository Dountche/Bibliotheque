<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Auteur.php');
    exit;
}

// Récupération des données
$idAut          = (int)($_POST['idAut'] ?? 0);
$prenoms        = trim($_POST['prenoms'] ?? '');
$nom            = trim($_POST['nom'] ?? '');
$born           = $_POST['born'] ?? '';
$type           = trim($_POST['type'] ?? '');
$pays           = trim($_POST['pays'] ?? '');
$biographie     = trim(string: $_POST['biographie'] ?? '');
$sexe           = $_POST['sexe'] ?? '';

if ($idAut <= 0) {
    header('Location: Gestion_Auteur.php?error=invalid_id');
    exit;
}

$refmax = DateTime::createFromFormat('Y-m-d', '2005-01-01');
$age  = DateTime::createFromFormat('Y-m-d', $born);

if ($age > $refmax) {
    header('Location: Gestion_Auteur.php?error=age_invalid');
    exit();
}


try {
    $stmt = $pdo->prepare("
        UPDATE Auteur SET
          prenom     = ?,
          nom        = ?,
          born       = ?,
          types      = ?,
          pays       = ?,
          biographie = ?,
          sexe       = ?
        WHERE idAut  = ?
    ");
    $stmt->execute([
        $prenoms, $nom, $born,
        $type, $pays, $biographie, $sexe,
        $idAut
    ]);
    header('Location: Gestion_Auteur.php?action=edit&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Auteur.php?action=edit&status=error');
}
exit;