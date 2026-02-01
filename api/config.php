<?php
/**
 * LIGUEY CONNECT - Configuration Base de Données
 * 
 * IMPORTANT: Ce fichier contient les identifiants de connexion à la base de données
 * Ne jamais commiter ce fichier sur Git en production !
 * Utilisez config.example.php comme modèle
 */
$host = "localhost";
$username = "root";
$password = ""; // Vide par défaut sur XAMPP
$data = "liguey_connect";

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Vide par défaut sur XAMPP
define('DB_NAME', 'liguey_connect');
define('DB_CHARSET', 'utf8mb4');

// Tentative de connexion
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        throw new Exception("Échec de la connexion: " . $conn->connect_error);
    }
    
    // Définir le charset
    $conn->set_charset(DB_CHARSET);
    
} catch (Exception $e) {
    // En production, logger l'erreur au lieu de l'afficher
    die(json_encode([
        'success' => false,
        'error' => 'Erreur de connexion à la base de données',
        'message' => $e->getMessage()
    ]));
}

// Configuration des headers CORS (pour les requêtes depuis le frontend)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Gérer les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Fonction utilitaire pour sécuriser les entrées
 */
function securiser_entree($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

/**
 * Fonction pour envoyer une réponse JSON
 */
function envoyer_reponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Fonction pour vérifier si l'utilisateur est connecté
 */
function verifier_session() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        envoyer_reponse(false, null, 'Session expirée. Veuillez vous reconnecter.', 401);
    }
    return $_SESSION['user_id'];
}
?>
