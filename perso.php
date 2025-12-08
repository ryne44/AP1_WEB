
<?php
session_start(); 
include '_conf.php';

if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit();
}

$message = '';
$message_type = '';

if (isset($_POST['envoi_info'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $tel = $_POST['tel'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    $id = $_SESSION['id'];
    
    $connexion = mysqli_connect($serveurBDD,$userBDD,$mdpBDD,$nomBDD);
    $requete = "UPDATE utilisateur SET 
                nom = '$nom', 
                prenom = '$prenom', 
                tel = '$tel', 
                login = '$login', 
                email = '$email' 
                WHERE num = $id";
    
    if(mysqli_query($connexion,$requete)) {
        $message = "Votre profil a été mis à jour avec succès.";
        $message_type = 'success';
        $_SESSION["prenom"] = $prenom;
        $_SESSION["nom"] = $nom;
    } else {
        $message = "Erreur lors de la mise à jour de votre profil.";
        $message_type = 'error';
    }
}

$id = $_SESSION["id"];
$connexion = mysqli_connect($serveurBDD,$userBDD,$mdpBDD,$nomBDD);
$requete = "SELECT * FROM utilisateur WHERE num = '$id'";
$resultat = mysqli_query($connexion, $requete);
$donnees = mysqli_fetch_assoc($resultat);

$nom = $donnees['nom'];
$prenom = $donnees['prenom'];
$tel = $donnees['tel'];
$login = $donnees['login'];
$email = $donnees['email'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <?php if($_SESSION["type"]==0): ?>
        <link rel="stylesheet" href="css/menueleve.css">
    <?php else: ?>
        <link rel="stylesheet" href="css/menuprof.css">
    <?php endif; ?>
    <link rel="stylesheet" href="css/formulaire.css">
</head>
<body>
    <div class="main-container">
        <?php if($_SESSION["type"]==0): ?>
            <?php include '_menuEleve.php'; ?>
        <?php else: ?>
            <?php include '_menuProf.php'; ?>
        <?php endif; ?>
        
        <div class="content-center">
            <div class="form-card">
                <div class="form-header">
                    <h1>Mon profil</h1>
                    <p>Modifiez vos informations personnelles</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="perso.php" method="post">
                    <div class="form-group">
                        <label class="form-label">Nom :</label>
                        <input type="text" name="nom" class="form-control" 
                               value="<?php echo htmlspecialchars($nom); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Prénom :</label>
                        <input type="text" name="prenom" class="form-control" 
                               value="<?php echo htmlspecialchars($prenom); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Téléphone :</label>
                        <input type="tel" name="tel" class="form-control" 
                               value="<?php echo htmlspecialchars($tel); ?>" 
                               placeholder="Ex: 0612345678">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nom d'utilisateur :</label>
                        <input type="text" name="login" class="form-control" 
                               value="<?php echo htmlspecialchars($login); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Adresse email :</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full" 
                                name="envoi_info" value="1">
                            Mettre à jour mon profil
                        </button>
                        
                        <a href="accueil.php" class="btn btn-secondary btn-full">
                            Retour à l'accueil
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
