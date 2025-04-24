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
$idEtu = (int)$stmt->fetchColumn();

if ($idEtu<=0) {
    header('Location: Gestion_Emprunt.php?error=etudiant_unknown');
    exit();

}

if ($idLiv<=0) {
    header('Location: Gestion_Emprunt.php?error=livre_unknown');
    exit();

} else {
    $stmt = $pdo->prepare("SELECT disponible FROM Livre WHERE idLiv = ?");
    $stmt->execute([$idLiv]);
    $disponible = (int)$stmt->fetchColumn();
    if ($disponible <= 0) {
        header('Location: Gestion_Emprunt.php?error=livre_indisponible');
        exit();
    }
}

$empDate  = DateTime::createFromFormat('Y-m-d', $datemp);
$retDate  = DateTime::createFromFormat('Y-m-d', $dateret);

if ($retDate <= $empDate) {
    header('Location: Gestion_Emprunt.php?error=date_inferieur');
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM Emprunt WHERE idLiv = ? AND idEtu = ?");
$stmt->execute([$idLiv, $idEtu]);
if ($stmt->rowCount() > 0) {
    header('Location: Gestion_Emprunt.php?error=emprunt_exists');
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO Emprunt (idEtu, idLiv, dateEmp, dateRetour)
        VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$idEtu, $idLiv, $datemp, $dateret]);

    $stmt = $pdo->prepare(
        "UPDATE Livre
            SET disponible = disponible - 1
        WHERE idLiv = ?
        AND disponible > 0"
    );
    $stmt->execute([$idLiv]);
    $pdo->commit();

    header('Location: Gestion_Emprunt.php?action=add&status=success');
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: Gestion_Emprunt.php?action=add&status=error');
    exit;
}
?>