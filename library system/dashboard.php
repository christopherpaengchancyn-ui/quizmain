<?php
require_once __DIR__ . '/config/database.php';

if (isset($_GET['mode']) && $_GET['mode'] === 'json') {
    $stmt = $pdo->query("
        SELECT file_type, COUNT(*) AS total_files
        FROM files
        GROUP BY file_type
        ORDER BY total_files DESC
    ");
    $filesPerType = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT DATE(upload_date) AS upload_day,
               COUNT(*)          AS total_uploads
        FROM files
        GROUP BY DATE(upload_date)
        ORDER BY upload_day
    ");
    $uploadsPerDay = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT
            COUNT(*)                    AS total_files,
            COUNT(DISTINCT file_type)   AS distinct_types,
            MAX(upload_date)            AS last_upload
        FROM files
    ");
    $summary = $stmt->fetch();

    header('Content-Type: application/json');
    echo json_encode([
        'filesPerType'   => $filesPerType,
        'uploadsPerDay'  => $uploadsPerDay,
        'summary'        => $summary,
    ]);
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $pdo->query("
        SELECT original_name, file_type, folder_path, upload_date
        FROM files
        ORDER BY upload_date DESC
    ");
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=library_files_export.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['File name', 'File type', 'Folder location', 'Upload date']);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['original_name'],
            $row['file_type'],
            $row['folder_path'],
            $row['upload_date'],
        ]);
    }

    fclose($output);
    exit;
}

$stmt = $pdo->query("
    SELECT file_type,
           COUNT(*)                  AS total_files,
           ROUND(AVG(size_bytes), 2) AS avg_size_bytes
    FROM files
    GROUP BY file_type
    ORDER BY total_files DESC
");
$descriptiveRows = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT original_name, file_type, folder_path, upload_date
    FROM files
    ORDER BY upload_date DESC
    LIMIT 20
");
$recentFiles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Analytics - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/charts.js" defer></script>
</head>
<body>
<header>
    <div class="brand">Library Analytics System</div>
    <nav>
        <a href="index.php">Upload</a>
        <a href="dashboard.php" class="active">Dashboard</a>
    </nav>
</header>

<main>
    <h1 class="page-title">Analytics Dashboard</h1>
    <p class="page-subtitle">
        Live statistics from the MariaDB database: file counts, distributions, and upload trends.
    </p>

    <section class="card-grid">
        <article class="card">
            <div class="card-header">
                <div class="card-title">Total files</div>
                <div class="card-sub">Tracked in MariaDB</div>
            </div>
            <div class="card-value" id="totalFiles">0</div>
        </article>
        <article class="card">
            <div class="card-header">
                <div class="card-title">File types</div>
                <div class="card-sub">Distinct extensions</div>
            </div>
            <div class="card-value" id="totalTypes">0</div>
        </article>
        <article class="card">
            <div class="card-header">
                <div class="card-title">Last upload</div>
                <div class="card-sub">Timestamp</div>
            </div>
            <div class="card-value" id="lastUpload">—</div>
        </article>
        <article class="card">
            <div class="card-header">
                <div class="card-title">Export</div>
                <div class="card-sub">Download CSV</div>
            </div>
            <button class="btn-secondary" onclick="window.location.href='dashboard.php?export=csv'">
                Export table as CSV
            </button>
        </article>
    </section>

    <section class="charts-grid">
        <article class="chart-card">
            <h3>Files per type (bar)</h3>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </article>
        <article class="chart-card">
            <h3>File distribution (pie)</h3>
            <div class="chart-container">
                <canvas id="pieChart"></canvas>
            </div>
        </article>
    </section>

    <section class="charts-grid" style="margin-top:18px;">
        <article class="chart-card">
            <h3>Uploads over time (line)</h3>
            <div class="chart-container">
                <canvas id="lineChart"></canvas>
            </div>
        </article>
        <article class="chart-card">
            <h3>Descriptive statistics</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Total files</th>
                            <th>Average size (bytes)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($descriptiveRows): ?>
                            <?php foreach ($descriptiveRows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['file_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_files']); ?></td>
                                    <td><?php echo htmlspecialchars($row['avg_size_bytes']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No data yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section style="margin-top:22px;">
        <h2 class="page-title" style="font-size:1.1rem;">Files table</h2>
        <p class="page-subtitle">Latest uploaded files with type, location, and date.</p>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>File name</th>
                        <th>File type</th>
                        <th>Folder location</th>
                        <th>Upload date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentFiles): ?>
                        <?php foreach ($recentFiles as $file): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($file['original_name']); ?></td>
                                <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                                <td><?php echo htmlspecialchars($file['folder_path']); ?></td>
                                <td><?php echo htmlspecialchars($file['upload_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No files uploaded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
