<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// 1) Récupérer tous les livres
$stmt = $pdo->prepare("
  SELECT
    l.idLiv AS idLiv,
    l.titre,
    l.idAut AS idAut,
    l.idEdit AS idEdit,
    CONCAT(a.nom, ' ', a.prenom) AS auteur,
    e.nom AS editeur,
    l.genre,
    l.datepub,
    l.disponible,
    l.nbExemplaire
  FROM Livre l
  INNER JOIN Auteur a ON l.idAut = a.idAut
  INNER JOIN Editeur e ON l.idEdit = e.idEdit
");
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Lire l’action et le statut pour les popups
$action = $_GET['action'] ?? null;      // 'add', 'edit', 'delete'
$status = $_GET['status'] ?? null;      // 'success', 'error'
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestion Livres</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- SweetAlert2 CSS local -->
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
        <a class="navbar-brand" href="accueil.php">
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
              <a class="nav-link active" href="Gestion_Livre.php" style="color : #fff ! important"><i class="fa-solid fa-book"></i> Livres</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Emprunt.php"><i class="fa-solid fa-ticket"></i> Emprunts</a>
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
    <h2 class="mb-4 text-center">Gestion des Livres</h2>
    <!-- Section Recherche / Filtrage -->
    <div class="row mb-30">
      <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par le titre ou le type de livre">
      </div>
    </div>

    <!-- Total -->
    <div class="total mb-30">
      <h5><strong>Total Livre : </strong> <span id="totalCount"><?= count($books) ?></span></h5>
    </div>

    <!-- Bouton Ajouter -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addbook">
      <i class="fa-solid fa-plus"></i> Ajouter un Livre
    </button>
    
    <div class="table-responsive">
      <table id="bookTable" class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID Livre</th>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Edition</th>
            <th>type du livre</th>
            <th>Publié le </th>
            <th>Disponible</th>
            <th>Nb Nbres en stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($books as $book): ?>
          <tr>
            <td><?php echo htmlspecialchars($book['idLiv']); ?></td>
            <td><?php echo htmlspecialchars($book['titre']); ?></td>
            <td><?php echo htmlspecialchars($book['auteur']); ?></td>
            <td><?php echo htmlspecialchars($book['editeur']); ?></td>
            <td><?php echo htmlspecialchars($book['genre']); ?></td>
            <td><?php echo htmlspecialchars($book['datepub']); ?></td>
            <td><?php echo htmlspecialchars($book['disponible']); ?></td>
            <td><?php echo htmlspecialchars(string: $book['nbExemplaire']); ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editbookModal<?php echo $book['idLiv']; ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $book['idLiv']; ?>)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
          <!-- modal de modification pour ce Livre-->
          <div class="modal fade" id="editbookModal<?php echo $book['idLiv']; ?>" tabindex="-1" aria-labelledby="editbookModalLabel<?php echo $book['idLiv']; ?>" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST" action="edit_book.php">
                  <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editbookModalLabel<?php echo $book['idLiv']; ?>">Modifier le Livre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="idLiv" value="<?php echo $book['idLiv']; ?>">
                    <div class="mb-3">
                      <label for="titre<?php echo $book['idLiv']; ?>" class="form-label">Titre</label>
                      <input type="text" name="titre" id="titre<?php echo $book['idLiv']; ?>" class="form-control" value="<?php echo htmlspecialchars($book['titre']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="idAut<?php echo $book['idLiv']; ?>" class="form-label">ID de l'auteur*</label>
                      <input type="number" name="idAut" id="idAut<?php echo $book['idLiv']; ?>" class="form-control" value="<?php echo htmlspecialchars($book['idAut']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="idEdit<?php echo $book['idLiv']; ?>" class="form-label">ID de l'auteur*</label>
                      <input type="number" name="idEdit" id="idEdit<?php echo $book['idLiv']; ?>" class="form-control" value="<?php echo htmlspecialchars($book['idEdit']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="genre<?php echo $book['idLiv']; ?>" class="form-label">type *</label>
                      <input type="text" name="genre" id="genre<?php echo $book['idLiv']; ?>" class="form-control" value="<?php echo htmlspecialchars($book['genre']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="pub<?php echo $book['idLiv']; ?>" class="form-label">Publié le :*</label>
                      <input type="date" name="pub" id="pub<?php echo $book['idLiv']; ?>" class="form-control" value="<?php echo htmlspecialchars($book['datepub']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="disponible<?php echo $book['idLiv']; ?>" class="form-label">Disponible *</label>
                      <input type="number" name="disponible" id="disponible<?php echo $book['idLiv']; ?>" class="form-control" value="<?php echo htmlspecialchars($book['disponible']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="exemplaire<?php echo $book['idLiv']; ?>" class="form-label">en Stock</label>
                      <input type="number" name="exemplaire" id="exemplaire<?php echo $book['idLiv']; ?>" class="form-control" value="<?php echo htmlspecialchars($book['nbExemplaire']); ?>">
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

  <!-- Modal d'ajout d'un Livre -->
  <div class="modal fade" id="addbook" tabindex="-1" aria-labelledby="addbookModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="add_book.php" id="addbookForm">
          <div class="modal-header text-white"> <!-- bg-primary à ajouter si souhaité -->
            <h5 class="modal-title" id="addbookModalLabel">Ajouter un Livre</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="titre" class="form-label">Titre *</label>
              <input type="text" name="titre" id="nom" class="form-control" placeholder="Titre du livre" required>
            </div>
            <div class="mb-3">
              <label for="idAut" class="form-label">ID Auteur *</label>
              <input type="number" name="idAut" id="idAut" class="form-control" placeholder="ID de l'auteur" required>
            </div>
            <div class="mb-3">
              <label for="idEdit" class="form-label">ID Editeur *</label>
              <input type="number" name="idEdit" id="idEdit" class="form-control" placeholder="ID de l'editeur" required>
            </div>
            <div class="mb-3">
              <label for="genre" class="form-label">Type du Livre *</label>
              <input type="text" name="genre" id="genre" class="form-control" placeholder="Roman, Guide, Annales...?">
            </div>
            <div class="mb-3">
              <label for="date" class="form-label">Publié le*</label>
              <input type="date" name="pub" id="date" class="form-control" placeholder="jj/mm/aaaa" required>
            </div>
            <div class="mb-3">
              <label for="disponible" class="form-label">Disponible</label>
              <input type="number" name="disponible" id="disponible" class="form-control" placeholder="nom emprunté ">
            </div>
            <div class="mb-3">
              <label for="exemplaire" class="form-label">Nbres en stock</label>
              <input type="number" name="exemplaire" id="exemplaire" class="form-control" placeholder="Nbre total">
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
  function confirmDelete(idLiv) {
    Swal.fire({
      title: 'Supprimer ce Livre?',
      text: "Cette action est irréversible et les emprunts qui y sont lié seront aussi éffacés !",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'del_book.php?idLiv=' + idLiv;
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
        invalid_id:       { title: 'ID invalide',           text: 'Aucun enregistrement ne correspond.',},
        book_exists:   { title: 'Livre déjà présent',       text: 'Ce livre existe déjà.'}
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
      $rows       = $('#bookTable tbody tr');
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
    $('#bookTable tbody').html(newRowsHtml);
    pager.refresh();
  }
});

    //----------------------------------------------------------------
    $(function(){
    function refreshTable(){
      const search  = $('#searchInput').val().trim();

      $.ajax({
        url: 'ajax_livre.php',
        dataType: 'json',
        data: { search },
        success(resp) {
        $('#totalCount').text(resp.total);

          const rows = resp.data.map(book => `
            <tr>
              <td>${book.idLiv}</td>
              <td>${book.titre}</td>
              <td>${book.auteur}</td>
              <td>${book.editeur}</td>
              <td>${book.genre}</td>
              <td>${book.datepub}</td>
              <td>${book.disponible}</td>
              <td>${book.nbExemplaire}</td>
              <td>
                <button class="btn btn-sm btn-warning"
                      data-bs-toggle="modal"
                      data-bs-target="#editbookModal${book.idLiv}">
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn btn-sm btn-danger"
                      onclick="confirmDelete(${book.idLiv})">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          `).join('');

          $('#bookTable tbody').html(rows);

          pager.refresh();
          },
          error(err) {
            console.error('Erreur AJAX :', err);
          }
      });
    }

    $('#searchInput')
      .on('input change', refreshTable);

      reloadTable(html);
  });
  </script>
</body>
</html>
