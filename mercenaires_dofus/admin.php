<?php include 'config.php';

if (!is_logged_in() || !is_passeur()) {
    header('Location: index.php');
    exit();
}

// Gestion des prix des donjons
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_prices'])) {
    foreach ($_POST['prices'] as $donjon_id => $price) {
        $stmt = $pdo->prepare("UPDATE donjons SET prix = ? WHERE id = ?");
        $stmt->execute([$price, $donjon_id]);
    }
    set_message("Prix de base mis à jour");
    header('Location: admin.php');
    exit();
}

// Gestion des prix par succès
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_succes_prices'])) {
    $pdo->beginTransaction();
    try {
        $pdo->exec("DELETE FROM succes_prix");
        $stmt = $pdo->prepare("INSERT INTO succes_prix (donjon_id, succes_id, prix) VALUES (?, ?, ?)");
        
        foreach ($_POST['succes_prices'] as $donjon_id => $succes_prices) {
            foreach ($succes_prices as $succes_id => $price) {
                if ($price > 0) {
                    $stmt->execute([$donjon_id, $succes_id, $price]);
                }
            }
        }
        
        $pdo->commit();
        set_message("Prix par succès mis à jour");
    } catch (Exception $e) {
        $pdo->rollBack();
        set_message("Erreur : " . $e->getMessage(), 'error');
    }
    header('Location: admin.php');
    exit();
}

// Gestion des statuts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE demandes SET statut = ? WHERE id = ?");
    $stmt->execute([$_POST['statut'], $_POST['demande_id']]);
    set_message("Statut mis à jour");
    header('Location: admin.php');
    exit();
}

// Récupération des données
$donjons = $pdo->query("SELECT * FROM donjons")->fetchAll();
$succesTypes = $pdo->query("SELECT * FROM succes_types")->fetchAll();
$demandes = $pdo->query("SELECT d.*, u.username, dn.nom as donjon_nom 
                         FROM demandes d 
                         JOIN users u ON d.user_id = u.id 
                         JOIN donjons dn ON d.donjon_id = dn.id 
                         ORDER BY d.created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panneau Passeur</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-panel {
            background: rgba(26, 26, 74, 0.9);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .admin-table th, .admin-table td {
            padding: 10px;
            border: 1px solid #4a4a8a;
            text-align: left;
        }
        .admin-table th {
            background-color: #1a1a4a;
            color: #b8860b;
        }
        .admin-table input[type="number"] {
            width: 80px;
            padding: 5px;
        }
        .statut-select {
            padding: 5px;
            background: #2a2a5a;
            color: #fff;
            border: 1px solid #4a4a8a;
        }
        .tab-container {
            margin-top: 20px;
        }
        .tab-button {
            padding: 10px 20px;
            background: #1a1a4a;
            border: none;
            color: #b8860b;
            cursor: pointer;
        }
        .tab-button.active {
            background: #b8860b;
            color: #000;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Panneau Passeur</h1>
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

        <div class="tab-container">
            <button class="tab-button active" onclick="openTab('demandes')">Demandes</button>
            <button class="tab-button" onclick="openTab('prix-base')">Prix Base</button>
            <button class="tab-button" onclick="openTab('prix-succes')">Prix par Succès</button>
        </div>

        <div id="demandes" class="tab-content active">
            <div class="admin-panel">
                <h2>Gestion des Demandes</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Donjon</th>
                            <th>Succès</th>
                            <th>Persos</th>
                            <th>Prix</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td><?= htmlspecialchars($demande['username']) ?></td>
                            <td><?= htmlspecialchars($demande['donjon_nom']) ?></td>
                            <td><?= htmlspecialchars($demande['succes']) ?></td>
                            <td><?= $demande['nb_personnages'] ?></td>
                            <td><?= number_format($demande['prix_total'], 0, ',', ' ') ?> k</td>
                            <td><?= date('d/m/Y H:i', strtotime($demande['created_at'])) ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                                    <select name="statut" class="statut-select" onchange="this.form.submit()">
                                        <option value="en attente" <?= $demande['statut'] == 'en attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="accepté" <?= $demande['statut'] == 'accepté' ? 'selected' : '' ?>>Accepté</option>
                                        <option value="terminé" <?= $demande['statut'] == 'terminé' ? 'selected' : '' ?>>Terminé</option>
                                        <option value="refusé" <?= $demande['statut'] == 'refusé' ? 'selected' : '' ?>>Refusé</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                                    <button type="submit" name="delete_demande" class="btn-small">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="prix-base" class="tab-content">
            <div class="admin-panel">
                <h2>Prix de Base des Donjons</h2>
                <form method="post">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Donjon</th>
                                <th>Prix Actuel</th>
                                <th>Nouveau Prix</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donjons as $donjon): ?>
                            <tr>
                                <td><?= htmlspecialchars($donjon['nom']) ?></td>
                                <td><?= number_format($donjon['prix'], 0, ',', ' ') ?> k</td>
                                <td>
                                    <input type="number" name="prices[<?= $donjon['id'] ?>]" 
                                           value="<?= $donjon['prix'] ?>" min="0">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="update_prices" class="btn">Mettre à jour</button>
                </form>
            </div>
        </div>

        <div id="prix-succes" class="tab-content">
            <div class="admin-panel">
                <h2>Configuration des Prix par Succès</h2>
                <form method="post">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Donjon</th>
                                <?php foreach ($succesTypes as $type): ?>
                                <th><?= htmlspecialchars($type['nom']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donjons as $donjon): 
                                $prixParSucces = [];
                                $stmt = $pdo->prepare("SELECT succes_id, prix FROM succes_prix WHERE donjon_id = ?");
                                $stmt->execute([$donjon['id']]);
                                foreach ($stmt->fetchAll() as $row) {
                                    $prixParSucces[$row['succes_id']] = $row['prix'];
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($donjon['nom']) ?></td>
                                <?php foreach ($succesTypes as $type): 
                                    $currentPrice = $prixParSucces[$type['id']] ?? '';
                                ?>
                                <td>
                                    <input type="number" 
                                           name="succes_prices[<?= $donjon['id'] ?>][<?= $type['id'] ?>]"
                                           value="<?= $currentPrice ?>" 
                                           min="0" 
                                           placeholder="Prix...">
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="update_succes_prices" class="btn">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openTab(tabName) {
        // Masque tous les contenus d'onglets
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Désactive tous les boutons d'onglets
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Active l'onglet sélectionné
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }
    </script>
</body>
</html>