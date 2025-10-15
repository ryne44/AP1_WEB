<?php
session_start();
include 'conf.php';

// Vérification de la connexion et du type utilisateur
if (!isset($_SESSION['Sid']) || $_SESSION['Stype'] != 2) {
    echo "⚠️ Accès refusé. <a href='index.php'>Connectez-vous</a>";
    exit;
}

// Connexion BDD
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion BDD : " . mysqli_connect_error());
}

// Récupération de l'ID de l'élève connecté
$id_utilisateur = $_SESSION['Sid'];

// Récupération de tous les comptes rendus de l'élève
$requete = "SELECT * FROM cr WHERE num_utilisateur='$id_utilisateur' ORDER BY date DESC";
$resultat = mysqli_query($bdd, $requete);

if (!$resultat) {
    die("Erreur SQL : " . mysqli_error($bdd));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des comptes rendus</title>
</head>
<body>
    <h1>📋 Mes comptes rendus</h1>
    
    <p><a href="accueil.php">⬅️ Retour à l'accueil</a></p>
    
    <hr>
    
    <?php
    // Vérifier s'il y a des comptes rendus
    if (mysqli_num_rows($resultat) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr>
                <th>Numéro</th>
                <th>Date</th>
                <th>Description</th>
                <th>Vu par le prof</th>
                <th>Date de création</th>
                <th>Actions</th>
              </tr>";
        
        while ($cr = mysqli_fetch_assoc($resultat)) {
            $vu_texte = $cr['vu'] == 1 ? "✅ Oui" : "❌ Non";
            $date_affichage = $cr['date'] ? date('d/m/Y', strtotime($cr['date'])) : "Non définie";
            $datetime_affichage = $cr['datetime'] ? date('d/m/Y H:i', strtotime($cr['datetime'])) : "Non définie";
            
            echo "<tr>";
            echo "<td>" . $cr['num'] . "</td>";
            echo "<td>" . $date_affichage . "</td>";
            echo "<td>" . nl2br(htmlspecialchars(substr($cr['description'], 0, 100))) . "...</td>";
            echo "<td>" . $vu_texte . "</td>";
            echo "<td>" . $datetime_affichage . "</td>";
            echo "<td>
                    <a href='creer_compte_rendu.php?id=" . $cr['num'] . "'>✏️ Modifier</a>
                  </td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Aucun compte rendu trouvé.</p>";
        echo "<p><a href='creer_compte_rendu.php'>➕ Créer mon premier compte rendu</a></p>";
    }
    ?>
    
    <br>
    <p><a href="creer_compte_rendu.php">➕ Créer un nouveau compte rendu</a></p>
    
</body>
</html>