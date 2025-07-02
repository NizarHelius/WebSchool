<?php
ob_start(); // Start output buffering
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db.php'; // Path to your db.php

$response = ['success' => false, 'message' => '', 'modules' => []];

// Check if user is logged in and is an admin
if (!isset($_SESSION['id_role']) || $_SESSION['id_role'] != 1) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT id_module, nom_module, code_module, coefficient, id_semestre AS semestre FROM module ORDER BY nom_module ASC");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['modules'] = $modules;
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Database error in get_modules.php: " . $e->getMessage()); // Log error
} catch (Exception $e) {
    $response['message'] = 'General error: ' . $e->getMessage();
    error_log("General error in get_modules.php: " . $e->getMessage()); // Log other errors
}

echo json_encode($response);
ob_end_flush(); // Flush the output buffer
