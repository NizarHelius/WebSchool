<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Start output buffering
session_start();
header('Content-Type: application/json');

require_once '../db.php'; // Corrected path

$response = ['success' => false, 'message' => ''];

// Only allow admins
if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) {
    $response['message'] = 'Unauthorized access.';
    ob_end_clean(); // Clean any previous output
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_utilisateur = filter_input(INPUT_POST, 'id_utilisateur', FILTER_VALIDATE_INT);
    $est_actif = filter_input(INPUT_POST, 'est_actif', FILTER_VALIDATE_INT);

    if (!$id_utilisateur || !in_array($est_actif, [0, 1])) {
        $response['message'] = 'Invalid student ID or status.';
        ob_end_clean(); // Clean any previous output
        echo json_encode($response);
        exit();
    }

    try {
        $pdo = get_db_connection();
        // Directly set the est_actif status to the received value
        $stmt = $pdo->prepare("UPDATE utilisateur SET est_actif = ? WHERE id_utilisateur = ?");
        if ($stmt->execute([$est_actif, $id_utilisateur])) {
            $action = ($est_actif == 0) ? 'archivé' : 'désarchivé';
            $response['success'] = true;
            $response['message'] = "Étudiant {$action} avec succès.";
        } else {
            $response['message'] = 'Erreur lors de la mise à jour du statut de l\'étudiant.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

ob_end_clean(); // Clean any previous output before sending JSON
echo json_encode($response);
