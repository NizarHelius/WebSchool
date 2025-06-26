<?php
require_once 'db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS document (
        id_document INT AUTO_INCREMENT PRIMARY KEY,
        id_etudiant INT NOT NULL,
        nom_document VARCHAR(255) NOT NULL,
        type_document VARCHAR(100) NOT NULL,
        chemin_fichier VARCHAR(255) NOT NULL,
        date_soumission TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        statut ENUM('En attente', 'Approuvé', 'Rejeté') DEFAULT 'En attente',
        FOREIGN KEY (id_etudiant) REFERENCES etudiant(id_etudiant) ON DELETE CASCADE
    );
    ";

    $pdo->exec($sql);
    echo "Table 'document' created successfully or already exists.\n";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
