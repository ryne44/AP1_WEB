<?php
session_start();

// Inclusion de PHPMailer en haut du fichier
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Vérifier si le formulaire a été envoyé
if (isset($_POST['email'])) {
    $lemail = $_POST['email'];
    
    // Afficher l'email pour test
    echo "le formulaire a été envoyé avec comme email la valeur : " . htmlspecialchars($lemail);
    echo "<br><br>";
    
    // Connexion à la base de données
    include '_conf.php';
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    
    if (!$connexion) {
        die("Erreur de connexion à la base de données : " . mysqli_connect_error());
    }
    
    // Requête pour vérifier si l'email existe
    $requete = "SELECT * FROM utilisateur WHERE email = ?";
    $stmt = mysqli_prepare($connexion, $requete);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $lemail);
        mysqli_stmt_execute($stmt);
        $resultat = mysqli_stmt_get_result($stmt);
        
        $login = 0;
        $mdp = "";
        $nom = "";
        $prenom = "";
        $id_utilisateur = 0;
        
        while($donnees = mysqli_fetch_assoc($resultat)) {
            $login = $donnees['login']; // mettre le nom du champ dans la table
            $mdp = $donnees['motdepasse']; // mettre le nom du champ dans la table
            $nom = $donnees['nom'];
            $prenom = $donnees['prenom'];
            $id_utilisateur = $donnees['num'];
        }
        
        mysqli_stmt_close($stmt);
        
        // Vérifier si l'utilisateur a été trouvé
        if ($login == 0) {
            echo "❌ Cette adresse email n'appartient à aucun utilisateur.";
            echo "<br><br><a href='oubli.php' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour</a>";
        } else {
            echo "✅ Utilisateur trouvé : " . htmlspecialchars($prenom) . " " . htmlspecialchars($nom) . "<br>";
            echo "Login : " . htmlspecialchars($login) . "<br><br>";
            
            // Vérifier si la table reinitialisation_mdp existe
            $table_check = mysqli_query($connexion, "SHOW TABLES LIKE 'reinitialisation_mdp'");
            if (mysqli_num_rows($table_check) == 0) {
                // Créer la table si elle n'existe pas
                $create_table = "CREATE TABLE reinitialisation_mdp (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    token VARCHAR(64) NOT NULL,
                    id_utilisateur INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expiration DATETIME NOT NULL,
                    used TINYINT(1) DEFAULT 0,
                    INDEX idx_token (token),
                    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(num) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                if (!mysqli_query($connexion, $create_table)) {
                    echo "❌ Erreur lors de la création de la table : " . mysqli_error($connexion);
                    echo "<br><br><a href='oubli.php' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour</a>";
                    mysqli_close($connexion);
                    exit();
                }
            }
            
            // Générer un token de réinitialisation
            $token = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Insérer le token dans la base de données
            $requete_token = "INSERT INTO reinitialisation_mdp (token, id_utilisateur, expiration) VALUES (?, ?, ?)";
            $stmt_token = mysqli_prepare($connexion, $requete_token);
            
            if ($stmt_token) {
                mysqli_stmt_bind_param($stmt_token, "sis", $token, $id_utilisateur, $expiration);
                
                if (mysqli_stmt_execute($stmt_token)) {
                    // Configurer PHPMailer
                    $mail = new PHPMailer(true);
                    
                    try {
                        // Configuration SMTP Hostinger avec vos identifiants
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.hostinger.com'; // Serveur SMTP de Hostinger
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'contact@siolapie.com'; // Votre email
                        $mail->Password   = 'EmailL@pie25'; // Votre mot de passe
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
                        $mail->Port       = 587; // Port pour TLS
                        
                        // Encodage
                        $mail->CharSet = 'UTF-8';
                        
                        // Expéditeur
                        $mail->setFrom('contact@siolapie.com', 'Suivi Stages');
                        // Destinataire
                        $mail->addAddress($lemail, $prenom . ' ' . $nom);
                        
                        // Contenu de l'email
                        $mail->isHTML(true);
                        $mail->Subject = 'Réinitialisation de votre mot de passe - Suivi Stages';
                        
                        // Lien de réinitialisation
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                        $lien_reinitialisation = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reinitialisation.php?token=' . $token;
                        
                        $mail->Body    = '
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <meta charset="UTF-8">
                                <style>
                                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
                                    .header { background-color: #3498db; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                                    .content { background-color: white; padding: 30px; border-radius: 0 0 5px 5px; }
                                    .button { display: inline-block; background-color: #e74c3c; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                                    .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #777; font-size: 12px; }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <div class="header">
                                        <h1>Suivi des Stages</h1>
                                    </div>
                                    <div class="content">
                                        <h2>Bonjour ' . htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom) . ',</h2>
                                        <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte <strong>Suivi des Stages</strong>.</p>
                                        <p>Votre login : <strong>' . htmlspecialchars($login) . '</strong></p>
                                        <p>Pour réinitialiser votre mot de passe, cliquez sur le bouton ci-dessous :</p>
                                        <p style="text-align: center;">
                                            <a href="' . $lien_reinitialisation . '" class="button">Réinitialiser mon mot de passe</a>
                                        </p>
                                        <p>Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :</p>
                                        <p style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; font-family: monospace; font-size: 12px;">
                                            ' . $lien_reinitialisation . '
                                        </p>
                                        <p><strong>Ce lien expirera dans 24 heures.</strong></p>
                                        <p>Si vous n\'êtes pas à l\'origine de cette demande, veuillez ignorer cet email.</p>
                                        <div class="footer">
                                            <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                                            <p>© ' . date('Y') . ' Suivi des Stages</p>
                                        </div>
                                    </div>
                                </div>
                            </body>
                            </html>
                        ';
                        
                        $mail->AltBody = "Bonjour " . $prenom . " " . $nom . ",

Vous avez demandé la réinitialisation de votre mot de passe pour votre compte Suivi des Stages.

Votre login : " . $login . "

Pour réinitialiser votre mot de passe, cliquez sur ce lien :
" . $lien_reinitialisation . "

Ce lien expirera dans 24 heures.

Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.

Cordialement,
L'équipe Suivi des Stages";
                        
                        if ($mail->send()) {
                            echo "✅ Email envoyé avec succès à <strong>" . htmlspecialchars($lemail) . "</strong> !";
                            echo "<br><br><strong>Consignes :</strong>";
                            echo "<br>1. Vérifiez votre boîte de réception (et vos spams)";
                            echo "<br>2. Cliquez sur le lien dans l'email";
                            echo "<br>3. Créez un nouveau mot de passe";
                            echo "<br><br><a href='index.php' style='background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Retour à la connexion</a>";
                        } else {
                            echo "❌ Erreur d'envoi de l'email : " . $mail->ErrorInfo;
                            echo "<br><br><a href='oubli.php' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour</a>";
                        }
                    } catch (Exception $e) {
                        echo "❌ Erreur PHPMailer : " . $e->getMessage();
                        echo "<br><br><a href='oubli.php' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour</a>";
                    }
                } else {
                    echo "❌ Erreur lors de l'enregistrement du token : " . mysqli_error($connexion);
                    echo "<br><br><a href='oubli.php' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour</a>";
                }
                
                mysqli_stmt_close($stmt_token);
            } else {
                echo "❌ Erreur de préparation de la requête token : " . mysqli_error($connexion);
                echo "<br><br><a href='oubli.php' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour</a>";
            }
        }
        
        mysqli_close($connexion);
    } else {
        echo "❌ Erreur de préparation de la requête : " . mysqli_error($connexion);
        echo "<br><br><a href='oubli.php' style='background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Retour</a>";
    }
} else {
    // Afficher le formulaire si le formulaire n'a pas été envoyé
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mot de passe oublié - Suivi Stages</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/connexion.css">
        <link rel="stylesheet" href="css/oubli.css">
        <style>
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
                border-color: #e74c3c;
                outline: none;
                box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
            }
            
            .btn-submit {
                width: 100%;
                padding: 14px;
                background: #e74c3c;
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
                background: #c0392b;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            
            .login-links {
                text-align: center;
                margin-top: 25px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                width: 100%;
            }
            
            .login-links a {
                color: #e74c3c;
                text-decoration: none;
                font-weight: 500;
                transition: color 0.3s;
            }
            
            .login-links a:hover {
                color: #c0392b;
                text-decoration: underline;
            }
            
            .info-box {
                background: #e8f4fd;
                border-left: 4px solid #3498db;
                padding: 15px;
                margin: 20px 0;
                border-radius: 0 8px 8px 0;
                font-size: 14px;
                color: #2c3e50;
            }
        </style>
    </head>
    <body class="connexion-body">
        <div class="oubli-container">
            <div class="oubli-header">
                <h1>Mot de passe oublié</h1>
                <p>Entrez votre email pour réinitialiser votre mot de passe</p>
            </div>
            
            <div class="info-box">
                <strong>📧 Comment ça marche ?</strong><br>
                1. Saisissez l'email associé à votre compte<br>
                2. Nous vous enverrons un lien de réinitialisation<br>
                3. Cliquez sur le lien pour créer un nouveau mot de passe
            </div>
            
            <form action="oubli.php" method="post" class="form-container">
                <div class="form-group">
                    <label class="form-label">Email :</label>
                    <input type="email" name="email" class="form-control" 
                           required placeholder="exemple@email.com" autocomplete="email">
                </div>
                
                <button type="submit" class="btn-submit" name="envoi_perdu" value="1">
                    Envoyer le lien de réinitialisation
                </button>
                
                <div class="login-links">
                    <a href="index.php">← Retour à la connexion</a>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
}
?>