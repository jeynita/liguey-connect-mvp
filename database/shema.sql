-- ============================================
-- LIGUEY CONNECT - SCHÉMA BASE DE DONNÉES
-- ============================================
-- Version: 1.0
-- Date: Janvier 2026
-- ============================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS liguey_connect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE liguey_connect;

-- ============================================
-- TABLE: users (Utilisateurs)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    type_utilisateur ENUM('demandeur', 'prestataire', 'recruteur', 'admin') NOT NULL,
    photo_profil VARCHAR(255) DEFAULT NULL,
    localisation VARCHAR(255),
    region VARCHAR(100),
    commune VARCHAR(100),
    quartier VARCHAR(100),
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL,
    INDEX idx_type_utilisateur (type_utilisateur),
    INDEX idx_localisation (region, commune),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: profils_prestataires (Profils des prestataires de services)
-- ============================================
CREATE TABLE profils_prestataires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    metier VARCHAR(100) NOT NULL,
    competences TEXT,
    annees_experience INT DEFAULT 0,
    tarif_horaire DECIMAL(10,2),
    description TEXT,
    disponibilite ENUM('immediat', 'sous_24h', 'sous_semaine', 'sur_rendez_vous') DEFAULT 'sur_rendez_vous',
    jours_disponibles SET('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'),
    horaires_debut TIME DEFAULT '08:00:00',
    horaires_fin TIME DEFAULT '18:00:00',
    certifications TEXT,
    note_moyenne DECIMAL(3,2) DEFAULT 0.00,
    nombre_avis INT DEFAULT 0,
    profil_verifie BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_metier (metier),
    INDEX idx_note (note_moyenne),
    INDEX idx_disponibilite (disponibilite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: profils_demandeurs (Profils des demandeurs d'emploi)
-- ============================================
CREATE TABLE profils_demandeurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    competences TEXT,
    experience_professionnelle TEXT,
    formations TEXT,
    diplomes TEXT,
    langues VARCHAR(255),
    cv_fichier VARCHAR(255),
    disponibilite ENUM('immediat', 'sous_1mois', 'sous_3mois') DEFAULT 'immediat',
    type_contrat_recherche SET('CDD', 'CDI', 'mission', 'stage', 'freelance'),
    salaire_souhaite DECIMAL(10,2),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_disponibilite (disponibilite),
    INDEX idx_type_contrat (type_contrat_recherche)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: offres_emploi (Offres d'emploi publiées)
-- ============================================
CREATE TABLE offres_emploi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruteur_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type_contrat ENUM('CDD', 'CDI', 'mission', 'stage', 'freelance') NOT NULL,
    secteur_activite VARCHAR(100),
    competences_requises TEXT,
    localisation VARCHAR(255) NOT NULL,
    region VARCHAR(100),
    commune VARCHAR(100),
    salaire_min DECIMAL(10,2),
    salaire_max DECIMAL(10,2),
    date_debut DATE,
    duree_contrat VARCHAR(50),
    nombre_postes INT DEFAULT 1,
    statut ENUM('active', 'pourvue', 'expiree', 'supprimee') DEFAULT 'active',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_expiration DATE,
    nombre_candidatures INT DEFAULT 0,
    FOREIGN KEY (recruteur_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type_contrat (type_contrat),
    INDEX idx_statut (statut),
    INDEX idx_localisation (region, commune),
    INDEX idx_date_creation (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: candidatures (Candidatures aux offres)
-- ============================================
CREATE TABLE candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    offre_id INT NOT NULL,
    demandeur_id INT NOT NULL,
    message_motivation TEXT,
    statut ENUM('en_attente', 'vue', 'acceptee', 'refusee') DEFAULT 'en_attente',
    date_candidature TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_reponse TIMESTAMP NULL,
    FOREIGN KEY (offre_id) REFERENCES offres_emploi(id) ON DELETE CASCADE,
    FOREIGN KEY (demandeur_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidature (offre_id, demandeur_id),
    INDEX idx_statut (statut),
    INDEX idx_date (date_candidature)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: demandes_services (Demandes de services de proximité)
-- ============================================
CREATE TABLE demandes_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    prestataire_id INT,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    categorie_service VARCHAR(100),
    localisation VARCHAR(255) NOT NULL,
    region VARCHAR(100),
    commune VARCHAR(100),
    quartier VARCHAR(100),
    budget_max DECIMAL(10,2),
    date_souhaitee DATE,
    urgence ENUM('urgent', 'normal', 'flexible') DEFAULT 'normal',
    statut ENUM('ouverte', 'en_cours', 'terminee', 'annulee') DEFAULT 'ouverte',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_completion TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prestataire_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_statut (statut),
    INDEX idx_categorie (categorie_service),
    INDEX idx_urgence (urgence),
    INDEX idx_localisation (region, commune)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: messages (Messagerie interne)
-- ============================================
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    sujet VARCHAR(255),
    contenu TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_destinataire (destinataire_id, lu),
    INDEX idx_date (date_envoi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: avis (Avis et évaluations)
-- ============================================
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluateur_id INT NOT NULL,
    evalue_id INT NOT NULL,
    note INT NOT NULL CHECK (note >= 1 AND note <= 5),
    commentaire TEXT,
    type_service VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluateur_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (evalue_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_avis (evaluateur_id, evalue_id),
    INDEX idx_evalue (evalue_id),
    INDEX idx_note (note),
    INDEX idx_date (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: notifications (Notifications utilisateurs)
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type_notification ENUM('nouveau_message', 'nouvelle_offre', 'candidature_acceptee', 'candidature_refusee', 'nouveau_avis', 'demande_service') NOT NULL,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT,
    lue BOOLEAN DEFAULT FALSE,
    lien VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_lue (user_id, lue),
    INDEX idx_date (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: categories_metiers (Catégories de métiers)
-- ============================================
CREATE TABLE categories_metiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icone VARCHAR(100),
    ordre_affichage INT DEFAULT 0,
    INDEX idx_ordre (ordre_affichage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERTION DES CATÉGORIES DE MÉTIERS
-- ============================================
INSERT INTO categories_metiers (nom, description, ordre_affichage) VALUES
('Plomberie', 'Installation et réparation de plomberie', 1),
('Électricité', 'Travaux électriques et installations', 2),
('Menuiserie', 'Travaux de menuiserie et ébénisterie', 3),
('Maçonnerie', 'Construction et rénovation', 4),
('Peinture', 'Peinture intérieure et extérieure', 5),
('Jardinage', 'Entretien espaces verts', 6),
('Mécanique', 'Réparation automobile et moto', 7),
('Informatique', 'Dépannage et services informatiques', 8),
('Couture', 'Couture et retouches', 9),
('Nettoyage', 'Services de nettoyage', 10),
('Immobilier', 'Services immobiliers', 11),
('Sécurité', 'Services de sécurité', 12),
('Transport', 'Services de transport', 13),
('Cuisine', 'Services de restauration', 14),
('Autre', 'Autres services', 99);

-- ============================================
-- VUES UTILES
-- ============================================

-- Vue: Liste des prestataires avec leur profil complet
CREATE VIEW v_prestataires_complets AS
SELECT 
    u.id,
    u.nom,
    u.prenom,
    u.telephone,
    u.email,
    u.photo_profil,
    u.localisation,
    u.region,
    u.commune,
    u.quartier,
    p.metier,
    p.competences,
    p.annees_experience,
    p.tarif_horaire,
    p.description,
    p.disponibilite,
    p.note_moyenne,
    p.nombre_avis,
    p.profil_verifie
FROM users u
INNER JOIN profils_prestataires p ON u.id = p.user_id
WHERE u.statut = 'actif' AND u.type_utilisateur = 'prestataire';

-- Vue: Offres d'emploi actives avec informations recruteur
CREATE VIEW v_offres_actives AS
SELECT 
    o.id,
    o.titre,
    o.description,
    o.type_contrat,
    o.secteur_activite,
    o.localisation,
    o.region,
    o.commune,
    o.salaire_min,
    o.salaire_max,
    o.date_creation,
    o.nombre_candidatures,
    u.nom AS recruteur_nom,
    u.prenom AS recruteur_prenom
FROM offres_emploi o
INNER JOIN users u ON o.recruteur_id = u.id
WHERE o.statut = 'active'
ORDER BY o.date_creation DESC;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger: Mettre à jour la note moyenne après ajout d'un avis
DELIMITER //
CREATE TRIGGER after_avis_insert
AFTER INSERT ON avis
FOR EACH ROW
BEGIN
    UPDATE profils_prestataires p
    INNER JOIN users u ON p.user_id = u.id
    SET 
        p.note_moyenne = (
            SELECT AVG(note) 
            FROM avis 
            WHERE evalue_id = NEW.evalue_id
        ),
        p.nombre_avis = (
            SELECT COUNT(*) 
            FROM avis 
            WHERE evalue_id = NEW.evalue_id
        )
    WHERE u.id = NEW.evalue_id;
END//
DELIMITER ;

-- Trigger: Incrémenter le nombre de candidatures
DELIMITER //
CREATE TRIGGER after_candidature_insert
AFTER INSERT ON candidatures
FOR EACH ROW
BEGIN
    UPDATE offres_emploi
    SET nombre_candidatures = nombre_candidatures + 1
    WHERE id = NEW.offre_id;
END//
DELIMITER ;

-- ============================================
-- FIN DU SCHÉMA
-- ============================================
