<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// 1) Récupérer tous les Auteurs
$stmt = $pdo->prepare("SELECT * FROM Auteur");
$stmt->execute();
$auteur = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Lire l’action et le statut pour les popups
$action = $_GET['action'] ?? null;      // 'add', 'edit', 'delete'
$status = $_GET['status'] ?? null;      // 'success', 'error'
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestion Auteurs</title>
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
              <a class="nav-link active" href="Gestion_Auteur.php" style="color : #fff ! important"><i class="fa-solid fa-user"></i> Auteurs</a>
            </li>
            <li class="nav-item ">
              <a class="nav-link" href="Gestion_Editeur.php"><i class="fa-solid fa-users"></i> Éditeurs</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Livre.php"><i class="fa-solid fa-book"></i> Livres</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="GEstion_Emprunter.php"><i class="fa-solid fa-ticket"></i> Emprunts</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="library.php"><i class="fa-solid fa-book-atlas"></i> Librairie</a>
            </li>
          </ul>
          <!-- Profil de l'utilisateur connecté -->
          <div class="d-flex align-items-center">
            <img src="./images/user.png" alt="User profil" class="rounded-circle me-2" style="width:40px; height:40px;">
            <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['user']['usermail']); ?></span>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <main class="container mt-5">
    <h2 class="mb-4 text-center">Gestion des Auteurs</h2>
    <!-- Section Recherche / Filtrage -->
    <div class="row mb-30">
      <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par le nom ou le type">
      </div>
    </div>

    <!-- Total -->
    <div class="total mb-30">
      <h5><strong>Total auteurs : </strong> <span id="totalCount"><?= count($auteur) ?></span></h5>
    </div>

    <!-- Bouton Ajouter -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addauthor">
      <i class="fa-solid fa-plus"></i> Ajouter un auteur
    </button>
    
    <!-- Tableau des auteurs -->
    <div class="table-responsive">
      <table id="authorTable" class="table table-striped">
        <thead class="table-dark">
          <tr>
          <th>ID Auteur</th>
            <th>Prénoms</th>
            <th>Nom</th>
            <th>Sexe</th>
            <th>Date de Naissance</th>
            <th>Type  </th>
            <th>Pays</th>
            <th>Biogrqphie</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($auteur as $auth): ?>
          <tr>
          <td><?php echo htmlspecialchars($auth['idAut']); ?></td>
            <td><?php echo htmlspecialchars($auth['prenom']); ?></td>
            <td><?php echo htmlspecialchars($auth['nom']); ?></td>
            <td><?php echo htmlspecialchars($auth['sexe']); ?></td>
            <td><?php echo htmlspecialchars($auth['born']); ?></td>
            <td><?php echo htmlspecialchars($auth['types']); ?></td>
            <td><?php echo htmlspecialchars($auth['pays']); ?></td>
            <td>
              <?php echo $auth['biographie']  && trim($auth['biographie']) !== ''  ? htmlspecialchars($auth['biographie']) : 'Aucune Biographie !'; ?>
            </td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editauthorModal<?php echo $auth['idAut']; ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $auth['idAut']; ?>)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
          <!-- modal de modification pour cet Auteur-->
          <div class="modal fade" id="editauthorModal<?php echo $auth['idAut']; ?>" tabindex="-1" aria-labelledby="editauthorModalLabel<?php echo $auth['idAut']; ?>" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST" action="edit_author.php">
                  <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editauthorModalLabel<?php echo $auth['idAut']; ?>">Modifier l'auteur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="idAut" value="<?php echo $auth['idAut']; ?>">
                    <!-- Vous devez prévoir des inputs pour toutes les colonnes que vous souhaitez modifier -->
                    <div class="mb-3">
                      <label for="prenoms<?php echo $auth['idAut']; ?>" class="form-label">Prénoms</label>
                      <input type="text" name="prenoms" id="prenoms<?php echo $auth['idAut']; ?>" class="form-control" value="<?php echo htmlspecialchars($auth['prenom']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="nom<?php echo $auth['idAut']; ?>" class="form-label">Nom</label>
                      <input type="text" name="nom" id="nom<?php echo $auth['idAut']; ?>" class="form-control" value="<?php echo htmlspecialchars($auth['nom']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="sexe<?php echo $auth['idAut']; ?>" class="form-label">Sexe</label>
                      <select name="sexe" id="sexe<?php echo $auth['idAut']; ?>" class="form-select" required>
                        <option value="M" <?php echo ($auth['sexe'] == 'M') ? 'selected' : ''; ?>>M</option>
                        <option value="F" <?php echo ($auth['sexe'] == 'F') ? 'selected' : ''; ?>>F</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="born<?php echo $auth['idAut']; ?>" class="form-label">Date de naissance</label>
                      <input type="date" name="born" id="born<?php echo $auth['idAut']; ?>" class="form-control" value="<?php echo htmlspecialchars($auth['born']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="type<?php echo $auth['idAut']; ?>" class="form-label">Type d'écrivain *</label>
                      <input type="text" name="type" id="type<?php echo $auth['idAut']; ?>" class="form-control" value="<?php echo htmlspecialchars($auth['types']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="pays<?php echo $auth['idAut']; ?>" class="form-label">Pays *</label>
                      <input type="text" name="pays" id="pays<?php echo $auth['idAut']; ?>" class="form-control" value="<?php echo htmlspecialchars($auth['pays']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="biographie<?php echo $auth['idAut']; ?>" class="form-label">Biographie</label>
                      <textarea name="biographie" id="biographie<?php echo $auth['idAut']; ?>" class="form-control"><?php echo htmlspecialchars($auth['biographie']); ?></textarea>
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

  <!-- Modal d'ajout d'un Auteur -->
  <div class="modal fade" id="addauthor" tabindex="-1" aria-labelledby="addauthorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="add_author.php" id="addauthorForm">
          <div class="modal-header text-white"> <!-- bg-primary à ajouter si souhaité -->
            <h5 class="modal-title" id="addauthorModalLabel">Ajouter un Auteur</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="nom" class="form-label">Nom *</label>
              <input type="text" name="nom" id="nom" class="form-control" placeholder="Nom de l'auteur" required>
            </div>
            <div class="mb-3">
              <label for="prenoms" class="form-label">Prénoms *</label>
              <input type="text" name="prenoms" id="prenoms" class="form-control" placeholder="Prénom de l'auteur" required>
            </div>
            <div class="mb-3">
              <label for="sexe" class="form-label">Sexe *</label>
              <select id="sexe" name="sexe" class="form-select" required>
                <option value="" disabled selected>M/F</option>
                <option value="M">M</option>
                <option value="F">F</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="date" class="form-label">Date de naissance *</label>
              <input type="date" name="date" id="date" class="form-control" placeholder="jj/mm/aaaa" required>
            </div>
            <div class="mb-3">
            <div class="mb-3">
              <label for="type" class="form-label">typê d'écrivain *</label>
              <input type="text" name="type" id="type" class="form-control" placeholder="poête, romancier ?">
            </div>
            <div class="mb-3">
              <label for="pays" class="form-label">Pays d'origine *</label>
              <input type="text" name="pays" id="pays" class="form-control" placeholder="Sénégal, Côte d'Ivoire ?">
            </div>
            <div class="mb-3">
              <label for="biographie" class="form-label">Biographie (faculatif)</label>
              <textarea style="resize: none;" name="biographie" id="biographie" placeholder="Ajouter une Biographie ?"></textarea>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/sweetalert2.min.js"></script>
  <script src="js/jquery.simplePagination.js"></script>

  <script>
  // Confirmation SweetAlert2 pour la suppression
  function confirmDelete(idAut) {
    Swal.fire({
      title: 'Supprimer cet Auteur?',
      text: "Cette action est irréversible !",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'del_author.php?idAut=' + idAut;
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
        author_exists:    { title: 'Auteur déjà présent',    text: 'Cet auteur existe déjà.'},
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
      $rows       = $('#authorTable tbody tr');
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
  pager.refresh();

  function reloadTable(newRowsHtml) {
    $('#authorTable tbody').html(newRowsHtml);
    pager.refresh();
  }
});

    //----------------------------------------------------------------
    $(function(){
    function refreshTable(){
      const search  = $('#searchInput').val().trim();

      $.ajax({
        url: 'ajax_auteur.php',
        dataType: 'json',
        data: { search },
        success(resp) {
        $('#totalCount').text(resp.total);

          const rows = resp.data.map(auth => `
            <tr>
              <td>${auth.idAut}</td>
              <td>${auth.prenom}</td>
              <td>${auth.nom}</td>
              <td>${auth.sexe}</td>
              <td>${auth.born}</td>
              <td>${auth.types}</td>
              <td>${auth.pays}</td>
              <td>${auth.biographie && auth.biographie.trim() !== '' ? auth.biographie : 'Aucune biographie  !'}</td>
              <td>
                <button class="btn btn-sm btn-warning"
                      data-bs-toggle="modal"
                      data-bs-target="#editauthorModal${auth.idAut}">
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn btn-sm btn-danger"
                      onclick="confirmDelete(${auth.idAut})">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          `).join('');

          $('#authorTable tbody').html(rows);

          // 3) Réinitialiser la pagination si vous l'utilisez
          page.refresh();
          },
          error(err) {
            console.error('Erreur AJAX :', err);
          }
      });
    }

    // lier événements
    $('#searchInput')
      .on('input change', refreshTable);

     // chargement initial
     reloadTable(html);
    });
  </script>
</body>
</html>
