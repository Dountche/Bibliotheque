<?php
// action_add_student.php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Etudiant.php');
    exit;
}

// Récupération et nettoyage des données
$nom              = trim($_POST['nom'] ?? '');
$prenoms          = trim($_POST['prenoms'] ?? '');
$matricule        = trim($_POST['matricule'] ?? '');
$sexe             = trim($_POST['sexe'] ?? '');
$dateNaiss        = trim($_POST['date'] ?? '');
$tel              = trim($_POST['tel'] ?? '');
$email            = trim($_POST['regemail'] ?? '');
$password         = $_POST['regPassword'] ?? '';
$confirm_password = $_POST['regConfirmPassword'] ?? '';
$ecole            = trim($_POST['ecole'] ?? '');
$filiere          = trim($_POST['filiere'] ?? '');
$specialite       = trim($_POST['specialite'] ?? '');
$niveau           = trim($_POST['niveau'] ?? '');
$role       = 'Etudiant';

    // Vérifier que les mots de passe correspondent
if ($password !== $confirm_password) {
    header('Location: Gestion_Etudiant.php?error=password_mismatch');
    exit();
}

// Vérifier l'existence de l'email dans la table User
$stmt = $pdo->prepare("SELECT * FROM Etudiant WHERE mail = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    header('Location: Gestion_Etudiant.php?error=email_exists');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Etudiant WHERE matEtu = ?");
$stmt->execute([$matricule]);
if ($stmt->rowCount() > 0) {
    header('Location: Gestion_Etudiant.php?error=matricule_exists');
    exit();
}

$refmin = DateTime::createFromFormat('Y-m-d', '1960-01-01');
$refmax = DateTime::createFromFormat('Y-m-d', '2008-01-01');
$age  = DateTime::createFromFormat('Y-m-d', $dateNaiss);

if ($age < $refmin || $age > $refmax) {
    header('Location: Gestion_Etudiant.php?error=age_invalid');
    exit();
}


// Hachage du mot de passe en utilisant BCRYPT
$hashed_password = password_hash($password, PASSWORD_BCRYPT);


try {
    // Insertion dans la table User
    $stmt = $pdo->prepare("INSERT INTO User (usermail, nom, prenom,  passwd, roles) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$email, $nom, $prenoms, $hashed_password, $role]);

    $idUser = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO Etudiant (matEtu, prenoms, nom, born, ecole, filiere, specialite, niveau, sexe, mail, tel, idUser) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([$matricule, $prenoms, $nom, $dateNaiss, $ecole, $filiere, $specialite, $niveau, $sexe, $email, $tel, $idUser]);

    header('Location: Gestion_Etudiant.php?action=add&status=success');
    exit();
} catch (Exception $e) {
    header('Location: Gestion_Etudiant.php?action=add&status=error');
    exit;
}
?>