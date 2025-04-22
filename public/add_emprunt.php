<?php

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Emprunt.php');
    exit;
}

// Récupération et nettoyage des données
$matricule         = trim($_POST['matricule'] ?? '');
$idLiv             = (int)($_POST['idLiv'] ?? 0);
$datemp            = trim($_POST['datemp'] ?? '');
$dateret            = trim($_POST['dateret'] ?? '');

$stmt = $pdo->prepare("SELECT idEtu FROM Etudiant WHERE matEtu = ?");
$stmt->execute([$matricule]);
$result = $stmt->fetchColumn();
$idEtu = $result !== false ? (int) $result : 0;

if ($idEtu<=0) {
    header('Location: Gestion_Emprunt.php?error=etudiant_unknown');
    exit();

}

$stmt = $pdo->prepare("SELECT * FROM Emprunt WHERE idLiv = ? AND idEtu = ?");
$stmt->execute([$idLiv, $idEtu]);
if ($stmt->rowCount() > 0) {
    header('Location: Gestion_Emprunt.php?error=emprunt_exists');
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO Emprunt (idEtu, idLiv, dateEmp, dateRetour) VALUES (?, ?, ?, ?)");

    $stmt->execute([$idEtu, $idLiv, $datemp, $dateret]);

    header('Location: Gestion_Emprunt.php?action=add&status=success');
    exit();
} catch (Exception $e) {
    header('Location: Gestion_Emprunt.php?action=add&status=error');
    exit;
}
?>