<?php
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->query("SELECT * FROM files ORDER BY upload_date DESC LIMIT 10");
$recentFiles = $stmt->fetchAll();

$statusText = '';
$statusClass = 'status';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'ok') {
        $statusText = 'File uploaded and processed successfully.';
        $statusClass = 'status ok';
    } elseif ($_GET['status'] === 'duplicate') {
        $statusText = 'File uploaded (duplicate detected in database).';
        $statusClass = 'status ok';
    } else {
        $statusText = 'Upload error.';
        $statusClass = 'status err';
    }
} else {
    $statusText = 'Waiting for file...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Analytics - Upload</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <div class="brand">Library Analytics System</div>
    <nav>
        <a href="index.php" class="active">Upload</a>
        <a href="dashboard.php">Dashboard</a>
    </nav>
</header>

<main>
    <h1 class="page-title">File Ingestion & Sorting</h1>
    <p class="page-subtitle">
        Upload library files in multiple formats; the system will classify, sort, and store them automatically.
    </p>

    <div class="upload-wrapper">
        <section class="upload-panel">
            <h2>Upload file</h2>
            <div id="dropZone" class="upload-drop">
                <span>Drop a file here or click to browse</span>
                <small>Supported: PDF, DOCX, XLSX, CSV and others</small>
                <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="library_file" id="fileInput" required>
                    <button type="submit" class="btn-primary">Upload & Process</button>
                </form>
            </div>
            <div id="status" class="<?php echo htmlspecialchars($statusClass); ?>">
                <?php echo htmlspecialchars($statusText); ?>
            </div>
        </section>

        <section>
            <div class="card-grid">
                <article class="card">
                    <div class="card-header">
                        <div class="card-title">Algorithm</div>
                    </div>
                    <p class="card-sub">
                        1. Read file extension<br>
                        2. Check duplicate in database<br>
                        3. Assign category and folder<br>
                        4. Move file and save metadata<br>
                        5. Dashboard reads analytics from MariaDB
                    </p>
                </article>
                <article class="card">
                    <div class="card-header">
                        <div class="card-title">Storage Paths</div>
                    </div>
                    <p class="card-sub">
                        PDF → uploads/pdf<br>
                        DOCX → uploads/docx<br>
                        XLSX, CSV → uploads/excel<br>
                        Others → uploads/others
                    </p>
                </article>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>File name</th>
                            <th>Type</th>
                            <th>Folder</th>
                            <th>Uploaded at</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentFiles): ?>
                            <?php foreach ($recentFiles as $file): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($file['original_name']); ?></td>
                                    <td>
                                        <span class="badge badge-type">
                                            <?php echo htmlspecialchars($file['file_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-folder">
                                            <?php echo htmlspecialchars($file['folder_path']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($file['upload_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No files uploaded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const uploadForm = document.getElementById('uploadForm');
const statusEl = document.getElementById('status');

dropZone.addEventListener('click', () => fileInput.click());

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.add('dragover');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('dragover');
    });
});

dropZone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        statusEl.textContent = 'File selected: ' + files[0].name;
        statusEl.className = 'status';
    }
});

uploadForm.addEventListener('submit', () => {
    statusEl.textContent = 'Uploading and processing file...';
    statusEl.className = 'status';
});
</script>
</body>
</html>
