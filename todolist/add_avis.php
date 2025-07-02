<?php
ob_start(); // Start output buffering at the very beginning
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';

$pdo = get_db_connection();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = htmlspecialchars(trim($_POST['titre']), ENT_QUOTES, 'UTF-8');
    $contenu = htmlspecialchars(trim($_POST['contenu']), ENT_QUOTES, 'UTF-8');

    if (empty($titre) || empty($contenu)) {
        echo json_encode(['success' => false, 'message' => 'Title and content cannot be empty.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO avis (id_utilisateur, titre, contenu) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $titre, $contenu]);

        ob_clean(); // Clean the output buffer before sending JSON
        echo json_encode(['success' => true, 'message' => 'Avis added successfully.']);
        exit();
    } catch (PDOException $e) {
        ob_clean(); // Clean the buffer on error too
        error_log("Database error adding avis: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred.', 'error' => $e->getMessage()]);
        exit();
    }
} else {
    ob_clean(); // Clean the buffer on invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
