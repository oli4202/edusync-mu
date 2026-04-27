<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;

class CourseController extends Controller
{
    /**
     * API: Get semesters for a batch
     */
    public function semesters(): void
    {
        $this->requireLogin();
        $batch = $_GET['batch'] ?? '';
        if (empty($batch)) {
            $this->json(['error' => 'Batch is required'], 400);
        }

        $semesters = Course::getSemestersByBatch($batch);
        $this->json($semesters);
    }

    /**
     * API: Get courses for a batch and semester
     */
    public function filter(): void
    {
        $this->requireLogin();
        $batch = $_GET['batch'] ?? '';
        $semester = (int)($_GET['semester'] ?? 0);

        if (empty($batch) || $semester <= 0) {
            $this->json(['error' => 'Batch and Semester are required'], 400);
        }

        $courses = Course::findByBatchAndSemester($batch, $semester);
        $this->json($courses);
    }
}
