<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

$idUser      = $_SESSION['user']['idUser'];
$nom         = trim($_POST['nom']);
$prenom      = trim($_POST['prenom']);
$email       = trim($_POST['email']);

if ($nom==='' || $prenom==='' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status'=>'error','message'=>'Champs invalides']);
    exit;
}

// Unicité email
$stmt = $pdo->prepare("SELECT COUNT(*) FROM User WHERE usermail = ? AND idUser <> ?");
$stmt->execute([$email, $idUser]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['status'=>'error','message'=>'Email déjà utilisé']);
    exit;
}

// Mise à jour
$stmt = $pdo->prepare("
UPDATE User SET
    usermail = ?,
    nom =?,
    prenom =?
    WHERE idUser = ?"
);
$stmt->execute([$email, $nom, $prenom, $idUser,]);

if ($_SESSION['user']['roles']==="Admin"){
    $stmt= $pdo->prepare("
    UPDATE Administrateur SET
        mail = ?,
        nom =?,
        prenoms =?
    WHERE idUser = ?
    ");
    $stmt->execute([$email, $nom, $prenom, $idUser]);
} else{
    $stmt= $pdo->prepare("
    UPDATE Etudiant SET
        mail = ?,
        nom =?,
        prenoms =?
    WHERE idUser = ?
    ");
    $stmt->execute([$email, $nom, $prenom, $idUser]);
}


// Mets à jour la session
$_SESSION['user']['nom']       = $nom;
$_SESSION['user']['prenom']    = $prenom;
$_SESSION['user']['usermail']  = $email;

echo json_encode(['status'=>'success','message'=>'Profil mis à jour']);
exit;