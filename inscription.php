
<?php
session_start();
include '_conf.php';

$erreur = '';
$success = '';

if (isset($_POST['inscription'])) {
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    
    if (!$connexion) {
        $erreur = "Erreur de connexion à la base de données";
    } else {
        $nom = mysqli_real_escape_string($connexion, $_POST['nom']);
        $prenom = mysqli_real_escape_string($connexion, $_POST['prenom']);
        $tel = mysqli_real_escape_string($connexion, $_POST['tel']);
        $login = mysqli_real_escape_string($connexion, $_POST['login']);
        $email = mysqli_real_escape_string($connexion, $_POST['email']);
        $mdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        $confirmation_mdp = $_POST['confirmation_mdp']; //modifie
        
        // Récupération du type de compte (élève ou prof)
        $type_compte = $_POST['type_compte'];
        if ($type_compte == 'prof') {
            $type = 1;  // Professeur
            $option = 0; // Option ELEVE (Note: option et type sont inversés dans la BD)
        } else {
            $type = 0;  // Élève
            $option = 1; // Option PROF (Note: option et type sont inversés dans la BD)
        }

      // Vérification des mots de passe
if ($_POST['mdp'] !== $_POST['confirmation_mdp']) {
     $erreur = "Les mots de passe ne correspondent pas";
} else {
     // Le mot de passe est déjà hashé dans $mdp
     // ... reste du code ...
}
            
            if (mysqli_num_rows($resultat_check) > 0) {
                $erreur = "Ce login ou cet email est déjà utilisé";
            } else {
                // Insertion dans la base de données
                $requete = "INSERT INTO utilisateur (nom, prenom, tel, login, motdepasse, type, email, option, num_stage) 
                           VALUES ('$nom', '$prenom', '$tel', '$login', '$mdp', '$type', '$email', '$option', NULL)";
                
                if (mysqli_query($connexion, $requete)) {
                    $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                } else {
                    $erreur = "Erreur lors de l'inscription : " . mysqli_error($connexion);
                }
            }
        }
        mysqli_close($connexion);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/inscription.css">
</head>
<body>
    <div class="inscription-container">
        <div class="inscription-header">
            <h1>Créer un compte</h1>
            <p>Rejoignez la plateforme de suivi de stages</p>
        </div>
        
        <?php if ($erreur): ?>
            <div class="form-message error"><?php echo $erreur; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="form-message success">
                <?php echo $success; ?>
                <br><br>
                <a href="index.php" class="btn btn-primary">Se connecter</a>
            </div>
        <?php else: ?>
            <form action="inscription.php" method="post">
                <div class="form-group">
                    <label class="form-label">Nom :</label>
                    <input type="text" name="nom" class="form-control" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Prénom :</label>
                    <input type="text" name="prenom" class="form-control" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Téléphone :</label>
                    <input type="tel" name="tel" class="form-control" value="<?php echo isset($_POST['tel']) ? htmlspecialchars($_POST['tel']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Login :</label>
                    <input type="text" name="login" class="form-control" value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email :</label>
                    <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Type de compte :</label>
                    <div class="account-type-selector">
                        <div class="account-type-option">
                            <input type="radio" id="type_eleve" name="type_compte" value="eleve" <?php echo (isset($_POST['type_compte']) && $_POST['type_compte'] == 'eleve') ? 'checked' : 'checked'; ?>>
                            <label for="type_eleve" class="account-type-label">
                                <div class="account-type-icon">👨‍🎓</div>
                                <div class="account-type-text">
                                    <h4>Élève</h4>
                                    <p>Suivez vos stages et créez vos comptes-rendus</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="account-type-option">
                            <input type="radio" id="type_prof" name="type_compte" value="prof" <?php echo (isset($_POST['type_compte']) && $_POST['type_compte'] == 'prof') ? 'checked' : ''; ?>>
                            <label for="type_prof" class="account-type-label">
                                <div class="account-type-icon">👨‍🏫</div>
                                <div class="account-type-text">
                                    <h4>Professeur</h4>
                                    <p>Suivez les stages de vos étudiants et consultez leurs comptes-rendus</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mot de passe :</label>
                    <input type="password" name="mdp" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmation du mot de passe :</label>
                    <input type="password" name="confirmation_mdp" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-submit" name="inscription" value="1">S'inscrire</button>
                
                <div class="login-links">
                    <p>Déjà inscrit ? <a href="index.php">Connectez-vous ici</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
