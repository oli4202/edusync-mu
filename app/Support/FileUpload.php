<?php

namespace App\Support;

/**
 * File Upload Handler
 * Handles file validation, storage, and management
 */
class FileUpload
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads';
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    
    private static array $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'text/plain' => 'txt',
    ];

    /**
     * Validate upload file
     */
    public static function validate($file): array
    {
        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['type'])) {
            return ['success' => false, 'error' => 'Invalid file structure'];
        }

        if ($file['size'] === 0) {
            return ['success' => false, 'error' => 'File is empty'];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'File exceeds maximum size (10MB)'];
        }

        if (!isset(self::$allowed_types[$file['type']])) {
            return ['success' => false, 'error' => 'File type not allowed'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'File upload failed validation'];
        }

        return ['success' => true];
    }

    /**
     * Save uploaded file to storage
     */
    public static function save($file, string $category = 'general'): array
    {
        // Validate
        $validation = self::validate($file);
        if (!$validation['success']) {
            return $validation;
        }

        // Ensure upload directory exists
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        // Create category subdirectory
        $categoryDir = self::UPLOAD_DIR . '/' . $category;
        if (!is_dir($categoryDir)) {
            mkdir($categoryDir, 0755, true);
        }

        // Generate unique filename
        $extension = self::$allowed_types[$file['type']] ?? 'bin';
        $basename = pathinfo($file['name'], PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $basename);
        $filename = uniqid() . '_' . $basename . '.' . $extension;
        $filepath = $categoryDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'error' => 'Failed to save file'];
        }

        // Return relative path for database storage
        $relativePath = '/uploads/' . $category . '/' . $filename;

        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename,
            'size' => $file['size'],
            'type' => $file['type'],
        ];
    }

    /**
     * Delete file from storage
     */
    public static function delete(string $relativePath): bool
    {
        $fullPath = __DIR__ . '/../../public' . $relativePath;
        
        // Prevent directory traversal attacks
        $realPath = realpath($fullPath);
        if ($realPath === false || strpos($realPath, realpath(self::UPLOAD_DIR)) !== 0) {
            return false;
        }

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Get file path for serving
     */
    public static function getPath(string $relativePath): string
    {
        return '/uploads' . (strpos($relativePath, '/uploads') === 0 ? substr($relativePath, 8) : $relativePath);
    }

    /**
     * Check if file exists
     */
    public static function exists(string $relativePath): bool
    {
        $fullPath = __DIR__ . '/../../public' . $relativePath;
        return file_exists($fullPath) && is_file($fullPath);
    }

    /**
     * Get file size in human readable format
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Add allowed file type
     */
    public static function allowType(string $mimeType, string $extension): void
    {
        self::$allowed_types[$mimeType] = $extension;
    }

    /**
     * Get allowed types
     */
    public static function getAllowedTypes(): array
    {
        return self::$allowed_types;
    }
}
