<?php
session_start();
include 'conf.php';

// Vérification de connexion
if (!isset($_SESSION['Sid'])) {
    echo "⚠️ Accès refusé. <a href='index.php'>Connectez-vous</a>";
    exit;
}

$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur connexion BDD : " . mysqli_connect_error());
}

$id = $_SESSION['Sid'];
$message = "";

// ===== 1. MISE À JOUR INFOS =====
if (isset($_POST['update'])) {
    $email = mysqli_real_escape_string($bdd, $_POST['email']);
    $old_mdp = md5($_POST['old_mdp']);
    $new_mdp = md5($_POST['new_mdp']);

    // Vérifie que l'ancien mot de passe est bon
    $check = mysqli_query($bdd, "SELECT * FROM utilisateur WHERE num='$id' AND motdepasse='$old_mdp'");
    if (mysqli_num_rows($check) == 1) {
        $sql = "UPDATE utilisateur SET email='$email', motdepasse='$new_mdp' WHERE num='$id'";
        if (mysqli_query($bdd, $sql)) {
            $message = "<p style='color:green;'>✅ Informations mises à jour avec succès.</p>";
        } else {
            $message = "<p style='color:red;'>❌ Erreur lors de la mise à jour.</p>";
        }
    } else {
        $message = "<p style='color:red;'>❌ Ancien mot de passe incorrect.</p>";
    }
}

// ===== 2. RÉCUPÉRATION DES INFOS =====
$result = mysqli_query($bdd, "SELECT * FROM utilisateur WHERE num='$id'");
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes informations</title>
</head>
<body>
    <h2>Informations personnelles de <?php echo htmlspecialchars($_SESSION['Slogin']); ?></h2>

    <?php echo $message; ?>

    <form method="post" action="">
        <label>Email :</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

        <label>Ancien mot de passe :</label><br>
        <input type="password" name="old_mdp" required><br><br>

        <label>Nouveau mot de passe :</label><br>
        <input type="password" name="new_mdp" required><br><br>

        <button type="submit" name="update">Mettre à jour</button>
    </form>

    <br>
    <a href="accueil.php">⬅ Retour à l'accueil</a>
</body>
</html>
