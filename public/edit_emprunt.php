<?php
session_start();
require_once __DIR__.'/../config/database.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') {
    header('Location: Gestion_Emprunt.php');
    exit;
}

$idEmp   = (int)($_POST['idEmp'] ?? 0);
if ($idEmp <= 0) {
    header('Location: Gestion_Emprunt.php?error=invalid_id');
    exit;
}
// Nouvelles valeurs saisies
$newMat  = trim($_POST['matricule'] ?? '');
$newIdLiv= (int)($_POST['idLiv'] ?? 0);
$newDateEmp   = trim($_POST['datemp']    ?? '');
$newDateRetour= trim($_POST['dateret']   ?? '');
$newDateRendu = trim($_POST['dateren']   ?? '');

// Valider qu’on retrouve bien l’étudiant
$stmt = $pdo->prepare("SELECT idEtu FROM Etudiant WHERE matEtu = ?");
$stmt->execute([$newMat]);
$newIdEtu = (int)$stmt->fetchColumn();
if ($newIdEtu <= 0) {
    header('Location: Gestion_Emprunt.php?error=etudiant_unknown');
    exit;
}

$empDate  = DateTime::createFromFormat('Y-m-d', $newDateEmp);
$renDate  = DateTime::createFromFormat('Y-m-d', $newDateRendu);
$jourj = new DateTime('today');

if ($newDateRendu !== '') {
    if ($renDate <= $empDate) {
        header('Location: Gestion_Emprunt.php?error=dateren_inferieur');
        exit();
    } elseif ($renDate > $jourj) {
        header('Location: Gestion_Emprunt.php?error=dateren_supjj');
        exit();
    }
}


$stmt = $pdo->prepare("SELECT idLiv AS oldIdLiv, dateRendu AS oldDateRendu FROM Emprunt WHERE idEmp = ?");
$stmt->execute([$idEmp]);
$old = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$old) {
    header('Location: Gestion_Emprunt.php?error=not_found');
    exit;
}

$oldIdLiv     = (int)$old['oldIdLiv'];
$oldDateRendu = $old['oldDateRendu'];

// Démarrer la transaction
try {
    $pdo->beginTransaction();

    // Mettre à jour la ligne Emprunt
    $stmt = $pdo->prepare("
      UPDATE Emprunt SET
        idEtu      = ?,
        idLiv      = ?,
        dateEmp    = ?,
        dateRetour = ?,
        dateRendu  = ?
      WHERE idEmp  = ?
    ");
    $stmt->execute([
      $newIdEtu,
      $newIdLiv,
      $newDateEmp,
      $newDateRetour,
      $newDateRendu ?: null,
      $idEmp
    ]);

    if ($oldDateRendu === null) {
        // si livre changé OU date de rendu ajoutée
        if ($oldIdLiv !== $newIdLiv || ($oldIdLiv === $newIdLiv && $newDateRendu !== '')) {
            $pdo->prepare("UPDATE Livre SET disponible = disponible + 1 WHERE idLiv = ?")
                ->execute([$oldIdLiv]);
        }
    }

    if ($newDateRendu === '' && $newIdLiv !== $oldIdLiv) {
        $pdo->prepare("UPDATE Livre SET disponible = disponible - 1 WHERE idLiv = ? AND disponible > 0")
            ->execute([$newIdLiv]);
    }

    $pdo->commit();
    header('Location: Gestion_Emprunt.php?action=edit&status=success');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: Gestion_Emprunt.php?action=edit&status=error');
    exit;
}
