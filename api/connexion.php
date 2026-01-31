<?php
/**
 * LIGUEY CONNECT - API Connexion
 * 
 * Endpoint: POST /api/connexion.php
 * Description: Authentifier un utilisateur
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

// Vérifier les champs obligatoires
if (!isset($data['identifiant']) || !isset($data['mot_de_passe'])) {
    envoyer_reponse(false, null, 'Identifiant et mot de passe requis', 400);
}

$identifiant = securiser_entree($data['identifiant']);
$mot_de_passe = $data['mot_de_passe'];

// Rechercher l'utilisateur par téléphone ou email
$sql = "SELECT id, nom, prenom, telephone, email, mot_de_passe, type_utilisateur, photo_profil, statut 
        FROM users 
        WHERE (telephone = ? OR email = ?) 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $identifiant, $identifiant);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    envoyer_reponse(false, null, 'Identifiant ou mot de passe incorrect', 401);
}

$user = $result->fetch_assoc();
$stmt->close();

// Vérifier le statut du compte
if ($user['statut'] !== 'actif') {
    $message = $user['statut'] === 'suspendu' 
        ? 'Votre compte a été suspendu. Contactez l\'administration.' 
        : 'Votre compte est inactif.';
    envoyer_reponse(false, null, $message, 403);
}

// Vérifier le mot de passe
if (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
    envoyer_reponse(false, null, 'Identifiant ou mot de passe incorrect', 401);
}

// Mettre à jour la dernière connexion
$update_sql = "UPDATE users SET derniere_connexion = CURRENT_TIMESTAMP WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $user['id']);
$update_stmt->execute();
$update_stmt->close();

// Récupérer les informations complémentaires selon le type d'utilisateur
$profil_complementaire = null;

if ($user['type_utilisateur'] === 'prestataire') {
    $sql_profil = "SELECT metier, competences, annees_experience, tarif_horaire, description, 
                          disponibilite, note_moyenne, nombre_avis, profil_verifie 
                   FROM profils_prestataires 
                   WHERE user_id = ?";
    $stmt_profil = $conn->prepare($sql_profil);
    $stmt_profil->bind_param("i", $user['id']);
    $stmt_profil->execute();
    $result_profil = $stmt_profil->get_result();
    if ($result_profil->num_rows > 0) {
        $profil_complementaire = $result_profil->fetch_assoc();
    }
    $stmt_profil->close();
}

if ($user['type_utilisateur'] === 'demandeur') {
    $sql_profil = "SELECT competences, experience_professionnelle, formations, diplomes, 
                          disponibilite, type_contrat_recherche, salaire_souhaite 
                   FROM profils_demandeurs 
                   WHERE user_id = ?";
    $stmt_profil = $conn->prepare($sql_profil);
    $stmt_profil->bind_param("i", $user['id']);
    $stmt_profil->execute();
    $result_profil = $stmt_profil->get_result();
    if ($result_profil->num_rows > 0) {
        $profil_complementaire = $result_profil->fetch_assoc();
    }
    $stmt_profil->close();
}

// Démarrer la session
session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['type_utilisateur'] = $user['type_utilisateur'];
$_SESSION['nom'] = $user['nom'];
$_SESSION['prenom'] = $user['prenom'];

// Préparer la réponse (sans le mot de passe)
unset($user['mot_de_passe']);

envoyer_reponse(true, [
    'user' => $user,
    'profil' => $profil_complementaire
], 'Connexion réussie ! Bienvenue ' . $user['prenom'] . ' !', 200);

$conn->close();
?>
