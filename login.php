<?php 
include 'config.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Initialiser toutes les données de session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['dofus_pseudo'] = $user['dofus_pseudo'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['created_at'] = $user['created_at'];
            
            set_message("Connexion réussie ! Bienvenue " . htmlspecialchars($user['username']));
            header('Location: profile.php');
            exit();
        } else {
            set_message("Identifiant ou mot de passe incorrect", 'error');
        }
    } catch (PDOException $e) {
        error_log("Erreur de connexion: " . $e->getMessage());
        set_message("Une erreur est survenue", 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mercenaire Dofus</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-sign-in-alt"></i> Connexion</h1>
        </header>
        
        <?php include 'nav.php'; ?>
        
        <?php if ($message = get_message()): ?>
            <div class="message <?= $message['type'] ?>"><?= $message['text'] ?></div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-block">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
        
        <p class="text-center">
            Pas encore de compte ? <a href="register.php" class="text-primary">Inscrivez-vous ici</a>
        </p>
    </div>
</body>
</html>