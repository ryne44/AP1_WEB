
<?php
session_start(); 
include '_conf.php';

if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit();
}

$message = '';
$message_class = '';

if (isset($_POST['insertion'])) {
    $date = $_POST['date'];
    $contenu = addslashes($_POST['contenu']);
    $id = $_SESSION["id"];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    $requete = "INSERT INTO cr (date, datetime, description, num_utilisateur) VALUES ('$date', NOW(), '$contenu', '$id')";
    
    if(!mysqli_query($connexion, $requete)) {
        $message = "Erreur lors de la création du compte-rendu";
        $message_class = 'error';
    } else {
        $message = "Compte-rendu créé avec succès";
        $message_class = 'success';
    }
}

if (isset($_POST['update'])) {
    $date = $_POST["date"];
    $description = addslashes($_POST["contenu"]);
    $idCR = $_POST["idCR"];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    $requete = "UPDATE cr SET date = '$date', description = '$description' WHERE num = $idCR";
    
    if(!mysqli_query($connexion, $requete)) {
        $message = "Erreur lors de la modification du compte-rendu";
        $message_class = 'error';
    } else {
        $message = "Compte-rendu modifié avec succès";
        $message_class = 'success';
    }
}

if (isset($_POST['delete'])) {
    $idCR = $_POST["idCR"];
    $id = $_SESSION["id"];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    $requete = "DELETE FROM cr WHERE num = $idCR AND num_utilisateur = $id";
    
    if(!mysqli_query($connexion, $requete)) {
        $message = "Erreur lors de la suppression du compte-rendu";
        $message_class = 'error';
    } else {
        $message = "Compte-rendu supprimé avec succès";
        $message_class = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte-rendus - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/tableau.css">
    <?php if($_SESSION["type"]==0): ?>
        <link rel="stylesheet" href="css/menueleve.css">
    <?php else: ?>
        <link rel="stylesheet" href="css/menuprof.css">
    <?php endif; ?>
</head>
<body>
    <?php if($_SESSION["type"]==0): ?>
        <?php include '_menuEleve.php'; ?>
    <?php else: ?>
        <?php include '_menuProf.php'; ?>
    <?php endif; ?>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="table-container">
            <h2>Liste des comptes-rendus</h2>
            
            <?php
            if ($_SESSION["type"] == 1) {
                $requete = "SELECT cr.*, utilisateur.prenom, utilisateur.nom FROM cr, utilisateur WHERE utilisateur.num = cr.num_utilisateur ORDER BY date DESC";
            } else {
                $id = $_SESSION["id"];
                $requete = "SELECT * FROM cr WHERE num_utilisateur = $id ORDER BY date DESC";
            }
            
            $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
            $resultat = mysqli_query($connexion, $requete);
            
            if (mysqli_num_rows($resultat) > 0) {
                echo "<table class='data-table'>";
                echo "<thead>";
                echo "<tr>";
                if ($_SESSION["type"] == 1) {
                    echo "<th>Élève</th>";
                }
                echo "<th>Date</th>";
                echo "<th>Contenu</th>";
                if ($_SESSION["type"] == 0) {
                    echo "<th>Actions</th>";
                }
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                
                while($donnees = mysqli_fetch_assoc($resultat)) {
                    echo "<tr>";
                    if ($_SESSION["type"] == 1) {
                        echo "<td>" . $donnees['prenom'] . " " . $donnees['nom'] . "</td>";
                    }
                    echo "<td>" . date('d/m/Y', strtotime($donnees['date'])) . "</td>";
                    echo "<td>" . substr(stripslashes($donnees['description']), 0, 150) . "...</td>";
                    if ($_SESSION["type"] == 0) {
                        echo "<td class='table-actions'>";
                        echo "<a href='ccr.php?id=" . $donnees['num'] . "' class='action-btn edit-btn'>Modifier</a>";
                        echo "<form method='post' style='margin: 0;'>";
                        echo "<input type='hidden' name='idCR' value='" . $donnees['num'] . "'>";
                        echo "<button type='submit' name='delete' class='action-btn delete-btn' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer ce compte-rendu ?');\">Supprimer</button>";
                        echo "</form>";
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p style='text-align: center; color: #7f8c8d; padding: 30px;'>Aucun compte-rendu trouvé</p>";
                if ($_SESSION["type"] == 0) {
                    echo "<div style='text-align: center; margin-top: 20px;'>";
                    echo "<a href='ccr.php' class='btn btn-primary'>Créer un compte-rendu</a>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>