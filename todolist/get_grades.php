<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$semester_filter = isset($_GET['semestre']) ? $_GET['semestre'] : null;

try {
    // Get the etudiant ID from the user ID
    $stmt_etudiant = $pdo->prepare("SELECT id_etudiant FROM etudiant WHERE id_utilisateur = ?");
    $stmt_etudiant->execute([$user_id]);
    $etudiant = $stmt_etudiant->fetch();

    if (!$etudiant) {
        echo json_encode(['success' => false, 'message' => 'Student not found.']);
        exit();
    }

    $id_etudiant = $etudiant['id_etudiant'];

    // Fetch all notes for the student, including module details
    $sql = "SELECT ";
    $sql .= "    n.id_module, ";
    $sql .= "    m.code_module, ";
    $sql .= "    m.nom_module, ";
    $sql .= "    n.valeur AS note, ";
    $sql .= "    n.date_evaluation AS date_examen, ";
    $sql .= "    n.semestre, ";
    $sql .= "    m.coefficient ";
    $sql .= "FROM NOTE n ";
    $sql .= "JOIN MODULE m ON n.id_module = m.id_module ";
    $sql .= "WHERE n.id_etudiant = ?";
    $params = [$id_etudiant];

    if ($semester_filter && $semester_filter !== 'Tous les semestres') {
        $sql .= " AND n.semestre = ?";
        $params[] = $semester_filter;
    }

    $sql .= " ORDER BY n.semestre, m.nom_module, n.date_evaluation";

    $stmt_grades = $pdo->prepare($sql);
    $stmt_grades->execute($params);
    $all_notes = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);

    $modules = [];
    $total_notes_sum = 0;
    $total_coefficients_sum = 0;
    $best_note = 0;
    $lowest_note = 20; // Assuming notes are out of 20
    $validated_modules_count = 0;

    // Group notes by module to calculate module average and status
    foreach ($all_notes as $note) {
        $module_id = $note['id_module'];
        if (!isset($modules[$module_id])) {
            $modules[$module_id] = [
                'code_module' => $note['code_module'],
                'nom_module' => $note['nom_module'],
                'coefficient' => $note['coefficient'],
                'notes_sum' => 0,
                'notes_count' => 0,
                'notes_details' => []
            ];
        }
        $modules[$module_id]['notes_sum'] += $note['note'];
        $modules[$module_id]['notes_count']++;
        $modules[$module_id]['notes_details'][] = [
            'note' => $note['note'],
            'date_examen' => $note['date_examen'],
            'semestre' => $note['semestre']
        ];

        // Update best and lowest note
        if ($note['note'] > $best_note) {
            $best_note = $note['note'];
        }
        if ($note['note'] < $lowest_note) {
            $lowest_note = $note['note'];
        }
    }

    $final_modules_data = [];
    foreach ($modules as $module_id => $data) {
        $moyenne_module = $data['notes_count'] > 0 ? round($data['notes_sum'] / $data['notes_count'], 2) : 0;
        $statut = ($moyenne_module >= 10) ? 'Validé' : 'Non validé'; // Assuming 10 is passing grade

        if ($statut === 'Validé') {
            $validated_modules_count++;
        }

        // Only include notes that match the current semester filter for the detailed table
        $filtered_notes_details = [];
        foreach ($data['notes_details'] as $note_detail) {
            if (!$semester_filter || $semester_filter === 'Tous les semestres' || $note_detail['semestre'] === $semester_filter) {
                $filtered_notes_details[] = $note_detail;
            }
        }

        // Add to total for general average calculation
        $total_notes_sum += ($moyenne_module * $data['coefficient']);
        $total_coefficients_sum += $data['coefficient'];

        $final_modules_data[] = [
            'id_module' => $module_id,
            'code_module' => $data['code_module'],
            'nom_module' => $data['nom_module'],
            'moyenne_module' => $moyenne_module,
            'statut' => $statut,
            'coefficient' => $data['coefficient'],
            'notes_details' => $filtered_notes_details
        ];
    }

    $moyenne_generale = $total_coefficients_sum > 0 ? round($total_notes_sum / $total_coefficients_sum, 2) : 0;

    echo json_encode([
        'success' => true,
        'grades' => $final_modules_data,
        'stats' => [
            'moyenne_generale' => $moyenne_generale,
            'meilleure_note' => $best_note,
            'plus_faible_note' => ($lowest_note == 20 && count($all_notes) == 0) ? 0 : $lowest_note, // If no notes, lowest is 0
            'modules_valides' => $validated_modules_count
        ],
        'available_semesters' => array_values(array_unique(array_column($all_notes, 'semestre')))
    ]);
} catch (PDOException $e) {
    error_log("Database error fetching grades: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error fetching grades.', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error fetching grades: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'General error fetching grades.', 'error' => $e->getMessage()]);
}
