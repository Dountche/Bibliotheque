# Bibliothèque INP-HB

Ce projet est une plateforme web de gestion de bibliothèque pour l’INP-HB.  
Il permet :
- de gérer les utilisateurs (administrateurs, étudiants),
- d’ajouter/modifier/supprimer des livres, des auteurs, etc.
- de gérer l’emprunt et le retour des documents numériques.

## Prérequis

- PHP 8.0 ou supérieur
- MySQL/MariaDB
- XAMPP ou WAMP (si vous travaillez sous Windows)
- Composer (pour les dépendances PHP éventuelles)

## Installation en local

1. Cloner le dépôt :
   git clone https://github.com/Dountche/Bibliotheque.git
   cd Bibliotheque

2. Copier le fichier de configuration :

cp config/database.sample.php config/database.php

puis adapter les identifiants MySQL (hôte, utilisateur, mot de passe).


3. Importer la base de données :

mysql -u root -p Bibliotheque < Bibliotheque.sql


4. Démarrer Apache + MySQL (via XAMPP/WAMP).


5. Ouvrir votre navigateur à l’adresse :

http://localhost/Bibliotheque/public/

Structure du dépôt

Bibliotheque/
├── config/
│   └── database.php
├── public/
│   ├── css/
│   ├── images/
│   ├── js/
│   ├── documents/
│   ├── index.php
│   └── … (toutes les pages .php et ressources servies publiquement)
├── src/
│   └── views/         #  footer 
├── Bibliotheque.sql   # script de la base de données
└── README.md

Usage

Se connecter avec un compte Administrateur ou Étudiant.

Pour tout Administrateur : possibilité de CRUD sur les étudiants, auteurs, éditeurs, livres, emprunts, etc.

Pour un Étudiant : accès en lecture des documents, emprunt.

Auteur

Dountché Issa – BE PHP (Web2) TS STIC Ibfo2 2024-2025
