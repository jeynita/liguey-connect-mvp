<?php
/**
 * LIGUEY CONNECT - API Profils Utilisateurs
 * 
 * Endpoints:
 * - GET /api/profils.php?id=X - Récupérer un profil par ID
 * - GET /api/profils.php?user_id=X - Récupérer le profil de l'utilisateur connecté
 * - PUT /api/profils.php - Mettre à jour le profil (nécessite session)
 * - POST /api/profils.php/photo - Upload photo de profil
 */

require_once 'config.php';

// ============================================
// GET - Récupérer un profil
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $user_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['user_id']) ? intval($_GET['user_id']) : null);
    
    if (!$user_id) {
        envoyer_reponse(false, null, 'ID utilisateur requis', 400);
    }
    
    // Récupérer les informations de base de l'utilisateur
    $sql = "SELECT id, nom, prenom, telephone, email, type_utilisateur, photo_profil, 
                   localisation, region, commune, quartier, statut, date_creation, derniere_connexion
            FROM users 
            WHERE id = ? AND statut = 'actif'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        envoyer_reponse(false, null, 'Profil non trouvé', 404);
    }
    
    $profil = $result->fetch_assoc();
    $stmt->close();
    
    // Récupérer les informations complémentaires selon le type d'utilisateur
    $profil_complementaire = null;
    
    // PRESTATAIRE
    if ($profil['type_utilisateur'] === 'prestataire') {
        $sql_profil = "SELECT metier, competences, annees_experience, tarif_horaire, description,
                              disponibilite, jours_disponibles, horaires_debut, horaires_fin,
                              certifications, note_moyenne, nombre_avis, profil_verifie,
                              date_creation as date_inscription_service
                       FROM profils_prestataires 
                       WHERE user_id = ?";
        
        $stmt_profil = $conn->prepare($sql_profil);
        $stmt_profil->bind_param("i", $user_id);
        $stmt_profil->execute();
        $result_profil = $stmt_profil->get_result();
        
        if ($result_profil->num_rows > 0) {
            $profil_complementaire = $result_profil->fetch_assoc();
            
            // Récupérer les avis du prestataire
            $sql_avis = "SELECT a.*, 
                               u.nom as evaluateur_nom, 
                               u.prenom as evaluateur_prenom,
                               u.photo_profil as evaluateur_photo
                        FROM avis a
                        INNER JOIN users u ON a.evaluateur_id = u.id
                        WHERE a.evalue_id = ?
                        ORDER BY a.date_creation DESC
                        LIMIT 10";
            
            $stmt_avis = $conn->prepare($sql_avis);
            $stmt_avis->bind_param("i", $user_id);
            $stmt_avis->execute();
            $result_avis = $stmt_avis->get_result();
            
            $avis = [];
            while ($row = $result_avis->fetch_assoc()) {
                $avis[] = $row;
            }
            $profil_complementaire['avis'] = $avis;
            $stmt_avis->close();
        }
        
        $stmt_profil->close();
    }
    
    // DEMANDEUR D'EMPLOI
    if ($profil['type_utilisateur'] === 'demandeur') {
        $sql_profil = "SELECT competences, experience_professionnelle, formations, diplomes,
                              langues, cv_fichier, disponibilite, type_contrat_recherche,
                              salaire_souhaite, date_creation as date_inscription_demandeur
                       FROM profils_demandeurs 
                       WHERE user_id = ?";
        
        $stmt_profil = $conn->prepare($sql_profil);
        $stmt_profil->bind_param("i", $user_id);
        $stmt_profil->execute();
        $result_profil = $stmt_profil->get_result();
        
        if ($result_profil->num_rows > 0) {
            $profil_complementaire = $result_profil->fetch_assoc();
            
            // Récupérer les candidatures du demandeur
            $sql_candidatures = "SELECT c.*, 
                                       o.titre as offre_titre,
                                       o.type_contrat,
                                       u.nom as recruteur_nom,
                                       u.prenom as recruteur_prenom
                                FROM candidatures c
                                INNER JOIN offres_emploi o ON c.offre_id = o.id
                                INNER JOIN users u ON o.recruteur_id = u.id
                                WHERE c.demandeur_id = ?
                                ORDER BY c.date_candidature DESC
                                LIMIT 10";
            
            $stmt_cand = $conn->prepare($sql_candidatures);
            $stmt_cand->bind_param("i", $user_id);
            $stmt_cand->execute();
            $result_cand = $stmt_cand->get_result();
            
            $candidatures = [];
            while ($row = $result_cand->fetch_assoc()) {
                $candidatures[] = $row;
            }
            $profil_complementaire['candidatures'] = $candidatures;
            $stmt_cand->close();
        }
        
        $stmt_profil->close();
    }
    
    // RECRUTEUR
    if ($profil['type_utilisateur'] === 'recruteur') {
        // Récupérer les offres publiées par le recruteur
        $sql_offres = "SELECT id, titre, description, type_contrat, secteur_activite,
                             localisation, region, commune, statut, nombre_candidatures,
                             date_creation, date_expiration
                      FROM offres_emploi
                      WHERE recruteur_id = ?
                      ORDER BY date_creation DESC
                      LIMIT 10";
        
        $stmt_offres = $conn->prepare($sql_offres);
        $stmt_offres->bind_param("i", $user_id);
        $stmt_offres->execute();
        $result_offres = $stmt_offres->get_result();
        
        $offres = [];
        while ($row = $result_offres->fetch_assoc()) {
            $offres[] = $row;
        }
        $profil_complementaire = ['offres_publiees' => $offres];
        $stmt_offres->close();
    }
    
    // Statistiques générales de l'utilisateur
    $statistiques = [];
    
    // Messages
    $sql_messages = "SELECT COUNT(*) as total_messages FROM messages WHERE destinataire_id = ?";
    $stmt_msg = $conn->prepare($sql_messages);
    $stmt_msg->bind_param("i", $user_id);
    $stmt_msg->execute();
    $result_msg = $stmt_msg->get_result();
    $statistiques['messages'] = $result_msg->fetch_assoc()['total_messages'];
    $stmt_msg->close();
    
    // Notifications non lues
    $sql_notif = "SELECT COUNT(*) as notif_non_lues FROM notifications WHERE user_id = ? AND lue = 0";
    $stmt_notif = $conn->prepare($sql_notif);
    $stmt_notif->bind_param("i", $user_id);
    $stmt_notif->execute();
    $result_notif = $stmt_notif->get_result();
    $statistiques['notifications_non_lues'] = $result_notif->fetch_assoc()['notif_non_lues'];
    $stmt_notif->close();
    
    // Préparer la réponse complète
    $reponse = [
        'profil_base' => $profil,
        'profil_complementaire' => $profil_complementaire,
        'statistiques' => $statistiques
    ];
    
    envoyer_reponse(true, $reponse, 'Profil récupéré avec succès', 200);
}

// ============================================
// PUT - Mettre à jour le profil
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    
    // Vérifier la session
    $user_id = verifier_session();
    
    // Récupérer les données
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (!$data) {
        envoyer_reponse(false, null, 'Données invalides', 400);
    }
    
    // Récupérer le type d'utilisateur
    $sql_type = "SELECT type_utilisateur FROM users WHERE id = ?";
    $stmt_type = $conn->prepare($sql_type);
    $stmt_type->bind_param("i", $user_id);
    $stmt_type->execute();
    $result_type = $stmt_type->get_result();
    $user_data = $result_type->fetch_assoc();
    $type_utilisateur = $user_data['type_utilisateur'];
    $stmt_type->close();
    
    // Mettre à jour les informations de base (users)
    $champs_base = [];
    $params_base = [];
    $types_base = "";
    
    if (isset($data['nom']) && !empty($data['nom'])) {
        $champs_base[] = "nom = ?";
        $params_base[] = securiser_entree($data['nom']);
        $types_base .= "s";
    }
    
    if (isset($data['prenom']) && !empty($data['prenom'])) {
        $champs_base[] = "prenom = ?";
        $params_base[] = securiser_entree($data['prenom']);
        $types_base .= "s";
    }
    
    if (isset($data['email'])) {
        $champs_base[] = "email = ?";
        $params_base[] = securiser_entree($data['email']);
        $types_base .= "s";
    }
    
    if (isset($data['localisation'])) {
        $champs_base[] = "localisation = ?";
        $params_base[] = securiser_entree($data['localisation']);
        $types_base .= "s";
    }
    
    if (isset($data['region'])) {
        $champs_base[] = "region = ?";
        $params_base[] = securiser_entree($data['region']);
        $types_base .= "s";
    }
    
    if (isset($data['commune'])) {
        $champs_base[] = "commune = ?";
        $params_base[] = securiser_entree($data['commune']);
        $types_base .= "s";
    }
    
    if (isset($data['quartier'])) {
        $champs_base[] = "quartier = ?";
        $params_base[] = securiser_entree($data['quartier']);
        $types_base .= "s";
    }
    
    // Exécuter la mise à jour des informations de base si nécessaire
    if (!empty($champs_base)) {
        $sql_update_base = "UPDATE users SET " . implode(", ", $champs_base) . " WHERE id = ?";
        $stmt_update_base = $conn->prepare($sql_update_base);
        
        $params_base[] = $user_id;
        $types_base .= "i";
        
        $stmt_update_base->bind_param($types_base, ...$params_base);
        $stmt_update_base->execute();
        $stmt_update_base->close();
    }
    
    // Mettre à jour le profil spécifique selon le type
    
    // PRESTATAIRE
    if ($type_utilisateur === 'prestataire') {
        $champs_prestataire = [];
        $params_prestataire = [];
        $types_prestataire = "";
        
        if (isset($data['metier'])) {
            $champs_prestataire[] = "metier = ?";
            $params_prestataire[] = securiser_entree($data['metier']);
            $types_prestataire .= "s";
        }
        
        if (isset($data['competences'])) {
            $champs_prestataire[] = "competences = ?";
            $params_prestataire[] = securiser_entree($data['competences']);
            $types_prestataire .= "s";
        }
        
        if (isset($data['annees_experience'])) {
            $champs_prestataire[] = "annees_experience = ?";
            $params_prestataire[] = intval($data['annees_experience']);
            $types_prestataire .= "i";
        }
        
        if (isset($data['tarif_horaire'])) {
            $champs_prestataire[] = "tarif_horaire = ?";
            $params_prestataire[] = floatval($data['tarif_horaire']);
            $types_prestataire .= "d";
        }
        
        if (isset($data['description'])) {
            $champs_prestataire[] = "description = ?";
            $params_prestataire[] = securiser_entree($data['description']);
            $types_prestataire .= "s";
        }
        
        if (isset($data['disponibilite'])) {
            $champs_prestataire[] = "disponibilite = ?";
            $params_prestataire[] = securiser_entree($data['disponibilite']);
            $types_prestataire .= "s";
        }
        
        if (isset($data['jours_disponibles'])) {
            $champs_prestataire[] = "jours_disponibles = ?";
            $params_prestataire[] = securiser_entree($data['jours_disponibles']);
            $types_prestataire .= "s";
        }
        
        if (isset($data['horaires_debut'])) {
            $champs_prestataire[] = "horaires_debut = ?";
            $params_prestataire[] = securiser_entree($data['horaires_debut']);
            $types_prestataire .= "s";
        }
        
        if (isset($data['horaires_fin'])) {
            $champs_prestataire[] = "horaires_fin = ?";
            $params_prestataire[] = securiser_entree($data['horaires_fin']);
            $types_prestataire .= "s";
        }
        
        if (!empty($champs_prestataire)) {
            $sql_update_prestataire = "UPDATE profils_prestataires SET " . 
                                     implode(", ", $champs_prestataire) . 
                                     " WHERE user_id = ?";
            
            $stmt_update_prest = $conn->prepare($sql_update_prestataire);
            $params_prestataire[] = $user_id;
            $types_prestataire .= "i";
            
            $stmt_update_prest->bind_param($types_prestataire, ...$params_prestataire);
            $stmt_update_prest->execute();
            $stmt_update_prest->close();
        }
    }
    
    // DEMANDEUR D'EMPLOI
    if ($type_utilisateur === 'demandeur') {
        $champs_demandeur = [];
        $params_demandeur = [];
        $types_demandeur = "";
        
        if (isset($data['competences'])) {
            $champs_demandeur[] = "competences = ?";
            $params_demandeur[] = securiser_entree($data['competences']);
            $types_demandeur .= "s";
        }
        
        if (isset($data['experience_professionnelle'])) {
            $champs_demandeur[] = "experience_professionnelle = ?";
            $params_demandeur[] = securiser_entree($data['experience_professionnelle']);
            $types_demandeur .= "s";
        }
        
        if (isset($data['formations'])) {
            $champs_demandeur[] = "formations = ?";
            $params_demandeur[] = securiser_entree($data['formations']);
            $types_demandeur .= "s";
        }
        
        if (isset($data['diplomes'])) {
            $champs_demandeur[] = "diplomes = ?";
            $params_demandeur[] = securiser_entree($data['diplomes']);
            $types_demandeur .= "s";
        }
        
        if (isset($data['langues'])) {
            $champs_demandeur[] = "langues = ?";
            $params_demandeur[] = securiser_entree($data['langues']);
            $types_demandeur .= "s";
        }
        
        if (isset($data['disponibilite'])) {
            $champs_demandeur[] = "disponibilite = ?";
            $params_demandeur[] = securiser_entree($data['disponibilite']);
            $types_demandeur .= "s";
        }
        
        if (isset($data['type_contrat_recherche'])) {
            $champs_demandeur[] = "type_contrat_recherche = ?";
            $params_demandeur[] = securiser_entree($data['type_contrat_recherche']);
            $types_demandeur .= "s";
        }
        
        if (isset($data['salaire_souhaite'])) {
            $champs_demandeur[] = "salaire_souhaite = ?";
            $params_demandeur[] = floatval($data['salaire_souhaite']);
            $types_demandeur .= "d";
        }
        
        if (!empty($champs_demandeur)) {
            $sql_update_demandeur = "UPDATE profils_demandeurs SET " . 
                                   implode(", ", $champs_demandeur) . 
                                   " WHERE user_id = ?";
            
            $stmt_update_dem = $conn->prepare($sql_update_demandeur);
            $params_demandeur[] = $user_id;
            $types_demandeur .= "i";
            
            $stmt_update_dem->bind_param($types_demandeur, ...$params_demandeur);
            $stmt_update_dem->execute();
            $stmt_update_dem->close();
        }
    }
    
    envoyer_reponse(true, ['user_id' => $user_id], 'Profil mis à jour avec succès', 200);
}

// ============================================
// POST - Upload photo de profil
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    
    // Vérifier la session
    $user_id = verifier_session();
    
    $file = $_FILES['photo'];
    
    // Vérifier les erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        envoyer_reponse(false, null, 'Erreur lors de l\'upload du fichier', 400);
    }
    
    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        envoyer_reponse(false, null, 'Format de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP', 400);
    }
    
    // Vérifier la taille (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        envoyer_reponse(false, null, 'Fichier trop volumineux. Maximum 5MB', 400);
    }
    
    // Créer le dossier uploads s'il n'existe pas
    $upload_dir = '../uploads/profils/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profil_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        
        // Mettre à jour la BDD
        $photo_url = '/uploads/profils/' . $filename;
        $sql_update = "UPDATE users SET photo_profil = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $photo_url, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        envoyer_reponse(true, ['photo_url' => $photo_url], 'Photo de profil mise à jour', 200);
    } else {
        envoyer_reponse(false, null, 'Erreur lors de l\'enregistrement du fichier', 500);
    }
}

$conn->close();
?>
