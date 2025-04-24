<?php
// action_edit_student.php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Gestion_Etudiant.php');
    exit;
}

// Récupération des données
$idEtu      = (int)($_POST['idEtu'] ?? 0);
$matEtu     = trim($_POST['matEtu'] ?? '');
$prenoms    = trim($_POST['prenoms'] ?? '');
$nom        = trim($_POST['nom'] ?? '');
$born       = $_POST['born'] ?? '';
$ecole      = trim($_POST['ecole'] ?? '');
$filiere    = trim($_POST['filiere'] ?? '');
$specialite = trim($_POST['specialite'] ?? '');
$niveau     = trim($_POST['niveau'] ?? '');
$sexe       = $_POST['sexe'] ?? '';
$mail       = trim($_POST['mail'] ?? '');
$tel        = trim($_POST['tel'] ?? '');

if ($idEtu <= 0) {
    header('Location: Gestion_Etudiant.php?error=invalid_id');
    exit;
}

$refmin = DateTime::createFromFormat('Y-m-d', '1960-01-01');
$refmax = DateTime::createFromFormat('Y-m-d', '2008-01-01');
$age  = DateTime::createFromFormat('Y-m-d', $born);

if ($age < $refmin || $age > $refmax) {
    header('Location: Gestion_Etudiant.php?error=age_invalid');
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE Etudiant SET
          matEtu     = ?,
          prenoms    = ?,
          nom        = ?,
          born       = ?,
          ecole      = ?,
          filiere    = ?,
          specialite = ?,
          niveau     = ?,
          sexe       = ?,
          mail       = ?,
          tel        = ?
        WHERE idEtu = ?
    ");
    $stmt->execute([
        $matEtu, $prenoms, $nom, $born,
        $ecole, $filiere, $specialite, $niveau,
        $sexe, $mail, $tel,
        $idEtu
    ]);
    header('Location: Gestion_Etudiant.php?action=edit&status=success');
} catch (Exception $e) {
    header('Location: Gestion_Etudiant.php?action=edit&status=error');
}
exit;