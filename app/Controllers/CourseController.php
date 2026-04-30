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

        $semesters = Course::getSemesterOptionsByBatch($batch);
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

    /**
     * API: Get all courses for a batch
     */
    public function filterAll(): void
    {
        $this->requireLogin();
        $batch = $_GET['batch'] ?? '';

        if (empty($batch)) {
            $this->json(['error' => 'Batch is required'], 400);
        }

        $courses = Course::findByBatch($batch);
        $this->json($courses);
    }

    /**
     * Show student's enrolled courses
     */
    public function myEnrollments(): void
    {
        $this->requireLogin();
        
        $user = $this->session->getUser();
        if (!$user || $user['role'] !== 'student') {
            redirect('/login');
        }

        $enrolledCourses = Course::getStudentEnrolledCourses((int)$user['id']);
        $currentPage = 'enrollments';
        $pageTitle = 'My Course Enrollments - EduSync MU';

        $this->render('pages/enrollments', compact('enrolledCourses', 'currentPage', 'pageTitle', 'user'));
    }

    /**
     * Show enrollment form for a specific course
     */
    public function enrollForm(): void
    {
        $this->requireLogin();
        
        $user = $this->session->getUser();
        if (!$user || $user['role'] !== 'student') {
            redirect('/login');
        }

        $courseId = (int)($_GET['courseId'] ?? $_POST['courseId'] ?? 0);
        $course = Course::findById($courseId);

        if (!$course) {
            $this->json(['error' => 'Course not found'], 404);
            return;
        }

        // Check if already enrolled
        $isEnrolled = Course::isStudentEnrolled((int)$user['id'], $courseId);

        $this->json([
            'success' => true,
            'course' => $course,
            'isEnrolled' => $isEnrolled
        ]);
    }

    /**
     * API: Enroll student in a course
     */
    public function enroll(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request method'], 400);
            return;
        }

        $user = $this->session->getUser();
        if (!$user || $user['role'] !== 'student') {
            $this->json(['error' => 'Only students can enroll'], 403);
            return;
        }

        $courseId = (int)($_POST['course_id'] ?? 0);
        $course = Course::findById($courseId);

        if (!$course) {
            $this->json(['error' => 'Course not found'], 404);
            return;
        }

        // Use course's batch and semester from the course record
        $result = Course::enrollStudent(
            (int)$user['id'],
            $courseId,
            $course['batch'] ?? $user['batch'] ?? '',
            (int)$course['semester']
        );

        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * API: Unenroll student from a course
     */
    public function unenroll(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request method'], 400);
            return;
        }

        $user = $this->session->getUser();
        if (!$user || $user['role'] !== 'student') {
            $this->json(['error' => 'Only students can unenroll'], 403);
            return;
        }

        $courseId = (int)($_POST['course_id'] ?? 0);

        $result = Course::unenrollStudent((int)$user['id'], $courseId);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * API: Get enrolled students for a course (for faculty attendance)
     */
    public function getEnrolledStudents(): void
    {
        $this->requireLogin();

        $user = $this->session->getUser();
        if (!$user || !in_array($user['role'], ['faculty', 'admin'])) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }

        $courseId = (int)($_GET['courseId'] ?? 0);
        
        // Verify teacher teaches this course (unless admin)
        if ($user['role'] === 'faculty') {
            if (!Course::isTeacherAssigned((int)$user['id'], $courseId)) {
                $this->json(['error' => 'You do not teach this course'], 403);
                return;
            }
        }

        $students = Course::getEnrolledStudents($courseId);
        $this->json(['success' => true, 'students' => $students]);
    }
}
