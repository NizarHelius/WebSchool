<?php
require_once 'db.php';

try {
    // Insert sample modules
    $modules_to_insert = [
        ['MOD101', 'Introduction to Programming', 1, 3.0],
        ['MOD102', 'Data Structures', 1, 4.0],
        ['MOD201', 'Algorithms', 2, 4.0],
        ['MOD202', 'Database Systems', 2, 3.0],
        ['MOD301', 'Web Development', 3, 5.0],
        ['MOD302', 'Operating Systems', 3, 4.0]
    ];

    $stmt_module = $pdo->prepare("INSERT INTO module (code_module, nom_module, id_semestre, coefficient) VALUES (?, ?, ?, ?)");
    foreach ($modules_to_insert as $module) {
        // Check if module already exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM module WHERE code_module = ?");
        $check_stmt->execute([$module[0]]);
        if ($check_stmt->fetchColumn() == 0) {
            $stmt_module->execute($module);
        } else {
            echo "Module " . $module[0] . " already exists, skipping insertion.<br>";
        }
    }

    // Insert sample notes
    $notes_to_insert = [
        [2, 1, 15.5, '2023-01-15', 'Semestre 1'], // id_module 1 (MOD101)
        [2, 2, 12.0, '2023-01-20', 'Semestre 1'], // id_module 2 (MOD102)
        [2, 3, 14.0, '2023-06-10', 'Semestre 2'], // id_module 3 (MOD201)
        [2, 4, 10.0, '2023-06-15', 'Semestre 2'], // id_module 4 (MOD202)
        [2, 5, 18.0, '2024-01-25', 'Semestre 3'], // id_module 5 (MOD301)
        [2, 6, 9.0, '2024-01-30', 'Semestre 3']  // id_module 6 (MOD302)
    ];

    $stmt_note = $pdo->prepare("INSERT INTO note (id_etudiant, id_module, valeur, date_evaluation, semestre) VALUES (?, ?, ?, ?, ?)");
    foreach ($notes_to_insert as $note) {
        $stmt_note->execute($note);
    }

    echo "Sample data (modules and notes) inserted successfully.";
} catch (PDOException $e) {
    echo "Error inserting sample data: " . $e->getMessage();
} catch (Exception $e) {
    echo "General error: " . $e->getMessage();
}
