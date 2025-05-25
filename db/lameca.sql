CREATE TABLE Utilisateur (
    idUtilisateur INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    pays VARCHAR(100),
    ville VARCHAR(100),
    code_postal VARCHAR(20),
    date_naissance DATE,
    photo VARCHAR(150),
    genre ENUM('Homme', 'Femme', 'Autre'),
    role ENUM('Admin', 'Artisan', 'Client') NOT NULL
);

CREATE TABLE Administrateur (
    idAdministrateur INT PRIMARY KEY,
    adresseProfessionnelle TEXT,
    FOREIGN KEY (idAdministrateur) REFERENCES Utilisateur(idUtilisateur)
);

CREATE TABLE Artisan (
    idArtisan INT PRIMARY KEY,
    certification TEXT,
    specialite VARCHAR(255),
    portfolio TEXT,
    statut_verification BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (idArtisan) REFERENCES Utilisateur(idUtilisateur)
);

CREATE TABLE Client (
    idClient INT PRIMARY KEY,
    FOREIGN KEY (idClient) REFERENCES Utilisateur(idUtilisateur)
);

CREATE TABLE Oeuvre (
    idOeuvre INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    caracteristiques TEXT,
    datePublication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    disponibilite BOOLEAN DEFAULT TRUE,
    idArtisan INT,
    FOREIGN KEY (idArtisan) REFERENCES Artisan(idArtisan)
);

CREATE TABLE Photooeuvre (
    idPhoto INT PRIMARY KEY AUTO_INCREMENT,
    url VARCHAR(500) NOT NULL,
    idOeuvre INT,
    FOREIGN KEY (idOeuvre) REFERENCES Oeuvre(idOeuvre) ON DELETE CASCADE
);

CREATE TABLE Commande (
    idCommande INT PRIMARY KEY AUTO_INCREMENT,
    nombreArticles INT NOT NULL,
    dateCommande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('En attente', 'Confirmée', 'Expédiée', 'Livrée') DEFAULT 'En attente',
    idClient INT,
    idOeuvre INT,
    FOREIGN KEY (idClient) REFERENCES Client(idClient),
    FOREIGN KEY (idOeuvre) REFERENCES Oeuvre(idOeuvre)
);

CREATE TABLE Aimer (
    idAimer INT PRIMARY KEY AUTO_INCREMENT,
    dateAimer TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    idClient INT,
    idOeuvre INT,
    FOREIGN KEY (idClient) REFERENCES Client(idClient),
    FOREIGN KEY (idOeuvre) REFERENCES Oeuvre(idOeuvre)
);

CREATE TABLE Avisoeuvre (
    idAvis INT PRIMARY KEY AUTO_INCREMENT,
    dateAvisoeuvre TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notation INT, 
    message TEXT,
    idClient INT,
    idOeuvre INT,
    CONSTRAINT chk_notation CHECK (notation BETWEEN 1 AND 5),
    FOREIGN KEY (idClient) REFERENCES Client(idClient),
    FOREIGN KEY (idOeuvre) REFERENCES Oeuvre(idOeuvre)
);

CREATE TABLE Avisartisan (
    idAvis INT PRIMARY KEY AUTO_INCREMENT,
    dateAvisoeuvre TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notation INT,
    message TEXT,
    idClient INT,
    idArtisan INT,
    CONSTRAINT chk_notation_artisan CHECK (notation BETWEEN 1 AND 5),
    FOREIGN KEY (idClient) REFERENCES Client(idClient),
    FOREIGN KEY (idArtisan) REFERENCES Artisan(idArtisan)
);

CREATE TABLE Signalement (
    idSignalement INT PRIMARY KEY AUTO_INCREMENT,
    idSignaleur INT,
    idCible INT,
    typeCible ENUM('Utilisateur', 'Oeuvre') NOT NULL,
    motif TEXT NOT NULL,
    statut ENUM('En attente', 'Résolu', 'Rejeté') DEFAULT 'En attente',
    dateSignalement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idSignaleur) REFERENCES Utilisateur(idUtilisateur)
);

CREATE TABLE Evenement (
    idEvenement INT PRIMARY KEY AUTO_INCREMENT,
    nomEvenement VARCHAR(255) NOT NULL,
    description TEXT,
    dateDebut DATE NOT NULL,
    dateFin DATE,
    lieu VARCHAR(255),
    mis_en_avant BOOLEAN DEFAULT FALSE,
    idArtisan INT,
    FOREIGN KEY (idArtisan) REFERENCES Artisan(idArtisan)
);

CREATE TABLE Clientevenement (
    idClientevenement INT PRIMARY KEY AUTO_INCREMENT,
    idClient INT,
    idEvenement INT,
    FOREIGN KEY (idClient) REFERENCES Client(idClient),
    FOREIGN KEY (idEvenement) REFERENCES Evenement(idEvenement)
);

CREATE TABLE Message (
    idMessage INT PRIMARY KEY AUTO_INCREMENT,
    idEmetteur INT,
    idRecepteur INT,
    contenu TEXT NOT NULL,
    dateEnvoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('Lu', 'Non Lu') DEFAULT 'Non Lu',
    FOREIGN KEY (idEmetteur) REFERENCES Utilisateur(idUtilisateur),
    FOREIGN KEY (idRecepteur) REFERENCES Utilisateur(idUtilisateur)
);

CREATE TABLE FAQ (
    idFaq INT PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    reponse TEXT NOT NULL
);

-- Ajouter un champ date_creation à la table Utilisateur s'il n'existe pas déjà
ALTER TABLE Utilisateur 
ADD COLUMN date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Créer une table pour stocker les communications avec les clients
CREATE TABLE communication (
    idCommunication INT PRIMARY KEY AUTO_INCREMENT,
    idClient INT NOT NULL,
    type ENUM('email', 'sms', 'appel', 'autre') NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idClient) REFERENCES Client(idClient) ON DELETE CASCADE
);

-- Créer une table pour stocker les préférences des clients
CREATE TABLE preference_client (
    idPreference INT PRIMARY KEY AUTO_INCREMENT,
    idClient INT NOT NULL,
    recevoir_newsletter BOOLEAN DEFAULT TRUE,
    recevoir_promotions BOOLEAN DEFAULT TRUE,
    theme_prefere VARCHAR(100),
    derniere_connexion TIMESTAMP,
    FOREIGN KEY (idClient) REFERENCES Client(idClient) ON DELETE CASCADE
);

-- Créer une table pour les tags/étiquettes de clients
CREATE TABLE tag_client (
    idTag INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    couleur VARCHAR(7) DEFAULT '#000000',
    description TEXT
);

-- Créer une table de relation entre clients et tags
CREATE TABLE client_tag (
    idClient INT,
    idTag INT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (idClient, idTag),
    FOREIGN KEY (idClient) REFERENCES Client(idClient) ON DELETE CASCADE,
    FOREIGN KEY (idTag) REFERENCES tag_client(idTag) ON DELETE CASCADE
);

-- Ajouter quelques tags par défaut
INSERT INTO tag_client (nom, couleur, description) VALUES
('VIP', '#FFD700', 'Clients importants avec traitement privilégié'),
('Inactif', '#808080', 'Clients n\'ayant pas commandé depuis plus de 6 mois'),
('Nouveau', '#32CD32', 'Clients inscrits récemment'),
('Grand compte', '#1E90FF', 'Clients ayant dépensé plus de 1000€');









-- Table pour les favoris d'événements
CREATE TABLE favoris_evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idClient INT NOT NULL,
    idEvenement INT NOT NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idClient) REFERENCES Utilisateur(idUtilisateur) ON DELETE CASCADE,
    FOREIGN KEY (idEvenement) REFERENCES Evenement(idEvenement) ON DELETE CASCADE,
    UNIQUE KEY unique_favori (idClient, idEvenement)
);

-- Table pour les logs d'actions
CREATE TABLE log_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    event_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_event_id (event_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES Utilisateur(idUtilisateur) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES Evenement(idEvenement) ON DELETE SET NULL
);

-- Optionnel : Table pour stocker les notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    title VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES Utilisateur(idUtilisateur) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created_at (created_at)
);

-- Optionnel : Table pour les paramètres d'événements
CREATE TABLE evenement_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idEvenement INT NOT NULL,
    max_participants INT DEFAULT 100,
    require_approval BOOLEAN DEFAULT FALSE,
    allow_cancellation_hours INT DEFAULT 2,
    send_reminders BOOLEAN DEFAULT TRUE,
    reminder_hours_before INT DEFAULT 24,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idEvenement) REFERENCES Evenement(idEvenement) ON DELETE CASCADE,
    UNIQUE KEY unique_event_settings (idEvenement)
);





-- Ajouter une colonne date_inscription à la table Clientevenement
-- (Optionnel - seulement si vous voulez une vraie date d'inscription)

ALTER TABLE Clientevenement 
ADD COLUMN date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Mettre à jour les enregistrements existants avec des dates simulées
-- (Les nouvelles inscriptions auront automatiquement la date actuelle)

UPDATE Clientevenement ce
JOIN Evenement e ON ce.idEvenement = e.idEvenement
SET ce.date_inscription = DATE_SUB(e.dateDebut, INTERVAL FLOOR(RAND() * 30 + 1) DAY)
WHERE ce.date_inscription IS NULL;










-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 22 avr. 2025 à 18:04
-- Version du serveur : 8.4.0
-- Version de PHP : 8.2.12
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;
--
-- Base de données : `lameca`
--

-- --------------------------------------------------------
--
-- Structure de la table `administrateur`
--

CREATE TABLE `administrateur` (
  `idAdministrateur` int NOT NULL,
  `adresseProfessionnelle` text
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
--
-- Déchargement des données de la table `administrateur`
--

INSERT INTO `administrateur` (`idAdministrateur`, `adresseProfessionnelle`)
VALUES (1, '123 Rue de Administration, Paris, France');
-- --------------------------------------------------------
--
-- Structure de la table `aimer`
--

CREATE TABLE `aimer` (
  `idAimer` int NOT NULL,
  `dateAimer` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `idClient` int DEFAULT NULL,
  `idOeuvre` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `artisan`
--

CREATE TABLE `artisan` (
  `idArtisan` int NOT NULL,
  `certification` text,
  `specialite` varchar(255) DEFAULT NULL,
  `portfolio` text,
  `statut_verification` tinyint(1) DEFAULT '0'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `avisartisan`
--

CREATE TABLE `avisartisan` (
  `idAvis` int NOT NULL,
  `dateAvisoeuvre` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notation` int DEFAULT NULL,
  `message` text,
  `idClient` int DEFAULT NULL,
  `idArtisan` int DEFAULT NULL
);
-- --------------------------------------------------------
--
-- Structure de la table `avisoeuvre`
--

CREATE TABLE `avisoeuvre` (
  `idAvis` int NOT NULL,
  `dateAvisoeuvre` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notation` int DEFAULT NULL,
  `message` text,
  `idClient` int DEFAULT NULL,
  `idOeuvre` int DEFAULT NULL
);
-- --------------------------------------------------------
--
-- Structure de la table `client`
--

CREATE TABLE `client` (`idClient` int NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `clientevenement`
--

CREATE TABLE `clientevenement` (
  `idClientevenement` int NOT NULL,
  `idClient` int DEFAULT NULL,
  `idEvenement` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `idCommande` int NOT NULL,
  `nombreArticles` int NOT NULL,
  `dateCommande` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('En attente', 'Confirmée', 'Expédiée', 'Livrée') DEFAULT 'En attente',
  `idClient` int DEFAULT NULL,
  `idOeuvre` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `idEvenement` int NOT NULL,
  `nomEvenement` varchar(255) NOT NULL,
  `description` text,
  `dateDebut` date NOT NULL,
  `dateFin` date DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `mis_en_avant` tinyint(1) DEFAULT '0',
  `idArtisan` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `faq`
--

CREATE TABLE `faq` (
  `idFaq` int NOT NULL,
  `question` text NOT NULL,
  `reponse` text NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `idMessage` int NOT NULL,
  `idEmetteur` int DEFAULT NULL,
  `idRecepteur` int DEFAULT NULL,
  `contenu` text NOT NULL,
  `dateEnvoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('Lu', 'Non Lu') DEFAULT 'Non Lu'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `oeuvre`
--

CREATE TABLE `oeuvre` (
  `idOeuvre` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `prix` decimal(10, 2) NOT NULL,
  `caracteristiques` text,
  `datePublication` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `disponibilite` tinyint(1) DEFAULT '1',
  `idArtisan` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `signalement`
--

CREATE TABLE `signalement` (
  `idSignalement` int NOT NULL,
  `idSignaleur` int DEFAULT NULL,
  `idCible` int DEFAULT NULL,
  `typeCible` enum('Utilisateur', 'Oeuvre') NOT NULL,
  `motif` text NOT NULL,
  `statut` enum('En attente', 'Résolu', 'Rejeté') DEFAULT 'En attente',
  `dateSignalement` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `idUtilisateur` int NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text,
  `pays` varchar(100) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `photo` varchar(150) DEFAULT NULL,
  `genre` enum('Homme', 'Femme', 'Autre') DEFAULT NULL,
  `role` enum('Admin', 'Artisan', 'Client') NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (
    `idUtilisateur`,
    `nom`,
    `prenom`,
    `email`,
    `mot_de_passe`,
    `telephone`,
    `adresse`,
    `pays`,
    `ville`,
    `code_postal`,
    `date_naissance`,
    `photo`,
    `genre`,
    `role`
  )
VALUES (
    1,
    'Soukhouna',
    'Mouhamadou',
    'mouhamedprgramer@gmail.com',
    '$2y$10$fLK8s7ZDnM.1lE7XMP.J6OuPbQ.DPUVKBo7rENnQY7gYq0xAzsKJy',
    '0751245296',
    '92',
    'France',
    'Paris',
    '75000',
    '1990-12-12',
    'mouhamadou_soukhouna.jpg',
    'Homme',
    'Admin'
  );
--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateur`
--
ALTER TABLE `administrateur`
ADD PRIMARY KEY (`idAdministrateur`);
--
-- Index pour la table `aimer`
--
ALTER TABLE `aimer`
ADD PRIMARY KEY (`idAimer`),
  ADD KEY `idClient` (`idClient`),
  ADD KEY `idOeuvre` (`idOeuvre`);
--
-- Index pour la table `artisan`
--
ALTER TABLE `artisan`
ADD PRIMARY KEY (`idArtisan`);
--
-- Index pour la table `avisartisan`
--
ALTER TABLE `avisartisan`
ADD PRIMARY KEY (`idAvis`),
  ADD KEY `idClient` (`idClient`),
  ADD KEY `idArtisan` (`idArtisan`);
--
-- Index pour la table `avisoeuvre`
--
ALTER TABLE `avisoeuvre`
ADD PRIMARY KEY (`idAvis`),
  ADD KEY `idClient` (`idClient`),
  ADD KEY `idOeuvre` (`idOeuvre`);
--
-- Index pour la table `client`
--
ALTER TABLE `client`
ADD PRIMARY KEY (`idClient`);
--
-- Index pour la table `clientevenement`
--
ALTER TABLE `clientevenement`
ADD PRIMARY KEY (`idClientevenement`),
  ADD KEY `idClient` (`idClient`),
  ADD KEY `idEvenement` (`idEvenement`);
--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
ADD PRIMARY KEY (`idCommande`),
  ADD KEY `idClient` (`idClient`),
  ADD KEY `idOeuvre` (`idOeuvre`);
--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
ADD PRIMARY KEY (`idEvenement`),
  ADD KEY `idArtisan` (`idArtisan`);
--
-- Index pour la table `faq`
--
ALTER TABLE `faq`
ADD PRIMARY KEY (`idFaq`);
--
-- Index pour la table `message`
--
ALTER TABLE `message`
ADD PRIMARY KEY (`idMessage`),
  ADD KEY `idEmetteur` (`idEmetteur`),
  ADD KEY `idRecepteur` (`idRecepteur`);
--
-- Index pour la table `oeuvre`
--
ALTER TABLE `oeuvre`
ADD PRIMARY KEY (`idOeuvre`),
  ADD KEY `idArtisan` (`idArtisan`);
--
-- Index pour la table `signalement`
--
ALTER TABLE `signalement`
ADD PRIMARY KEY (`idSignalement`),
  ADD KEY `idSignaleur` (`idSignaleur`);
--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
ADD PRIMARY KEY (`idUtilisateur`),
  ADD UNIQUE KEY `email` (`email`);
--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `aimer`
--
ALTER TABLE `aimer`
MODIFY `idAimer` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `avisartisan`
--
ALTER TABLE `avisartisan`
MODIFY `idAvis` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `avisoeuvre`
--
ALTER TABLE `avisoeuvre`
MODIFY `idAvis` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `clientevenement`
--
ALTER TABLE `clientevenement`
MODIFY `idClientevenement` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
MODIFY `idCommande` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
MODIFY `idEvenement` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `faq`
--
ALTER TABLE `faq`
MODIFY `idFaq` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
MODIFY `idMessage` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `oeuvre`
--
ALTER TABLE `oeuvre`
MODIFY `idOeuvre` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `signalement`
--
ALTER TABLE `signalement`
MODIFY `idSignalement` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
MODIFY `idUtilisateur` int NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;
--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `administrateur`
--
ALTER TABLE `administrateur`
ADD CONSTRAINT `administrateur_ibfk_1` FOREIGN KEY (`idAdministrateur`) REFERENCES `utilisateur` (`idUtilisateur`);
--
-- Contraintes pour la table `aimer`
--
ALTER TABLE `aimer`
ADD CONSTRAINT `aimer_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`),
  ADD CONSTRAINT `aimer_ibfk_2` FOREIGN KEY (`idOeuvre`) REFERENCES `oeuvre` (`idOeuvre`);
--
-- Contraintes pour la table `artisan`
--
ALTER TABLE `artisan`
ADD CONSTRAINT `artisan_ibfk_1` FOREIGN KEY (`idArtisan`) REFERENCES `utilisateur` (`idUtilisateur`);
--
-- Contraintes pour la table `avisartisan`
--
ALTER TABLE `avisartisan`
ADD CONSTRAINT `avisartisan_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`),
  ADD CONSTRAINT `avisartisan_ibfk_2` FOREIGN KEY (`idArtisan`) REFERENCES `artisan` (`idArtisan`);
--
-- Contraintes pour la table `avisoeuvre`
--
ALTER TABLE `avisoeuvre`
ADD CONSTRAINT `avisoeuvre_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`),
  ADD CONSTRAINT `avisoeuvre_ibfk_2` FOREIGN KEY (`idOeuvre`) REFERENCES `oeuvre` (`idOeuvre`);
--
-- Contraintes pour la table `client`
--
ALTER TABLE `client`
ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `utilisateur` (`idUtilisateur`);
--
-- Contraintes pour la table `clientevenement`
--
ALTER TABLE `clientevenement`
ADD CONSTRAINT `clientevenement_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`),
  ADD CONSTRAINT `clientevenement_ibfk_2` FOREIGN KEY (`idEvenement`) REFERENCES `evenement` (`idEvenement`);
--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`idClient`),
  ADD CONSTRAINT `commande_ibfk_2` FOREIGN KEY (`idOeuvre`) REFERENCES `oeuvre` (`idOeuvre`);
--
-- Contraintes pour la table `evenement`
--
ALTER TABLE `evenement`
ADD CONSTRAINT `evenement_ibfk_1` FOREIGN KEY (`idArtisan`) REFERENCES `artisan` (`idArtisan`);
--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`idEmetteur`) REFERENCES `utilisateur` (`idUtilisateur`),
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`idRecepteur`) REFERENCES `utilisateur` (`idUtilisateur`);
--
-- Contraintes pour la table `oeuvre`
--
ALTER TABLE `oeuvre`
ADD CONSTRAINT `oeuvre_ibfk_1` FOREIGN KEY (`idArtisan`) REFERENCES `artisan` (`idArtisan`);
--
-- Contraintes pour la table `signalement`
--
ALTER TABLE `signalement`
ADD CONSTRAINT `signalement_ibfk_1` FOREIGN KEY (`idSignaleur`) REFERENCES `utilisateur` (`idUtilisateur`);
COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;