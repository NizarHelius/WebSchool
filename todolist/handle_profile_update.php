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
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // 'personal_info', 'password_change', 'profile_picture'

    try {
        $pdo->beginTransaction();

        if ($action === 'personal_info') {
            $firstName = $_POST['firstName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';

            // Basic validation
            if (empty($firstName) || empty($lastName) || empty($phone)) {
                throw new Exception('Missing required personal info fields.');
            }

            // Update etudiant table
            $stmt = $pdo->prepare("
                UPDATE etudiant 
                SET nom = ?, prenom = ?, telephone = ?, adresse = ? 
                WHERE id_utilisateur = ?
            ");
            $stmt->execute([$lastName, $firstName, $phone, $address, $user_id]);

            $response['success'] = true;
            $response['message'] = 'Personal information updated successfully.';
        } else if ($action === 'password_change') {
            $currentPassword = $_POST['currentPassword'] ?? '';
            $newPassword = $_POST['newPassword'] ?? '';
            $confirmNewPassword = $_POST['confirmNewPassword'] ?? '';

            // Basic validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
                throw new Exception('All password fields are required.');
            }
            if ($newPassword !== $confirmNewPassword) {
                throw new Exception('New passwords do not match.');
            }
            if (strlen($newPassword) < 8) {
                throw new Exception('New password must be at least 8 characters long.');
            }

            // Verify current password
            $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['mot_de_passe'])) {
                throw new Exception('Current password is incorrect.');
            }

            // Hash new password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update utilisateur table
            $stmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?");
            $stmt->execute([$hashedNewPassword, $user_id]);

            $response['success'] = true;
            $response['message'] = 'Password updated successfully.';
        } else if ($action === 'profile_picture') {
            if (!isset($_FILES['profilePicture']) || $_FILES['profilePicture']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error.');
            }

            $uploadDir = '../uploads/'; // Directory to save uploads
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
            }

            $fileTmpPath = $_FILES['profilePicture']['tmp_name'];
            $fileName = basename($_FILES['profilePicture']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Generate a unique file name
            $newFileName = uniqid('profile_', true) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Get current student's id_etudiant
                $stmt = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE id_utilisateur = ?");
                $stmt->execute([$user_id]);
                $etudiant = $stmt->fetch();

                if (!$etudiant) {
                    throw new Exception('Student profile not found.');
                }
                $id_etudiant = $etudiant['id_etudiant'];

                // Update etudiant table with new profile picture path
                $stmt = $pdo->prepare("UPDATE etudiant SET profile_picture_path = ? WHERE id_etudiant = ?");
                $stmt->execute([$destPath, $id_etudiant]);

                $response['success'] = true;
                $response['message'] = 'Profile picture updated successfully.';
                $response['profile_picture_path'] = $destPath; // Send back new path
            } else {
                throw new Exception('Failed to move uploaded file.');
            }
        } else {
            throw new Exception('Invalid action specified.');
        }

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database error in handle_profile_update.php: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        $pdo->rollBack(); // Rollback if any other exception occurs
        error_log("General error in handle_profile_update.php: " . $e->getMessage());
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
