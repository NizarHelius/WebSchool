<?php
session_start();
require_once 'db.php';

$pdo = get_db_connection();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get the etudiant ID from the user ID
    $stmt_etudiant = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE id_utilisateur = ?");
    $stmt_etudiant->execute([$user_id]);
    $etudiant = $stmt_etudiant->fetch();

    if (!$etudiant) {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
        exit();
    }

    $id_etudiant = $etudiant['id_etudiant'];

    // Fetch documents for the student
    $stmt_documents = $pdo->prepare("SELECT id_document, nom_document, type_document, chemin_fichier, date_soumission, statut FROM document WHERE id_etudiant = ? ORDER BY date_soumission DESC");
    $stmt_documents->execute([$id_etudiant]);
    $documents = $stmt_documents->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'documents' => $documents]);
} catch (PDOException $e) {
    error_log("Database error fetching documents: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error fetching documents.', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error fetching documents: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'General error fetching documents.', 'error' => $e->getMessage()]);
}
