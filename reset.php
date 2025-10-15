<?php
include 'conf.php';

// === ÉTAPE 1 : Connexion à la base de données ===
$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur connexion BDD");
}

// === ÉTAPE 2 : Vérification du token dans l'URL ===
// Le token est passé dans l'URL quand l'utilisateur clique sur le lien reçu par email
// Exemple : reset.php?token=abc123def456...
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('Lien invalide'); 
}
$token = mysqli_real_escape_string($bdd, $_GET['token']);

// === ÉTAPE 3 : Recherche de l'utilisateur avec ce token ===
// On vérifie que le token existe bien dans la base de données
// Si le token n'existe pas = quelqu'un essaie d'accéder à la page sans lien valide
$query = "SELECT num FROM utilisateur WHERE token = '$token'";
$result = mysqli_query($bdd, $query);

if (mysqli_num_rows($result) === 0) {
    die('Token invalide ou expiré'); 
}

$user = mysqli_fetch_assoc($result);

// === ÉTAPE 4 : Traitement du formulaire de changement de mot de passe ===
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['password_confirm'];
    
    // === Validation des données saisies ===
    // On vérifie que les deux mots de passe sont identiques
    if ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas";
    } 
    // On vérifie que le mot de passe fait au moins 6 caractères
    elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit faire au moins 6 caractères";
    } 
    // Si tout est OK, on enregistre le nouveau mot de passe
    else {
        // === ÉTAPE 5 : Hachage du mot de passe ===
        // On ne stocke JAMAIS un mot de passe en clair dans la base de données
        // MD5 transforme le mot de passe en une chaîne de 32 caractères
        $md5_hash = md5($password);
        
        // === ÉTAPE 6 : Mise à jour en base de données ===
        // On change le mot de passe ET on supprime le token pour qu'il ne soit plus utilisable
        $update_query = "UPDATE utilisateur SET motdepasse = '$md5_hash', token = '' WHERE num = " . $user['num'];
        $update_result = mysqli_query($bdd, $update_query);
        
        // === ÉTAPE 7 : Confirmation du succès ===
        if ($update_result) {
            echo "✅ Votre mot de passe a bien été réinitialisé.<br>";
            echo "<a href='index.php'>🔐 Se connecter avec votre nouveau mot de passe</a>";
            exit;
        } else {
            $error = "❌ Erreur lors de la mise à jour: " . mysqli_error($bdd); 
        }
    }
}
?>

<!-- === FORMULAIRE DE RÉINITIALISATION === -->
<!-- L'utilisateur doit entrer son nouveau mot de passe deux fois pour éviter les erreurs de frappe -->
<form method="post">
    <?php if (!empty($error)) echo "<p style='color:red'>⚠️ $error</p>"; ?>
    
    <label for="password">Nouveau mot de passe :</label><br>
    <input type="password" id="password" name="password" placeholder="Minimum 6 caractères" required><br><br>
    
    <label for="password_confirm">Confirmer le mot de passe :</label><br>
    <input type="password" id="password_confirm" name="password_confirm" placeholder="Retapez le mot de passe" required><br><br>
    
    <button type="submit">🔄 Changer le mot de passe</button>
</form>