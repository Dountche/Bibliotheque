<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$idUser = $_SESSION['user']['idUser'];

// Supprimer l’utilisateur
$stmt = $pdo->prepare("DELETE FROM User WHERE idUser = ?");
$stmt->execute([$idUser]);

if($_SESSION['user']['roles']==="Admin"){
    $stmt = $pdo->prepare("DELETE FROM Administrateur WHERE idUser = ?");
    $stmt->execute([$idUser]);

} else{
    $stmt = $pdo->prepare("DELETE FROM Etudiant WHERE idUser = ?");
    $stmt->execute([$idUser]);
}

// Détruire la session et rediriger
session_destroy();
header('Location: index.php');
exit;
