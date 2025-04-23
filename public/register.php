<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Inclure le fichier de connexion à la base de données
require_once __DIR__ . '/../config/database.php';

$error = $_GET['error'] ?? '';
$success = isset($_GET['success']);

// Vérifier que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer le rôle et les données du formulaire
    $role             = $_POST['role'] ?? 'etudiant';
    $nom              = trim($_POST['nom'] ?? '');
    $prenoms          = trim($_POST['prenoms'] ?? '');
    $matricule        = trim($_POST['matricule'] ?? '');
    $sexe             = trim($_POST['sexe'] ?? '');
    $dateNaiss        = trim($_POST['date'] ?? ''); // Date de naissance
    $tel              = trim($_POST['tel'] ?? '');

    // Champs importants pour la table User

    $email            = trim($_POST['regemail'] ?? '');
    $password         = $_POST['regPassword'] ?? '';
    $confirm_password = $_POST['regConfirmPassword'] ?? '';

    // Pour les étudiants, champs additionnels
    $ecole            = trim($_POST['ecole'] ?? '');
    $filiere          = trim($_POST['filiere'] ?? '');
    $specialite       = trim($_POST['specialite'] ?? '');
    $niveau           = trim($_POST['niveau'] ?? '');

    // Vérifier que les mots de passe correspondent
    if ($password !== $confirm_password) {
        header('Location: login.php?error=password_mismatch');
        exit();
    }

    // Vérifier l'existence de l'email dans la table User
    $stmt = $pdo->prepare("SELECT * FROM User WHERE usermail = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header('Location: login.php?error=email_exists');
        exit();
    }
    
    // Hachage du mot de passe en utilisant BCRYPT
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Insertion dans la table User
        $stmt = $pdo->prepare("INSERT INTO User (usermail, nom, prenom, passwd, roles) VALUES (?, ?, ?)");
        $stmt->execute([$email, $nom, $prenoms, $hashed_password, $role]);

        // Récupérer l'ID inséré pour l'utilisateur
        $idUser = $pdo->lastInsertId();

        // Insertion dans la table spécifique en fonction du rôle
        if ($role === 'admin') {
            $stmt = $pdo->prepare("INSERT INTO Administrateur (matAdmin, prenoms, nom, born, sexe, mail, tel, idUser) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$matricule, $prenoms, $nom, $dateNaiss, $sexe, $email, $tel, $idUser]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO Etudiant (matEtu, prenoms, nom, born, ecole, filiere, specialite, niveau, sexe, mail, tel, idUser) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$matricule, $prenoms, $nom, $dateNaiss, $ecole, $filiere, $specialite, $niveau, $sexe, $email, $tel, $idUser]);
        }
        
        //retourne au login
        header('Location: login.php?success=registered');
        exit();
    } catch (PDOException $e) {
        // En cas d'erreur, affichez un message détaillé (peut être journalisé pour la production)
        die("Erreur durant l'inscription : " . $e->getMessage());
    }

} else {
    // Si la méthode n'est pas POST, rediriger vers la page de connexion
    header('Location: login.php');
    exit();
}
?>
