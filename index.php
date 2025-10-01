<?php
// Fichier de configuration (à créer ou adapter)
include 'conf.php';

// Connexion à la base de données
if ($bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD)) {
   echo "✅ Connexion réussie !";
} else {
    echo "❌ Connexion échouée : " . mysqli_connect_error();
}


?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion BDD</title>
</head>
<body>
    <h1>Connexion à la base de données</h1>


    <form method="POST" action="">
        <label>Login : <input type="text" name="login" required></label><br><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br><br>
        <button type="submit">Tester la connexion</button>
    </form>

    <p><a href="oubli.php">Mot de passe oublié ?</a></p>
</body>
</html>
