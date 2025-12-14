<?php
session_start(); 
include '_conf.php';

if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit();
}

$message = '';
$message_type = '';

$id = $_SESSION["id"];
$connexion = mysqli_connect($serveurBDD,$userBDD,$mdpBDD,$nomBDD);

// Traitement de la mise à jour des informations personnelles
if (isset($_POST['envoi_info'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $tel = $_POST['tel'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    
    // Utilisation de requête préparée pour éviter les injections SQL
    $requete = $connexion->prepare("UPDATE utilisateur SET nom=?, prenom=?, tel=?, login=?, email=? WHERE num=?");
    $requete->bind_param("sssssi", $nom, $prenom, $tel, $login, $email, $id);
    
    if($requete->execute()) {
        $message = "Votre profil a été mis à jour avec succès.";
        $message_type = 'success';
        $_SESSION["prenom"] = $prenom;
        $_SESSION["nom"] = $nom;
    } else {
        $message = "Erreur lors de la mise à jour de votre profil.";
        $message_type = 'error';
    }
    $requete->close();
}

// Traitement du changement de mot de passe
if (isset($_POST['changer_mdp'])) {
    $ancien_mdp = $_POST['ancien_mdp'];
    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirmer_mdp = $_POST['confirmer_mdp'];
    
    if ($nouveau_mdp !== $confirmer_mdp) {
        $message = "Les nouveaux mots de passe ne correspondent pas.";
        $message_type = 'error';
    } else {
        // Récupérer le mot de passe actuel hashé
        $req = $connexion->prepare("SELECT motdepasse FROM utilisateur WHERE num=?");
        $req->bind_param("i", $id);
        $req->execute();
        $req->store_result();
        $req->bind_result($motdepasse_hash);
        $req->fetch();
        $req->close();
        
        if (password_verify($ancien_mdp, $motdepasse_hash)) {
            // Hacher le nouveau mot de passe
            $nouveau_mdp_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            
            $update_req = $connexion->prepare("UPDATE utilisateur SET motdepasse=? WHERE num=?");
            $update_req->bind_param("si", $nouveau_mdp_hash, $id);
            
            if ($update_req->execute()) {
                $message = "Votre mot de passe a été changé avec succès.";
                $message_type = 'success';
            } else {
                $message = "Erreur lors du changement de mot de passe.";
                $message_type = 'error';
            }
            $update_req->close();
        } else {
            $message = "L'ancien mot de passe est incorrect.";
            $message_type = 'error';
        }
    }
}

// Récupérer les informations actuelles de l'utilisateur
$requete = $connexion->prepare("SELECT * FROM utilisateur WHERE num = ?");
$requete->bind_param("i", $id);
$requete->execute();
$resultat = $requete->get_result();
$donnees = $resultat->fetch_assoc();
$requete->close();

$nom = $donnees['nom'];
$prenom = $donnees['prenom'];
$tel = $donnees['tel'];
$login = $donnees['login'];
$email = $donnees['email'];

mysqli_close($connexion);
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
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-card">
                <div class="form-header">
                    <h1>Mes informations personnelles</h1>
                    <p>Modifiez vos informations personnelles</p>
                </div>
                
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
                            Mettre à jour mes informations
                        </button>
                    </div>
                </form>
            </div>

            <div class="form-card" style="margin-top: 30px;">
                <div class="form-header">
                    <h2>Changer mon mot de passe</h2>
                    <p>Pour changer votre mot de passe, veuillez remplir les champs ci-dessous</p>
                </div>
                
                <form action="perso.php" method="post">
                    <div class="form-group">
                        <label class="form-label">Ancien mot de passe :</label>
                        <input type="password" name="ancien_mdp" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nouveau mot de passe :</label>
                        <input type="password" name="nouveau_mdp" class="form-control" required minlength="4">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirmer le nouveau mot de passe :</label>
                        <input type="password" name="confirmer_mdp" class="form-control" required minlength="4">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full" 
                                name="changer_mdp" value="1">
                            Changer mon mot de passe
                        </button>
                    </div>
                </form>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <a href="accueil.php" class="btn btn-secondary">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>