<?php
session_start(); 
include '_conf.php';

// Gestion de la connexion avec password_hash/password_verify
if (isset($_POST['envoi'])) {
    $login = $_POST['login'];
    $mdp = $_POST['mdp'];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    
    // Requête préparée pour éviter les injections SQL
    $requete = "SELECT * FROM utilisateur WHERE login = ? LIMIT 1";
    $stmt = mysqli_prepare($connexion, $requete);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $login);
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);
        
        if($donnees = mysqli_fetch_assoc($resultat)) {
            // VÉRIFICATION AVEC PASSWORD_VERIFY
            if(password_verify($mdp, $donnees['motdepasse'])) {
                $trouve = 1;
                $_SESSION["id"] = $donnees['num'];
                $_SESSION["login"] = $donnees['login'];
                $_SESSION["type"] = $donnees['type'];
                $_SESSION["prenom"] = $donnees['prenom'];
                $_SESSION["nom"] = $donnees['nom'];
                
                // Redirection pour éviter le renvoi du formulaire
                header("Location: accueil.php");
                exit();
            }
        }
        
        mysqli_stmt_close($stmt);
        
        // Si on arrive ici, c'est que la connexion a échoué
        $error_message = "Erreur de connexion : login ou mot de passe incorrect.";
    } else {
        $error_message = "Erreur technique. Veuillez réessayer.";
    }
    
    mysqli_close($connexion);
}

// Vérification de la session
if (!isset($_SESSION["login"])) {
    // Si pas connecté, rediriger vers index.php pour le formulaire de connexion
    // Mais d'abord, afficher le formulaire de connexion
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
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
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
    <?php
    exit(); // Arrêter ici pour ne pas montrer la page d'accueil
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <?php if($_SESSION["type"]==0): ?>
        <link rel="stylesheet" href="css/menueleve.css">
    <?php else: ?>
        <link rel="stylesheet" href="css/menuprof.css">
    <?php endif; ?>
</head>
<body>
    <?php if($_SESSION["type"]==0): ?>
        <?php include '_menuEleve.php'; ?>
    <?php else: ?>
        <?php include '_menuProf.php'; ?>
    <?php endif; ?>
    
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="welcome-section">
            <h1>Bienvenue <?php echo $_SESSION["prenom"] . " " . $_SESSION["nom"]; ?></h1>
            <p>Vous êtes connecté en tant que <?php echo ($_SESSION["type"]==0 ? "élève" : "professeur"); ?></p>
        </div>
        
        <div class="info-cards">
            <?php if($_SESSION["type"]==0): ?>
                <div class="info-card">
                    <h3>Compte-rendus</h3>
                    <p>Consultez et gérez vos comptes-rendus de stage</p>
                    <a href="cr.php" class="btn btn-primary">Voir mes CR</a>
                </div>
                
                <div class="info-card">
                    <h3>Profil</h3>
                    <p>Modifiez vos informations personnelles</p>
                    <a href="perso.php" class="btn btn-primary">Modifier mon profil</a>
                </div>
                
                <div class="info-card">
                    <h3>Nouveau CR</h3>
                    <p>Créez un nouveau compte-rendu de stage</p>
                    <a href="ccr.php" class="btn btn-primary">Créer un CR</a>
                </div>
            <?php else: ?>
                <div class="info-card">
                    <h3>Compte-rendus élèves</h3>
                    <p>Consultez les comptes-rendus de tous les élèves</p>
                    <a href="cr.php" class="btn btn-primary">Voir tous les CR</a>
                </div>
                
                <div class="info-card">
                    <h3>Profil</h3>
                    <p>Modifiez vos informations personnelles</p>
                    <a href="perso.php" class="btn btn-primary">Modifier mon profil</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>