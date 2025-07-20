<?php
session_start();

// Configuration de la base de données
$host = 'localhost';
$dbname = 'mercenaires_dofus';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

/**
 * Vérifie si un utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est un passeur (admin)
 */
function is_passeur() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'passeur';
}

/**
 * Stocke un message dans la session
 */
function set_message($message, $type = 'success') {
    $_SESSION['messages'][] = [
        'text' => $message,
        'type' => $type
    ];
}

/**
 * Récupère et supprime les messages de la session
 */
function get_message() {
    if (empty($_SESSION['messages'])) {
        return null;
    }
    $message = array_shift($_SESSION['messages']);
    return [
        'text' => $message['text'],
        'type' => $message['type']
    ];
}

/**
 * Récupère les types de succès disponibles pour un donjon
 */
function get_succes_for_donjon($donjon_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT st.* 
                          FROM succes_types st
                          JOIN succes_prix sp ON st.id = sp.succes_id
                          WHERE sp.donjon_id = ?
                          ORDER BY st.nom");
    $stmt->execute([$donjon_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère le prix pour un donjon et un type de succès donné
 */
function get_prix_for_donjon_succes($donjon_id, $succes_nom) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT sp.prix 
                          FROM succes_prix sp
                          JOIN succes_types st ON sp.succes_id = st.id
                          WHERE sp.donjon_id = ? AND st.nom = ?");
    $stmt->execute([$donjon_id, $succes_nom]);
    return $stmt->fetchColumn();
}

/**
 * Récupère tous les donjons avec leurs prix de base
 */
function get_all_donjons() {
    global $pdo;
    return $pdo->query("SELECT * FROM donjons ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les demandes pour un utilisateur
 */
function get_user_demandes($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT d.*, dn.nom as donjon_nom 
                          FROM demandes d
                          JOIN donjons dn ON d.donjon_id = dn.id
                          WHERE d.user_id = ?
                          ORDER BY d.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les demandes (pour l'admin)
 */
function get_all_demandes() {
    global $pdo;
    
    return $pdo->query("SELECT d.*, u.username, dn.nom as donjon_nom
                       FROM demandes d
                       JOIN users u ON d.user_id = u.id
                       JOIN donjons dn ON d.donjon_id = dn.id
                       ORDER BY d.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Vérifie si un donjon existe
 */
function donjon_exists($donjon_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM donjons WHERE id = ?");
    $stmt->execute([$donjon_id]);
    return (bool)$stmt->fetch();
}

/**
 * Génère un hash sécurisé pour les mots de passe
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Valide un mot de passe
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}