-- ============================================
-- LIGUEY CONNECT - DONNÉES DE TEST
-- ============================================
-- Version: 1.0
-- Date: Janvier 2026
-- ============================================

USE liguey_connect;

-- ============================================
-- UTILISATEURS DE TEST
-- ============================================

-- Mot de passe pour tous: "Liguey2025!" (hashé avec password_hash)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- PRESTATAIRES
INSERT INTO users (nom, prenom, telephone, email, mot_de_passe, type_utilisateur, localisation, region, commune, quartier, statut) VALUES
('Diallo', 'Mamadou', '77 123 45 67', 'mamadou.diallo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'prestataire', 'Guédiawaye, Dakar', 'Dakar', 'Guédiawaye', 'Médina Gounass', 'actif'),
('Sow', 'Ibrahima', '77 234 56 78', 'ibrahima.sow@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'prestataire', 'Pikine, Dakar', 'Dakar', 'Pikine', 'Thiaroye', 'actif'),
('Sarr', 'Moussa', '77 345 67 89', 'moussa.sarr@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'prestataire', 'Rufisque, Dakar', 'Dakar', 'Rufisque', 'Centre', 'actif'),
('Fall', 'Amadou', '77 456 78 90', 'amadou.fall@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'prestataire', 'Parcelles Assainies, Dakar', 'Dakar', 'Dakar', 'Parcelles', 'actif'),
('Gueye', 'Cheikh', '77 567 89 01', 'cheikh.gueye@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'prestataire', 'Mbao, Dakar', 'Dakar', 'Pikine', 'Mbao', 'actif');

-- DEMANDEURS D'EMPLOI
INSERT INTO users (nom, prenom, telephone, email, mot_de_passe, type_utilisateur, localisation, region, commune, statut) VALUES
('Ndiaye', 'Fatou', '76 987 65 43', 'fatou.ndiaye@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'demandeur', 'Plateau, Dakar', 'Dakar', 'Dakar', 'actif'),
('Ba', 'Aissatou', '76 876 54 32', 'aissatou.ba@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'demandeur', 'Grand Yoff, Dakar', 'Dakar', 'Dakar', 'actif'),
('Diouf', 'Omar', '76 765 43 21', 'omar.diouf@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'demandeur', 'Parcelles Assainies, Dakar', 'Dakar', 'Dakar', 'actif');

-- RECRUTEURS
INSERT INTO users (nom, prenom, telephone, email, mot_de_passe, type_utilisateur, localisation, region, commune, statut) VALUES
('Entreprise ABC', 'SARL', '33 821 00 00', 'recrutement@abc.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruteur', 'Almadies, Dakar', 'Dakar', 'Dakar', 'actif'),
('Tech Solutions', 'Sénégal', '33 822 11 11', 'rh@techsolutions.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruteur', 'Point E, Dakar', 'Dakar', 'Dakar', 'actif');

-- ADMINISTRATEUR
INSERT INTO users (nom, prenom, telephone, email, mot_de_passe, type_utilisateur, localisation, region, commune, statut) VALUES
('Admin', 'Liguey Connect', '33 800 00 00', 'admin@ligueyconnect.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Dakar', 'Dakar', 'Dakar', 'actif');

-- ============================================
-- PROFILS PRESTATAIRES
-- ============================================

INSERT INTO profils_prestataires (user_id, metier, competences, annees_experience, tarif_horaire, description, disponibilite, jours_disponibles, note_moyenne, nombre_avis, profil_verifie) VALUES
(1, 'Plomberie', 'Installation sanitaire, Réparation fuites, Débouchage canalisations, Installation chauffe-eau', 10, 5000.00, 
'Plombier professionnel avec 10 ans d\'expérience. Intervention rapide, travail soigné. Spécialiste des installations et réparations. Devis gratuit.', 
'immediat', 'lundi,mardi,mercredi,jeudi,vendredi,samedi', 4.80, 15, TRUE),

(2, 'Électricité', 'Installation électrique, Réparation pannes, Dépannage urgence, Installation climatisation', 8, 6000.00, 
'Électricien qualifié. Interventions rapides pour tous vos problèmes électriques. Installation et dépannage. Disponible 7j/7.', 
'immediat', 'lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche', 4.60, 12, TRUE),

(3, 'Menuiserie', 'Fabrication meubles, Portes et fenêtres, Agencement, Réparation meubles', 15, 7500.00, 
'Menuisier ébéniste avec 15 ans d\'expérience. Fabrication sur mesure, réparation, agencement. Travail de qualité garanti.', 
'sous_24h', 'lundi,mardi,mercredi,jeudi,vendredi,samedi', 4.90, 20, TRUE),

(4, 'Maçonnerie', 'Construction, Rénovation, Carrelage, Plâtrerie, Peinture', 12, 8000.00, 
'Maçon professionnel. Construction, rénovation, carrelage. Équipe disponible pour petits et grands chantiers.', 
'sous_semaine', 'lundi,mardi,mercredi,jeudi,vendredi', 4.50, 18, TRUE),

(5, 'Informatique', 'Dépannage PC, Installation logiciels, Réseaux, Maintenance', 6, 4500.00, 
'Technicien informatique. Dépannage rapide ordinateurs et réseaux. Installation et configuration. Service à domicile.', 
'immediat', 'lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche', 4.70, 10, TRUE);

-- ============================================
-- PROFILS DEMANDEURS D'EMPLOI
-- ============================================

INSERT INTO profils_demandeurs (user_id, competences, experience_professionnelle, formations, diplomes, langues, disponibilite, type_contrat_recherche, salaire_souhaite) VALUES
(6, 'Secrétariat, Bureautique, Accueil, Gestion administrative', 
'3 ans comme secrétaire chez ABC SARL. Gestion agenda, accueil clients, rédaction documents.', 
'BTS Secrétariat Bureautique - ESP Dakar', 
'Baccalauréat, BTS Secrétariat', 
'Français, Wolof, Anglais basique', 
'immediat', 'CDD,CDI', 150000.00),

(7, 'Comptabilité, Gestion, Excel, Logiciels comptables', 
'2 ans comme assistante comptable. Saisie, rapprochements bancaires, déclarations.', 
'Licence en Comptabilité - UCAD', 
'Baccalauréat, Licence Comptabilité', 
'Français, Wolof', 
'immediat', 'CDI,CDD', 180000.00),

(8, 'Commercial, Vente, Relation client, Marketing', 
'5 ans dans la vente. Prospection, fidélisation clients, atteinte objectifs.', 
'Formation commerciale - CESAG', 
'Baccalauréat', 
'Français, Wolof, Anglais', 
'sous_1mois', 'CDI', 200000.00);

-- ============================================
-- OFFRES D'EMPLOI
-- ============================================

INSERT INTO offres_emploi (recruteur_id, titre, description, type_contrat, secteur_activite, competences_requises, localisation, region, commune, salaire_min, salaire_max, date_debut, duree_contrat, nombre_postes, statut, date_expiration) VALUES
(9, 'Secrétaire Bilingue H/F', 
'Nous recherchons un(e) secrétaire bilingue pour notre siège à Dakar. Missions: Accueil clients, gestion agenda, rédaction courriers, organisation réunions. Maîtrise français et anglais obligatoire.', 
'CDI', 'Administration', 'Secrétariat, Bureautique, Anglais, Organisation', 
'Almadies, Dakar', 'Dakar', 'Dakar', 150000.00, 200000.00, '2026-02-15', 'Indéterminée', 1, 'active', '2026-02-28'),

(9, 'Comptable Junior', 
'ABC SARL recrute un comptable junior. Missions: Saisie comptable, rapprochements bancaires, déclarations fiscales. Expérience 1-2 ans souhaitée.', 
'CDD', 'Finance', 'Comptabilité, Excel, Logiciels comptables', 
'Plateau, Dakar', 'Dakar', 'Dakar', 180000.00, 220000.00, '2026-02-10', '6 mois renouvelable', 1, 'active', '2026-02-20'),

(10, 'Développeur Web Junior', 
'Tech Solutions cherche développeur web junior. Technologies: HTML, CSS, JavaScript, PHP. Formation assurée. Débutants acceptés.', 
'CDD', 'Informatique', 'HTML, CSS, JavaScript, PHP', 
'Point E, Dakar', 'Dakar', 'Dakar', 200000.00, 300000.00, '2026-03-01', '12 mois', 2, 'active', '2026-03-15'),

(10, 'Commercial Terrain H/F', 
'Recherche commercial dynamique pour prospection B2B. Secteur IT. Permis de conduire requis. Commission attractive sur ventes.', 
'CDI', 'Commercial', 'Vente, Prospection, Permis B', 
'Dakar, toutes communes', 'Dakar', 'Dakar', 150000.00, 300000.00, '2026-02-20', 'Indéterminée', 3, 'active', '2026-03-05'),

(9, 'Maçon Qualifié', 
'Nous recherchons maçon qualifié pour chantier rénovation. Expérience 5 ans minimum. Travail en équipe. Mission 3 mois.', 
'mission', 'BTP', 'Maçonnerie, Carrelage, Lecture plans', 
'Guédiawaye, Dakar', 'Dakar', 'Guédiawaye', 8000.00, 10000.00, '2026-02-05', '3 mois', 2, 'active', '2026-02-15');

-- ============================================
-- DEMANDES DE SERVICES
-- ============================================

INSERT INTO demandes_services (client_id, titre, description, categorie_service, localisation, region, commune, quartier, budget_max, date_souhaitee, urgence, statut) VALUES
(6, 'Réparation fuite d\'eau urgente', 
'Grosse fuite sous l\'évier de cuisine. Besoin d\'intervention rapide aujourd\'hui si possible.', 
'Plomberie', 'Médina, Dakar', 'Dakar', 'Dakar', 'Médina', 15000.00, CURDATE(), 'urgent', 'ouverte'),

(7, 'Installation climatiseur', 
'Installation d\'un climatiseur split dans chambre. Climatiseur déjà acheté. Besoin installation et branchement électrique.', 
'Électricité', 'Grand Yoff, Dakar', 'Dakar', 'Dakar', 'Grand Yoff', 50000.00, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'normal', 'ouverte'),

(8, 'Fabrication armoire sur mesure', 
'Besoin d\'une armoire 2m x 1,5m x 0,6m en bois pour chambre. Devis souhaité avant travaux.', 
'Menuiserie', 'Parcelles Assainies, Dakar', 'Dakar', 'Dakar', 'Parcelles', 150000.00, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'flexible', 'ouverte'),

(9, 'Rénovation salle de bain', 
'Rénovation complète salle de bain 3m². Carrelage, plomberie, peinture. Devis détaillé demandé.', 
'Maçonnerie', 'Almadies, Dakar', 'Dakar', 'Dakar', 'Almadies', 500000.00, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'normal', 'ouverte'),

(10, 'Dépannage informatique', 
'Ordinateur portable ne démarre plus. Besoin diagnostic et réparation. Intervention à domicile souhaitée.', 
'Informatique', 'Point E, Dakar', 'Dakar', 'Dakar', 'Point E', 30000.00, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'urgent', 'ouverte');

-- ============================================
-- CANDIDATURES
-- ============================================

INSERT INTO candidatures (offre_id, demandeur_id, message_motivation, statut) VALUES
(1, 6, 'Madame, Monsieur, Je suis très intéressée par le poste de Secrétaire Bilingue. Mon expérience de 3 ans et ma maîtrise du français et de l\'anglais correspondent parfaitement à vos attentes. Je reste à votre disposition pour un entretien.', 'en_attente'),
(2, 7, 'Bonjour, Je candidate au poste de Comptable Junior. Ma licence en comptabilité et mes 2 ans d\'expérience me permettront d\'être opérationnelle rapidement. Cordialement.', 'vue'),
(3, 8, 'Bonjour, Bien que je sois en reconversion, je maîtrise les bases du web (HTML, CSS). Votre formation m\'intéresse beaucoup. Motivé et sérieux.', 'en_attente'),
(4, 8, 'Bonjour, Commercial avec 5 ans d\'expérience, je maîtrise la prospection B2B. Permis B et véhicule personnel. Disponible immédiatement.', 'acceptee');

-- ============================================
-- MESSAGES
-- ============================================

INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu, lu) VALUES
(6, 1, 'Demande de devis plomberie', 'Bonjour Mamadou, J\'ai une fuite sous mon évier. Pouvez-vous passer pour un devis ? Je suis disponible cette semaine. Merci, Fatou', FALSE),
(1, 6, 'Re: Demande de devis plomberie', 'Bonjour Madame Fatou, Pas de problème. Je peux passer demain matin vers 10h si ça vous convient. Le déplacement est gratuit. Cordialement, Mamadou', TRUE),
(9, 6, 'Votre candidature - Secrétaire Bilingue', 'Bonjour Madame Ndiaye, Nous avons bien reçu votre candidature. Nous vous proposons un entretien le 10 février à 10h. Veuillez confirmer votre présence. Cordialement, ABC SARL', FALSE),
(7, 2, 'Installation climatiseur', 'Bonjour, J\'ai besoin d\'installer un climatiseur. Êtes-vous disponible cette semaine ? Quel serait votre tarif ? Merci', FALSE);

-- ============================================
-- AVIS
-- ============================================

INSERT INTO avis (evaluateur_id, evalue_id, note, commentaire, type_service) VALUES
(6, 1, 5, 'Très rapide et professionnel. Problème de fuite résolu en moins d\'une heure. Je recommande vivement !', 'Plomberie'),
(7, 1, 5, 'Excellent travail. Mamadou a réparé ma fuite et vérifié toute l\'installation. Très sérieux.', 'Plomberie'),
(8, 1, 4, 'Bon travail mais un peu cher. Néanmoins intervention rapide et efficace.', 'Plomberie'),
(9, 2, 5, 'Électricien très compétent. Installation climatiseur parfaite. Recommandé.', 'Électricité'),
(10, 2, 4, 'Bon professionnel. Travail soigné. Juste un peu de retard sur le RDV.', 'Électricité'),
(6, 3, 5, 'Menuisier exceptionnel ! Armoire sur mesure magnifique. Finitions parfaites.', 'Menuiserie'),
(7, 3, 5, 'Travail de grande qualité. Moussa est un vrai artisan. Très satisfaite.', 'Menuiserie'),
(8, 4, 4, 'Bonne équipe de maçons. Chantier bien géré. Quelques petits détails à revoir mais globalement satisfait.', 'Maçonnerie'),
(9, 5, 5, 'Technicien informatique très compétent. A résolu mon problème rapidement. Service impeccable.', 'Informatique'),
(10, 5, 4, 'Bon dépannage. Ordinateur réparé. Prix correct.', 'Informatique');

-- ============================================
-- NOTIFICATIONS
-- ============================================

INSERT INTO notifications (user_id, type_notification, titre, contenu, lue) VALUES
(6, 'nouveau_message', 'Nouveau message de Mamadou Diallo', 'Vous avez reçu une réponse concernant votre demande de devis plomberie.', FALSE),
(6, 'nouveau_message', 'Nouveau message de ABC SARL', 'Proposition d\'entretien pour le poste de Secrétaire Bilingue.', FALSE),
(1, 'nouveau_message', 'Nouveau message de Fatou Ndiaye', 'Nouvelle demande de devis pour plomberie.', TRUE),
(2, 'nouveau_message', 'Nouveau message de Aissatou Ba', 'Demande d\'information pour installation climatiseur.', FALSE),
(8, 'candidature_acceptee', 'Candidature acceptée !', 'Votre candidature pour le poste de Commercial Terrain a été acceptée. Vous serez contacté prochainement.', FALSE),
(1, 'nouveau_avis', 'Nouvel avis reçu', 'Fatou Ndiaye a laissé un avis 5 étoiles sur votre profil.', TRUE);

-- ============================================
-- STATISTIQUES RÉCAPITULATIVES
-- ============================================

-- Afficher les statistiques
SELECT 'STATISTIQUES LIGUEY CONNECT - DONNÉES DE TEST' AS info;
SELECT 
    (SELECT COUNT(*) FROM users WHERE type_utilisateur = 'prestataire') AS 'Prestataires',
    (SELECT COUNT(*) FROM users WHERE type_utilisateur = 'demandeur') AS 'Demandeurs emploi',
    (SELECT COUNT(*) FROM users WHERE type_utilisateur = 'recruteur') AS 'Recruteurs',
    (SELECT COUNT(*) FROM offres_emploi WHERE statut = 'active') AS 'Offres actives',
    (SELECT COUNT(*) FROM demandes_services WHERE statut = 'ouverte') AS 'Demandes services',
    (SELECT COUNT(*) FROM candidatures) AS 'Candidatures',
    (SELECT COUNT(*) FROM avis) AS 'Avis',
    (SELECT COUNT(*) FROM messages) AS 'Messages';

-- ============================================
-- FIN DES DONNÉES DE TEST
-- ============================================
