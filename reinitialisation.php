<?php
session_start();
include '_conf.php';

$message = '';
$message_class = '';
$show_form = false;
$token_valide = false;

// Vérifier si le token est présent dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    
    if (!$connexion) {
        $message = "Erreur de connexion à la base de données.";
        $message_class = 'error';
    } else {
        // Vérifier si la table existe
        $table_check = mysqli_query($connexion, "SHOW TABLES LIKE 'reinitialisation_mdp'");
        
        if (mysqli_num_rows($table_check) == 0) {
            $message = "Le lien de réinitialisation est invalide (table manquante).";
            $message_class = 'error';
        } else {
            // Vérifier si le token est valide et n'a pas expiré
            $requete = "SELECT r.*, u.nom, u.prenom, u.login 
                       FROM reinitialisation_mdp r 
                       JOIN utilisateur u ON r.id_utilisateur = u.num 
                       WHERE r.token = ? AND r.used = 0 AND r.expiration > NOW()";
            $stmt = mysqli_prepare($connexion, $requete);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $token);
                mysqli_stmt_execute($stmt);
                $resultat = mysqli_stmt_get_result($stmt);
                
                if ($donnees = mysqli_fetch_assoc($resultat)) {
                    $token_valide = true;
                    $id_utilisateur = $donnees['id_utilisateur'];
                    $nom = $donnees['nom'];
                    $prenom = $donnees['prenom'];
                    $login = $donnees['login'];
                    $show_form = true;
                    
                    // Traitement du nouveau mot de passe
                    if (isset($_POST['changer_mdp'])) {
                        $nouveau_mdp = $_POST['nouveau_mdp'];
                        $confirmer_mdp = $_POST['confirmer_mdp'];
                        
                        if (empty($nouveau_mdp) || empty($confirmer_mdp)) {
                            $message = "Tous les champs sont obligatoires.";
                            $message_class = 'error';
                        } elseif ($nouveau_mdp !== $confirmer_mdp) {
                            $message = "Les mots de passe ne correspondent pas.";
                            $message_class = 'error';
                        } elseif (strlen($nouveau_mdp) < 6) {
                            $message = "Le mot de passe doit contenir au moins 6 caractères.";
                            $message_class = 'error';
                        } else {
                            // Hasher le nouveau mot de passe
                            $nouveau_mdp_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
                            
                            // Mettre à jour le mot de passe
                            $requete_update = "UPDATE utilisateur SET motdepasse = ? WHERE num = ?";
                            $stmt_update = mysqli_prepare($connexion, $requete_update);
                            
                            if ($stmt_update) {
                                mysqli_stmt_bind_param($stmt_update, "si", $nouveau_mdp_hash, $id_utilisateur);
                                
                                if (mysqli_stmt_execute($stmt_update)) {
                                    // Marquer le token comme utilisé
                                    $requete_token = "UPDATE reinitialisation_mdp SET used = 1 WHERE token = ?";
                                    $stmt_token = mysqli_prepare($connexion, $requete_token);
                                    
                                    if ($stmt_token) {
                                        mysqli_stmt_bind_param($stmt_token, "s", $token);
                                        mysqli_stmt_execute($stmt_token);
                                        mysqli_stmt_close($stmt_token);
                                    }
                                    
                                    $message = "✅ Votre mot de passe a été réinitialisé avec succès !<br>Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.";
                                    $message_class = 'success';
                                    $show_form = false;
                                    
                                    // Optionnel : connecter automatiquement l'utilisateur
                                    // $_SESSION["id"] = $id_utilisateur;
                                    // $_SESSION["login"] = $login;
                                    // $_SESSION["nom"] = $nom;
                                    // $_SESSION["prenom"] = $prenom;
                                    
                                } else {
                                    $message = "❌ Erreur lors de la réinitialisation du mot de passe.";
                                    $message_class = 'error';
                                }
                                
                                mysqli_stmt_close($stmt_update);
                            } else {
                                $message = "❌ Erreur de préparation de la requête.";
                                $message_class = 'error';
                            }
                        }
                    }
                } else {
                    $message = "❌ Le lien de réinitialisation est invalide ou a expiré.<br>Veuillez faire une nouvelle demande.";
                    $message_class = 'error';
                    $show_form = false;
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $message = "❌ Erreur de préparation de la requête.";
                $message_class = 'error';
            }
        }
        
        mysqli_close($connexion);
    }
} else {
    $message = "❌ Token manquant dans l'URL.";
    $message_class = 'error';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/connexion.css">
    <link rel="stylesheet" href="css/oubli.css">
    <style>
        /* Styles spécifiques pour cette page */
        .reinit-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            text-align: center;
            margin: 20px;
        }
        
        .reinit-header {
            margin-bottom: 30px;
        }
        
        .reinit-header h1 {
            color: #27ae60;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .reinit-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .user-info {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            text-align: left;
        }
        
        .form-container {
            width: 100%;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Segoe UI', sans-serif;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            border-color: #27ae60;
            outline: none;
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.2);
        }
        
        .password-rules {
            background: #e8f4fd;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: left;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .password-rules ul {
            margin: 8px 0;
            padding-left: 20px;
        }
        
        .password-rules li {
            margin-bottom: 5px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .btn-submit:hover {
            background: #219150;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-secondary {
            background: #3498db;
        }
        
        .btn-secondary:hover {
            background: #2980b9;
        }
        
        .login-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            width: 100%;
        }
        
        .login-links a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .login-links a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 15px;
            width: 100%;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info {
            background: #e8f4fd;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        @media (max-width: 480px) {
            .reinit-container {
                padding: 30px 20px;
                max-width: 100%;
                margin: 10px;
            }
            
            .reinit-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body class="connexion-body">
    <div class="reinit-container">
        <div class="reinit-header">
            <h1>Réinitialisation du mot de passe</h1>
            <p>Créez votre nouveau mot de passe</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_class; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($show_form && $token_valide && isset($prenom) && isset($nom)): ?>
            <div class="user-info">
                <strong>👤 Identité vérifiée :</strong><br>
                • Nom : <?php echo htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom); ?><br>
                • Login : <?php echo htmlspecialchars($login); ?>
            </div>
            
            <form action="reinitialisation.php?token=<?php echo htmlspecialchars($token); ?>" method="post" class="form-container">
                <div class="password-rules">
                    <strong>📋 Règles de sécurité :</strong>
                    <ul>
                        <li>Minimum 6 caractères</li>
                        <li>Utilisez des lettres, chiffres et caractères spéciaux</li>
                        <li>Évitez les mots de passe faciles à deviner</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe :</label>
                    <input type="password" name="nouveau_mdp" class="form-control" 
                           required minlength="6" placeholder="Saisissez votre nouveau mot de passe">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe :</label>
                    <input type="password" name="confirmer_mdp" class="form-control" 
                           required minlength="6" placeholder="Resaisissez votre nouveau mot de passe">
                </div>
                
                <button type="submit" class="btn-submit" name="changer_mdp" value="1">
                    🔐 Réinitialiser mon mot de passe
                </button>
            </form>
        <?php elseif(!$show_form && $message_class == 'success'): ?>
            <div style="text-align: center; margin-top: 20px;">
                <p>Votre mot de passe a été mis à jour avec succès !</p>
                <a href="index.php" class="btn-submit">
                    Se connecter maintenant
                </a>
            </div>
        <?php elseif(!$token_valide): ?>
            <div style="text-align: center; margin-top: 20px;">
                <p>Le lien de réinitialisation n'est plus valide.</p>
                <a href="oubli.php" class="btn-submit">
                    Demander un nouveau lien
                </a>
            </div>
        <?php endif; ?>
        
        <div class="login-links">
            <a href="index.php">← Retour à la connexion</a>
        </div>
    </div>
</body>
</html>