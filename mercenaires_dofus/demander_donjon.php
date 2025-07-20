<?php
include 'config.php';

if (!is_logged_in() || is_passeur()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donjon_id'])) {
    $donjon_id = (int)$_POST['donjon_id'];
    $user_id = (int)$_SESSION['user_id'];
    $succes = htmlspecialchars($_POST['succes']);
    $nb_personnages = (int)$_POST['nb_personnages'];
    
    try {
        // Vérification du donjon
        $stmt = $pdo->prepare("SELECT id, nom FROM donjons WHERE id = ?");
        $stmt->execute([$donjon_id]);
        $donjon = $stmt->fetch();
        
        if (!$donjon) {
            throw new Exception("Donjon invalide");
        }
        
        // Validation du nombre de personnages
        if ($nb_personnages < 1 || $nb_personnages > 8) {
            throw new Exception("Nombre de personnages invalide (1-8)");
        }
        
        // Récupération du prix selon le succès
        $prix_succes = get_prix_for_donjon_succes($donjon_id, $succes);
        
        if (!$prix_succes) {
            throw new Exception("Configuration de prix manquante pour ce succès");
        }
        
        $prix_total = $prix_succes * $nb_personnages;
        
        // Insertion de la demande
        $stmt = $pdo->prepare("INSERT INTO demandes 
                              (user_id, donjon_id, succes, nb_personnages, prix_total, statut) 
                              VALUES (?, ?, ?, ?, ?, 'en attente')");
        $stmt->execute([$user_id, $donjon_id, $succes, $nb_personnages, $prix_total]);
        
        set_message("Demande enregistrée : " . htmlspecialchars($donjon['nom']) . 
                   " (" . $succes . ") pour " . $nb_personnages . " pers. - " . 
                   number_format($prix_total, 0, ',', ' ') . " kamas");
        header('Location: mes_demandes.php');
        exit();
        
    } catch (Exception $e) {
        set_message("Erreur : " . $e->getMessage(), 'error');
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>