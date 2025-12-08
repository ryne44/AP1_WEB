<nav class="main-menu menu-eleve">
    <div class="menu-container">
        <a href="accueil.php" class="menu-brand">ÉLÈVE</a>
        <div class="menu-user"><?php echo $_SESSION["prenom"] . " " . $_SESSION["nom"]; ?></div>
        <div class="menu-links">
            <a href="accueil.php" class="menu-link">Accueil</a>
            <a href="perso.php" class="menu-link">Profil</a>
            <a href="cr.php" class="menu-link">Compte-rendus</a>
            <a href="ccr.php" class="menu-link">Nouveau CR</a>
            <a href="index.php?deco=1" class="menu-link logout">Déconnexion</a>
        </div>
    </div>
</nav>