<?php
session_start();

// Définit le paramètre par défaut : "login" 
$page = $_GET['page'] ?? 'login';

switch($page){
  case 'login':
    require_once __DIR__ . '/login.php';
    break;
  case 'gestion_etudiants':
    require_once __DIR__ . '/../src/controllers/Gestion_Etudiant.php';
    break;
  case 'A_home':
    require_once __DIR__ . '/Admin_home.php';
    break;
  case 'E_home':
    require_once __DIR__ . '/Etudiant_home.php';
    break;
  default:
    // Par défaut, on peut rediriger vers login ou afficher une erreur.
    require_once __DIR__ . '/login.php';
    break;
}
?>
