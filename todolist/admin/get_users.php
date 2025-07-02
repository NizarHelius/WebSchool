<?php
ob_start(); // Start output buffering
session_start();
require_once '../db.php'; // Adjust path as needed

$pdo = get_db_connection();

// DEBUG: Output session info
file_put_contents(__DIR__ . '/session_debug.txt', print_r($_SESSION, true));

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) { // Assuming 1 is the admin role ID
    ob_clean(); // Clean any previous output before sending JSON
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

try {
    // Fetch users along with their role names
    $stmt = $pdo->prepare("SELECT 
        u.id_utilisateur,
        u.email,
        COALESCE(e.nom, '') AS nom_utilisateur,       
        COALESCE(e.prenom, '') AS prenom_utilisateur,    
        u.est_actif,
        r.nom_role
    FROM
        utilisateur u
    JOIN
        role r ON u.id_role = r.id_role
    LEFT JOIN
        etudiant e ON u.id_utilisateur = e.id_utilisateur AND r.id_role = 3; ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean(); // Clean any previous output before sending JSON
    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    ob_clean(); // Clean any previous output before sending JSON
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
}
