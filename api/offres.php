<?php
/**
 * LIGUEY CONNECT - API Offres d'Emploi
 * 
 * Endpoints:
 * - GET /api/offres.php - Liste des offres (avec filtres optionnels)
 * - GET /api/offres.php?id=X - Détails d'une offre
 * - POST /api/offres.php - Créer une nouvelle offre (recruteurs uniquement)
 */

require_once 'config.php';

// GET - Récupérer les offres
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Détail d'une offre spécifique
    if (isset($_GET['id'])) {
        $offre_id = intval($_GET['id']);
        
        $sql = "SELECT o.*, 
                       u.nom AS recruteur_nom, 
                       u.prenom AS recruteur_prenom,
                       u.telephone AS recruteur_telephone,
                       u.email AS recruteur_email
                FROM offres_emploi o
                INNER JOIN users u ON o.recruteur_id = u.id
                WHERE o.id = ? AND o.statut = 'active'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $offre_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            envoyer_reponse(false, null, 'Offre non trouvée', 404);
        }
        
        $offre = $result->fetch_assoc();
        $stmt->close();
        
        envoyer_reponse(true, $offre, 'Détails de l\'offre', 200);
    }
    
    // Liste des offres avec filtres
    $conditions = ["o.statut = 'active'"];
    $params = [];
    $types = "";
    
    // Filtres optionnels
    if (isset($_GET['type_contrat']) && !empty($_GET['type_contrat'])) {
        $conditions[] = "o.type_contrat = ?";
        $params[] = securiser_entree($_GET['type_contrat']);
        $types .= "s";
    }
    
    if (isset($_GET['region']) && !empty($_GET['region'])) {
        $conditions[] = "o.region = ?";
        $params[] = securiser_entree($_GET['region']);
        $types .= "s";
    }
    
    if (isset($_GET['commune']) && !empty($_GET['commune'])) {
        $conditions[] = "o.commune = ?";
        $params[] = securiser_entree($_GET['commune']);
        $types .= "s";
    }
    
    if (isset($_GET['secteur']) && !empty($_GET['secteur'])) {
        $conditions[] = "o.secteur_activite = ?";
        $params[] = securiser_entree($_GET['secteur']);
        $types .= "s";
    }
    
    if (isset($_GET['recherche']) && !empty($_GET['recherche'])) {
        $recherche = '%' . securiser_entree($_GET['recherche']) . '%';
        $conditions[] = "(o.titre LIKE ? OR o.description LIKE ? OR o.competences_requises LIKE ?)";
        $params[] = $recherche;
        $params[] = $recherche;
        $params[] = $recherche;
        $types .= "sss";
    }
    
    // Limite et pagination
    $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 20;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limite;
    
    // Construire la requête
    $sql = "SELECT o.id, o.titre, o.description, o.type_contrat, o.secteur_activite,
                   o.localisation, o.region, o.commune, o.salaire_min, o.salaire_max,
                   o.date_creation, o.nombre_candidatures,
                   u.nom AS recruteur_nom, u.prenom AS recruteur_prenom
            FROM offres_emploi o
            INNER JOIN users u ON o.recruteur_id = u.id
            WHERE " . implode(" AND ", $conditions) . "
            ORDER BY o.date_creation DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    
    // Ajouter limite et offset aux paramètres
    $params[] = $limite;
    $params[] = $offset;
    $types .= "ii";
    
    // Bind parameters dynamiquement
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $offres = [];
    while ($row = $result->fetch_assoc()) {
        $offres[] = $row;
    }
    
    $stmt->close();
    
    // Compter le total pour la pagination
    $sql_count = "SELECT COUNT(*) as total FROM offres_emploi o WHERE " . implode(" AND ", $conditions);
    $stmt_count = $conn->prepare($sql_count);
    
    if (!empty($params) && count($params) > 2) {
        $params_count = array_slice($params, 0, -2); // Enlever limite et offset
        $types_count = substr($types, 0, -2);
        $stmt_count->bind_param($types_count, ...$params_count);
    }
    
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total = $result_count->fetch_assoc()['total'];
    $stmt_count->close();
    
    envoyer_reponse(true, [
        'offres' => $offres,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limite' => $limite,
            'total_pages' => ceil($total / $limite)
        ]
    ], 'Liste des offres', 200);
}

// POST - Créer une nouvelle offre
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérifier la session
    $user_id = verifier_session();
    
    // Vérifier que l'utilisateur est un recruteur
    $sql_check = "SELECT type_utilisateur FROM users WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $user_data = $result_check->fetch_assoc();
    $stmt_check->close();
    
    if ($user_data['type_utilisateur'] !== 'recruteur') {
        envoyer_reponse(false, null, 'Seuls les recruteurs peuvent publier des offres', 403);
    }
    
    // Récupérer les données
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (!$data) {
        envoyer_reponse(false, null, 'Données invalides', 400);
    }
    
    // Champs obligatoires
    $champs_obligatoires = ['titre', 'description', 'type_contrat', 'localisation'];
    foreach ($champs_obligatoires as $champ) {
        if (!isset($data[$champ]) || empty(trim($data[$champ]))) {
            envoyer_reponse(false, null, "Le champ '$champ' est obligatoire", 400);
        }
    }
    
    // Extraire et sécuriser les données
    $titre = securiser_entree($data['titre']);
    $description = securiser_entree($data['description']);
    $type_contrat = securiser_entree($data['type_contrat']);
    $secteur = isset($data['secteur_activite']) ? securiser_entree($data['secteur_activite']) : null;
    $competences = isset($data['competences_requises']) ? securiser_entree($data['competences_requises']) : null;
    $localisation = securiser_entree($data['localisation']);
    $region = isset($data['region']) ? securiser_entree($data['region']) : null;
    $commune = isset($data['commune']) ? securiser_entree($data['commune']) : null;
    $salaire_min = isset($data['salaire_min']) ? floatval($data['salaire_min']) : null;
    $salaire_max = isset($data['salaire_max']) ? floatval($data['salaire_max']) : null;
    $date_debut = isset($data['date_debut']) ? securiser_entree($data['date_debut']) : null;
    $duree_contrat = isset($data['duree_contrat']) ? securiser_entree($data['duree_contrat']) : null;
    $nombre_postes = isset($data['nombre_postes']) ? intval($data['nombre_postes']) : 1;
    
    // Date d'expiration par défaut : 30 jours
    $date_expiration = isset($data['date_expiration']) 
        ? securiser_entree($data['date_expiration']) 
        : date('Y-m-d', strtotime('+30 days'));
    
    // Insérer l'offre
    $sql = "INSERT INTO offres_emploi 
            (recruteur_id, titre, description, type_contrat, secteur_activite, competences_requises,
             localisation, region, commune, salaire_min, salaire_max, date_debut, duree_contrat,
             nombre_postes, date_expiration, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssssssddsis",
        $user_id, $titre, $description, $type_contrat, $secteur, $competences,
        $localisation, $region, $commune, $salaire_min, $salaire_max, 
        $date_debut, $duree_contrat, $nombre_postes, $date_expiration
    );
    
    if ($stmt->execute()) {
        $offre_id = $stmt->insert_id;
        envoyer_reponse(true, [
            'offre_id' => $offre_id
        ], 'Offre publiée avec succès !', 201);
    } else {
        envoyer_reponse(false, null, 'Erreur lors de la publication: ' . $stmt->error, 500);
    }
    
    $stmt->close();
}

$conn->close();
?>
