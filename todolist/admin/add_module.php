<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php'; // Adjust path as necessary

$pdo = get_db_connection(); // Initialize $pdo here

$response = ['success' => false, 'message' => ''];

// Check if user is logged in and is an admin
if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input data
    $nom_module = htmlspecialchars(trim($_POST['nom_module']), ENT_QUOTES, 'UTF-8');
    $code_module = htmlspecialchars(trim($_POST['code_module']), ENT_QUOTES, 'UTF-8');
    $coefficient = filter_input(INPUT_POST, 'coefficient', FILTER_VALIDATE_INT);

    // Handle semestre input more robustly
    $semestre_raw = $_POST['semestre'] ?? '';
    if (empty($semestre_raw)) {
        $response['message'] = 'Veuillez remplir tous les champs obligatoires (Nom du module, Code, Coefficient, Semestre).';
        echo json_encode($response);
        exit();
    }
    $semestre = filter_var($semestre_raw, FILTER_VALIDATE_INT);
    if ($semestre === false) {
        $response['message'] = 'Semestre doit être un nombre entier valide.';
        echo json_encode($response);
        exit();
    }

    // Validate required fields
    if (empty($nom_module) || empty($code_module) || $coefficient === false) {
        $response['message'] = 'Veuillez remplir tous les champs obligatoires (Nom du module, Code, Coefficient, Semestre).';
        echo json_encode($response);
        exit();
    }

    try {
        // Check if code_module already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM module WHERE code_module = ?");
        $stmt->execute([$code_module]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'Le code de module existe déjà. Veuillez en choisir un autre.';
            echo json_encode($response);
            exit();
        }

        // Insert new module into the database, excluding 'enseignant'
        $stmt = $pdo->prepare("INSERT INTO module (nom_module, code_module, coefficient, id_semestre) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nom_module, $code_module, $coefficient, $semestre])) {
            $response['success'] = true;
            $response['message'] = 'Module ajouté avec succès.';
        } else {
            $response['message'] = 'Erreur lors de l\'ajout du module.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
