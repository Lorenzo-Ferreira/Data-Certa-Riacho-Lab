<?php
// Utilitário: cria o arquivo datacerta.zip para download
// Acesse: http://localhost/datacerta/criar_zip.php

$zipPath = __DIR__ . '/../datacerta.zip';
$sourceDir = __DIR__;

if (!class_exists('ZipArchive')) {
    die('Extensão ZipArchive não disponível neste servidor.');
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die('Não foi possível criar o arquivo ZIP.');
}

$ignora = ['criar_zip.php', '.', '..'];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    $filePath = $file->getRealPath();
    $relativePath = 'datacerta/' . substr($filePath, strlen($sourceDir) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    if ($file->isDir()) {
        $zip->addEmptyDir($relativePath);
    } else {
        if (!in_array(basename($filePath), $ignora)) {
            $zip->addFile($filePath, $relativePath);
        }
    }
}

$zip->close();

// Download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="datacerta.zip"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);
unlink($zipPath);
exit;
