<?php
require_once 'db.php'; // Include your database connection file

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS avis (
            id_avis INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            titre VARCHAR(255) NOT NULL,
            contenu TEXT NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(50) DEFAULT 'pending',
            FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
        )
    ");
    echo "Table 'avis' created successfully or already exists.";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
