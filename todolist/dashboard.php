<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Étudiant</title>
</head>
<body>
    <h1>Bienvenue Étudiant !</h1>
    <p>Vous êtes connecté en tant qu'étudiant.</p>
    <a href="../logout.php">Se déconnecter</a>
</body>
</html>