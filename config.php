<?php
// Démarrer la session en tout premier
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'mercenaires_dofus';
$username = 'root';
$password = '';

// Connexion PDO avec gestion d'erreurs
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Erreur de connexion : " . $e->getMessage());
    die("Une erreur est survenue. Veuillez réessayer plus tard.");
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est un passeur
 */
function is_passeur() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'passeur';
}

/**
 * Définit un message flash
 */
function set_message($message, $type = 'success') {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
}

/**
 * Récupère et efface le message flash
 */
function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

/**
 * Nettoie les données entrantes
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirige vers une page avec un message optionnel
 */
function redirect($page, $message = null, $type = 'success') {
    if ($message) {
        set_message($message, $type);
    }
    header("Location: $page");
    exit();
}

/**
 * Génère un token CSRF
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token'], $token) 
           && hash_equals($_SESSION['csrf_token'], $token);
}