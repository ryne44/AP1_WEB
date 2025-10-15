<?php
include 'conf.php';

// === IMPORTATION DE LA BIBLIOTHÈQUE PHPMAILER ===
// PHPMailer est une bibliothèque PHP qui permet d'envoyer des emails facilement
// Elle gère l'authentification SMTP, les pièces jointes, le HTML, etc.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

// === ÉTAPE 1 : Connexion à la base de données ===
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion à la base de données");
}

$msg = ''; // Variable pour stocker les messages de succès ou d'erreur

// === ÉTAPE 2 : Traitement du formulaire ===
// On vérifie que le formulaire a été soumis ET que le champ email n'est pas vide
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']); // trim() supprime les espaces avant/après
    
    // === ÉTAPE 3 : Recherche de l'utilisateur dans la base de données ===
    // On utilise une REQUÊTE PRÉPARÉE pour éviter les injections SQL
    // Les requêtes préparées séparent la structure SQL des données utilisateur
    $stmt = mysqli_prepare($bdd, "SELECT num, login FROM utilisateur WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email); // "s" = string (type de donnée)
    mysqli_stmt_execute($stmt); // Exécution de la requête
    $result = mysqli_stmt_get_result($stmt); // Récupération du résultat
    
    // === ÉTAPE 4 : Si l'email existe dans la base de données ===
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result); // Récupération des infos de l'utilisateur
        
        // === ÉTAPE 5 : Génération d'un token unique et sécurisé ===
        // bin2hex(random_bytes(32)) génère une chaîne aléatoire de 64 caractères
        // Ce token est impossible à deviner (2^256 combinaisons possibles)
        $token = bin2hex(random_bytes(32));
        
        // === ÉTAPE 6 : Définition de l'expiration du token ===
        // Le token expire dans 1 heure pour des raisons de sécurité
        // Si quelqu'un intercepte le lien, il ne pourra l'utiliser que pendant 1h
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // === ÉTAPE 7 : Enregistrement du token dans la base de données ===
        // On associe le token à l'utilisateur pour pouvoir le retrouver plus tard
        $stmt_update = mysqli_prepare($bdd, "UPDATE utilisateur SET token = ?, token_expiration = ? WHERE num = ?");
        if ($stmt_update) {
            mysqli_stmt_bind_param($stmt_update, "ssi", $token, $expiration, $user['num']);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        } else {
            $msg = "Erreur BDD : " . mysqli_error($bdd);
        }
        
        // === ÉTAPE 8 : Construction automatique du lien de réinitialisation ===
        // Le lien est généré automatiquement selon l'environnement (local ou en ligne)
        // Exemple : http://localhost/monprojet/reset.php?token=abc123...
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST']; // Ex: localhost ou www.monsite.com
        $path = dirname($_SERVER['PHP_SELF']); // Ex: /monprojet
        $lien = $protocol . "://" . $host . $path . "/reset.php?token=" . $token;
        
        // === ÉTAPE 9 : Configuration et envoi de l'email ===
        $mail = new PHPMailer(true); // true = active les exceptions en cas d'erreur
        try {
            // === Configuration du serveur SMTP ===
            // SMTP = protocole d'envoi d'emails (comme un facteur numérique)
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com'; // Serveur d'envoi de Hostinger
            $mail->SMTPAuth = true; // Authentification requise
            $mail->Username = 'contact@sioslam.fr'; // Adresse email d'envoi
            $mail->Password = '&5&Y@*QHb'; // Mot de passe de l'adresse email
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Chiffrement sécurisé
            $mail->Port = 587; // Port SMTP standard
            $mail->CharSet = 'UTF-8'; // Encodage des caractères (accents, etc.)

            // === Configuration du contenu de l'email ===
            $mail->setFrom('contact@sioslam.fr', 'CONTACT SIOSLAM'); // Expéditeur
            $mail->addAddress($email); // Destinataire (l'utilisateur qui a fait la demande)
            $mail->isHTML(true); // Active le HTML dans l'email
            $mail->Subject = 'Réinitialisation de mot de passe'; // Objet de l'email
            $mail->Body = "Cliquez sur ce lien pour réinitialiser votre mot de passe :<br><br><a href='$lien'>$lien</a><br><br>Ce lien expire dans 1 heure.";
            
            // === ÉTAPE 10 : Envoi effectif de l'email ===
            $mail->send();
            $msg = '✅ Email envoyé ! Vérifiez votre boîte mail (et les spams).';
        } catch (Exception $e) {
            // Si l'envoi échoue, on affiche l'erreur
            $msg = "❌ Erreur d'envoi : " . $mail->ErrorInfo;
        }
    } else {
        // === Message générique pour ne pas révéler si l'email existe ou non ===
        // Ceci évite qu'un pirate puisse tester des emails pour savoir qui est inscrit
        $msg = 'Si cet email existe, vous recevrez un lien.';
    }
    
    mysqli_stmt_close($stmt); // Fermeture de la requête préparée
}

mysqli_close($bdd); // Fermeture de la connexion à la base de données
?>

<!-- === FORMULAIRE DE DEMANDE DE RÉINITIALISATION === -->
<!-- L'utilisateur entre son email et reçoit un lien par mail -->
<form method="post">
    <label for="email">Adresse email :</label><br>
    <input type="email" id="email" name="email" placeholder="votre@email.com" required><br><br>
    <button type="submit">📧 Recevoir le lien de réinitialisation</button>
    
    <!-- Affichage des messages de succès ou d'erreur -->
    <?php if ($msg) echo "<p style='margin-top:15px;'>$msg</p>"; ?>
</form>

<p style="margin-top:20px;"><a href="index.php">← Retour à la connexion</a></p>