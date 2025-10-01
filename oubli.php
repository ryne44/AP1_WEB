<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

$mail = new PHPMailer(true);
?>

<?php


include 'conf.php';
//si j'ai envoyé un email
if (isset($_POST['email'])) {
    $lemail=$_POST['email'];
    //je me connecte a la BDD
    $bdd= mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    //je selectionne l'utilisateur qui a son email et je recupere son mdp
    $requete="Select * FROM utilisateur WHERE email='$lemail'";
    $resultat=mysqli_query($bdd,$requete);
    $mdp=0;  

    while($donnees=mysqli_fetch_assoc($resultat))
        {
        $mdp=$donnees['motdepasse'];
    }
    if($mdp==0)//si le mdp n'a pas changé c'est que l'email n'existe pasfficher erreur lemail nexiste pas
        {
            echo "erreur lemail nexiste pas";
        }
        else
        {   //sinon j'envoie le mdp a l'email
            echo "envoie de l'email";
            

            try {
    // Config SMTP Hostinger
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contact@sioslam.fr';  // ⚠️ remplace par ton email Hostinger
    $mail->Password   = '&5&Y@*QHb';            // ⚠️ remplace par le mot de passe de cette boîte mail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587;

    // Expéditeur
    $mail->setFrom('contact@sioslam.fr', 'CONTACT SIOSLAM');
    // Destinataire
    $mail->addAddress($lemail, 'Moi');

    // Contenu
    $mail->isHTML(true);
    $mail->Subject = 'Mot de passe perdue SIOSLAM';
    $mail->Body    = 'Mon message blablabla';

    $mail->send();
    echo "✅+ Email envoyé avec succès !";
} catch (Exception $e) {
    echo "❌ Erreur d'envoi : {$mail->ErrorInfo}";
}
        }



    }
        
    
    else //sinon j'affiche le formulaire
    {
        ?>
         <form method='post'>
            <input type='email' name='email' placeholder='votre email' required>
            <input type='submit' value='confirmer'>
        </form>
          
    
      <?php
    }
          



