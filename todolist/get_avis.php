<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

try {
    // Fetch all avis, joining with utilisateur and etudiant tables to get author's name
    $stmt = $pdo->prepare("SELECT a.id_avis, a.titre, a.contenu, a.date_creation, a.statut, e.nom, e.prenom FROM avis a JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur JOIN etudiant e ON u.id_utilisateur = e.id_utilisateur ORDER BY a.date_creation DESC");
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
    error_log("Database error fetching avis: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error fetching avis: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'General error occurred.', 'error' => $e->getMessage()]);
}
