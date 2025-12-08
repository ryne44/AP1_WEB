
<?php
include '_conf.php';

function genererMotDePasse($longueur) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $motDePasse = '';
    $tailleCaracteres = strlen($caracteres);
    
    for ($i = 0; $i < $longueur; $i++) {
        $indexAleatoire = random_int(0, $tailleCaracteres - 1);
        $motDePasse .= $caracteres[$indexAleatoire];
    }
    return $motDePasse;
}

$message = '';
$message_class = '';

if (isset($_POST['envoi_perdu'])) {
    $email = $_POST["email"];
    $login = $_POST["login"];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    $requete = "SELECT * FROM utilisateur WHERE email='$email' AND login='$login'";
    $resultat = mysqli_query($connexion, $requete);
    $trouve = 0;
    
    while($donnees = mysqli_fetch_assoc($resultat)) {
        $trouve = 1;
    }
    
    if($trouve == 1) {
        $newmdp = genererMotDePasse(10);
        $newmdphash = md5($newmdp);
        $requete = "UPDATE utilisateur SET motdepasse = '$newmdphash' WHERE email='$email' AND login='$login'";
        
        if(mysqli_query($connexion, $requete)) {
            $message = "Nouveau mot de passe généré : <strong>$newmdp</strong><br>Vous pouvez maintenant vous connecter avec ce mot de passe.";
            $message_class = 'success';
        } else {
            $message = "Erreur lors de la mise à jour du mot de passe.";
            $message_class = 'error';
        }
    } else {
        $message = "Email/login non trouvé dans notre base de données.";
        $message_class = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/oubli.css">
</head>
<body class="oubli-body">
    <div class="oubli-container">
        <div class="oubli-header">
            <h1>Mot de passe oublié</h1>
            <p>Réinitialisez votre mot de passe</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_class; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="oubli.php" method="post">
            <div class="form-group">
                <label class="form-label">Email :</label>
                <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Login :</label>
                <input type="text" name="login" class="form-control" value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>" required>
            </div>
            
            <button type="submit" class="btn-submit" name="envoi_perdu" value="1">Réinitialiser le mot de passe</button>
            
            <div class="login-links" style="margin-top: 20px;">
                <a href="index.php">Retour à la connexion</a>
            </div>
        </form>
    </div>
</body>
</html>
