<?php
include 'conf.php'; // Fichier de configuration qui contient les variables de connexion.
?>

<?php
// Test de connexion à la base de données
if ($bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD)) {
    echo "Connexion à la base de données réussie.<br>";
} else {
    echo "Erreur de connexion à la base de données";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h2>Connexion</h2>
    
    <!-- FORMULAIRE DE CONNEXION -->
    <!-- Envoie vers accueil.php avec la méthode POST -->
    <form action="accueil.php" method="POST">
        <label for="login">Identifiant :</label><br>
        <input type="text" id="login" name="login" required><br><br>

        <label for="mdp">Mot de passe :</label><br>
        <input type="password" id="mdp" name="mdp" required><br><br>

        <!-- Le name="send_con" est important pour que accueil.php détecte le clic -->
        <button type="submit" name="send_con">Se connecter</button>
    </form>

    <p><a href="sendmail.php">Mot de passe oublié ?</a></p>
</body>
</html>
