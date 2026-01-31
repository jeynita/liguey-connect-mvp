<?php
/**
 * LIGUEY CONNECT - Configuration Base de Données (EXEMPLE)
 * 
 * Ce fichier sert de modèle pour config.php
 * 
 * INSTRUCTIONS:
 * 1. Copier ce fichier et le renommer en "config.php"
 * 2. Modifier les valeurs ci-dessous selon votre environnement
 * 3. Ne jamais commiter config.php sur Git !
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');           // Adresse du serveur MySQL
define('DB_USER', 'root');                 // Nom d'utilisateur MySQL
define('DB_PASS', '');                     // Mot de passe MySQL (CHANGER EN PRODUCTION!)
define('DB_NAME', 'liguey_connect');       // Nom de la base de données
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
    die(json_encode([
        'success' => false,
        'error' => 'Erreur de connexion à la base de données',
        'message' => $e->getMessage()
    ]));
}

// Configuration CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function securiser_entree($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function envoyer_reponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function verifier_session() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        envoyer_reponse(false, null, 'Session expirée. Veuillez vous reconnecter.', 401);
    }
    return $_SESSION['user_id'];
}
?>
