<?php

namespace App\Models;

use function App\getDB;

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
}
