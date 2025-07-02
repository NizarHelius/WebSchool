<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php'; // Adjust path as necessary

$pdo = get_db_connection(); // Initialize $pdo here

$response = ['success' => false, 'message' => ''];

// Check if user is logged in and is an admin
if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input data
    $id_module = filter_input(INPUT_POST, 'id_module', FILTER_VALIDATE_INT);

    // Validate required fields
    if (!$id_module) {
        $response['message'] = 'Invalid module ID.';
        echo json_encode($response);
        exit();
    }

    try {
        // Delete module from the database
        $stmt = $pdo->prepare("DELETE FROM module WHERE id_module = ?");
        if ($stmt->execute([$id_module])) {
            $response['success'] = true;
            $response['message'] = 'Module supprimé avec succès.';
        } else {
            $response['message'] = 'Erreur lors de la suppression du module.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
