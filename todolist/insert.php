<?php
require_once 'db.php';

// Étape 1 : Insérer le rôle s'il n'existe pas
$pdo->exec("INSERT IGNORE INTO role (nom_role, description) VALUES ('admin', 'Administrateur'), ('étudiant', 'Étudiant régulier')");

// Étape 2 : Données utilisateur
$email = "john.doe@example.com";
$password = "password123";
$id_role = 2; // Doit correspondre à un id_role existant dans la table role

// Étape 3 : Hasher le mot de passe
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Étape 4 : Insertion utilisateur
    $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, id_role) VALUES (?, ?, ?)");
    $stmt->execute([$email, $hashed_password, $id_role]);
    $id_utilisateur = $pdo->lastInsertId();

    // Étape 5 : Insertion étudiant (facultatif)
    $stmt_etudiant = $pdo->prepare("
        INSERT INTO etudiant (
            id_utilisateur, nom, prenom, date_naissance, lieu_naissance, sexe
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt_etudiant->execute([
        $id_utilisateur,
        'Doe',
        'John',
        '2000-01-01',
        'Casablanca',
        'M'
    ]);

    echo "<p style='color:green;'>✅ Utilisateur ajouté avec succès !</p>";
    echo "<p>Email : $email</p>";
    echo "<p>Mot de passe : $password</p>";

} catch (PDOException $e) {
    die("<p style='color:red;'>❌ Erreur : " . $e->getMessage() . "</p>");
}
?>