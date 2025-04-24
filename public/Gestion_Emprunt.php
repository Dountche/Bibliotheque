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

$stmt = $pdo->prepare("
  SELECT
    emp.idEmp,
    emp.idLiv,
    emp.idEtu,
    etu.matEtu           AS matricule,
    CONCAT(etu.nom,' ',etu.prenoms) AS etudiant,
    l.titre              AS titre,
    l.genre              AS genre,
    emp.dateEmp,
    emp.dateRetour,
    emp.dateRendu
  FROM Emprunt AS emp
  INNER JOIN Etudiant AS etu
    ON emp.idEtu = etu.idEtu
  INNER JOIN Livre AS l
    ON emp.idLiv = l.idLiv
  ORDER BY dateEmp DESC
");
$stmt->execute();
$emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Lire l’action et le statut pour les popups
$action = $_GET['action'] ?? null;      // 'add', 'edit', 'delete'
$status = $_GET['status'] ?? null;      // 'success', 'error'
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestion Emprunts</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- SweetAlert2 CSS local -->
  <link rel="icon" href="./images/icon.png">
  <link rel="apple-touch-icon" href="./images/icon.png">
  <link rel="stylesheet" href="css/sweetalert2.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.6/simplePagination.css" />
  <!--  CSS perso -->
  <link rel="stylesheet" href="./css/styles.css">
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
              <a class="nav-link" href="GEstion_Emprunter.php" style="color : #fff ! important"><i class="fa-solid fa-ticket"></i> Emprunts</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="library.php"><i class="fa-solid fa-book-atlas"></i> Librairie</a>
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

  <main class="container mt-5">
    <h2 class="mb-4 text-center">Gestion des Emprunts</h2>
    <!-- Section Recherche / Filtrage -->
    <div class="row mb-30">
      <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un emprunt">
      </div>
      <div class="col-md-4">
        <select id="filterEmprunt" class="form-select">
          <option value="">Filtrer par date</option>
          <option value="retour">retourné</option>
          <option value="nonretour">non retourné</option>
        </select>
      </div>
    </div>

    <!-- Total -->
    <div class="total mb-30">
      <h5><strong>Total Emprunts : </strong> <?= count($emprunts) ?></h5>
    </div>

    <!-- Bouton Ajouter -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addemprunt">
      <i class="fa-solid fa-plus"></i> Ajouter un emprunt
    </button>
    <!-- Tableau des Emprunts -->
    <div class="table-responsive">
      <table id="emprunTable" class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th>Matricule</th>
            <th>Nom et prénoms</th>
            <th>Livre</th>
            <th>Type de livre</th>
            <th>date d'emprunt</th>
            <th>date de retour</th>
            <th>retourné le</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($emprunts as $emp): ?>
          <tr>
            <td><?php echo htmlspecialchars($emp['matricule']); ?></td>
            <td><?php echo htmlspecialchars($emp['etudiant']); ?></td>
            <td><?php echo htmlspecialchars($emp['titre']); ?></td>
            <td><?php echo htmlspecialchars($emp['genre']); ?></td>
            <td><?php echo htmlspecialchars($emp['dateEmp']); ?></td>
            <td><?php echo htmlspecialchars($emp['dateRetour']); ?></td>
            <td>
              <?php echo $emp['dateRendu'] !== null ? htmlspecialchars($emp['dateRendu']) : 'Non retourné !'; ?>
            </td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editempruntModal<?php echo $emp['idEmp']; ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $emp['idEmp']; ?>)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
          <!-- modal de modification pour cet Emprunt-->
          <div class="modal fade" id="editempruntModal<?php echo $emp['idEmp']; ?>" tabindex="-1" aria-labelledby="editempruntModalLabel<?php echo $emp['idEmp']; ?>" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST" action="edit_emprunt.php">
                  <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editempruntModalLabel<?php echo $emp['idEmp']; ?>">Modifier l'Emprunt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="idEmp" value="<?php echo $emp['idEmp']; ?>">
                    <div class="mb-3">
                      <label for="matricule<?php echo $emp['idEmp']; ?>" class="form-label">Matricule</label>
                      <input type="text" name="matricule" id="matricule<?php echo $emp['idEmp']; ?>" class="form-control" value="<?php echo htmlspecialchars($emp['matricule']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="idLiv<?php echo $emp['idEmp']; ?>" class="form-label">ID du livre*</label>
                      <input type="number" name="idLiv" id="idLiv<?php echo $emp['idEmp']; ?>" class="form-control" value="<?php echo htmlspecialchars($emp['idLiv']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="datemp<?php echo  $emp['idEmp']; ?>" class="form-label">Date d'emprunt</label>
                      <input type="date" name="datemp" id="datemp<?php echo  $emp['idEmp']; ?>" class="form-control" value="<?php echo htmlspecialchars($emp['dateEmp']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="dateret<?php echo  $emp['idEmp']; ?>" class="form-label">Date de retour</label>
                      <input type="date" name="dateret" id="dateret<?php echo  $emp['idEmp']; ?>" class="form-control" value="<?php echo htmlspecialchars($emp['dateRetour']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="dateren<?php echo  $emp['idEmp']; ?>" class="form-label">Date de renu</label>
                      <input type="date" name="dateren" id="dateren<?php echo  $emp['idEmp']; ?>" class="form-control" value="<?php echo htmlspecialchars($emp['dateRendu']); ?>">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <nav aria-label="Page navigation">
      <ul id="pagination-container" class="pagination justify-content-center"></ul>
    </nav>
  </main>

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

  <!-- Modal d'ajout d'un emprunt -->
  <div class="modal fade" id="addemprunt" tabindex="-1" aria-labelledby="addempruntModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="add_emprunt.php" id="addempruntForm">
          <div class="modal-header text-white"> <!-- bg-primary à ajouter si souhaité -->
            <h5 class="modal-title" id="addempruntModalLabel">Ajouter un Emprunt</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="matricule" class="form-label">Matricule *</label>
              <input type="text" name="matricule" id="matricule" class="form-control" placeholder="Matricule de l'etudiant" required>
            </div>
            <div class="mb-3">
              <label for="idLiv" class="form-label">ID du livre *</label>
              <input type="text" name="idLiv" id="idLiv" class="form-control" placeholder="ID du livre emprunter" required>
            </div>
            <div class="mb-3">
              <label for="datemp" class="form-label">Date d'emprunt *</label>
              <input type="date" name="datemp" id="datemp" class="form-control" placeholder="jj/mm/aaaa" required>
            </div>
            <div class="mb-3">
              <label for="dateret" class="form-label">Date de retour *</label>
              <input type="date" name="dateret" id="dateret" class="form-control" placeholder="jj/mm/aaaa" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JS externes -->
  <script src="js/jquery-3.7.1.js"></script>
  <script src="js/jquery.simplePagination.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/ajax_account.js"></script>
  <script src="js/sweetalert2.min.js"></script>
  <script>
  // Confirmation SweetAlert2 pour la suppression
  function confirmDelete(idEmp) {
    Swal.fire({
      title: 'Supprimer cet Emprunt?',
      text: "Cette action est irréversible !",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'del_emprunt.php?idEmp=' + idEmp;
      }
    });
  }

  // Popup Succès/Erreur selon action+status
  document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const action = params.get('action');
    const status = params.get('status');
    const error  = params.get('error');

    if (error) {
      const messages = {
        invalid_id:             { title: 'ID invalide',                 text: 'Aucun enregistrement ne correspond.',},
        emprunt_exists:         { title: 'Emprunt existant',            text: 'Cet emprunt est déjà enregistré.'},
        etudiant_unknown:       { title: 'Étudiant inconnu',            text: 'Aucun étudiant n’a ce matricule.'},
        livre_unknown:          { title: 'Livre inconnu',               text: 'Aucun livre n’a identifant. '},
        livre_indavailable:     { title: 'Livre nondisponible',         text: 'tous les exemplaires de  livres ont été emprunter'},
        date_inferieur:         { title: 'Date invalide',               text: 'La date de retour doit être postérieure à la date d\'emprunt.'},
        dateren_inferieur:      { title: 'Date invalide',               text: 'La date de rendu doit être postérieure à la date d\'emprunt.'},
        not_found:              { title: 'Ereur d\'enregistrement',     text: 'L\'enregistrement est corrompu.'},
        dateren_supjj:          { title: 'Date invalide',               text: 'La date de rendu ne peut pas être postérieure à Aujourd\'hui.'},
      };
      Swal.fire({
        icon: 'error',
        title: messages[error].title,
        text:  messages[error].text,
        confirmButtonText: 'OK'
      });
      history.replaceState(null, '', window.location.pathname);
      return;
    }

    if (action && status) {
      const titles = { add: 'Ajout', edit: 'Modification', delete: 'Suppression' };
      Swal.fire({
        icon: status === 'success' ? 'success' : 'error',
        title: titles[action] || '',
        text: `${titles[action] || ''} ${status==='success' ? 'réussie' : 'échouée'} !`,
        confirmButtonText: 'OK'
      });
      history.replaceState(null, '', window.location.pathname);
    }
  });
//--------------------------------------------------------------
  //paginaer l'affichage
  $(function(){
  function setupPagination(perPage) {
    let $rows, total, pages, currentPage;

    function recalc() {
      $rows       = $('#emprunTable tbody tr');
      total       = $rows.length;
      pages       = Math.ceil(total / perPage);
      currentPage = 1;
    }

    function showPage(page) {
      const start = (page - 1) * perPage;
      const end   = start + perPage;
      $rows.hide().slice(start, end).show();
      currentPage = page;
      renderPager();
    }

    function renderPager() {
      const $pager = $('#pagination-container').empty();
      if (pages <= 1) return;

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

    // gestion du clic sur les boutons
    $('#pagination-container')
      .off('click.pag')
      .on('click.pag', 'a.page-link', function(e){
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

  const pager = setupPagination(100);
  //  Initialisation
  pager.refresh();

  function reloadTable(newRowsHtml) {
    $('#emprunTable tbody').html(newRowsHtml);
    pager.refresh();
  }
});

    //----------------------------------------------------------------
    $(function(){
    function refreshTable(){
      const search  = $('#searchInput').val().trim();
      const statut   = $('#filterEmprunt').val();

      $.ajax({
        url: 'ajax_emprunt.php',
        dataType: 'json',
        data: { search, statut },
        success(resp) {
        $('#totalCount').text(resp.total);

          const rows = resp.data.map(emp => `
            <tr>
              <td>${emp.matricule}</td>
              <td>${emp.etudiant}</td>
              <td>${emp.titre}</td>
              <td>${emp.genre}</td>
              <td>${emp.dateEmp}</td>
              <td>${emp.dateRetour}</td>
              <td>${emp.dateRendu && emp.dateRendu.trim() !== '' ? emp.dateRendu : 'Non Retourné!'}</td>
              <td>
                <button class="btn btn-sm btn-warning"
                      data-bs-toggle="modal"
                      data-bs-target="#editempruntModal${emp.idEmp}">
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn btn-sm btn-danger"
                      onclick="confirmDelete(${emp.idEmp})">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          `).join('');

          $('#emprunTable tbody').html(rows);

          pager.refresh();
          },
          error(err) {
            console.error('Erreur AJAX :', err);
          }
      });
    }

    $('#searchInput, #filterSchool, #filterEmprunt')
      .on('input change', refreshTable);

      reloadTable(html);
  });

  </script>
</body>
</html>
