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