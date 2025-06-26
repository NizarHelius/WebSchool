<?php
session_start();
require_once '../db.php'; // Adjust path as needed for admin directory

header('Content-Type: application/json');

// Optional: Restrict access to admin roles if necessary
// if (!isset($_SESSION['user_id']) || $_SESSION['id_role'] != 1) { // Assuming 1 is admin role
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_avis = filter_input(INPUT_POST, 'id_avis', FILTER_VALIDATE_INT);
    $statut = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    if (!$id_avis || empty($statut)) {
        echo json_encode(['success' => false, 'message' => 'Invalid Avis ID or status.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE avis SET statut = ? WHERE id_avis = ?");
        $stmt->execute([$statut, $id_avis]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Avis status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Avis not found or status already set.']);
        }
    } catch (PDOException $e) {
        error_log("Database error updating avis status: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred.', 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
