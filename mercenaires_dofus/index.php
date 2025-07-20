<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercenaire Dofus</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .donjon-options {
            background: rgba(26, 26, 74, 0.8);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #4a4a8a;
        }
        .option-group {
            margin-bottom: 12px;
        }
        .option-group label {
            display: inline-block;
            width: 160px;
            color: #b8860b;
            margin-bottom: 5px;
        }
        .option-group select, .option-group input[type="number"] {
            padding: 8px 12px;
            background: #2a2a5a;
            border: 1px solid #4a4a8a;
            color: #fff;
            border-radius: 4px;
            width: 100%;
            max-width: 200px;
        }
        .price-summary {
            margin-top: 10px;
            padding: 10px;
            background: rgba(10, 10, 42, 0.6);
            border-radius: 5px;
            border-left: 3px solid #b8860b;
        }
        .price-total {
            font-size: 1.2em;
            color: #ffd700;
            font-weight: bold;
        }
        .donjon-icon {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #b8860b;
            margin-right: 10px;
        }
        .donjon-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Mercenaire Dofus</h1>
            <p>Service professionnel pour vos donjons les plus difficiles</p>
        </header>

        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <?php if (is_logged_in()): ?>
                    <?php if (is_passeur()): ?>
                        <li><a href="admin.php">Panneau Passeur</a></li>
                    <?php else: ?>
                        <li><a href="mes_demandes.php">Mes Demandes</a></li>
                    <?php endif; ?>
                    <li><a href="change_password.php">Modifier mot de passe</a></li>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <?php if ($message = get_message()): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>

        <h2>Nos Services de Donjons</h2>
        <div class="donjon-list">
            <?php
            $donjons = $pdo->query("SELECT * FROM donjons ORDER BY prix ASC")->fetchAll();
            foreach ($donjons as $donjon):
                $succesTypes = get_succes_for_donjon($donjon['id']);
            ?>
                <div class="donjon-card">
                    <div class="donjon-header">
                        <img src="images/donjons/<?= strtolower(str_replace(' ', '-', $donjon['nom'])) ?>.png" 
                             class="donjon-icon" 
                             alt="<?= htmlspecialchars($donjon['nom']) ?>">
                        <h3><?= htmlspecialchars($donjon['nom']) ?></h3>
                    </div>
                    <p><?= htmlspecialchars($donjon['description']) ?></p>
                    
                    <?php if (is_logged_in() && !is_passeur()): ?>
                        <div class="donjon-options">
                            <form action="demander_donjon.php" method="post">
                                <input type="hidden" name="donjon_id" value="<?= $donjon['id'] ?>">
                                
                                <div class="option-group">
                                    <label for="succes_<?= $donjon['id'] ?>">Type de succès:</label>
                                    <select name="succes" id="succes_<?= $donjon['id'] ?>" required>
                                        <?php foreach ($succesTypes as $type): ?>
                                            <option value="<?= htmlspecialchars($type['nom']) ?>">
                                                <?= htmlspecialchars($type['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="option-group">
                                    <label for="nb_perso_<?= $donjon['id'] ?>">Nombre de personnages:</label>
                                    <input type="number" name="nb_personnages" id="nb_perso_<?= $donjon['id'] ?>" 
                                           min="1" max="8" value="1" required>
                                </div>
                                
                                <button type="submit" class="btn">Demander ce donjon</button>
                            </form>
                        </div>
                    <?php elseif (!is_logged_in()): ?>
                        <a href="login.php" class="btn">Connectez-vous pour demander</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>