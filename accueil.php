<?php
session_start();
include "conf.php";

// Connexion BDD
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion BDD : " . mysqli_connect_error());
}

// Déconnexion
if (isset($_POST['deconnexion'])) {
    session_destroy();
    echo "Deconnexion effectuee. Merci de votre visite !<br>";
    echo '<a href="index.php">Retour a l\'accueil</a>';
    exit;
}

// Connexion
if (isset($_POST['send_con'])) {
    $login = mysqli_real_escape_string($bdd, $_POST['login']);
    $mdp = md5($_POST['mdp']);

    $requete = "SELECT * FROM utilisateur WHERE login='$login' AND motdepasse='$mdp' LIMIT 1";
    $resultat = mysqli_query($bdd, $requete);

    if (!$resultat) {
        die("Erreur SQL : " . mysqli_error($bdd));
    }

    if (mysqli_num_rows($resultat) === 1) {
        $user = mysqli_fetch_assoc($resultat);
        $_SESSION['Sid'] = $user['num'];
        $_SESSION['Slogin'] = $user['login'];
        $_SESSION['Stype'] = $user['type'];
        echo "Connexion reussie !<br>";
    } else {
        echo "Erreur de login ou mot de passe.<br>";
    }
}

// Vérification session
if (isset($_SESSION['Sid'])) {
    
    // ============================================
    // PARTIE ELEVE (type = 2)
    // ============================================
    if ($_SESSION['Stype'] == 2) {
        // Récupération du prénom de l'élève
        $id = $_SESSION['Sid'];
        $requete = "SELECT prenom FROM utilisateur WHERE num='$id'";
        $resultat = mysqli_query($bdd, $requete);
        $user = mysqli_fetch_assoc($resultat);
        $prenom = $user['prenom'] ?? $_SESSION['Slogin'];
        ?>
        
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Accueil Élève</title>
        </head>
        <body>
            <h1>Bienvenue <?php echo htmlspecialchars($prenom); ?> !</h1>
            
            <form method="post">
                <button type="submit" name="deconnexion">Se déconnecter</button>
            </form>
            
            <hr>
            
            <h2>Menu</h2>
            
            <ul>
                <li><a href="liste_comptes_rendus.php">📋 Liste des comptes rendus</a></li>
                <li><a href="creer_compte_rendu.php">✏️ Créer/modifier un compte rendu</a></li>
                <li><a href="commentaires.php">💬 Commentaires</a></li>
            </ul>
            
            <hr>
            
            <h3>Bonus</h3>
            <p><a href="perso.php">👤 Mes informations personnelles</a></p>
            
        </body>
        </html>
        
        <?php
    }
    
    // ============================================
    // PARTIE PROF (type = 1)
    // ============================================
    elseif ($_SESSION['Stype'] == 1) {
        echo "Vous etes connecte en tant que <b>" . $_SESSION['Slogin'] . "</b><br>";
        echo '<a href="perso.php">Voir mes infos</a><br>';

        echo '<form method="post">
                <input type="submit" name="deconnexion" value="Se deconnecter">
              </form>';
        
        echo "<br>PARTIE PROF (à développer)";
    }
    
    // ============================================
    // TYPE NON DEFINI
    // ============================================
    else {
        echo "Vous etes connecte en tant que <b>" . $_SESSION['Slogin'] . "</b><br>";
        echo '<form method="post">
                <input type="submit" name="deconnexion" value="Se deconnecter">
              </form>';
        echo "<br>Type non defini.";
    }

} else {
    echo "La connexion est perdue, veuillez revenir à la <a href='index.php'>page d'index</a>.";
}
?>