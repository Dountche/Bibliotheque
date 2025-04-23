<?php
// public/Gestion_Etudiants.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// 1) Récupérer tous les étudiants
$stmt = $pdo->prepare("SELECT * FROM Etudiant ORDER BY prenoms ASC");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Lire l’action et le statut pour les popups
$action = $_GET['action'] ?? null;      // 'add', 'edit', 'delete'
$status = $_GET['status'] ?? null;      // 'success', 'error'
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestion Étudiants</title>
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
              <a class="nav-link active" href="Gestion_Etudiant.php" style="color : #fff ! important"><i class="fa-solid fa-user-graduate"></i> Étudiants</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Auteur.php"><i class="fa-solid fa-user"></i> Auteurs</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Editeur.php"><i class="fa-solid fa-users"></i> Éditeurs</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Livre.php"><i class="fa-solid fa-book"></i> Livres</a>
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
    <h2 class="mb-4 text-center">Gestion des Étudiants</h2>
    <!-- Section Recherche / Filtrage -->
    <div class="row mb-30">
      <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par nom ou matricule">
      </div>
      <div class="col-md-4">
        <select id="filterSchool" class="form-select">
          <option value="">Filtrer par école</option>
          <option value="ESA">ESA</option>
                <option value="ESI">ESI</option>
                <option value="ESAS">ESAS</option>
                <option value="ESCAE">ESCAE</option>
                <option value="ESMG">ESMG</option>
                <option value="ESCPE">ESCPE</option>
                <option value="ESTP">ESTP</option>
                <option value="EPGE">EPGE</option>
                <option value="EDP">EDP</option>
                <option value="ESFPC">ESFPC</option>
                <option value="IDSI">IDSI</option>
                <option value="DESCOGEF">DESCOGEF</option>
        </select>
      </div>
      <div class="col-md-4">
        <select id="filterLevel" class="form-select">
          <option value="">Filtrer par Niveau</option>
          <option value="P1">P1</option>
          <option value="P2">P2</option>
          <option value="TS1">TS1</option>
          <option value="TS2">TS2</option>
          <option value="TS3">TS3</option>
          <option value="ING1">ING1</option>
          <option value="ING2">ING2</option>
          <option value="ING3">ING3</option>
          <option value="M1">M1</option>
          <option value="M2">M2</option>
          <option value="D1">D1</option>
          <option value="D2">D2</option>
          <option value="D3">D3</option>
          <option value="D4">D4</option>
        </select>
      </div>
    </div>

    <!-- Total -->
    <div class="total mb-30">
      <h5><strong>Total étudiants : </strong > <span id="totalCount"><?= count($students) ?></span></h5>
    </div>

    <!-- Bouton Ajouter -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addStudentModal">
      <i class="fa-solid fa-plus"></i> Ajouter un étudiant
    </button>
    
    <!-- Tableau des étudiants -->
    <div class="table-responsive">
      <table id="studentsTable" class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th>Matricule</th>
            <th>Prénoms</th>
            <th>Nom</th>
            <th>Sexe</th>
            <th>Date de Naissance</th>
            <th>Ecole</th>
            <th>Filière</th>
            <th>Specialité</th>
            <th>Niveau</th>
            <th>Email</th>
            <th>Téléphone</th>
            <!-- Vous pouvez ajouter d'autres colonnes si nécessaire -->
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $stu): ?>
          <tr>
            <td><?php echo htmlspecialchars($stu['matEtu']); ?></td>
            <td><?php echo htmlspecialchars($stu['prenoms']); ?></td>
            <td><?php echo htmlspecialchars($stu['nom']); ?></td>
            <td><?php echo htmlspecialchars($stu['sexe']); ?></td>
            <td><?php echo htmlspecialchars($stu['born']); ?></td>
            <td><?php echo htmlspecialchars($stu['ecole']); ?></td>
            <td><?php echo htmlspecialchars($stu['filiere']); ?></td>
            <td><?php echo htmlspecialchars($stu['specialite']); ?></td>
            <td><?php echo htmlspecialchars($stu['niveau']); ?></td>
            <td><?php echo htmlspecialchars($stu['mail']); ?></td>
            <td><?php echo htmlspecialchars($stu['tel']); ?></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editStudentModal<?php echo $stu['idEtu']; ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $stu['idEtu']; ?>)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
          <!-- modal de modification pour cet étudiant-->
          <div class="modal fade" id="editStudentModal<?php echo $stu['idEtu']; ?>" tabindex="-1" aria-labelledby="editStudentModalLabel<?php echo $stu['idEtu']; ?>" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST" action="edit_student.php">
                  <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editStudentModalLabel<?php echo $stu['idEtu']; ?>">Modifier l'étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="idEtu" value="<?php echo $stu['idEtu']; ?>">
                    <!-- Vous devez prévoir des inputs pour toutes les colonnes que vous souhaitez modifier -->
                    <div class="mb-3">
                      <label for="matEtu<?php echo $stu['idEtu']; ?>" class="form-label">Matricule</label>
                      <input type="text" name="matEtu" id="matEtu<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['matEtu']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="prenoms<?php echo $stu['idEtu']; ?>" class="form-label">Prénoms</label>
                      <input type="text" name="prenoms" id="prenoms<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['prenoms']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="nom<?php echo $stu['idEtu']; ?>" class="form-label">Nom</label>
                      <input type="text" name="nom" id="nom<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['nom']); ?>" required>
                    </div>
                    <div class="mb-3">
                    <div class="mb-3">
                      <label for="sexe<?php echo $stu['idEtu']; ?>" class="form-label">Sexe</label>
                      <select name="sexe" id="sexe<?php echo $stu['idEtu']; ?>" class="form-select" required>
                        <option value="M" <?php echo ($stu['sexe'] == 'M') ? 'selected' : ''; ?>>M</option>
                        <option value="F" <?php echo ($stu['sexe'] == 'F') ? 'selected' : ''; ?>>F</option>
                      </select>
                    </div>
                      <label for="born<?php echo $stu['idEtu']; ?>" class="form-label">Date de naissance</label>
                      <input type="date" name="born" id="born<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['born']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="ecole<?php echo $stu['idEtu']; ?>" class="form-label">Ecole</label>
                      <input type="text" name="ecole" id="ecole<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['ecole']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="filiere<?php echo $stu['idEtu']; ?>" class="form-label">Filière</label>
                      <input type="text" name="filiere" id="filiere<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['filiere']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="specialite<?php echo $stu['idEtu']; ?>" class="form-label">Specialité</label>
                      <input type="text" name="specialite" id="specialite<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['specialite']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="niveau<?php echo $stu['idEtu']; ?>" class="form-label">Niveau</label>
                      <input type="text" name="niveau" id="niveau<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['niveau']); ?>">
                    </div>
                    <div class="mb-3">
                      <label for="mail<?php echo $stu['idEtu']; ?>" class="form-label">Email</label>
                      <input type="email" name="mail" id="mail<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['mail']); ?>" required>
                    </div>
                    <div class="mb-3">
                      <label for="tel<?php echo $stu['idEtu']; ?>" class="form-label">Téléphone</label>
                      <input type="text" name="tel" id="tel<?php echo $stu['idEtu']; ?>" class="form-control" value="<?php echo htmlspecialchars($stu['tel']); ?>">
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

  <!-- Modal d'ajout d'un étudiant -->
  <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="add_student.php" id="addStudentForm">
          <div class="modal-header text-white"> <!-- bg-primary à ajouter si souhaité -->
            <h5 class="modal-title" id="addStudentModalLabel">Ajouter un étudiant</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="matricule" class="form-label">Matricule *</label>
              <input type="text" name="matricule" id="matricule" class="form-control" placeholder="Ex : 00INP00000" required>
            </div>
            <div class="mb-3">
              <label for="nom" class="form-label">Nom *</label>
              <input type="text" name="nom" id="nom" class="form-control" placeholder="Votre nom" required>
            </div>
            <div class="mb-3">
              <label for="prenoms" class="form-label">Prénoms *</label>
              <input type="text" name="prenoms" id="prenoms" class="form-control" placeholder="Votre prénom" required>
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
              <label for="tel" class="form-label">N° Téléphone *</label>
              <input type="tel" name="tel" id="tel" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="regemail" class="form-label">Email institutionnel</label>
              <input type="email" name="regemail" id="regemail" class="form-control" placeholder="votre email" required>
            </div>
            <div class="mb-3">
              <label for="regPassword" class="form-label">Mot de passe *</label>
              <input type="password" name="regPassword" id="regPassword" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="regConfirmPassword" class="form-label">Confirmez le mot de passe *</label>
              <input type="password" name="regConfirmPassword" id="regConfirmPassword" class="form-control" required>
            </div>
            <div id="formEtudiant">
              <div class="mb-3">
                <label for="ecole" class="form-label">Ecole *</label>
                <select id="ecole" name="ecole" class="form-select" required>
                  <option value="" disabled selected>Ecole ?</option>
                  <option value="ESA">ESA</option>
                  <option value="ESI">ESI</option>
                  <option value="ESAS">ESAS</option>
                  <option value="ESCAE">ESCAE</option>
                  <option value="ESMG">ESMG</option>
                  <option value="ESCPE">ESCPE</option>
                  <option value="ESTP">ESTP</option>
                  <option value="EPGE">EPGE</option>
                  <option value="EDP">EDP</option>
                  <option value="ESFPC">ESFPC</option>
                  <option value="IDSI">IDSI</option>
                  <option value="DESCOGEF">DESCOGEF</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="filiere" class="form-label">Filière *</label>
                <input type="text" name="filiere" id="filiere" class="form-control" placeholder="STIC. FCA...?">
              </div>
              <div class="mb-3">
                <label for="specialite" class="form-label">Specialité *</label>
                <input type="text" name="specialite" id="specialite" class="form-control" placeholder="INF,? PMSI ...?">
              </div>
              <div class="mb-3">
                <label for="niveau" class="form-label">Niveau *</label>
                <select id="niveau" name="niveau" class="form-select" required>
                  <option value="" disabled selected>niveau ?</option>
                  <option value="P1">P1</option>
                  <option value="P2">P2</option>
                  <option value="TS1">TS1</option>
                  <option value="TS2">TS2</option>
                  <option value="TS3">TS3</option>
                  <option value="ING1">ING1</option>
                  <option value="ING2">ING2</option>
                  <option value="ING3">ING3</option>
                  <option value="M1">M1</option>
                  <option value="M2">M2</option>
                  <option value="D1">D1</option>
                  <option value="D2">D2</option>
                  <option value="D3">D3</option>
                  <option value="D4">D4</option>
                </select>
              </div>
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
  function confirmDelete(idEtu) {
    Swal.fire({
      title: 'Supprimer cet étudiant ?',
      text: "Cette action est irréversible !",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Oui, supprimer',
      cancelButtonText: 'Annuler'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'del_student.php?idEtu=' + idEtu;
      }
    });
  }
  // gestion des errors
    document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const action = params.get('action');
    const status = params.get('status');
    const error  = params.get('error');

    if (error) {
      const messages = {
        invalid_id:       { title: 'ID invalide',           text: 'Aucun enregistrement ne correspond.',},
        email_exists:     { title: 'Email déjà utilisé',     text: 'Cet email existe déjà.'},
        matricule_exists: { title: 'Matricule déjà utilisé', text: 'Ce matricule existe déjà.'},
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
  // 1) Création de la fonction de pagination
  function setupPagination(perPage) {
    let $rows, total, pages, currentPage;

    // recalcul des données
    function recalc() {
      $rows       = $('#studentsTable tbody tr');
      total       = $rows.length;
      pages       = Math.ceil(total / perPage);
      currentPage = 1;
    }

    // affiche la page demandée (1-indexée)
    function showPage(page) {
      const start = (page - 1) * perPage;
      const end   = start + perPage;
      $rows.hide().slice(start, end).show();
      currentPage = page;
      renderPager();
    }

    // génère les boutons « Préc » / numéros / « Suiv »
    function renderPager() {
      const $pager = $('#pagination-container').empty();
      // si une seule page, on n’affiche rien
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
    $('#studentsTable tbody').html(newRowsHtml);
    pager.refresh();
  }
});


  //  Validation mot de passe / confirmation
  $('#addStudentForm').on('submit', function(e){
    const pass        = $('#newPassword').val().trim();
    const confirmPass = $('#newConfirmPassword').val().trim();
    if (pass !== confirmPass) {
      e.preventDefault();
      Swal.fire('Erreur', 'Les mots de passe ne correspondent pas.', 'error');
    }
  });

  //----------------------------------------------------------------
  $(function(){
    function refreshTable(){
      const search  = $('#searchInput').val().trim();
      const school  = $('#filterSchool').val();
      const levels   = $('#filterLevel').val();

      $.ajax({
        url: 'ajax_etudiant.php',
        dataType: 'json',
        data: { search, school, levels },
        success(resp) {
         // 1) Mettre à jour le compteur
        $('#totalCount').text(resp.total);

          // 2) Reconstruire le tbody
          const rows = resp.data.map(stu => `
            <tr>
              <td>${stu.matEtu}</td>
              <td>${stu.prenoms}</td>
              <td>${stu.nom}</td>
              <td>${stu.sexe}</td>
              <td>${stu.born}</td>
              <td>${stu.ecole}</td>
              <td>${stu.filiere}</td>
              <td>${stu.specialite}</td>
              <td>${stu.niveau}</td>
              <td>${stu.mail}</td>
              <td>${stu.tel}</td>
              <td>
                <button class="btn btn-sm btn-warning"
                      data-bs-toggle="modal"
                      data-bs-target="#editStudentModal${stu.idEtu}">
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn btn-sm btn-danger"
                      onclick="confirmDelete(${stu.idEtu})">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          `).join('');

          $('#studentsTable tbody').html(rows);

          // 3) Réinitialiser la pagination si vous l'utilisez
          pager.refresh();
          },
          error(err) {
            console.error('Erreur AJAX :', err);
          }
      });
    }

    // lier événements
    $('#searchInput, #filterSchool, #filterLevel')
      .on('input change', refreshTable);

    // chargement initial
    reloadTable(html);
  });
  </script>
</body>
</html>
