<?php
require_once __DIR__ . '/config/database.php';

if (!isset($_FILES['library_file']) || $_FILES['library_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: index.php?status=error');
    exit;
}

$file      = $_FILES['library_file'];
$origName  = $file['name'];
$tmpPath   = $file['tmp_name'];
$sizeBytes = (int)$file['size'];

$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
$fileType = $ext;

switch ($ext) {
    case 'pdf':
        $category = 'PDF Document';
        $folder   = 'uploads/pdf';
        break;
    case 'doc':
    case 'docx':
        $category = 'Word Document';
        $folder   = 'uploads/docx';
        break;
    case 'xls':
    case 'xlsx':
    case 'csv':
        $category = 'Spreadsheet';
        $folder   = 'uploads/excel';
        break;
    default:
        $category = 'Other';
        $folder   = 'uploads/others';
        break;
}

if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$mimeType = mime_content_type($tmpPath);

$checkStmt = $pdo->prepare("
    SELECT id FROM files
    WHERE original_name = :origName
      AND size_bytes    = :sizeBytes
      AND file_type     = :fileType
    LIMIT 1
");
$checkStmt->execute([
    ':origName'  => $origName,
    ':sizeBytes' => $sizeBytes,
    ':fileType'  => $fileType,
]);

$isDuplicate = (bool)$checkStmt->fetchColumn();

$timestamp  = date('Ymd_His');
$randomPart = bin2hex(random_bytes(4));
$sanitized  = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $origName);
$storedName = $timestamp . '_' . $randomPart . '_' . $sanitized;

$targetPath = $folder . DIRECTORY_SEPARATOR . $storedName;

if (!move_uploaded_file($tmpPath, $targetPath)) {
    header('Location: index.php?status=error');
    exit;
}

$insertStmt = $pdo->prepare("
    INSERT INTO files (
        original_name,
        stored_name,
        file_type,
        category,
        folder_path,
        size_bytes,
        mime_type,
        upload_date
    ) VALUES (
        :original_name,
        :stored_name,
        :file_type,
        :category,
        :folder_path,
        :size_bytes,
        :mime_type,
        NOW()
    )
");

$insertStmt->execute([
    ':original_name' => $origName,
    ':stored_name'   => $storedName,
    ':file_type'     => $fileType,
    ':category'      => $category,
    ':folder_path'   => $folder,
    ':size_bytes'    => $sizeBytes,
    ':mime_type'     => $mimeType,
]);

$status = $isDuplicate ? 'duplicate' : 'ok';
header('Location: index.php?status=' . urlencode($status));
exit;
