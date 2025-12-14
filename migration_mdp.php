<?php
/**
 * Script de migration des mots de passe MD5 vers password_hash()
 * À exécuter UNE SEULE FOIS pour mettre à jour tous les mots de passe
 */

include '_conf.php';

// Connexion à la base de données
$connexion = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);

if (!$connexion) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

echo "=== Migration des mots de passe ===\n\n";

// Liste des utilisateurs avec leurs nouveaux mots de passe temporaires
// Format: login => mot de passe temporaire
$utilisateurs_migration = [
    'bgravouil' => 'password123',  // Professeur
    'mtarif' => 'password123',     // Professeur
    'lbernard' => 'password123',   // Élève
    'llambert' => 'password123',   // Élève
    'erichard' => 'password123',   // Élève
    'hlefevre' => 'password123',   // Élève
    'cmoreau' => 'password123',    // Élève
    'nsimon' => 'password123',     // Élève
    'crousseau' => 'password123',  // Élève
    'tdurand' => 'password123',    // Élève
    'ryne44' => 'password123',
    'ryne444' => 'password123',
    'ryne4444' => 'password123',
    'eleve1' => 'password123'
];

$compteur_succes = 0;
$compteur_erreur = 0;

foreach ($utilisateurs_migration as $login => $mdp_temporaire) {
    // Hasher le mot de passe avec password_hash()
    $mdp_hash = password_hash($mdp_temporaire, PASSWORD_DEFAULT);
    
    // Préparer la requête de mise à jour
    $requete = "UPDATE utilisateur SET motdepasse = ? WHERE login = ?";
    $stmt = mysqli_prepare($connexion, $requete);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $mdp_hash, $login);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "✓ Mot de passe mis à jour pour : $login\n";
            $compteur_succes++;
        } else {
            echo "✗ Erreur pour : $login - " . mysqli_stmt_error($stmt) . "\n";
            $compteur_erreur++;
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "✗ Erreur de préparation pour : $login\n";
        $compteur_erreur++;
    }
}

mysqli_close($connexion);

echo "\n=== Résumé de la migration ===\n";
echo "Succès : $compteur_succes\n";
echo "Erreurs : $compteur_erreur\n";
echo "\n⚠️  IMPORTANT : Tous les utilisateurs doivent se connecter avec le mot de passe temporaire 'password123'\n";
echo "⚠️  Ils doivent ensuite le changer via leur profil (perso.php)\n";
?>