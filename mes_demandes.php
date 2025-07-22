<?php include 'config.php';

if (!is_logged_in() || is_passeur()) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT d.*, dn.nom as donjon_nom, dn.prix 
                       FROM demandes d 
                       JOIN donjons dn ON d.donjon_id = dn.id 
                       WHERE d.user_id = ? 
                       ORDER BY d.created_at DESC");
$stmt->execute([$user_id]);
$demandes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Demandes - Mercenaire Dofus</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-scroll"></i> Mes Demandes</h1>
        </header>
        
        <?php include 'nav.php'; ?>
        
        <?php if ($message = get_message()): ?>
            <div class="message <?= $message['type'] ?>"><?= $message['text'] ?></div>
        <?php endif; ?>
        
        <?php if (empty($demandes)): ?>
            <div class="message info">
                <i class="fas fa-info-circle"></i> Vous n'avez aucune demande pour le moment.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Donjon</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td><?= htmlspecialchars($demande['donjon_nom']) ?></td>
                            <td><?= number_format($demande['prix'], 0, ',', ' ') ?> kamas</td>
                            <td>
                                <span class="status-badge status-<?= str_replace(' ', '-', $demande['statut']) ?>">
                                    <?= htmlspecialchars($demande['statut']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($demande['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>