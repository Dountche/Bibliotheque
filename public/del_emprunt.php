<?php

session_start();
require_once __DIR__ . '/../config/database.php';

$idEmp = isset($_GET['idEmp']) ? (int)$_GET['idEmp'] : 0;
if ($idEmp <= 0) {
    header('Location: Gestion_emprunt.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM Emprunt WHERE idEmp = ?");
    $stmt->execute([$idEmp]);
    header('Location: Gestion_emprunt.php?action=delete&status=success');
} catch (Exception $e) {
    header('Location: Gestion_emprunt.php?action=delete&status=error');
}
exit;