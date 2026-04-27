<?php

namespace App\Models;

use function getDB;

/**
 * Course Model
 */
class Course
{
    /**
     * Get all courses
     */
    public static function getAll(): array
    {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM courses ORDER BY year, semester, name");
        return $stmt->fetchAll();
    }

    /**
     * Find course by ID
     */
    public static function findById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM courses WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get courses by year and semester
     */
    public static function findBySemester(int $year, int $semester): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM courses WHERE year = ? AND semester = ? ORDER BY name");
        $stmt->execute([$year, $semester]);
        return $stmt->fetchAll();
    }

    /**
     * Get courses by batch
     */
    public static function findByBatch(string $batch): array
    {
        $db = getDB();
        // Clean batch name (e.g., "Batch 5" -> "5")
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        
        $stmt = $db->prepare("SELECT * FROM courses WHERE (FIND_IN_SET(?, batch) OR batch IS NULL) AND status = 'active' ORDER BY year, semester, name");
        $stmt->execute([$batchNum]);
        return $stmt->fetchAll();
    }

    /**
     * Get distinct semesters for a specific batch
     */
    public static function getSemestersByBatch(string $batch): array
    {
        $db = getDB();
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        $stmt = $db->prepare("SELECT DISTINCT semester FROM courses WHERE (FIND_IN_SET(?, batch) OR batch IS NULL) AND status = 'active' ORDER BY semester");
        $stmt->execute([$batchNum]);
        return array_column($stmt->fetchAll(), 'semester');
    }

    /**
     * Find courses by batch and semester
     */
    public static function findByBatchAndSemester(string $batch, int $semester): array
    {
        $db = getDB();
        $batchNum = preg_replace('/[^0-9]/', '', $batch);
        $stmt = $db->prepare("SELECT * FROM courses WHERE (FIND_IN_SET(?, batch) OR batch IS NULL) AND semester = ? AND status = 'active' ORDER BY name");
        $stmt->execute([$batchNum, $semester]);
        return $stmt->fetchAll();
    }

    public static function getDistinctBatches(): array
    {
        $db = getDB();
        $stmt = $db->query("SELECT DISTINCT batch FROM courses WHERE batch IS NOT NULL AND batch != '' ORDER BY batch");
        $batches = [];

        foreach ($stmt->fetchAll() as $row) {
            foreach (explode(',', (string) $row['batch']) as $batch) {
                $batch = trim($batch);
                if ($batch !== '') {
                    $batches[$batch] = $batch;
                }
            }
        }

        ksort($batches, SORT_NATURAL);

        return array_values($batches);
    }
}
