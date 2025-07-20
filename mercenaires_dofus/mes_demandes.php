<?php include 'config.php';

if (!is_logged_in() || is_passeur()) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT d.*, dn.nom as donjon_nom 
                       FROM demandes d 
                       JOIN donjons dn ON d.donjon_id = dn.id 
                       WHERE d.user_id = ? 
                       ORDER BY d.created_at DESC");
$stmt->execute([$user_id]);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Demandes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .demande-card {
            background: rgba(26, 26, 74, 0.7);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #b8860b;
        }
        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .demande-title {
            font-size: 1.2em;
            color: #b8860b;
        }
        .demande-statut {
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .statut-en-attente { background-color: rgba(243, 156, 18, 0.2); color: #f39c12; }
        .statut-accepte { background-color: rgba(46, 204, 113, 0.2); color: #2ecc71; }
        .statut-termine { background-color: rgba(52, 152, 219, 0.2); color: #3498db; }
        .statut-refuse { background-color: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .demande-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .demande-detail {
            background: rgba(10, 10, 42, 0.5);
            padding: 8px;
            border-radius: 5px;
        }
        .demande-detail strong {
            color: #b8860b;
        }
        .no-demandes {
            text-align: center;
            padding: 20px;
            background: rgba(26, 26, 74, 0.5);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Mes Demandes</h1>
        </header>

        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <?php if ($message = get_message()): ?>
            <div class="message success"><?= $message ?></div>
        <?php endif; ?>

        <div class="mes-demandes-container">
            <?php if (empty($demandes)): ?>
                <div class="no-demandes">
                    <p>Vous n'avez aucune demande pour le moment.</p>
                    <a href="index.php" class="btn">Demander un donjon</a>
                </div>
            <?php else: ?>
                <?php foreach ($demandes as $demande): 
                    $statutClass = str_replace('é', 'e', strtolower($demande['statut']));
                ?>
                    <div class="demande-card">
                        <div class="demande-header">
                            <span class="demande-title"><?= htmlspecialchars($demande['donjon_nom']) ?></span>
                            <span class="demande-statut statut-<?= $statutClass ?>">
                                <?= htmlspecialchars($demande['statut']) ?>
                            </span>
                        </div>
                        
                        <div class="demande-details">
                            <div class="demande-detail">
                                <strong>Succès:</strong> <?= htmlspecialchars($demande['succes']) ?>
                            </div>
                            <div class="demande-detail">
                                <strong>Personnages:</strong> <?= $demande['nb_personnages'] ?>
                            </div>
                            <div class="demande-detail">
                                <strong>Prix total:</strong> <?= number_format($demande['prix_total'], 0, ',', ' ') ?> kamas
                            </div>
                            <div class="demande-detail">
                                <strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($demande['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>