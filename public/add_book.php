<?php

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Livre.php');
    exit;
}

// Récupération et nettoyage des données
$titre                = trim($_POST['titre'] ?? '');
$idAut                = (int)($_POST['idAut'] ?? 0);
$idEdit               = (int)($_POST['idEdit'] ?? 0);
$genre                = $_POST['genre'] ?? '';
$pub                  = trim($_POST['pub'] ?? '');
$disponible           = (int)($_POST['disponible'] ?? 0);
$exemplaire           = (int)($_POST['exemplaire'] ?? 0);


$stmt = $pdo->prepare("SELECT * FROM Livre WHERE titre = ? AND idAut = ?");
$stmt->execute([$titre, $idAut]);
if ($stmt->rowCount() > 0) {
    header('Location: Gestion_Livre.php?error=book_exists');
    exit();
}

$stmt = $pdo->prepare("SELECT idAut FROM Auteur WHERE  idAut = ? ");
$stmt->execute([$idAut]);
if ($stmt->rowCount() < 0) {
    header('Location: Gestion_Livre.php?error=auteur_unknown');
    exit();
}

$stmt = $pdo->prepare("SELECT idEdit FROM Editeur WHERE  idEdit = ?");
$stmt->execute([$idEdit]);
if ($stmt->rowCount() < 0) {
    header('Location: Gestion_Livre.php?error=editeur_unknown');
    exit();
}


if ($disponible > $exemplaire) {
    header('Location: Gestion_Livre.php?error=nbexemplaire_inferieur');
    exit();
}

if ($disponible < 0){
    header('Location: Gestion_Livre.php?error=disponible_inferieur');
    exit();
}
if ($exemplaire <0 ){
    header('Location: Gestion_Livre.php?error=exemplaire_inferieur');
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO Livre (idAut, idEdit, titre, genre, datepub, disponible, nbExemplaire ) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([$idAut, $idEdit, $titre, $genre, $pub, $disponible, $exemplaire]);

    header('Location: Gestion_Livre.php?action=add&status=success');
    exit();
} catch (Exception $e) {
    header('Location: Gestion_Livre.php?action=add&status=error');
    exit;
}
?>