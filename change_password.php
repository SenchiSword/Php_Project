<?php include 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        set_message("Tous les champs sont obligatoires", 'error');
    } elseif ($new_password !== $confirm_password) {
        set_message("Les nouveaux mots de passe ne correspondent pas", 'error');
    } elseif (strlen($new_password) < 6) {
        set_message("Le mot de passe doit contenir au moins 6 caractères", 'error');
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($current_password, $user['password'])) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_hashed_password, $_SESSION['user_id']]);
            
            set_message("Mot de passe mis à jour avec succès !");
            header('Location: index.php');
            exit();
        } else {
            set_message("Mot de passe actuel incorrect", 'error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le mot de passe - Mercenaire Dofus</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-key"></i> Modifier le mot de passe</h1>
        </header>
        
        <?php include 'nav.php'; ?>
        
        <?php if ($message = get_message()): ?>
            <div class="message <?= $message['type'] ?>"><?= $message['text'] ?></div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label for="current_password"><i class="fas fa-lock"></i> Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password"><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <button type="submit" class="btn btn-block">
                <i class="fas fa-save"></i> Modifier le mot de passe
            </button>
        </form>
        
        <p class="text-center">
            <a href="index.php" class="text-primary"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>