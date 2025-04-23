<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
        <a class="navbar-brand" href="accueil.php">
          <img src="./images/icon.png" alt="Logo INP-HB" class="d-inline-block align-text-top" style="max-height: 50px;">
        </a>
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
  <div class="text-center py-4">
      <h1 class="display-4" style="color: #3d3d3d;">INP-HB Open Files</h1>
      <p class="lead" style="font-size: 8px;">Tous les fichiers numeriques de votre institut sont disponibles ici</p>
    </div>
    <h2 class="mb-4 text-center"></h2>
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
          <option value="rapport">Rapport</option>
          <option value="Exercie">Exercice</option>
          <option value="Corrigé">Corrigé</option>
          <option value="guide">Guide</option>
          <option value="Kpla">Guide</option>
          <option value="roman">Roman</option>
          <option value="Theâtre">Guide</option>
          <option value="poésie">Guide</option>
          <option value="Tutoriel">Tutoriel</option>
          <option value="Codes">codes (.py, .php ...)</option>
          <option >Autre</option placeholder="preciser">
        </select>
      </div>
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
      </div>
    </div>
    <?php endforeach; ?>
  </div>

    <!-- PAGINATION (inchangée) -->
    <nav><ul id="pagination-container" class="pagination justify-content-center mt-4"></ul></nav>
  </main>>
  
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
  <?php include '../src/views/footer.php'; ?>
  <!-- SCRIPTS JS -->
  <script src="js/jquery-3.7.1.js"></script>
  <script src="js/jquery.simplePagination.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/ajax_account.js"></script>
  <script src="js/sweetalert2.min.js"></script>
  <script>
    // Ouvre un embed en plein écran
    function viewDoc(btn) {
      const card = btn.closest('.doc-card');
      const file = card.dataset.file;
      // Crée une fenêtre modale simple
      const modal = document.createElement('div');
      modal.style = 'position:fixed;top:0;left:0;width:100%;height:100%;background:#000e;display:flex;justify-content:center;align-items:center;';
      modal.innerHTML = `
        <embed src="documents/${file}" type="application/pdf" width="80%" height="90%">
        <button style="position:absolute;top:20px;right:20px;font-size:2rem;color:white;background:none;border:none;cursor:pointer;">&times;</button>
      `;
      modal.querySelector('button').onclick = () => modal.remove();
      document.body.appendChild(modal);
    }

  function openViewer(fileName) {
    window.open(`view_document.php?file=${fileName}`, '_blank');
  }

  $(function(){
  // 1) Afficher/cacher le champ “Autre…”
  $('#filterCat').on('change', function(){
    if (this.value === 'other') {
      $('#otherCatContainer').show();
      $('#otherCat').attr('required', true);
    } else {
      $('#otherCatContainer').hide();
      $('#otherCat').removeAttr('required').val('');
    }
    refreshLibrary();
  });


  function refreshLibrary(){
    const search   = $('#searchInput').val().trim();
    const category = $('#filterCat').val();
    const other    = $('#otherCat').val().trim();

    $.ajax({
      url: 'ajax_document.php',
      method: 'GET',
      dataType: 'json',
      data: { search, category, other },
      success(resp){

        $('#totalCount').text(resp.total);


        let html = '';
        resp.docs.forEach(d => {
          const esc = s => $('<div>').text(s).html();
          const isPdf   = d.typeMime.includes('pdf');
          const isOffice = ['doc','docx','ppt','pptx']
            .some(ext => d.fichier.toLowerCase().endsWith(ext));


            const preview = isPdf
            ? `<div class="mb-2" style="height:200px;overflow:hidden">
                 <iframe src="documents/${encodeURIComponent(d.fichier)}"
                         width="100%" height="100%" style="border:none;"></iframe>
               </div>`
            : '';

          // Bouton Lire en ligne
          let lireBtn;
          if (isPdf) {
            lireBtn = `<button onclick="openViewer('${esc(d.fichier)}')"
                             class="btn btn-outline-primary btn-sm w-100 mb-2">
                         Lire en ligne
                       </button>`;
          } else if (isOffice) {
            const fullUrl = encodeURIComponent(location.origin + '/documents/' + d.fichier);
            lireBtn = `<button onclick="window.open(
                            'https://docs.google.com/viewer?url=${fullUrl}&embedded=true',
                            '_blank')"
                          class="btn btn-outline-primary btn-sm w-100 mb-2">
                          Lire en ligne
                        </button>`;
          } else {
            lireBtn = `<a href="documents/${esc(d.fichier)}" target="_blank"
                          class="btn btn-outline-secondary btn-sm w-100 mb-2">
                          Voir le fichier
                        </a>`;
          }

          // Bouton Télécharger
          const dlBtn = d.telechargable
            ? `<a href="documents/${esc(d.fichier)}" download
                  class="btn btn-success btn-sm w-100">Télécharger</a>`
            : `<button class="btn btn-secondary btn-sm w-100" disabled>
                 Téléchargement interdit
               </button>`;

          html += `
            <div class="col doc-card" data-categorie="${esc(d.categorie)}" data-titre="${esc(d.titre).toLowerCase()}">
              <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title">${esc(d.titre)}</h5>
                  ${preview}
                  ${d.description ? `<p class="card-text">${esc(d.description)}</p>` : ''}
                  <small class="text-muted mb-3">
                    Publié le ${new Date(d.dateUpload).toLocaleDateString('fr-FR')}
                  </small>
                  <div class="mt-auto">
                    ${lireBtn}
                    ${dlBtn}
                  </div>
                </div>
              </div>
            </div>`;
        });

        $('#docsGrid').html(html);
        // relancer votre pagination si nécessaire
        if (typeof pager !== 'undefined') pager.refresh()
        reloadGrid(html);
      }
    });
  }

  // 3) Lier la recherche au champ
  $('#searchInput, #otherCat').on('input', refreshLibrary);
  $('#filterCat').on('change', refreshLibrary);

  // 4) Chargement initial
  refreshLibrary();
  reloadGrid(html);
});

    //---------------------------------------------------

    $(function(){
  /**
   * Initialise la pagination pour une grille de cartes (#docsGrid .doc-card)
   * @param {number} perPage — nombre d’items par page
   * @returns {Object} — { refresh(): void }
   */
  function setupGridPagination(perPage) {
    let $items, total, pages, currentPage;

    // Recalcule le nombre de pages et remet currentPage à 1
    function recalc() {
      // On ne prend que les cartes visibles (filtrage appliqué)
      $items      = $('#docsGrid .doc-card:visible');
      total       = $items.length;
      pages       = Math.ceil(total / perPage) || 1;
      currentPage = 1;
    }

    // Affiche la page demandée
    function showPage(page) {
      recalc();
      const start = (page - 1) * perPage;
      const end   = start + perPage;
      $items.hide().slice(start, end).show();
      currentPage = page;
      renderPager();
    }

    // Construit les boutons Précédent / Numéros / Suivant
    function renderPager() {
      const $pager = $('#pagination-container').empty();
      if (pages <= 1) return;  // pas de pagination si <= 1 page

      // « Précédent »
      if (currentPage > 1) {
        $pager.append(`
          <li class="page-item">
            <a class="page-link" href="#" data-page="${currentPage-1}">&laquo; Précedent</a>
          </li>`);
      }

      // numéros
      for (let p = 1; p <= pages; p++) {
        const active = (p === currentPage) ? ' active' : '';
        $pager.append(`
          <li class="page-item${active}">
            <a class="page-link" href="#" data-page="${p}">${p}</a>
          </li>`);
      }

      // « Suivant »
      if (currentPage < pages) {
        $pager.append(`
          <li class="page-item">
            <a class="page-link" href="#" data-page="${currentPage+1}">Suivant &raquo;</a>
          </li>`);
      }
    }

    $('#pagination-container')
      .off('click.gridPg')
      .on('click.gridPg', 'a.page-link', function(e){
        e.preventDefault();
        const p = parseInt($(this).data('page'), 10);
        if (p && p !== currentPage) showPage(p);
      });

    // API publique
    return {
      refresh() {
        recalc();
        showPage(1);
      }
    };
  }

  const pager = setupGridPagination(2);
  pager.refresh();

  function reloadGrid(newRowsHtml) {
    $('#authorTable tbody').html(newRowsHtml);
    pager.refresh();
  }

});

  </script>
</body>
</html>
