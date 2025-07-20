<?php include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'client';
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        $error = "Ce nom d'utilisateur est déjà pris";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        set_message("Inscription réussie ! Vous pouvez maintenant vous connecter.");
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Mercenaire Dofus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Inscription</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="login.php">Connexion</a></li>
            </ul>
        </nav>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" style="max-width: 400px; margin: 0 auto;">
            <div style="margin-bottom: 15px;">
                <label for="username">Nom d'utilisateur:</label>
                <input type="text" id="username" name="username" required style="width: 100%; padding: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 8px;">
            </div>
            <button type="submit" class="btn" style="width: 100%;">S'inscrire</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            Déjà un compte ? <a href="login.php" style="color: #b8860b;">Connectez-vous ici</a>
        </p>
    </div>
</body>
</html>