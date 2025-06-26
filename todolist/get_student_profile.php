<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user and student data using a JOIN
    $stmt = $pdo->prepare("
        SELECT u.email, e.nom, e.prenom, e.telephone, e.adresse, e.profile_picture_path, e.sexe, e.lieu_naissance, e.code_apogee
        FROM utilisateur u
        JOIN etudiant e ON u.id_utilisateur = e.id_utilisateur
        WHERE u.id_utilisateur = ?
    ");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch();

    if ($student) {
        // Adjust profile picture path for web access
        if ($student['profile_picture_path']) {
            // Assuming uploads are in todolist/uploads/
            // We need to strip '../uploads/' and prepend '/nv_cite/todolist/uploads/'
            $basePath = 'todolist/uploads/';
            $filename = basename($student['profile_picture_path']);
            $student['profile_picture_path'] = $basePath . $filename;
        } else {
            // Default placeholder if no picture is set
            $student['profile_picture_path'] = 'https://via.placeholder.com/150/E0E0E0/808080?text=Profil';
        }

        echo json_encode(['success' => true, 'student' => $student]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student profile not found.']);
    }
} catch (PDOException $e) {
    error_log("Database error fetching student profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error fetching profile.']);
} catch (Exception $e) {
    error_log("General error fetching student profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'General error fetching profile.']);
}
