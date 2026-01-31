<?php
/**
 * LIGUEY CONNECT - API Inscription
 * 
 * Endpoint: POST /api/inscription.php
 * Description: Créer un nouveau compte utilisateur
 */

require_once 'config.php';

// Vérifier que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    envoyer_reponse(false, null, 'Méthode non autorisée. Utilisez POST.', 405);
}

// Récupérer les données JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Vérifier que les données ont été reçues
if (!$data) {
    envoyer_reponse(false, null, 'Données invalides', 400);
}

// Champs obligatoires
$champs_obligatoires = ['nom', 'prenom', 'telephone', 'mot_de_passe', 'type_utilisateur'];

// Vérifier les champs obligatoires
foreach ($champs_obligatoires as $champ) {
    if (!isset($data[$champ]) || empty(trim($data[$champ]))) {
        envoyer_reponse(false, null, "Le champ '$champ' est obligatoire", 400);
    }
}

// Extraire et sécuriser les données
$nom = securiser_entree($data['nom']);
$prenom = securiser_entree($data['prenom']);
$telephone = securiser_entree($data['telephone']);
$email = isset($data['email']) ? securiser_entree($data['email']) : null;
$mot_de_passe = $data['mot_de_passe'];
$type_utilisateur = securiser_entree($data['type_utilisateur']);
$localisation = isset($data['localisation']) ? securiser_entree($data['localisation']) : null;
$region = isset($data['region']) ? securiser_entree($data['region']) : null;
$commune = isset($data['commune']) ? securiser_entree($data['commune']) : null;
$quartier = isset($data['quartier']) ? securiser_entree($data['quartier']) : null;

// Valider le type d'utilisateur
$types_valides = ['demandeur', 'prestataire', 'recruteur'];
if (!in_array($type_utilisateur, $types_valides)) {
    envoyer_reponse(false, null, 'Type d\'utilisateur invalide', 400);
}

// Valider le format du téléphone (simple validation)
if (!preg_match('/^[0-9\s\+\-]{8,20}$/', $telephone)) {
    envoyer_reponse(false, null, 'Format de téléphone invalide', 400);
}

// Valider l'email si fourni
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    envoyer_reponse(false, null, 'Format d\'email invalide', 400);
}

// Vérifier si le téléphone existe déjà
$stmt = $conn->prepare("SELECT id FROM users WHERE telephone = ?");
$stmt->bind_param("s", $telephone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    envoyer_reponse(false, null, 'Ce numéro de téléphone est déjà utilisé', 409);
}
$stmt->close();

// Vérifier si l'email existe déjà (si fourni)
if ($email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        envoyer_reponse(false, null, 'Cet email est déjà utilisé', 409);
    }
    $stmt->close();
}

// Hasher le mot de passe
$mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

// Insérer le nouvel utilisateur
$sql = "INSERT INTO users (nom, prenom, telephone, email, mot_de_passe, type_utilisateur, localisation, region, commune, quartier, statut) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif')";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssss",
    $nom,
    $prenom,
    $telephone,
    $email,
    $mot_de_passe_hash,
    $type_utilisateur,
    $localisation,
    $region,
    $commune,
    $quartier
);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Créer le profil spécifique selon le type d'utilisateur
    if ($type_utilisateur === 'prestataire' && isset($data['metier'])) {
        $metier = securiser_entree($data['metier']);
        $competences = isset($data['competences']) ? securiser_entree($data['competences']) : '';
        $annees_experience = isset($data['annees_experience']) ? intval($data['annees_experience']) : 0;
        $tarif_horaire = isset($data['tarif_horaire']) ? floatval($data['tarif_horaire']) : null;
        $description = isset($data['description']) ? securiser_entree($data['description']) : '';
        
        $sql_profil = "INSERT INTO profils_prestataires (user_id, metier, competences, annees_experience, tarif_horaire, description) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_profil = $conn->prepare($sql_profil);
        $stmt_profil->bind_param("issids", $user_id, $metier, $competences, $annees_experience, $tarif_horaire, $description);
        $stmt_profil->execute();
        $stmt_profil->close();
    }
    
    if ($type_utilisateur === 'demandeur' && isset($data['competences'])) {
        $competences = securiser_entree($data['competences']);
        $type_contrat = isset($data['type_contrat_recherche']) ? securiser_entree($data['type_contrat_recherche']) : 'CDD,CDI';
        
        $sql_profil = "INSERT INTO profils_demandeurs (user_id, competences, type_contrat_recherche) 
                       VALUES (?, ?, ?)";
        $stmt_profil = $conn->prepare($sql_profil);
        $stmt_profil->bind_param("iss", $user_id, $competences, $type_contrat);
        $stmt_profil->execute();
        $stmt_profil->close();
    }
    
    // Démarrer la session
    session_start();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['type_utilisateur'] = $type_utilisateur;
    $_SESSION['nom'] = $nom;
    $_SESSION['prenom'] = $prenom;
    
    envoyer_reponse(true, [
        'user_id' => $user_id,
        'nom' => $nom,
        'prenom' => $prenom,
        'type_utilisateur' => $type_utilisateur
    ], 'Inscription réussie ! Bienvenue sur Liguey Connect.', 201);
    
} else {
    envoyer_reponse(false, null, 'Erreur lors de l\'inscription: ' . $stmt->error, 500);
}

$stmt->close();
$conn->close();
?>
