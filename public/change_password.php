<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$idUser = $_SESSION['user']['idUser'];
$old = $_POST['old_password'];
$new = $_POST['new_password'];
$conf= $_POST['confirm_password'];

// Vérification mot de passe actuel
$stmt = $pdo->prepare("SELECT password_hash FROM user WHERE id = ?");
$stmt->execute([$userId]);
$hash = $stmt->fetchColumn();
if (!password_verify($old, $hash)) {
    echo json_encode(['status'=>'error','message'=>'Mot de passe incorrect']);
    exit;
}
if ($new !== $conf) {
    echo json_encode(['status'=>'error','message'=>'Les mots de passe ne correspondent pas']);
    exit;
}

// Mise à jour
$newHash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE user SET passwd = ? WHERE idUser = ?");
$stmt->execute([$newHash, $idUser]);

echo json_encode(['status'=>'success','message'=>'Mot de passe modifié avec succès']);
exit;