<?php include 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas";
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
            $error = "Mot de passe actuel incorrect";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le mot de passe - Mercenaire Dofus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Modifier le mot de passe</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" style="max-width: 400px; margin: 0 auto;">
            <div style="margin-bottom: 15px;">
                <label for="current_password">Mot de passe actuel:</label>
                <input type="password" id="current_password" name="current_password" required style="width: 100%; padding: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="new_password">Nouveau mot de passe:</label>
                <input type="password" id="new_password" name="new_password" required style="width: 100%; padding: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="confirm_password">Confirmer le nouveau mot de passe:</label>
                <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 8px;">
            </div>
            <button type="submit" class="btn" style="width: 100%;">Modifier le mot de passe</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #b8860b;">Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>