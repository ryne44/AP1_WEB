
<?php
session_start(); 
include '_conf.php';

// Gestion de la connexion
if (isset($_POST['envoi'])) {
    $login = $_POST['login'];
    $mdp = md5($_POST['mdp']);
    
    $connexion = mysqli_connect($serveurBDD,$userBDD,$mdpBDD,$nomBDD);
    $requete="SELECT * FROM utilisateur WHERE login = '$login' AND motdepasse = '$mdp'";
    $resultat = mysqli_query($connexion, $requete);
    $trouve=0;
    
    while($donnees = mysqli_fetch_assoc($resultat)) {
        $trouve=1;
        $_SESSION["id"] = $donnees['num'];
        $_SESSION["login"] = $donnees['login'];
        $_SESSION["type"] = $donnees['type'];
        $_SESSION["prenom"] = $donnees['prenom'];
        $_SESSION["nom"] = $donnees['nom'];
    }
    
    if($trouve==0) {
        $error_message = "Erreur de connexion : login ou mot de passe incorrect.";
    }
}

// Vérification de la session
if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit();
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
