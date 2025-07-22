<?php 
include 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Récupérer les infos complètes depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Mettre à jour les données de session
$_SESSION['email'] = $user['email'] ?? '';
$_SESSION['dofus_pseudo'] = $user['dofus_pseudo'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Mercenaire Dofus</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-user-circle"></i> Mon Profil</h1>
        </header>
        
        <?php include 'nav.php'; ?>
        
        <?php if ($message = get_message()): ?>
            <div class="message <?= $message['type'] ?>"><?= $message['text'] ?></div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                    <?php if (!empty($user['dofus_pseudo'])): ?>
                        <p class="dofus-pseudo"><?= htmlspecialchars($user['dofus_pseudo']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="profile-details">
                    <?php if (!empty($user['email'])): ?>
                    <div class="detail">
                        <span class="label"><i class="fas fa-envelope"></i> Email :</span>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail">
                        <span class="label"><i class="fas fa-user-tag"></i> Rôle :</span>
                        <span class="role-badge"><?= htmlspecialchars($user['role']) ?></span>
                    </div>
                    
                    <div class="detail">
                        <span class="label"><i class="fas fa-calendar-alt"></i> Inscrit depuis :</span>
                        <span><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <a href="change_password.php" class="btn btn-block">
                        <i class="fas fa-key"></i> Changer le mot de passe
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>