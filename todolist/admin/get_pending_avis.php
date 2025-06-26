<?php
session_start();
require_once '../db.php'; // Adjust path as needed for admin directory

header('Content-Type: application/json');

// Optional: Restrict access to admin roles if necessary
// if (!isset($_SESSION['user_id']) || $_SESSION['id_role'] != 1) { // Assuming 1 is admin role
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
//     exit();
// }

try {
    // Fetch all avis with 'pending' status, joining with utilisateur and etudiant tables to get author's name
    $stmt = $pdo->prepare("SELECT a.id_avis, a.titre, a.contenu, a.date_creation, a.statut, e.nom, e.prenom FROM avis a JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur JOIN etudiant e ON u.id_utilisateur = e.id_utilisateur WHERE a.statut = 'pending' ORDER BY a.date_creation DESC");
    $stmt->execute();
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the output to include the author's full name
    $formattedAvis = [];
    foreach ($avis as $row) {
        $row['auteur'] = $row['prenom'] . ' ' . $row['nom'];
        unset($row['nom']); // Remove individual name fields
        unset($row['prenom']);
        $formattedAvis[] = $row;
    }

    echo json_encode(['success' => true, 'avis' => $formattedAvis]);
} catch (PDOException $e) {
    error_log("Database error fetching pending avis: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error fetching pending avis: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'General error occurred.', 'error' => $e->getMessage()]);
}
