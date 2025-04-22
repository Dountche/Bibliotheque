<?php

session_start();
require_once __DIR__ . '/../config/database.php';

$idLiv = isset($_GET['idLiv']) ? (int)$_GET['idLiv'] : 0;
if ($idLiv <= 0) {
    header('Location: Gestion_Livre.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM Livre WHERE idLiv = ?");
    $stmt->execute([$idLiv]);
    header('Location: Gestion_Livre.php?action=delete&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Livre.php?action=delete&status=error');
}
exit;