<?php

// 1. Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// 2. Connexion BDD
require_once __DIR__ . '/../config/database.php';

// 3. Messages inline pour erreurs de connexion
$error_message = '';
if (isset($_GET['error'])) {
  switch ($_GET['error']) {
      case 'login_failed':
          $error_message = "Email ou mot de passe incorrect.";
          break;
      case 'admin_key_incorrect':
          $error_message = "Clé admin incorrecte.";
          break;
  }
}

// 4. Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  // Récupérer role stocké en BDD, pas via formulaire
  $stmt = $pdo->prepare("SELECT * FROM User WHERE usermail = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['passwd'])) {
      $_SESSION['user'] = $user;
      if ($user['roles'] === 'Admin') {
        header('Location: Admin_home.php');
      } else {
        header('Location: Etudiant_home.php');
      }
      exit();
  } else {
      header('Location: login.php?error=login_failed');
      exit();
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion - INP-HB</title>
<!-- Bootstrap + FontAwesome + CSS perso -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="./css/styles.css" rel="stylesheet">
<!-- jQuery + SweetAlert2 -->
<script src="./js/jquery-3.7.1.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="accueil.php">
          <img src="./images/icon.png" alt="Logo INP-HB" style="max-height: 50px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false"
                aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </nav>
  </header>

<main class="container my-4">
  <div class="row justify-content-center"  style ="height: 400px; margin-top : 100px">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="text-center mb-4">Connexion</h2>
          <!-- Message d'erreur inline -->
          <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
          <?php endif; ?>
          <form method="POST" action="login.php" id="loginForm">
            <div class="mb-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Mot de passe</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-warning w-100">Se connecter</button>
          </form>
          <div class="mt-3 text-center">
            <span>Pas de compte ? </span>
            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">S'inscrire</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Popup succès après inscription -->
<!-- Modal d'inscription -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="registerForm" method="POST" action="register.php">
        <div class="modal-header">
          <h5 class="modal-title" id="registerModalLabel" style="font-size: 25px; font-weight: bolder;">Inscription</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Sélecteur de rôle pour l'inscription -->
          <div class="mb-3">
            <label for="registerRole" class="form-label">Vous êtes :</label>
            <select name="role" id="registerRole" class="form-select">
              <option value="etudiant" selected>Etudiant</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <!-- Champs communs -->
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
          <!-- Autres champs (sexe, date, téléphone, etc.) -->
          <div class="mb-3">
            <label for="sexe" class="form-label">Sexe *</label>
            <select id="sexe" name="sexe" class="form-select" required>
              <option value="" disabled selected>M/F</option>
              <option value="masculin">M</option>
              <option value="feminin">F</option>
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
          <!-- Champs spécifiques aux étudiants -->
          <div id="formEtudiant">
            <div class="mb-3">
              <label for="ecole" class="form-label">Ecole *</label>
              <select id="ecole" name="ecole" class="form-select">
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
              <select id="niveau" name="niveau" class="form-select">
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">S'inscrire</button>
        </div>
      </form>
    </div>
  </div>
</div>

  <!-- Footer -->
  <?php include '../src/views/footer.php'; ?>

    
  <!-- Bootstrap Bundle JS (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
  <!-- jQuery pour la gestion des validations et de l'affichage conditionnel -->
  <script>

$(document).ready(function(){
    // 2. Gestion du changement de rôle dans le modal d’inscription
    $('#registerRole').on('change', function(){
    if ($(this).val() === 'admin') {
      // masquer temporairement le modal pour éviter conflit
      $('#registerModal').modal('hide');
      // afficher SweetAlert2
      Swal.fire({
        title: 'Clé Admin',
        input: 'password',
        inputPlaceholder: 'Entrez la clé admin',
        showCancelButton: true,
        didOpen: () => {
          Swal.getInput().focus();
        }
      }).then((result) => {
        // réouvrir le modal
        $('#registerModal').modal('show');
        if (result.isConfirmed && result.value === 'admininphb@2025.beweb') {
          $('#formEtudiant').hide();
        } else {
          Swal.fire('Clé incorrecte', 'Vous restez en mode Étudiant', 'error');
          $('#registerRole').val('etudiant');
          $('#formEtudiant').show();
        }
      });
    } else {
      $('#formEtudiant').show();
    }
  });

  //  Validation mot de passe / confirmation
  $('#registerForm').on('submit', function(e){
    const pass        = $('#regPassword').val().trim();
    const confirmPass = $('#regConfirmPassword').val().trim();
    if (pass !== confirmPass) {
      e.preventDefault();
      Swal.fire('Erreur', 'Les mots de passe ne correspondent pas.', 'error');
    }
  });

  //  Validation login
  $('#loginForm').on('submit', function(e){
    const email = $('#email').val().trim();
    const pwd   = $('#password').val().trim();
    if (!email || !pwd) {
      e.preventDefault();
      Swal.fire('Champs manquants', 'Veuillez saisir email et mot de passe.', 'warning');
    }
  });

});

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const succes = params.get('success');
    const error  = params.get('error');

    if (error) {
      const messages = {
        email_exists:      { title: 'Email déjà utilisé',     text: 'Cet email existe déjà.'},
        password_mismatch: { title: 'mot de passe Incorrect', text: ' Les mots de passes ne correspondent pas.'},
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
    if (succes === 'registered' ) {
      Swal.fire('Inscription réussie', 'Vous pouvez maintenant vous connecter.', 'success');
      history.replaceState(null, '', window.location.pathname);
    }
  });

</script>
