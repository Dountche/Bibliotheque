<?php

session_start();
require_once __DIR__ . '/../config/database.php';

$idEdit = isset($_GET['idEdit']) ? (int)$_GET['idEdit'] : 0;
if ($idEdit <= 0) {
    header('Location: Gestion_Editeur.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM Editeur WHERE idEdit = ?");
    $stmt->execute([$idEdit]);
    header('Location: Gestion_Editeur.php?action=delete&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Editeur.php?action=delete&status=error');
}
exit;