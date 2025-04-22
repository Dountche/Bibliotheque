<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
if ($_SESSION['user']['roles'] !== 'Admin') {
    header('Location: library.php?error=accès_interdit');
    exit;
}

$idDoc = (int)($_GET['idDoc'] ?? 0);
if (!$idDoc) { header('Location: library.php?error=id_invalide'); exit; }

// Récupérer l’existant
$stmt = $pdo->prepare("SELECT * FROM Document WHERE idDoc = ?");
$stmt->execute([$idDoc]);
$d = $stmt->fetch();
if (!$d) { header('Location: library.php?error=doc_introuvable'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Mise à jour
    $titre       = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $categorie   = $_POST['categorie'];
    $telech      = isset($_POST['telechargable']) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE Document SET
            titre=?, description=?, categorie=?, telechargable=?
        WHERE idDoc=?
    ");
    $stmt->execute([$titre,$description,$categorie,$telech,$idDoc]);
    header('Location: library.php?action=edit&status=success');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <h3>Modifier “<?= htmlspecialchars($d['titre']) ?>”</h3>
    <form method="POST" action="" class="mt-3">
        <div class="mb-3">
        <label class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" required
                value="<?= htmlspecialchars($d['titre']) ?>">
        </div>
        <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($d['description']) ?></textarea>
        </div>
        <div class="mb-3">
        <label class="form-label">Catégorie</label>
            <select name="categorie" class="form-select">
                <?php foreach(['rapport','roman','guide'] as $c): ?>
                <option value="<?= $c ?>" <?= $d['categorie']===$c?'selected':'' ?>><?= ucfirst($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="telechargable" id="telech"
            <?= $d['telechargable']?'checked':'' ?>>
            <label class="form-check-label" for="telech">Autoriser téléchargement</label>
        </div>
        <button class="btn btn-primary">Enregistrer</button>
        <a href="library.php" class="btn btn-secondary">Annuler</a>
    </form>
</body>
</html>
