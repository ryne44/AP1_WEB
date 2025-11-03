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
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accueil</title>
        <link rel="stylesheet" href="css/menu.css">
    </head>
    <body>
        <?php
        // Inclusion du menu selon le type d'utilisateur
        if ($_SESSION['Stype'] == 2) {
            include 'menu_eleve.php';
        } elseif ($_SESSION['Stype'] == 1) {
            include 'menu_prof.php';
        }
        ?>
        
        <div class="main-content">
            <?php
            // PARTIE ELEVE
            if ($_SESSION['Stype'] == 2) {
                $id = $_SESSION['Sid'];
                $requete = "SELECT prenom FROM utilisateur WHERE num='$id'";
                $resultat = mysqli_query($bdd, $requete);
                $user = mysqli_fetch_assoc($resultat);
                $prenom = $user['prenom'] ?? $_SESSION['Slogin'];
                ?>
                <h1>Bienvenue <?php echo htmlspecialchars($prenom); ?> !</h1>
                <p>Vous êtes connecté en tant qu'élève.</p>
                <?php
            }
            // PARTIE PROF
            elseif ($_SESSION['Stype'] == 1) {
                ?>
                <h1>Bienvenue <?php echo htmlspecialchars($_SESSION['Slogin']); ?> !</h1>
                <p>Vous êtes connecté en tant que professeur.</p>
                <?php
            }
            ?>
        </div>
    </body>
    </html>
    <?php
} else {
    echo "La connexion est perdue, veuillez revenir à la <a href='index.php'>page d'index</a>.";
}
?>