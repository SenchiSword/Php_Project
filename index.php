<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercenaire Dofus - Accueil</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-dragon"></i> Mercenaire Dofus</h1>
            <p>Service professionnel pour vos donjons les plus difficiles</p>
        </header>

        <?php include 'nav.php'; ?>

        <?php if ($message = get_message()): ?>
            <div class="message <?= $message['type'] ?>"><?= $message['text'] ?></div>
        <?php endif; ?>

        <section>
            <h2><i class="fas fa-dungeon"></i> Nos Services de Donjons</h2>
            <div class="donjon-list">
                <?php
                $stmt = $pdo->query("SELECT * FROM donjons");
                while ($donjon = $stmt->fetch()):
                ?>
                    <div class="donjon-card">
                        <h3><?= htmlspecialchars($donjon['nom']) ?></h3>
                        <p><?= htmlspecialchars($donjon['description']) ?></p>
                        <p class="price"><?= number_format($donjon['prix'], 0, ',', ' ') ?> kamas</p>
                        
                        <?php if (is_logged_in() && !is_passeur()): ?>
                            <form action="demander_donjon.php" method="post">
                                <input type="hidden" name="donjon_id" value="<?= $donjon['id'] ?>">
                                <button type="submit" class="btn btn-block">
                                    <i class="fas fa-hand-holding-usd"></i> Demander ce donjon
                                </button>
                            </form>
                        <?php elseif (!is_logged_in()): ?>
                            <a href="login.php" class="btn btn-block">
                                <i class="fas fa-sign-in-alt"></i> Connectez-vous pour demander
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</body>
</html>