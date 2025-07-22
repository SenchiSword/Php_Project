<?php include 'config.php';

if (!is_logged_in() || !is_passeur()) {
    header('Location: index.php');
    exit();
}

// Gestion de l'ajout de donjon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_donjon'])) {
    $nom = clean_input($_POST['nom']);
    $description = clean_input($_POST['description']);
    $prix = (int)$_POST['prix'];
    
    $stmt = $pdo->prepare("INSERT INTO donjons (nom, description, prix) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $description, $prix]);
    set_message("Le donjon a été ajouté avec succès !");
    header('Location: admin.php');
    exit();
}

// Gestion de la suppression de donjon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_donjon'])) {
    $donjon_id = (int)$_POST['donjon_id'];
    
    // Vérifier qu'il n'y a pas de demandes en cours pour ce donjon
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes WHERE donjon_id = ? AND statut = 'en attente'");
    $stmt->execute([$donjon_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        set_message("Impossible de supprimer ce donjon : il y a des demandes en cours", 'error');
    } else {
        $stmt = $pdo->prepare("DELETE FROM donjons WHERE id = ?");
        $stmt->execute([$donjon_id]);
        set_message("Le donjon a été supprimé avec succès !");
    }
    
    header('Location: admin.php');
    exit();
}

// Gestion des prix (existant)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_prices'])) {
    foreach ($_POST['prices'] as $donjon_id => $price) {
        $price = (int)$price;
        $stmt = $pdo->prepare("UPDATE donjons SET prix = ? WHERE id = ?");
        $stmt->execute([$price, $donjon_id]);
    }
    set_message("Les prix ont été mis à jour avec succès !");
    header('Location: admin.php');
    exit();
}

// Gestion des statuts (existant)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $demande_id = (int)$_POST['demande_id'];
    $statut = clean_input($_POST['statut']);
    
    $stmt = $pdo->prepare("UPDATE demandes SET statut = ? WHERE id = ?");
    $stmt->execute([$statut, $demande_id]);
    set_message("Statut de la demande mis à jour !");
    header('Location: admin.php');
    exit();
}

$donjons = $pdo->query("SELECT * FROM donjons")->fetchAll();
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
    <title>Panneau Passeur - Mercenaire Dofus</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-user-shield"></i> Panneau Passeur</h1>
        </header>
        
        <?php include 'nav.php'; ?>
        
        <?php if ($message = get_message()): ?>
            <div class="message <?= $message['type'] ?>"><?= $message['text'] ?></div>
        <?php endif; ?>
        
        <!-- Nouvelle section pour la gestion des donjons -->
        <section class="admin-section">
            <h2><i class="fas fa-dungeon"></i> Gérer les Donjons</h2>
            
            <h3><i class="fas fa-plus-circle"></i> Ajouter un nouveau donjon</h3>
            <form method="post">
                <div class="form-group">
                    <label for="nom"><i class="fas fa-tag"></i> Nom du donjon</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="prix"><i class="fas fa-coins"></i> Prix (en kamas)</label>
                    <input type="number" id="prix" name="prix" required min="0" step="1000">
                </div>
                
                <button type="submit" name="add_donjon" class="btn">
                    <i class="fas fa-plus"></i> Ajouter le donjon
                </button>
            </form>
            
            <h3><i class="fas fa-list"></i> Liste des donjons existants</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donjons as $donjon): ?>
                        <tr>
                            <td><?= htmlspecialchars($donjon['nom']) ?></td>
                            <td><?= htmlspecialchars($donjon['description']) ?></td>
                            <td><?= number_format($donjon['prix'], 0, ',', ' ') ?> kamas</td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="donjon_id" value="<?= $donjon['id'] ?>">
                                    <button type="submit" name="delete_donjon" class="btn btn-sm danger" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce donjon ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Section existante pour modifier les prix -->
        <section class="admin-section">
            <h2><i class="fas fa-coins"></i> Modifier les Prix des Donjons</h2>
            <form method="post">
                <table>
                    <thead>
                        <tr>
                            <th>Donjon</th>
                            <th>Prix actuel</th>
                            <th>Nouveau Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donjons as $donjon): ?>
                            <tr>
                                <td><?= htmlspecialchars($donjon['nom']) ?></td>
                                <td><?= number_format($donjon['prix'], 0, ',', ' ') ?> kamas</td>
                                <td>
                                    <input type="number" name="prices[<?= $donjon['id'] ?>]" 
                                           value="<?= $donjon['prix'] ?>" min="0" step="1000">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="update_prices" class="btn">
                    <i class="fas fa-save"></i> Mettre à jour les prix
                </button>
            </form>
        </section>
        
        <!-- Section existante pour gérer les demandes -->
        <section class="admin-section">
            <h2><i class="fas fa-tasks"></i> Gérer les Demandes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Donjon</th>
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
                            <td><?= date('d/m/Y H:i', strtotime($demande['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= str_replace(' ', '-', $demande['statut']) ?>">
                                    <?= htmlspecialchars($demande['statut']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="demande_id" value="<?= $demande['id'] ?>">
                                    <select name="statut" class="status-select">
                                        <option value="en attente" <?= $demande['statut'] == 'en attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="accepté" <?= $demande['statut'] == 'accepté' ? 'selected' : '' ?>>Accepté</option>
                                        <option value="terminé" <?= $demande['statut'] == 'terminé' ? 'selected' : '' ?>>Terminé</option>
                                        <option value="refusé" <?= $demande['statut'] == 'refusé' ? 'selected' : '' ?>>Refusé</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm">
                                        <i class="fas fa-sync-alt"></i> Mettre à jour
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>