<?php include 'config.php';

if (is_logged_in()) {
    header('Location: profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = clean_input($_POST['email']);
    $dofus_pseudo = clean_input($_POST['dofus_pseudo']);
    $role = 'client';
    
    // Validation
    if (strlen($username) < 3) {
        set_message("Le nom d'utilisateur doit contenir au moins 3 caractères", 'error');
    } elseif (strlen($_POST['password']) < 6) {
        set_message("Le mot de passe doit contenir au moins 6 caractères", 'error');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_message("Adresse email invalide", 'error');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            set_message("Nom d'utilisateur ou email déjà utilisé", 'error');
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, dofus_pseudo, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $email, $dofus_pseudo, $role]);
            
            // Connecter automatiquement l'utilisateur
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            
            set_message("Inscription réussie ! Bienvenue $username");
            header('Location: profile.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Mercenaire Dofus</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-user-plus"></i> Inscription</h1>
        </header>
        
        <?php include 'nav.php'; ?>
        
        <?php if ($message = get_message()): ?>
            <div class="message <?= $message['type'] ?>"><?= $message['text'] ?></div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required minlength="3">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Adresse email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="dofus_pseudo"><i class="fas fa-gamepad"></i> Pseudo Dofus</label>
                <input type="text" id="dofus_pseudo" name="dofus_pseudo" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <button type="submit" class="btn btn-block">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
        </form>
        
        <p class="text-center">
            Déjà un compte ? <a href="login.php" class="text-primary">Connectez-vous ici</a>
        </p>
    </div>
</body>
</html>