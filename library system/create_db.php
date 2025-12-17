<?php
require_once __DIR__ . '/config/database.php';

// Create database and table if not exists.
$sql = <<<SQL
CREATE DATABASE IF NOT EXISTS library_analytics
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE library_analytics;

CREATE TABLE IF NOT EXISTS files (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,
    folder_path VARCHAR(255) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    upload_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_type (file_type),
    INDEX idx_category (category),
    INDEX idx_upload_date (upload_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

try {
    $pdo->exec($sql);
    echo "Database and table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
