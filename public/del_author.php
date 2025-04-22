<?php

session_start();
require_once __DIR__ . '/../config/database.php';

$idAut = isset($_GET['idAut']) ? (int)$_GET['idAut'] : 0;
if ($idAut <= 0) {
    header('Location: Gestion_Auteur.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM Auteur WHERE idAut = ?");
    $stmt->execute([$idAut]);
    header('Location: Gestion_Auteur.php?action=delete&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Auteur.php?action=delete&status=error');
}
exit;