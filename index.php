
<?php
session_start();

if (isset($_GET['deco'])) {
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/connexion.css">
</head>
<body class="connexion-body">
    <div class="login-container">
        <div class="login-header">
            <h1>Suivi des Stages</h1>
            <p>Connexion à votre espace</p>
        </div>
        
        <?php if (isset($_GET['deco'])): ?>
            <div class="message success">Vous avez été déconnecté avec succès.</div>
        <?php endif; ?>
        
        <form action="accueil.php" method="post" class="form-container">
            <div class="form-group">
                <label class="form-label">Login :</label>
                <input type="text" name="login" class="form-control" placeholder="Entrez votre login" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mot de passe :</label>
                <input type="password" name="mdp" class="form-control" placeholder="Entrez votre mot de passe" required>
            </div>
            
            <button type="submit" class="btn-submit" name="envoi" value="1">Se connecter</button>
            
            <div class="login-links">
                <a href="inscription.php">Créer un compte</a> | 
                <a href="oubli.php">Mot de passe oublié ?</a>
            </div>
        </form>
    </div>
</body>
</html>
