<?php
session_start();
require_once 'db.php'; // Ensure this path is correct
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    // Retrieve form data
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $firstNameAr = $_POST['firstNameAr'] ?? '';
    $lastNameAr = $_POST['lastNameAr'] ?? '';
    $birthDate = $_POST['birthDate'] ?? '';
    $cne = $_POST['cne'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $language = $_POST['language'] ?? '';
    $filiere = $_POST['filiere'] ?? '';
    $niveau = $_POST['niveau'] ?? '';
    $semestre = $_POST['semestre'] ?? '';
    $inscriptionDate = $_POST['inscriptionDate'] ?? '';
    $accommodation = $_POST['accommodation'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation (add more robust validation as needed)
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($cne)) {
        $response['message'] = 'Please fill all required fields.';
        echo json_encode($response);
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Generate a unique verification token
    $verificationToken = bin2hex(random_bytes(32)); // 64 character hex string

    $pdo->beginTransaction();

    // 1. Insert into 'utilisateur' table
    $id_role = 3; // Student
    $stmt = $pdo->prepare("INSERT INTO utilisateur (email, mot_de_passe, id_role, est_actif, email_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $hashedPassword, $id_role, 1, 0, $verificationToken]);
    $id_utilisateur = $pdo->lastInsertId();

    // 2. Insert into 'etudiant' table
    // You need to map filiere name to id_filiere
    $stmt = $pdo->prepare("SELECT id_filiere FROM filiere WHERE nom_filiere = ?");
    $stmt->execute([$filiere]);
    $filiereData = $stmt->fetch();
    $id_filiere = $filiereData ? $filiereData['id_filiere'] : null; // Handle case where filiere not found

    $stmt = $pdo->prepare("
        INSERT INTO etudiant (code_apogee, id_utilisateur, id_filiere, nom, prenom, date_naissance, telephone, adresse)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    // Note: 'lieu_naissance' and 'sexe' from your DB schema are not in the form, setting default/null
    // Assuming 'adresse' is not in form either, using a placeholder or null
    $stmt->execute([
        $cne,
        $id_utilisateur,
        $id_filiere,
        $lastName,
        $firstName,
        $birthDate,
        $phone,
        null // Replace null with actual address if available
    ]);
    $id_etudiant = $pdo->lastInsertId();

    // 3. Handle document uploads
    $uploadDir = '../uploads/'; // Directory to save uploads
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $documentTypes = ['cinRecto', 'cinVerso', 'releveNotes', 'baccalaureateDiploma']; // Matching name attributes in HTML
    foreach ($documentTypes as $docType) {
        if (isset($_FILES[$docType]) && $_FILES[$docType]['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES[$docType]['tmp_name'];
            $fileName = basename($_FILES[$docType]['name']);
            $fileSize = $_FILES[$docType]['size'];
            $fileType = $_FILES[$docType]['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Sanitize file name and create unique name
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Insert document info into 'document' table
                $stmt = $pdo->prepare("
                    INSERT INTO document (id_etudiant, type_document, chemin_fichier, date_depot) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$id_etudiant, $docType, $destPath]);
            } else {
                error_log("Failed to move uploaded file: " . $fileName . " to " . $destPath);
                // You might want to rollback transaction here or log a warning
            }
        } else if (isset($_FILES[$docType]) && $_FILES[$docType]['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log("File upload error for " . $docType . ": " . $_FILES[$docType]['error']);
        }
    }

    $pdo->commit();

    $response['success'] = true;
    $response['message'] = 'Registration successful! Please check your email for verification.';
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error during registration: " . $e->getMessage());
    $response['message'] = 'Database error during registration: ' . $e->getMessage();
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("General error during registration: " . $e->getMessage());
    $response['message'] = 'General error during registration: ' . $e->getMessage();
}

// Send verification email
if ($response['success']) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'localhost';        // Should be 'localhost' for Mailpit
        $mail->SMTPAuth   = false;              // Mailpit usually doesn't require authentication for local dev
        $mail->Username   = '';                 // Leave empty
        $mail->Password   = '';                 // Leave empty
        $mail->SMTPSecure = false;         // Set to false for local Mailpit
        $mail->Port       = 1025;               // Should be 1025 for Mailpit

        //Recipients
        $mail->setFrom('no-reply@e6school.com', 'E6SCHOOL Registration');
        $mail->addAddress($email, $firstName . ' ' . $lastName);     // Add a recipient

        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = 'Verify Your Email Address for E6SCHOOL';
        $verificationLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/nv_cite/todolist/verify_email.php?token=' . $verificationToken;
        $mail->Body    = '
            <p>Dear ' . $firstName . ' ' . $lastName . ',</p>
            <p>Thank you for registering with E6SCHOOL! To complete your registration and activate your account, please verify your email address by clicking the link below:</p>
            <p><a href="' . $verificationLink . '">Verify Email Address</a></p>
            <p>If you did not register for an account, please ignore this email.</p>
            <p>Sincerely,</p>
            <p>The E6SCHOOL Team</p>
        ';
        $mail->AltBody = 'Dear ' . $firstName . ' ' . $lastName . ',\nThank you for registering with E6SCHOOL! To complete your registration and activate your account, please verify your email address by clicking the link below: \n' . $verificationLink . '\nIf you did not register for an account, please ignore this email.\nSincerely,\nThe E6SCHOOL Team';

        $mail->send();
        // Email sent successfully, no need to change $response['message']
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        $response['message'] .= " However, the verification email could not be sent. Please contact support.";
        // Optional: set success to false if email sending is critical for registration
        // $response['success'] = false;
        $response['email_error'] = "Mailer Error: {$mail->ErrorInfo}";
    }
}

echo json_encode($response);
