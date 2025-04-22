<?php

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Editeur.php');
    exit;
}

// Récupération et nettoyage des données
$nom              = trim($_POST['nom'] ?? '');
$pays             = trim($_POST['pays'] ?? '');
$siteweb       = trim($_POST['siteweb'] ?? '');

// Vérifier l'existence de l'email dans la table User
$stmt = $pdo->prepare("SELECT * FROM Editeur WHERE nom = ?");
$stmt->execute([$nom]);
if ($stmt->rowCount() > 0) {
    header('Location: Gestion_Editeur.php?error=editeur_exists');
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO Editeur (nom, pays, siteweb) VALUES (?, ?, ?)");

    $stmt->execute([$nom, $pays, $siteweb]);

    header('Location: Gestion_Editeur.php?action=add&status=success');
    exit();
} catch (Exception $e) {
    header('Location: Gestion_Editeur.php?action=add&status=error');
    exit;
}
?>