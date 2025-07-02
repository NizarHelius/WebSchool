<?php
session_start();
header('Content-Type: application/json');

require_once '../../db.php'; // Adjust path as necessary

$response = array('success' => false, 'message' => '');

// Check if user is logged in and is an admin
if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $id_role = filter_input(INPUT_POST, 'id_role', FILTER_VALIDATE_INT);

    if (!$email || !$password || !$id_role) {
        $response['message'] = 'Invalid input for email, password, or role.';
        echo json_encode($response);
        exit();
    }

    try {
        $pdo = get_db_connection();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'Email already registered.';
            echo json_encode($response);
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into utilisateur table
        $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, id_role, est_actif) VALUES (?, ?, ?, 1)");
        if (!$stmt->execute([$email, $hashed_password, $id_role])) {
            $response['message'] = 'Failed to create user account.';
            echo json_encode($response);
            exit();
        }

        $id_utilisateur = $pdo->lastInsertId();

        // If role is student (id_role = 3), insert into etudiant table
        if ($id_role == 3) {
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
            $date_naissance = filter_input(INPUT_POST, 'date_naissance', FILTER_SANITIZE_STRING);
            $lieu_naissance = filter_input(INPUT_POST, 'lieu_naissance', FILTER_SANITIZE_STRING);
            $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
            $adresse = filter_input(INPUT_POST, 'adresse', FILTER_SANITIZE_STRING);
            $sexe = filter_input(INPUT_POST, 'sexe', FILTER_SANITIZE_STRING);

            $stmt = $pdo->prepare("INSERT INTO etudiant (id_utilisateur, nom, prenom, date_naissance, lieu_naissance, telephone, adresse, sexe) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt->execute([$id_utilisateur, $nom, $prenom, $date_naissance, $lieu_naissance, $telephone, $adresse, $sexe])) {
                // If etudiant insertion fails, consider rolling back utilisateur insertion or logging it
                $response['message'] = 'User account created, but failed to add student details.';
                // You might want to delete the user from 'utilisateur' table here if transaction is not used.
                echo json_encode($response);
                exit();
            }
        }

        $response['success'] = true;
        $response['message'] = 'User account created successfully.';
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
