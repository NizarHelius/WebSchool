<?php
ob_start(); // Start output buffering
session_start();
require_once '../db.php'; // Corrected path

// DEBUG: Output session info
file_put_contents(__DIR__ . '/session_debug.txt', print_r($_SESSION, true));

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) { // Assuming 1 is the admin role ID
    ob_clean(); // Clean any previous output before sending JSON
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

try {
    $pdo = get_db_connection();

    // Fetch users along with their role names
    $stmt = $pdo->prepare("SELECT u.id_utilisateur, u.nom_utilisateur, u.prenom_utilisateur, u.email, u.est_active, r.nom_role FROM utilisateur u JOIN role r ON u.id_role = r.id_role");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean(); // Clean any previous output before sending JSON
    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    ob_clean(); // Clean any previous output before sending JSON
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
