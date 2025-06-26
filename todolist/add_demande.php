<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['subject']) || !isset($input['message'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$subject = trim($input['subject']);
$message = trim($input['message']);

// Validate input
if (empty($subject) || empty($message)) {
    echo json_encode(['error' => 'Subject and message cannot be empty']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    // Get student ID for this user
    $stmt = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE id_utilisateur = ?");
    $stmt->execute([$user_id]);
    $etudiant = $stmt->fetch();

    if (!$etudiant) {
        echo json_encode(['error' => 'No student profile found for user ID: ' . $user_id]);
        exit;
    }

    $id_etudiant = $etudiant['id_etudiant'];

    // Insert the demande into database
    $stmt = $pdo->prepare("
        INSERT INTO demande (id_etudiant, type_demande, description, date_demande, statut) 
        VALUES (?, ?, ?, NOW(), 'en_attente')
    ");

    $stmt->execute([$id_etudiant, $subject, $message]);

    // Get the inserted demande data
    $demande_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        SELECT id_demande, type_demande, description, date_demande, statut 
        FROM demande 
        WHERE id_demande = ?
    ");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Demande envoyée avec succès',
        'demande' => $demande
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
