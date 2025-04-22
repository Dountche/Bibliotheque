<?php
session_start();
require_once __DIR__ . '/../config/database.php';
// Vérifier rôle
if ($_SESSION['user']['roles'] !== 'Admin') {
  header('Location: library.php?error=accès_interdit');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['fichier'])) {
  header('Location: library.php?error=req_invalide');
  exit;
}

$titre       = trim($_POST['titre']);
$description = trim($_POST['description']);
$categorie   = $_POST['categorie'];
$telech      = isset($_POST['telechargable']) ? 1 : 0;

// Traitement du fichier
$u = $_FILES['fichier'];
$ext = pathinfo($u['name'], PATHINFO_EXTENSION);
$allowed = ['pdf','doc','docx','ppt','pptx','txt', 'txt', 'py', 'php', 'cpp', 'c', 'html', 'css', 'js', 'sq'];
if (!in_array(strtolower($ext), $allowed)) {
  header('Location: library.php?error=ext_invalide');
  exit;
}
$destDir = __DIR__.'/documents/';
if (!is_dir($destDir)) mkdir($destDir,0755,true);
$filename = uniqid().'_'.preg_replace('/[^a-z0-9_.-]/i','_',basename($u['name']));
move_uploaded_file($u['tmp_name'], $destDir.$filename);

// Insertion en BDD
$stmt = $pdo->prepare("
  INSERT INTO Document
    (titre,description, fichier, telechargable, typeMime, categorie)
  VALUES(?,?,?,?,?,?)
");
$stmt->execute([
  $titre, $description, $filename, $telech,
  $u['type'], $categorie
]);

header('Location: library.php?action=add&status=success');
exit;
