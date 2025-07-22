<?php
/**
 * Fichier de navigation principale
 * Affiche différents liens selon l'état de connexion
 */
?>
<nav>
    <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
        <?php if (is_logged_in()): ?>
            <li><a href="profile.php"><i class="fas fa-user"></i> Mon Profil</a></li>
            <?php if (is_passeur()): ?>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Panneau Passeur</a></li>
            <?php else: ?>
                <li><a href="mes_demandes.php"><i class="fas fa-scroll"></i> Mes Demandes</a></li>
            <?php endif; ?>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        <?php else: ?>
            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
            <li><a href="register.php"><i class="fas fa-user-plus"></i> Inscription</a></li>
        <?php endif; ?>
    </ul>
</nav>