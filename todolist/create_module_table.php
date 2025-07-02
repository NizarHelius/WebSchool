<?php
require_once 'db.php';

try {
    $pdo = get_db_connection();

    $sql = "
    CREATE TABLE IF NOT EXISTS module (
        id_module INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nom_module VARCHAR(255) NOT NULL,
        code_module VARCHAR(50) NOT NULL UNIQUE,
        enseignant VARCHAR(255) DEFAULT NULL,
        coefficient INT(11) NOT NULL,
        semestre INT(11) NOT NULL
    );";

    $pdo->exec($sql);
    echo "Table 'module' created successfully or already exists.\n";
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
