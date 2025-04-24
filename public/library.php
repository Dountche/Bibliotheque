<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if (empty($_SESSION['user'])) {
  header('Location: index.php?page=default');
  exit;
}
require_once __DIR__ . '/../config/database.php';

// 1) Récupère tous les documents
$stmt = $pdo->prepare("SELECT * FROM Document ORDER BY dateUpload DESC");
$stmt->execute();
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Feedback (upload/edit/delete)
$action = $_GET['action'] ?? '';
$status = $_GET['status'] ?? '';
$error  = $_GET['error']  ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Librairie numérique</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- SweetAlert2 CSS -->
  <link rel="icon" href="./images/icon.png">
  <link rel="apple-touch-icon" href="./images/icon.png">
  <link rel="stylesheet" href="css/sweetalert2.min.css">
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Perso CSS -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <!-- Navbar simplifiée -->
  <header>
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="https://inphb.ci/">
          <img src="./images/icon.png" alt="Logo INP-HB" class="d-inline-block align-text-top" style="max-height: 50px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" 
                aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="Admin_home.php"><i class="fas fa-home"></i> Accueil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Etudiant.php"><i class="fa-solid fa-user-graduate"></i> Étudiants</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Auteur.php"><i class="fa-solid fa-user"></i> Auteurs</a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="Gestion_Editeur.php"><i class="fa-solid fa-users"></i> Éditeurs</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Livre.php"><i class="fa-solid fa-book"></i> Livres</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Emprunt.php"><i class="fa-solid fa-ticket"></i> Emprunts</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="library.php" style="color : #fff ! important" ><i class="fa-solid fa-book-atlas"></i> Librairie</a>
            </li>
          </ul>
          <!-- Profil de l'utilisateur connecté -->
          <div class="d-flex align-items-center">
            <a href="#" data-bs-toggle="modal" data-bs-target="#accountModal" class="d-flex align-items-center text-decoration-none text-dark">
              <img
                src="<?= !empty($_SESSION['user']['photo'])
                ? 'images/'.htmlspecialchars($_SESSION['user']['photo'])
                : 'images/user.png' ?>"
                class="rounded-circle me-2"
                style="width:40px; height:40px;"
                alt="Photo profil"
                >
              <span class="fw-bold"><?= htmlspecialchars($_SESSION['user']['usermail']) ?></span>
            </a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <main class="container my-5">
    <h2 class="mb-4 text-center">Librairie numérique</h2>
    <!-- Feedback SweetAlert2 (inchangé) -->
    <script src="js/sweetalert2.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const a = '<?= $action ?>', s = '<?= $status ?>', e = '<?= $error ?>';
        if (e) {
          Swal.fire('Erreur', e.replace(/_/g,' '), 'error');
          history.replaceState(null,'','<?= basename(__FILE__) ?>');
        } else if (a && s) {
          Swal.fire(
            s==='success' ? 'Succès' : 'Échec',
            `${a.charAt(0).toUpperCase()+a.slice(1)} ${s}!`,
            s==='success' ? 'success' : 'error'
          );
          history.replaceState(null,'','<?= basename(__FILE__) ?>');
        }
      });
    </script>

    <!-- Recherche / Filtre (inchangé) -->
<!-- RECHERCHE / FILTRE -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par titre">
      </div>
      <div class="col-md-4">
        <select id="filterCat" class="form-select">
          <option value="">Filtrer par catégorie</option>
          <option value="Cours">Cours</option>
          <option value="Rapport">Rapport</option>
          <option value="Exercice">Exercice</option>
          <option value="Corrigé">Corrigé</option>
          <option value="Guide">Guide</option>
          <option value="Kpla">Kpla</option>
          <option value="Roman">Roman</option>
          <option value="Theâtre">Theâtre</option>
          <option value="Poésie">Poésie</option>
          <option value="Tutoriel">Tutoriel</option>
          <option value="Codes">codes (.py, .php ...)</option>
          <option value="other">Autre</option placeholder="preciser">
        </select>
        <div class="col-md-4" id="otherCatContainer" style="display:none;">
            <input type="text" id="otherCat" class="form-control" placeholder="Préciser la catégorie">
        </div>
      </div>
      <?php if ($_SESSION['user']['roles']==='Admin'): ?>
      <div class="col-md-4 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
          <i class="fa-solid fa-upload"></i> Ajouter un document
        </button>
      </div>
      <?php endif; ?>
    </div>

    <div class="total d-flex justify-content-between align-items-center mb-2">
    <h5><strong>Total Documents : </strong> <span id="totalCount"><?= count($docs) ?></span></h5>
    </div>

    <!-- GRILLE DOCUMENTS -->
    <div class="row row-cols-1 row-cols-md-3 g-4" id="docsGrid">
      <?php foreach($docs as $d): ?>
      <div class="col doc-card" data-categorie="<?= htmlspecialchars($d['categorie']) ?>"
        data-titre="<?= htmlspecialchars(strtolower($d['titre'])) ?>">
      <div class="card h-100 shadow-sm">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= htmlspecialchars($d['titre']) ?></h5>
          <?php if($d['description']): ?>
            <p class="card-text"><?= nl2br(htmlspecialchars($d['description'])) ?></p>
          <?php endif; ?>
          <small class="text-muted mb-3">
            Publié le <?= date('d/m/Y', strtotime($d['dateUpload'])) ?>
          </small>
          <div class="mt-auto">
            <?php
              $fileUrl = "documents/" . urlencode($d['fichier']);
              $fullUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $fileUrl;
              $extension = strtolower(pathinfo($d['fichier'], PATHINFO_EXTENSION));
            ?>

            <?php if($extension === 'pdf'): ?>
            <!-- Affichage PDF avec <embed> -->
              <div class="mb-2" style="height: 200px; overflow: hidden;">
                <iframe src="documents/<?= urlencode($d['fichier']) ?>" width="100%" height="100%" style="border: none; overflow: hidden;"></iframe>
              </div>
              <a href="view_document.php?file=<?= urlencode($d['fichier']) ?>"
                target="_blank"
                class="btn btn-outline-primary btn-sm w-100 mb-2">
                Lire en ligne
              </a>
            <?php elseif(in_array($extension, ['doc', 'docx', 'ppt', 'pptx'])): ?>
            <!-- Affichage via Google Docs Viewer -->
              <button
                class="btn btn-outline-primary btn-sm w-100 mb-2"
                onclick="openViewer('<?= rawurlencode($d['fichier']) ?>')">
                  Lire en ligne
              </button>
            <?php else: ?>
            <!-- Autres formats : téléchargement uniquement -->
              <a href="<?= $fileUrl ?>"
                target="_blank"
                class="btn btn-outline-secondary btn-sm w-100 mb-2">
                Voir le fichier
              </a>
            <?php endif; ?>

            <?php if($d['telechargable']): ?>
              <a href="<?= $fileUrl ?>"
                download
                class="btn btn-success btn-sm w-100">
                Télécharger
              </a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>
                Téléchargement interdit
              </button>
            <?php endif; ?>
          </div>
        </div>
        <?php if($_SESSION['user']['roles']==='Admin'): ?>
        <div class="card-footer text-end">
          <a href="edit_document.php?idDoc=<?= $d['idDoc'] ?>"
            class="btn btn-sm btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
          <a href="del_document.php?idDoc=<?= $d['idDoc'] ?>"
            class="btn btn-sm btn-danger"
            onclick="return confirm('Confirmer la suppression ?')">
            <i class="fa-solid fa-trash"></i>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <nav><ul id="pagination-container" class="pagination justify-content-center mt-4"></ul></nav>
  </main>

    <!-- Account Modal -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="accountModalLabel">Mon compte</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Onglets -->
        <ul class="nav" id="acctTab" role="tablist" style="font-size: 15px !important;">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">Profil</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="photo-tab" data-bs-toggle="tab" data-bs-target="#photo" type="button">Photo</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">Mot de passe</button>
          </li>
          <li class="nav-item ms-auto" role="presentation">
            <button class="nav-link text-danger" id="delete-tab" data-bs-toggle="tab" data-bs-target="#delete" type="button">Supprimer</button>
          </li>
        </ul>

        <div class="tab-content mt-3" id="acctTabContent">
          <!-- === Profil === -->
          <div class="tab-pane fade show active" id="profile" style="color: #000 !important">
            <form id="profileForm" class="ajax-form"  action="update_profile.php" method="POST">
              <div class="mb-3">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['nom']) ?>" required>
              </div>
              <div class="mb-3">
                <label>Prénom</label>
                <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['prenom']) ?>" required>
              </div>
              <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['usermail']) ?>" required>
              </div>
              <button type="submit" class="btn">Enregistrer</button>
            </form>
          </div>
          <!-- === Photo de profil === -->
          <div class="tab-pane fade" id="photo" style="color: #000 !important">
            <form id="photoForm" class="ajax-form" action="update_photo.php" method="POST" enctype="multipart/form-data">
              <div class="mb-3 text-center">
                <img src="<?= !empty($_SESSION['user']['photo'])
                            ? 'images/'.htmlspecialchars($_SESSION['user']['photo'])
                            : 'images/user.png' ?>"
                    class="rounded-circle mb-3"
                    style="width:120px;height:120px;"
                    alt="Photo actuelle">
              </div>
              <div class="mb-3">
                <label style="width: 100%;">Nouvelle photo</label>
                <input type="file" name="photo" accept="image/*" class="form-control" required>
              </div>
              <button type="submit" class="btn">Mettre à jour</button>
            </form>
          </div>

          <!-- === Changer mot de passe === -->
          <div class="tab-pane fade" id="password"style="color: #000 !important">
            <form id="ajaxForm" class="ajax-form" action="change_password.php" method="POST">
              <div class="mb-3">
                <label>Mot de passe actuel</label>
                <input type="password" name="old_password" class="form-control" required>
              </div>
              <div class="mb-3" style="gap: 5px;">
                <label style="width: 100%;">Nouveau mot de passe</label>
                <input type="password" name="new_password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label>Confirmation</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary">Modifier</button>
            </form>
          </div>

          <!-- === Supprimer mon compte === -->
          <div class="tab-pane fade text-center" id="delete" style="color: #000 !important">
            <p class="text-danger">Attention : cette action est irréversible.</p>
            <form id="deleteForm" class="ajax-form"  action="delete_account.php" method="POST">
              <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-between">
        <a href="logout.php" class="btn">Se déconnecter</a>
        <button type="button" class="btn" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

    <!-- Footer -->
  <!-- Modal d’upload (inchangé) -->
  <?php if($_SESSION['user']['roles']==='Admin'): ?>
  <div class="modal fade" id="uploadModal" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" action="upload_document.php"
            enctype="multipart/form-data" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalLabel">Ajouter un document</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="margin-top: 100px;">
          <div class="mb-3">
            <label class="form-label">Titre du document</label>
            <input type="text" name="titre" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Fichier</label>
            <input type="file" name="fichier" class="form-control"
                  accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.py,.php,.cpp,.html,.css,.js,.sql" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Catégorie</label>
            <select id="categorie" name="categorie" class="form-select">
              <option value="">Filtrer par catégorie</option>
              <option value="Cours">Cours</option>
              <option value="Rapport">Rapport</option>
              <option value="Exercice">Exercice</option>
              <option value="Corrigé">Corrigé</option>
              <option value="Guide">Guide</option>
              <option value="Kpla">Kpla</option>
              <option value="Roman">Roman</option>
              <option value="Theâtre">Theâtre</option>
              <option value="Poésie">Poésie</option>
              <option value="Tutoriel">Tutoriel</option>
              <option value="Codes">codes (.py, .php ...)</option>
              <option value="other">Autre</option placeholder="preciser">
        </select>
            <div class="col-md-4" id="otherCatContainer" style="display:none;">
              <input type="text" id="otherCat" class="form-control" placeholder="Préciser la catégorie">
            </div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="telechargable" id="telech">
            <label class="form-check-label" for="telech" style="width: 100%;">
              Autoriser le téléchargement
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary">Uploader</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- SCRIPTS JS -->
  <script src="js/jquery-3.7.1.js"></script>
  <script src="js/jquery.simplePagination.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/ajax_account.js"></script>
  <script src="js/sweetalert2.min.js"></script>

  <script src="js/librairy.js"></script>
</body>
</html>
