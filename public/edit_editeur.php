<?php
// action_edit_student.php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Editeur.php');
    exit;
}

// Récupération des données
$idEdit         = (int)($_POST['idEdit'] ?? 0);
$nom            = trim($_POST['nom'] ?? '');
$pays           = trim($_POST['pays'] ?? '');
$siteweb        = trim(string: $_POST['siteweb'] ?? '');

if ($idEdit <= 0) {
    header('Location: Gestion_Editeur.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE Editeur SET
            nom         = ?,
            pays        = ?,
            siteweb     = ?
        WHERE idEdit  = ?
    ");
    $stmt->execute([
        $nom, $pays, $siteweb,
        $idEdit
    ]);
    header('Location: Gestion_Editeur.php?action=edit&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Editeur.php?action=edit&status=error');
}
exit;