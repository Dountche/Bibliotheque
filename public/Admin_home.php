<?php

// 1. Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>INP-HB - Gestion de bibliothèque</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Feuille de style personnalisée -->
  <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
  <!-- Header / Navbar -->
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
              <a class="nav-link active" href="Admin_home.php" style="color : #fff ! important"><i class="fas fa-home"></i> Accueil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Gestion_Etudiant.php"><i class="fa-solid fa-user-graduate"></i> Étudiants</a>
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
            <img src="./images/user.png" alt="User profil" class="rounded-circle me-2" style="width:40px; height:40px;">
            <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['user']['usermail']); ?></span>
            </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Contenu principal -->
  <main class="container my-4">
    <div class="text-center py-4">
      <h1 class="display-4" style="color: #3d3d3d;">INP-HB Gestion de Bibliothèque</h1>
      <p class="lead">Bienvenue sur votre plateforme de gestion de la bibliothèque.</p>
    </div>

    <!-- Section d'actions avec des cartes (cards) -->
    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <i class="fa-solid fa-user-graduate fa-3x mb-3" style="color: var(--primary-color);"></i>
            <h5 class="card-title">Gestion Étudiants</h5>
            <p class="card-text">Ajouter, modifier ou supprimer des étudiants.</p>
            <a href="Gestion_Etudiant.php" class="btn btn-warning">Voir plus</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <i class="fa-solid fa-book fa-3x mb-3" style="color: var(--accent-green);"></i>
            <h5 class="card-title">Gestion Livres</h5>
            <p class="card-text">Gérer l'inventaire des livres de la bibliothèque.</p>
            <a href="Gestion_Livre.php" class="btn btn-warning">Voir plus</a>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <i class="fa-solid fa-ticket fa-3x mb-3" style="color: var(--secondary-color);"></i>
            <h5 class="card-title">Gestion Emprunts</h5>
            <p class="card-text">Suivre et gérer les emprunts de livres.</p>
            <a href="GEstion_Emprunter.php" class="btn btn-warning">Voir plus</a>
          </div>
        </div>
      </div>
    </div>

    <div class="message">
      <h2 style="color: #3d3d3d;" >&emsp;&emsp; Un problème ?</h2>
      <br>
      <h5>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; Vous ouvez nous contacter en cas de  problème</h5>

      <form>
        <div class="form-group">
          <label for="nom">Votre nom :</label>
          <input type="text" id="nom" name="nom" placeholder="Votre nom">
        </div>
      
        <div class="form-group">
          <label for="email">Votre mail :</label>
          <input type="email" id="email" name="email" placeholder="Votre mail" required>
        </div>
      
        <div class="form-groupta">
          <label for="message">Exprimez-vous :</label>
          <textarea id="message" name="message" placeholder="Exprimez-vous !" required></textarea>
        </div>
      
        <button type="submit">Envoyer</button>
      </form>      
    </div>
    
  </main>

  <!-- Footer -->
  <?php include '../src/views/footer.php'; ?>

  <!-- Bootstrap Bundle JS (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
