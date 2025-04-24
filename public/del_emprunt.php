<?php
session_start();
require_once __DIR__.'/../config/database.php';

$idEmp = isset($_GET['idEmp']) ? (int)$_GET['idEmp'] : 0;
if ($idEmp <= 0) {
    header('Location: Gestion_Emprunt.php?error=invalid_id');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT idLiv, dateRendu
        FROM Emprunt
        WHERE idEmp = ?
    ");
    $stmt->execute([$idEmp]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$old) {
        header('Location: Gestion_Emprunt.php?error=not_found');
        exit;
        }

    if ($old['dateRendu'] === null) {
        $stmt = $pdo->prepare("
            UPDATE Livre
            SET disponible = disponible + 1
            WHERE idLiv = ?
        ");
        $stmt->execute([$old['idLiv']]);
    }

    $stmt = $pdo->prepare("DELETE FROM Emprunt WHERE idEmp = ?");
    $stmt->execute([$idEmp]);

    $pdo->commit();
    header('Location: Gestion_Emprunt.php?action=delete&status=success');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: Gestion_Emprunt.php?action=delete&status=error');
    exit;
}
