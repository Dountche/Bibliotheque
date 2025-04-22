<?php
// action_delete_student.php
session_start();
require_once __DIR__ . '/../config/database.php';

$idEtu = isset($_GET['idEtu']) ? (int)$_GET['idEtu'] : 0;
if ($idEtu <= 0) {
    header('Location: Gestion_Etudiant.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM Etudiant WHERE idEtu = ?");
    $stmt->execute([$idEtu]);
    header('Location: Gestion_Etudiant.php?action=delete&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Etudiant.php?action=delete&status=error');
}
exit;