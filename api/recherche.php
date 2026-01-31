<?php
/**
 * LIGUEY CONNECT - API Recherche
 * 
 * Endpoint: GET /api/recherche.php
 * Description: Rechercher des prestataires ou des offres d'emploi
 * 
 * Paramètres:
 * - type: 'prestataires' ou 'offres'
 * - q: terme de recherche
 * - region, commune, metier, etc.
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    envoyer_reponse(false, null, 'Méthode non autorisée. Utilisez GET.', 405);
}

$type = isset($_GET['type']) ? securiser_entree($_GET['type']) : 'prestataires';

// RECHERCHE DE PRESTATAIRES
if ($type === 'prestataires') {
    
    $conditions = ["u.statut = 'actif'", "u.type_utilisateur = 'prestataire'"];
    $params = [];
    $types = "";
    
    // Filtres
    if (isset($_GET['metier']) && !empty($_GET['metier'])) {
        $conditions[] = "p.metier = ?";
        $params[] = securiser_entree($_GET['metier']);
        $types .= "s";
    }
    
    if (isset($_GET['region']) && !empty($_GET['region'])) {
        $conditions[] = "u.region = ?";
        $params[] = securiser_entree($_GET['region']);
        $types .= "s";
    }
    
    if (isset($_GET['commune']) && !empty($_GET['commune'])) {
        $conditions[] = "u.commune = ?";
        $params[] = securiser_entree($_GET['commune']);
        $types .= "s";
    }
    
    if (isset($_GET['q']) && !empty($_GET['q'])) {
        $recherche = '%' . securiser_entree($_GET['q']) . '%';
        $conditions[] = "(p.metier LIKE ? OR p.competences LIKE ? OR p.description LIKE ?)";
        $params[] = $recherche;
        $params[] = $recherche;
        $params[] = $recherche;
        $types .= "sss";
    }
    
    if (isset($_GET['disponibilite']) && !empty($_GET['disponibilite'])) {
        $conditions[] = "p.disponibilite = ?";
        $params[] = securiser_entree($_GET['disponibilite']);
        $types .= "s";
    }
    
    // Tri
    $ordre = "p.note_moyenne DESC, p.nombre_avis DESC";
    if (isset($_GET['tri'])) {
        $tri = securiser_entree($_GET['tri']);
        if ($tri === 'note') {
            $ordre = "p.note_moyenne DESC";
        } elseif ($tri === 'tarif_asc') {
            $ordre = "p.tarif_horaire ASC";
        } elseif ($tri === 'tarif_desc') {
            $ordre = "p.tarif_horaire DESC";
        } elseif ($tri === 'experience') {
            $ordre = "p.annees_experience DESC";
        }
    }
    
    $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 20;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limite;
    
    $sql = "SELECT u.id, u.nom, u.prenom, u.telephone, u.photo_profil,
                   u.localisation, u.region, u.commune, u.quartier,
                   p.metier, p.competences, p.annees_experience, p.tarif_horaire,
                   p.description, p.disponibilite, p.note_moyenne, p.nombre_avis,
                   p.profil_verifie
            FROM users u
            INNER JOIN profils_prestataires p ON u.id = p.user_id
            WHERE " . implode(" AND ", $conditions) . "
            ORDER BY " . $ordre . "
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $params[] = $limite;
    $params[] = $offset;
    $types .= "ii";
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $prestataires = [];
    while ($row = $result->fetch_assoc()) {
        $prestataires[] = $row;
    }
    
    $stmt->close();
    
    envoyer_reponse(true, [
        'prestataires' => $prestataires,
        'total' => count($prestataires)
    ], 'Résultats de recherche', 200);
}

// RECHERCHE D'OFFRES
if ($type === 'offres') {
    
    $conditions = ["o.statut = 'active'"];
    $params = [];
    $types = "";
    
    if (isset($_GET['q']) && !empty($_GET['q'])) {
        $recherche = '%' . securiser_entree($_GET['q']) . '%';
        $conditions[] = "(o.titre LIKE ? OR o.description LIKE ?)";
        $params[] = $recherche;
        $params[] = $recherche;
        $types .= "ss";
    }
    
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
    
    $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 20;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limite;
    
    $sql = "SELECT o.*, u.nom AS recruteur_nom, u.prenom AS recruteur_prenom
            FROM offres_emploi o
            INNER JOIN users u ON o.recruteur_id = u.id
            WHERE " . implode(" AND ", $conditions) . "
            ORDER BY o.date_creation DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $params[] = $limite;
    $params[] = $offset;
    $types .= "ii";
    
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
    
    envoyer_reponse(true, [
        'offres' => $offres,
        'total' => count($offres)
    ], 'Résultats de recherche', 200);
}

$conn->close();
?>
