<?php
include 'config.php';

if (!is_logged_in() || is_passeur()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donjon_id'])) {
    $donjon_id = (int)$_POST['donjon_id'];
    $user_id = $_SESSION['user_id'];
    
    // Vérifier que le donjon existe
    $stmt = $pdo->prepare("SELECT id FROM donjons WHERE id = ?");
    $stmt->execute([$donjon_id]);
    
    if ($stmt->fetch()) {
        // Vérifier qu'il n'y a pas déjà une demande en cours pour ce donjon
        $stmt = $pdo->prepare("SELECT id FROM demandes WHERE user_id = ? AND donjon_id = ? AND statut = 'en attente'");
        $stmt->execute([$user_id, $donjon_id]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO demandes (user_id, donjon_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $donjon_id]);
            set_message("Votre demande a été envoyée avec succès !");
        } else {
            set_message("Vous avez déjà une demande en attente pour ce donjon", 'error');
        }
    } else {
        set_message("Donjon invalide", 'error');
    }
    
    header('Location: mes_demandes.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>