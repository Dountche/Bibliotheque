<?php

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Auteur.php');
    exit;
}

// Récupération et nettoyage des données
$nom              = trim($_POST['nom'] ?? '');
$prenoms          = trim($_POST['prenoms'] ?? '');
$sexe             = trim($_POST['sexe'] ?? '');
$dateNaiss        = trim($_POST['date'] ?? '');
$type             = $_POST['type'] ?? '';
$pays             = trim($_POST['pays'] ?? '');
$biographie       = trim($_POST['biographie'] ?? '');

// Vérifier l'existence de l'email dans la table User
$stmt = $pdo->prepare("SELECT * FROM Auteur WHERE nom = ? AND prenom = ?");
$stmt->execute([$nom, $prenoms]);
if ($stmt->rowCount() > 0) {
    header('Location: Gestion_Auteur.php?error=author_exists');
    exit();
}

$refmax = DateTime::createFromFormat('Y-m-d', '2005-01-01');
$age  = DateTime::createFromFormat('Y-m-d', $dateNaiss);

if ($age > $refmax) {
    header('Location: Gestion_Auteur.php?error=age_invalid');
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO Auteur (nom, prenom, sexe, born, types, pays, biographie) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([$nom, $prenoms, $sexe, $dateNaiss, $type, $pays, $biographie]);

    header('Location: Gestion_Auteur.php?action=add&status=success');
    exit();
} catch (Exception $e) {
    header('Location: Gestion_Auteur.php?action=add&status=error');
    exit;
}
?>