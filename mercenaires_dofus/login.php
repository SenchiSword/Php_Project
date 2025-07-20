<?php include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        set_message("Connexion réussie !");
        header('Location: index.php');
        exit();
    } else {
        $error = "Identifiant ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Mercenaire Dofus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Connexion</h1>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="register.php">Inscription</a></li>
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
            <button type="submit" class="btn" style="width: 100%;">Se connecter</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            Pas encore de compte ? <a href="register.php" style="color: #b8860b;">Inscrivez-vous ici</a>
        </p>
    </div>
</body>
</html>