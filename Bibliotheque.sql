-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `Bibliotheque`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `Bibliotheque`;

-- Table User
CREATE TABLE `User` (
  `idUser`    INT(11)                  NOT NULL AUTO_INCREMENT,
  `usermail`  VARCHAR(100)             NOT NULL UNIQUE,
  `passwd`    VARCHAR(255)             NOT NULL,
  `roles`     ENUM('Admin','Etudiant')      NULL,
  `photo`     VARCHAR(255)             NULL,
  `nom`       VARCHAR(25)              NOT NULL,
  `prenom`    VARCHAR(25)              NOT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Etudiant
CREATE TABLE `Etudiant` (
  `idEtu`      INT(11)       NOT NULL AUTO_INCREMENT,
  `matEtu`     VARCHAR(50)   NOT NULL UNIQUE,
  `prenoms`    VARCHAR(100)  NOT NULL,
  `nom`        VARCHAR(100)  NOT NULL,
  `born`       DATE          NOT NULL,
  `ecole`      VARCHAR(100)  NOT NULL,
  `filiere`    VARCHAR(100)  NOT NULL,
  `specialite` VARCHAR(100)  NOT NULL,
  `niveau`     VARCHAR(50)   NOT NULL,
  `sexe`       ENUM('M','F') NOT NULL,
  `mail`       VARCHAR(100)  NOT NULL UNIQUE,
  `tel`        VARCHAR(20)   NOT NULL,
  `idUser`     INT(11)       NULL UNIQUE,
  PRIMARY KEY (`idEtu`),
  CONSTRAINT `fk_Etudiant_User`
    FOREIGN KEY (`idUser`)
    REFERENCES `User` (`idUser`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Administrateur
CREATE TABLE `Administrateur` (
  `idAdmin`  INT(11)       NOT NULL AUTO_INCREMENT,
  `matAdmin` VARCHAR(50)   NOT NULL UNIQUE,
  `prenoms`  VARCHAR(100)  NOT NULL,
  `nom`      VARCHAR(100)  NOT NULL,
  `born`     DATE          NOT NULL,
  `sexe`     ENUM('M','F') NOT NULL,
  `mail`     VARCHAR(100)  NOT NULL UNIQUE,
  `tel`      VARCHAR(20)   NOT NULL,
  `idUser`   INT(11)       NULL UNIQUE,
  PRIMARY KEY (`idAdmin`),
  CONSTRAINT `fk_Admin_User`
    FOREIGN KEY (`idUser`)
    REFERENCES `User` (`idUser`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Auteur
CREATE TABLE `Auteur` (
  `idAut`      INT(11)       NOT NULL AUTO_INCREMENT,
  `nom`        VARCHAR(100)  NOT NULL,
  `prenom`     VARCHAR(100)  NOT NULL,
  `born`       DATE          NOT NULL,
  `biographie` TEXT           NULL,
  `pays`       VARCHAR(50)   NOT NULL,
  `sexe`       ENUM('M','F') NOT NULL,
  `types`      VARCHAR(25)   NULL,
  PRIMARY KEY (`idAut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Editeur
CREATE TABLE `Editeur` (
  `idEdit`  INT(11)      NOT NULL AUTO_INCREMENT,
  `nom`     VARCHAR(100) NOT NULL,
  `pays`    VARCHAR(50)  NOT NULL,
  `siteweb` VARCHAR(255) NULL,
  PRIMARY KEY (`idEdit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Livre
CREATE TABLE `Livre` (
  `idLiv`        INT(11)      NOT NULL AUTO_INCREMENT,
  `titre`        VARCHAR(255) NOT NULL,
  `idAut`        INT(11)      NOT NULL,
  `idEdit`       INT(11)      NOT NULL,
  `genre`        VARCHAR(100) NOT NULL,
  `datepub`      DATE         NOT NULL,
  `disponible`   INT(8)       NULL,
  `nbExemplaire` INT(11)      NOT NULL,
  PRIMARY KEY (`idLiv`),
  KEY `idx_Livre_Auteur` (`idAut`),
  KEY `idx_Livre_Editeur` (`idEdit`),
  CONSTRAINT `fk_Livre_Auteur`
    FOREIGN KEY (`idAut`)
    REFERENCES `Auteur` (`idAut`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Livre_Editeur`
    FOREIGN KEY (`idEdit`)
    REFERENCES `Editeur` (`idEdit`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Emprunt
CREATE TABLE `Emprunt` (
  `idEmp`     INT(11) NOT NULL AUTO_INCREMENT,
  `idEtu`     INT(11) NOT NULL,
  `idLiv`     INT(11) NOT NULL,
  `dateEmp`   DATE    NOT NULL,
  `dateRetour`DATE    NOT NULL,
  `dateRendu` DATE    NULL,
  PRIMARY KEY (`idEmp`),
  KEY `idx_Emprunt_Etudiant` (`idEtu`),
  KEY `idx_Emprunt_Livre`    (`idLiv`),
  CONSTRAINT `fk_Emprunt_Etudiant`
    FOREIGN KEY (`idEtu`)
    REFERENCES `Etudiant` (`idEtu`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Emprunt_Livre`
    FOREIGN KEY (`idLiv`)
    REFERENCES `Livre` (`idLiv`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Document
CREATE TABLE `Document` (
  `idDoc`        INT(11)      NOT NULL AUTO_INCREMENT,
  `titre`        VARCHAR(255) NOT NULL,
  `description`  TEXT         NULL,
  `fichier`      VARCHAR(255) NOT NULL,
  `telechargable`TINYINT(1)   NOT NULL DEFAULT 0,
  `typeMime`     VARCHAR(100) NOT NULL,
  `categorie`    VARCHAR(50)  NOT NULL,
  `dateUpload`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idDoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
