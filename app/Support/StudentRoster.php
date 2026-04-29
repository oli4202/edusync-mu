<?php

namespace App\Support;

class StudentRoster
{
    private static ?array $rows = null;

    public static function all(): array
    {
        if (self::$rows === null) {
            $rows = require __DIR__ . '/../Data/student_roster.php';

            self::$rows = array_values(array_filter(array_map(static function (array $row): ?array {
                $studentId = trim((string) ($row['student_id'] ?? ''));
                if ($studentId === '') {
                    return null;
                }

                $name = trim((string) ($row['name'] ?? ''));
                $batchStr = (string) ($row['batch'] ?? '');
                $batchNum = (int) $batchStr;
                
                // Dynamically calculate semester:
                // Reference: Batch 6 is Semester 5
                // Formula: 5 + (6 - Batch)
                $semesterNum = 5 + (6 - $batchNum);
                if ($semesterNum < 1) $semesterNum = 1;

                // Determine Season (Spring = Jan-Jun, Fall/Summer = Jul-Dec)
                $currentMonth = (int) date('n');
                $season = ($currentMonth >= 1 && $currentMonth <= 6) ? 'Spring' : 'Fall';

                return [
                    'batch' => $batchStr,
                    'semester' => $semesterNum,
                    'season' => $season,
                    'label' => trim((string) ($row['label'] ?? '')),
                    'student_id' => $studentId,
                    'name' => $name !== '' ? $name : 'Student ' . $studentId,
                ];
            }, $rows)));
        }

        return self::$rows;
    }

    public static function findMemberships(string $studentId): array
    {
        $studentId = trim($studentId);

        return array_values(array_filter(self::all(), static fn(array $row): bool => $row['student_id'] === $studentId));
    }

    public static function findPrimary(string $studentId): ?array
    {
        $memberships = self::findMemberships($studentId);
        if ($memberships === []) {
            return null;
        }

        usort($memberships, static function (array $a, array $b): int {
            if ($a['semester'] !== $b['semester']) {
                return $b['semester'] <=> $a['semester'];
            }

            return (int) $a['batch'] <=> (int) $b['batch'];
        });

        return $memberships[0];
    }

    public static function primaryAssignments(): array
    {
        $grouped = [];
        foreach (self::all() as $row) {
            $grouped[$row['student_id']][] = $row;
        }

        $primary = [];
        foreach (array_keys($grouped) as $studentId) {
            $primary[$studentId] = self::findPrimary($studentId);
        }

        return $primary;
    }

    public static function batchOptions(): array
    {
        $batches = array_values(array_unique(array_map(static fn(array $row): string => $row['batch'], self::all())));
        sort($batches, SORT_NATURAL);

        return $batches;
    }
}
