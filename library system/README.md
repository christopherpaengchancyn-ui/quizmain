# Library Analytics System (PHP + MariaDB)

This project is a Library Analytics System that ingests library files (PDF, DOCX, XLSX, CSV, and others), automatically classifies and sorts them into folders, stores metadata in a standalone MariaDB database, and exposes analytics and visualizations using Chart.js.

## Structure

- `index.php` – Upload UI and recent files table.
- `upload.php` – File handling, classification, folder routing, and MariaDB insert.
- `dashboard.php` – Analytics dashboard, Chart.js JSON endpoint, CSV export.
- `config/database.php` – PDO connection to standalone MariaDB.
- `database/library.sql` – MariaDB schema and views.
- `assets/css/style.css` – UI styles.
- `assets/js/charts.js` – Chart.js integration.
- `uploads/...` – Runtime storage for uploaded files.

## Setup

1. Import the SQL:

