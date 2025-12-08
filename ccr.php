
<?php
session_start(); 
include '_conf.php';

if (!isset($_SESSION["login"]) || $_SESSION["type"] != 0) {
    header("Location: index.php");
    exit();
}

$page_title = isset($_GET["id"]) ? "Modifier un compte-rendu" : "Créer un compte-rendu";

if(isset($_GET["id"])) {
    $idCR = $_GET["id"];
    $iduser = $_SESSION["id"];
    
    $connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    $requete = "SELECT * FROM cr WHERE num = '$idCR' AND num_utilisateur = $iduser";
    $resultat = mysqli_query($connexion, $requete);
    $trouve = 0;
    
    while($donnees = mysqli_fetch_assoc($resultat)) {
        $trouve = 1;
        $date = $donnees['date'];
        $description = $donnees['description'];                 
    }
    
    if ($trouve == 0) {
        header("Location: cr.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Suivi Stages</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/menueleve.css">
    <link rel="stylesheet" href="css/formulaire.css">
</head>
<body>
    <div class="main-container">
        <?php include '_menuEleve.php'; ?>
        
        <div class="content-center">
            <div class="form-card">
                <div class="form-header">
                    <h1><?php echo isset($idCR) ? 'Modifier un compte-rendu' : 'Créer un compte-rendu'; ?></h1>
                    <p><?php echo isset($idCR) ? 'Modifiez les informations de votre compte-rendu' : 'Remplissez les informations pour créer un nouveau compte-rendu'; ?></p>
                </div>
                
                <form action="cr.php" method="post">
                    <div class="form-group">
                        <label class="form-label">Date du compte-rendu :</label>
                        <input type="date" name="date" class="form-control" 
                               value="<?php echo isset($date) ? $date : date('Y-m-d'); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contenu détaillé :</label>
                        <textarea name="contenu" class="form-control" rows="8" required 
                                  placeholder="Décrivez vos activités, les tâches réalisées, les compétences acquises, les difficultés rencontrées, etc..."><?php echo isset($description) ? stripslashes($description) : ''; ?></textarea>
                    </div>
                    
                    <?php if(isset($idCR)): ?>
                        <input type="hidden" name="idCR" value="<?php echo $idCR; ?>">
                    <?php endif; ?>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full" 
                                name="<?php echo isset($idCR) ? 'update' : 'insertion'; ?>">
                            <?php echo isset($idCR) ? 'Mettre à jour' : 'Créer le compte-rendu'; ?>
                        </button>
                        
                        <a href="cr.php" class="btn btn-secondary btn-full">
                            Annuler et retourner à la liste
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
