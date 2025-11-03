<?php
session_start();
include 'conf.php';

// Vérification de la connexion et du type utilisateur (ELEVE uniquement)
if (!isset($_SESSION['Sid']) || $_SESSION['Stype'] != 2) {
    echo "⚠️ Accès refusé. <a href='index.php'>Connectez-vous</a>";
    exit;
}

// Connexion BDD
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion BDD : " . mysqli_connect_error());
}

$id_utilisateur = $_SESSION['Sid'];
$message = "";
$cr_existant = null;
$mode = "insertion"; // Par défaut : insertion
$date_selectionnee = date('Y-m-d'); // Date du jour par défaut

// ===== GESTION DE LA VALIDATION DE LA DATE =====
// Quand l'utilisateur valide une date, on vérifie si un CR existe déjà
if (isset($_POST['valider_date'])) {
    $date_selectionnee = mysqli_real_escape_string($bdd, $_POST['date']);
    
    // Vérifier si un CR existe pour cette date
    $requete_check = "SELECT * FROM cr WHERE num_utilisateur='$id_utilisateur' AND date='$date_selectionnee'";
    $resultat_check = mysqli_query($bdd, $requete_check);
    
    if (mysqli_num_rows($resultat_check) > 0) {
        // CR existant trouvé -> mode MODIFICATION
        $cr_existant = mysqli_fetch_assoc($resultat_check);
        $mode = "modification";
    }
}

// ===== GESTION DE L'ÉDITION D'UN CR EXISTANT VIA L'ID =====
// Si on arrive sur la page avec ?id=X (depuis liste_comptes_rendus.php)
if (isset($_GET['id'])) {
    $id_cr = mysqli_real_escape_string($bdd, $_GET['id']);
    
    // Récupérer le CR correspondant
    $requete = "SELECT * FROM cr WHERE num='$id_cr' AND num_utilisateur='$id_utilisateur'";
    $resultat = mysqli_query($bdd, $requete);
    
    if (mysqli_num_rows($resultat) > 0) {
        $cr_existant = mysqli_fetch_assoc($resultat);
        $date_selectionnee = $cr_existant['date'];
        $mode = "modification";
    } else {
        $message = "<p style='color:red;'>❌ Compte rendu introuvable.</p>";
    }
}

// ===== INSERTION OU MISE À JOUR DU CR =====
if (isset($_POST['inserer'])) {
    $date = mysqli_real_escape_string($bdd, $_POST['date']);
    $descriptif = mysqli_real_escape_string($bdd, $_POST['descriptif']);
    
    // Vérifier si un CR existe déjà pour cette date
    $requete_verif = "SELECT num FROM cr WHERE num_utilisateur='$id_utilisateur' AND date='$date'";
    $resultat_verif = mysqli_query($bdd, $requete_verif);
    
    if (mysqli_num_rows($resultat_verif) > 0) {
        // UPDATE : Le CR existe déjà
        $cr = mysqli_fetch_assoc($resultat_verif);
        $num_cr = $cr['num'];
        
        $requete_update = "UPDATE cr 
                          SET description='$descriptif', datetime=NOW() 
                          WHERE num='$num_cr'";
        
        if (mysqli_query($bdd, $requete_update)) {
            $message = "<p style='color:green;'>✅ Compte rendu mis à jour avec succès !</p>";
            // Recharger le CR mis à jour
            $resultat = mysqli_query($bdd, "SELECT * FROM cr WHERE num='$num_cr'");
            $cr_existant = mysqli_fetch_assoc($resultat);
            $mode = "modification";
        } else {
            $message = "<p style='color:red;'>❌ Erreur lors de la mise à jour : " . mysqli_error($bdd) . "</p>";
        }
    } else {
        // INSERT : Nouveau CR
        // On ne spécifie pas 'num' car il est auto-incrémenté
        $requete_insert = "INSERT INTO cr (num_utilisateur, date, description, datetime, vu) 
                          VALUES ('$id_utilisateur', '$date', '$descriptif', NOW(), DEFAULT)";
        
        if (mysqli_query($bdd, $requete_insert)) {
            $message = "<p style='color:green;'>✅ Compte rendu créé avec succès !</p>";
            // Récupérer le CR nouvellement créé
            $nouveau_id = mysqli_insert_id($bdd);
            $resultat = mysqli_query($bdd, "SELECT * FROM cr WHERE num='$nouveau_id'");
            $cr_existant = mysqli_fetch_assoc($resultat);
            $mode = "modification";
        } else {
            $message = "<p style='color:red;'>❌ Erreur lors de l'insertion : " . mysqli_error($bdd) . "</p>";
        }
    }
    
    $date_selectionnee = $date;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affichage compte rendu</title>
    <link rel="stylesheet" href="css/creer_compte_rendu.css">
</head>
<body>
    <h1>📝 Affichage compte rendu</h1>
    
    <div class="nav-links">
        <a href="accueil.php">⬅️ Retour à l'accueil</a> | 
        <a href="liste_comptes_rendus.php">📋 Liste des CR</a>
    </div>
    
    <?php echo $message; ?>
    
    <?php if ($mode == "modification" && $cr_existant): ?>
        <div class="info-box">
            ℹ️ <strong>Mode modification</strong> - Un compte rendu existe déjà pour cette date
        </div>
    <?php endif; ?>
    
    <!-- ÉTAPE 1 : SÉLECTION DE LA DATE -->
    <div class="form-section">
        <h3>1️⃣ Sélection de la date</h3>
        <form method="post">
            <label for="date">Compte rendu du Date :</label>
            <input type="date" 
                   id="date" 
                   name="date" 
                   value="<?php echo htmlspecialchars($date_selectionnee); ?>" 
                   required>
            <button type="submit" name="valider_date" class="btn-secondary">📅 Valider la date</button>
        </form>
    </div>
    
    <!-- ÉTAPE 2 : SAISIE DU DESCRIPTIF -->
    <div class="form-section">
        <h3>2️⃣ Description du compte rendu</h3>
        <form method="post">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date_selectionnee); ?>">
            
            <label for="descriptif">Descriptif :</label>
            <textarea id="descriptif" 
                      name="descriptif" 
                      placeholder="Décrivez les activités réalisées ce jour...&#10;&#10;Exemple :&#10;- Réunion avec le tuteur&#10;- Développement de la fonctionnalité X&#10;- Tests et corrections"
                      required><?php 
                if ($cr_existant) {
                    echo htmlspecialchars($cr_existant['description']);
                }
            ?></textarea>
            
            <button type="submit" name="inserer">
                <?php echo ($mode == "modification") ? "💾 Modifier" : "➕ Insérer"; ?>
            </button>
        </form>
    </div>
    
    <!-- INFORMATIONS SUR LE CR EXISTANT -->
    <?php if ($cr_existant): ?>
        <div class="form-section">
            <h3>📊 Informations sur ce compte rendu</h3>
            <div class="info-details">
                <p><strong>📅 Date :</strong> <?php echo date('d/m/Y', strtotime($cr_existant['date'])); ?></p>
                <p><strong>🕐 Date de création :</strong> <?php echo $cr_existant['datetime'] ? date('d/m/Y à H:i', strtotime($cr_existant['datetime'])) : "Non définie"; ?></p>
                <p><strong>👁️ Vu par le prof :</strong> <?php echo $cr_existant['vu'] == 1 ? "✅ Oui" : "❌ Non"; ?></p>
                <p><strong>🔢 Numéro :</strong> <?php echo $cr_existant['num']; ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="links">
        <a href="commentaires.php">💬 Voir les commentaires</a>
    </div>
    
</body>
</html>

<?php
mysqli_close($bdd);
?>