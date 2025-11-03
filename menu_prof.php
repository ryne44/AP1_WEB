<?php
// menu_prof.php
?>
<nav class="main-menu">
    <div class="menu-container">
        <div class="menu-left">
            <a href="accueil.php" class="menu-brand">📚 Gestion CR</a>
            <a href="accueil.php" class="menu-item">🏠 Accueil</a>
            <a href="perso.php" class="menu-item">👤 Profil</a>
            <a href="parametres.php" class="menu-item">⚙️ Paramètres</a>
        </div>
        <div class="menu-right">
            <span style="color: white;">Prof: <?php echo htmlspecialchars($_SESSION['Slogin']); ?></span>
            <form method="post" style="display: inline;">
                <button type="submit" name="deconnexion" class="menu-item logout" style="border: none; background: none; cursor: pointer;">
                    🚪 Déconnexion
                </button>
            </form>
        </div>
    </div>
</nav>