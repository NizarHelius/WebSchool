<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Start output buffering
session_start();
header('Content-Type: application/json');

require_once '../db.php'; // Corrected path

$response = ['success' => false, 'message' => '', 'students' => []];

// Check if user is logged in and is an admin
if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) {
    $response['message'] = 'Unauthorized access.';
    ob_end_clean(); // Clean any previous output
    echo json_encode($response);
    exit();
}

try {
    $pdo = get_db_connection();

    $stmt = $pdo->prepare("
        SELECT
            u.id_utilisateur,
            u.email,
            u.est_actif,
            r.nom_role,
            e.nom AS student_nom,
            e.prenom AS student_prenom,
            e.date_naissance,
            e.lieu_naissance,
            e.telephone,
            e.adresse,
            e.sexe,
            e.id_filiere
        FROM
            utilisateur u
        JOIN
            role r ON u.id_role = r.id_role
        LEFT JOIN
            etudiant e ON u.id_utilisateur = e.id_utilisateur
        WHERE
            u.id_role = 3 AND u.est_actif = 1 -- Only fetch active students for this page
        ORDER BY
            u.id_utilisateur DESC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for the frontend
    $formattedStudents = [];
    foreach ($students as $student) {
        $nom_complet = !empty($student['student_nom']) && !empty($student['student_prenom'])
            ? $student['student_prenom'] . ' ' . $student['student_nom']
            : $student['email']; // Fallback to email if name is not set

        $formattedStudents[] = [
            'id_utilisateur' => $student['id_utilisateur'],
            'nom_complet' => $nom_complet,
            'email' => $student['email'],
            'nom_role' => $student['nom_role'],
            'est_actif' => $student['est_actif'],
            // Include student-specific fields if needed for display
            'date_naissance' => $student['date_naissance'],
            'lieu_naissance' => $student['lieu_naissance'],
            'telephone' => $student['telephone'],
            'adresse' => $student['adresse'],
            'sexe' => $student['sexe'],
            'id_filiere' => $student['id_filiere']
        ];
    }

    $response['success'] = true;
    $response['students'] = $formattedStudents;
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

ob_end_clean(); // Clean any previous output before sending JSON
echo json_encode($response);
