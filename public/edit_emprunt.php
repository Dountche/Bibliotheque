<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Emprunt.php');
    exit;
}

// Récupération des données
$idEmp             = (int)($_POST['idEmp'] ?? 0);
$matricule         = trim($_POST['matricule'] ?? '');
$idLiv             = (int)($_POST['idLiv'] ?? 0);
$datemp            = trim($_POST['datemp'] ?? '');
$dateret           = trim($_POST['dateret'] ?? '');
$dateren           = trim($_POST['dateren'] ?? '');

$stmt = $pdo->prepare("SELECT idEtu FROM Etudiant WHERE matEtu = ?");
$stmt->execute([$matricule]);
$result = $stmt->fetchColumn();
$idEtu = $result !== false ? (int) $result : 0;

if ($idEtu<=0) {
    header('Location: Gestion_Emprunt.php?error=etudiant_unknown');
    exit();

}

if ($idEmp<=0) {
    header('Location: Gestion_Emprunt.php?error=invalid_id');
    exit();

}

try {
    $stmt = $pdo->prepare("
        UPDATE Emprunt SET
        idEtu              = ?,
        idLiv              = ?,
        dateEmp            =  ?,
        dateRetour         =  ?,
        dateRendu          =  ?
        WHERE idEmp        = ?
    ");
    $stmt->execute([
        $idEtu, $idLiv, $datemp,
        $dateret, $dateren,
        $idEmp
    ]);
    header('Location: Gestion_Emprunt.php?action=edit&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Emprunt.php?action=edit&status=error');
}
exit;