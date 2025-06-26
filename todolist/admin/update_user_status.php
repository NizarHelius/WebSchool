<?php
session_start();
require_once '../../db.php'; // Adjust path as necessary

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['id_role'] != 1) { // Assuming 1 is the admin role ID
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $newStatus = filter_input(INPUT_POST, 'new_status', FILTER_VALIDATE_INT);

    if ($userId === null || $userId === false || ($newStatus !== 0 && $newStatus !== 1)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit();
    }

    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("UPDATE utilisateur SET est_active = :new_status WHERE id_utilisateur = :user_id");
        $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user status.']);
        }

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
