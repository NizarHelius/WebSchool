<?php
ob_start(); // Start output buffering
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
session_start();
require_once 'db.php'; // Adjust path as needed
require_once 'tcpdf/tcpdf.php'; // Adjust path to TCPDF library

if (!isset($_SESSION['user_id'])) {
    exit(); // No output
}

$user_id = $_SESSION['user_id'];
$id_document = isset($_GET['id_document']) ? (int)$_GET['id_document'] : 0;

if ($id_document === 0) {
    exit(); // No output
}

try {
    // Get the etudiant ID from the user ID
    $stmt_etudiant = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE id_utilisateur = ?");
    $stmt_etudiant->execute([$user_id]);
    $etudiant = $stmt_etudiant->fetch();

    if (!$etudiant) {
        exit(); // No output
    }

    $id_etudiant = $etudiant['id_etudiant'];

    // Fetch document details for the specific document and student
    $stmt_document = $pdo->prepare("SELECT * FROM document WHERE id_document = ? AND id_etudiant = ?");
    $stmt_document->execute([$id_document, $id_etudiant]);
    $document = $stmt_document->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        exit(); // No output
    }

    // Fetch full student details for the PDF content
    $stmt_student_details = $pdo->prepare("SELECT e.nom, e.prenom, u.email, e.date_naissance, e.adresse, e.telephone FROM utilisateur u JOIN etudiant e ON u.id_utilisateur = e.id_utilisateur WHERE u.id_utilisateur = ?");
    $stmt_student_details->execute([$user_id]);
    $student_details = $stmt_student_details->fetch(PDO::FETCH_ASSOC);

    if (!$student_details) {
        exit(); // No output
    }

    // Extend TCPDF with custom header and footer
    class MYPDF extends TCPDF
    {
        // Page header
        public function Header()
        {
            // Logo (adjust path as needed)
            // $image_file = K_PATH_IMAGES.'logo_example.jpg';
            // $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
            $this->SetFont('helvetica', 'B', 15);
            // Title
            $this->Cell(0, 15, 'Portail Étudiant - Document Officiel', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }

        // Page footer
        public function Footer()
        {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Portail Étudiant');
    $pdf->SetTitle('Document: ' . $document['nom_document']);
    $pdf->SetSubject('Document Officiel pour ' . $student_details['prenom'] . ' ' . $student_details['nom']);
    $pdf->SetKeywords($document['nom_document'] . ', ' . $student_details['nom'] . ', ' . $student_details['prenom'] . ', etudiant, document');

    // set header and footer fonts
    $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
    $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Set font
    $pdf->SetFont('dejavusans', '', 11, '', true);

    // Add a page
    $pdf->AddPage();

    // Set some content to print
    $html = '<h1 style="text-align:center;">' . $document['nom_document'] . '</h1>';
    $html .= '<p><strong>Type de Document:</strong> ' . $document['type_document'] . '</p>';
    $html .= '<p><strong>Date de Soumission:</strong> ' . date('d/m/Y H:i:s', strtotime($document['date_soumission'])) . '</p>';
    $html .= '<p><strong>Statut:</strong> ' . $document['statut'] . '</p>';
    $html .= '<hr/>';

    $html .= '<h2 style="color:#6a4949;">Informations Étudiant:</h2>';
    $html .= '<p><strong>Nom Complet:</strong> ' . $student_details['prenom'] . ' ' . $student_details['nom'] . '</p>';
    $html .= '<p><strong>Email:</strong> ' . $student_details['email'] . '</p>';
    if (!empty($student_details['date_naissance'])) {
        $html .= '<p><strong>Date de Naissance:</strong> ' . date('d/m/Y', strtotime($student_details['date_naissance'])) . '</p>';
    }
    if (!empty($student_details['adresse'])) {
        $html .= '<p><strong>Adresse:</strong> ' . $student_details['adresse'] . '</p>';
    }
    if (!empty($student_details['telephone'])) {
        $html .= '<p><strong>Téléphone:</strong> ' . $student_details['telephone'] . '</p>';
    }

    // Add a placeholder for document specific content
    $html .= '<br/><p><i>Ceci est un document officiel généré par le Portail Étudiant. Pour toute vérification, veuillez contacter l\'administration.</i></p>';

    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // Close and output PDF document
    ob_clean(); // Clean the output buffer
    $pdf->Output($document['nom_document'] . '.pdf', 'I');
} catch (PDOException $e) {
    error_log("Database error generating PDF: " . $e->getMessage());
    exit(); // No output
} catch (Exception $e) {
    error_log("General error generating PDF: " . $e->getMessage());
    exit(); // No output
}
