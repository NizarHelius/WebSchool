<?php
require_once 'db.php'; // الاتصال بقاعدة البيانات

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // تشفير المودباص
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // إدخال المستخدم للقاعدة
    $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, id_role, est_actif) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $hashedPassword, 2, 1]); // 2: étudiant, 1: actif

    echo "✅ Inscription réussie. Vous pouvez vous connecter maintenant.";
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>
