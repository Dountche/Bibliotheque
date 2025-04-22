<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Livre.php');
    exit;
}

// Récupération des données
$idLiv                = (int)($_POST['idLiv'] ?? 0);
$titre                = trim($_POST['titre'] ?? '');
$idAut                = (int)($_POST['idAut'] ?? 0);
$idEdit               = (int)($_POST['idEdit'] ?? 0);
$genre                = $_POST['genre'] ?? '';
$pub                  = trim($_POST['pub'] ?? '');
$disponible           = (int)($_POST['disponible'] ?? 0);
$exemplaire           = (int)($_POST['exemplaire'] ?? 0);

if ($idLiv <= 0) {
    header('Location: Gestion_Livre.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE Livre SET
        idAut              = ?,
        idEdit             = ?,
        titre              = ?,
        genre              = ?,
        datepub            =  ?,
        disponible         = ?,
        nbExemplaire       = ?
        WHERE idLiv        = ?
    ");
    $stmt->execute([
        $idAut, $idEdit, $titre,
        $genre, $pub, $disponible, $exemplaire,
        $idLiv
    ]);
    header('Location: Gestion_Livre.php?action=edit&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Livre.php?action=edit&status=error');
}
exit;