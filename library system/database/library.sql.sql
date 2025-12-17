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

CREATE OR REPLACE VIEW vw_file_counts_per_type AS
SELECT file_type,
       COUNT(*) AS total_files
FROM files
GROUP BY file_type;

CREATE OR REPLACE VIEW vw_uploads_per_day AS
SELECT DATE(upload_date) AS upload_day,
       COUNT(*)          AS total_uploads
FROM files
GROUP BY DATE(upload_date)
ORDER BY upload_day;

CREATE OR REPLACE VIEW vw_descriptive_stats AS
SELECT
    file_type,
    COUNT(*)                      AS total_files,
    MIN(upload_date)              AS first_upload,
    MAX(upload_date)              AS last_upload,
    ROUND(AVG(size_bytes), 2)     AS avg_size_bytes,
    MIN(size_bytes)               AS min_size_bytes,
    MAX(size_bytes)               AS max_size_bytes
FROM files
GROUP BY file_type;
