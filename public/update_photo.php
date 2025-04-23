<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$idUser = $_SESSION['user']['idUser'];

if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status'=>'error','message'=>'Une erreur est survenue lors du téléchargement du fichier']);
    exit;
}

// Validation du type MIME
$allowed = ['image/jpeg','image/png','image/gif'];
if (!in_array($_FILES['photo']['type'], $allowed)) {
    echo json_encode(['status'=>'error','message'=>'Le fichier doit être une image (JPEG, PNG ou GIF)']);
    exit;
}


$ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
$dest = uniqid('usr_'.$idUser.'_').'.'.$ext;
move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__.'/images/'.$dest);

// Mets à jour en base
$stmt = $pdo->prepare("UPDATE User SET photo = ? WHERE idUser = ?");
$stmt->execute([$dest, $idUser]);
$_SESSION['user']['photo'] = $dest;

echo json_encode(['status'=>'success','message'=>'Profil mise à jour avec succès']);
exit;