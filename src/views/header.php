<!-- footer.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>INP-HB - Gestion de bibliothèque</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Feuille de style personnalisée -->
  <link rel="stylesheet" href="../../public/css/styles.css">
  <!-- jQuery -->aa
  <script src="jquery-3.7.1.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
      <div class="container-fluid">
      <img src="../../public/images/icon.png" alt="Logo INP-HB" class="d-inline-block align-text-top" style="max-height: 50px;">
        <a class="navbar-brand" href="accueil.php">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" 
                aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="accueil.php"><i class="fas fa-home"></i> Accueil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="etudiant.php"><i class="fa-solid fa-user-graduate"></i> Étudiants</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="auteur.php"><i class="fa-solid fa-user"></i> Auteurs</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="editeur.php"><i class="fa-solid fa-users"></i> Éditeurs</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="livre.php"><i class="fa-solid fa-book"></i> Livres</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="emprunter.php"><i class="fa-solid fa-ticket"></i> Emprunts</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="library.php"><i class="fa-solid fa-book-atlas"></i> Librairie</a>
            </li>
          </ul>
          <!-- Profil de l'utilisateur connecté -->
          <div class="d-flex align-items-center">
            <img src="./images/user.png" alt="User profil" class="rounded-circle me-2" style="width:40px; height:40px;">
            <span class="fw-bold">Admin</span>
          </div>
        </div>
      </div>
    </nav>
</header>