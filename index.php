<?php
session_start();
include '_conf.php';

if (isset($_GET['deco'])) {
    session_destroy();
}

$error_message = '';
if (isset($_POST['envoi'])) {
    $login = $_POST['login'];
    $mdp = $_POST['mdp'];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    
    // Requête préparée pour éviter les injections SQL
    $requete = "SELECT * FROM utilisateur WHERE login = ? LIMIT 1";
    $stmt = mysqli_prepare($connexion, $requete);
    mysqli_stmt_bind_param($stmt, "s", $login);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    
    if($donnees = mysqli_fetch_assoc($resultat)) {
        // VÉRIFICATION AVEC PASSWORD_VERIFY
        if(password_verify($mdp, $donnees['motdepasse'])) {
            $_SESSION["id"] = $donnees['num'];
            $_SESSION["login"] = $donnees['login'];
            $_SESSION["type"] = $donnees['type'];
            $_SESSION["prenom"] = $donnees['prenom'];
            $_SESSION["nom"] = $donnees['nom'];
            
            // Redirection vers la page d'accueil
            header("Location: accueil.php");
            exit();
        } else {
            $error_message = "Login ou mot de passe incorrect.";
        }
    } else {
        $error_message = "Login ou mot de passe incorrect.";
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($connexion);
}

// Si l'utilisateur est déjà connecté, rediriger vers accueil.php
if (isset($_SESSION["login"])) {
    header("Location: accueil.php");
    exit();
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
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="index.php" method="post" class="form-container">
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