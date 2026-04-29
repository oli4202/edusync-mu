<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;
use App\Models\Course;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Grade;
use App\Models\Subject;
use function getDB;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireFaculty();
    }

    public function index(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $pendingQuestions = Question::findPending();
        $pendingAnswers = Answer::findPending();

        $stats = [
            'users' => User::getStudentCount(),
            'questions' => Question::getCount(true),
            'answers' => Answer::getCount(true),
            'groups' => Group::getCount(),
        ];

        $this->render('pages/admin/index', compact(
            'user', 'pendingQuestions', 'pendingAnswers', 'stats'
        ));
    }

    public function approveQuestion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Question::approve($id, $this->session->userId());
        }
        redirect('/admin');
    }

    public function rejectQuestion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Question::delete($id);
        }
        redirect('/admin');
    }

    public function approveAnswer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Answer::approve($id, $this->session->userId());
        }
        redirect('/admin');
    }

    public function rejectAnswer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Answer::delete($id);
        }
        redirect('/admin');
    }

    public function manageAttendance(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $msg = $this->session->getFlash('success');
        $err = $this->session->getFlash('error');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'bulk_mark') {
                $courseId = (int)$_POST['course_id'];
                $classDate = clean($_POST['class_date']);
                $statuses = $_POST['status'] ?? [];
                $notes = $_POST['note'] ?? [];

                if (!$courseId || !$classDate) {
                    $this->session->setFlash('error', 'Select a course and date.');
                } else {
                    $count = 0;
                    foreach ($statuses as $studentId => $status) {
                        $note = clean($notes[$studentId] ?? '');
                        Attendance::record((int)$studentId, $courseId, $classDate, $status, $note);
                        $count++;
                    }
                    $this->session->setFlash('success', "Attendance marked for $count students!");
                }
                $redirectUrl = "/admin/attendance?course_id=$courseId&class_date=$classDate";
                $redirectUrl .= '&batch=' . urlencode((string) ($_POST['batch'] ?? ''));
                $redirectUrl .= '&semester=' . urlencode((string) ($_POST['semester'] ?? ''));
                redirect($redirectUrl);
            } elseif ($action === 'random_results') {
                $batch = clean($_POST['batch'] ?? '');
                $semester = (int)($_POST['semester'] ?? 0);

                if ($batch === '' || $semester <= 0) {
                    $this->session->setFlash('error', 'Select batch and semester before generating random results.');
                } else {
                    $students = User::getStudentsForAttendance($batch, $semester, 0);
                    $generated = 0;
                    $db = getDB();
                    $existsStmt = $db->prepare("SELECT COUNT(*) FROM grades WHERE user_id = ? AND subject_id = ? AND title = ?");
                    $components = [
                        ['title' => 'Attendance', 'max' => 10, 'min' => 6],
                        ['title' => 'Class Test 1', 'max' => 15, 'min' => 7],
                        ['title' => 'Class Test 2', 'max' => 15, 'min' => 7],
                        ['title' => 'Assignment/Presentation', 'max' => 10, 'min' => 5],
                        ['title' => 'Viva', 'max' => 10, 'min' => 5],
                        ['title' => 'Final', 'max' => 40, 'min' => 16],
                    ];

                    foreach ($students as $student) {
                        $studentId = (int)($student['id'] ?? 0);
                        if ($studentId <= 0) {
                            continue;
                        }

                        Subject::syncForUserBatchSemester($studentId, $batch, $semester);
                        $subjects = Subject::findByUser($studentId);
                        foreach ($subjects as $subject) {
                            if ((int)($subject['semester'] ?? 0) !== $semester) {
                                continue;
                            }
                            $subjectId = (int)$subject['id'];
                            foreach ($components as $component) {
                                $title = 'Semester ' . $semester . ' ' . $component['title'];
                                $existsStmt->execute([$studentId, $subjectId, $title]);
                                if ((int)$existsStmt->fetchColumn() > 0) {
                                    continue;
                                }

                                Grade::create($studentId, [
                                    'subject_id' => $subjectId,
                                    'title' => $title,
                                    'score' => (float)random_int((int)$component['min'], (int)$component['max']),
                                    'max_score' => (float)$component['max'],
                                    'exam_date' => date('Y-m-d'),
                                ]);
                                $generated++;
                            }
                        }
                    }

                    $this->session->setFlash('success', "Random results generated: $generated records.");
                }

                $redirectUrl = '/admin/attendance';
                $redirectUrl .= '?batch=' . urlencode($batch);
                $redirectUrl .= '&semester=' . urlencode((string)$semester);
                redirect($redirectUrl);
            }
        }

        $courses = Course::getAll();
        $selCourse = (int)($_GET['course_id'] ?? 0);
        $selBatch = clean($_GET['batch'] ?? '');
        $selSemester = (int)($_GET['semester'] ?? 0);
        $selDate = clean($_GET['class_date'] ?? date('Y-m-d'));

        $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourse);

        $availableBatches = User::getBatchOptions();

        $existingAtt = [];
        if ($selCourse && $selDate) {
            $existingAtt = Attendance::getExistingForCourseAndDate($selCourse, $selDate);
        }

        $recentHistory = [];
        if ($selCourse) {
            $recentHistory = Attendance::getRecentHistoryForCourse($selCourse);
        }

        $stats = [
            'totalRecords' => Attendance::getTotalCount(),
            'todayCount' => Attendance::getTodayCount(),
        ];

        $this->render('pages/admin/attendance', compact(
            'courses', 'students', 'selCourse', 'selDate', 'selBatch', 'selSemester',
            'existingAtt', 'recentHistory', 'stats', 'availableBatches'
        ));
    }

    public function attendanceSheet(): void
    {
        User::ensureRosterSynced();
        $selCourseId = (int)($_GET['course_id'] ?? 0);
        $selBatch = clean($_GET['batch'] ?? '');
        $selSemester = (int)($_GET['semester'] ?? 0);

        $course = $selCourseId ? Course::findById($selCourseId) : null;
        $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourseId);

        $this->render('pages/admin/attendance-sheet', compact(
            'course', 'students', 'selBatch', 'selSemester'
        ));
    }

    public function studentDirectory(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $studentId = clean($_GET['student_id'] ?? '');
        $overview = $studentId !== '' ? User::getStudentOverviewByStudentId($studentId) : null;
        $notFound = $studentId !== '' && $overview === null;
        
        $allStudents = [];
        if ($studentId === '') {
            $allStudents = User::getAllStudentsWithStats();
        }

        $this->render('pages/admin/student-directory', compact('user', 'studentId', 'overview', 'notFound', 'allStudents'));
    }

    public function apiSettings(): void
    {
        $this->requireAdmin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groq = trim($_POST['groq'] ?? '');
            $gemini = trim($_POST['gemini'] ?? '');
            $preferred = trim($_POST['preferred_vision'] ?? 'groq');

            $content = "<?php\n\n\$api_keys = [\n";
            $content .= "    'GROQ_API_KEY' => '" . addslashes($groq) . "',\n";
            $content .= "    'GEMINI_API_KEY' => '" . addslashes($gemini) . "',\n";
            $content .= "    'PREFERRED_VISION_MODEL' => '" . addslashes($preferred) . "',\n";
            $content .= "];";
            
            file_put_contents(__DIR__ . '/../../config/api-keys.php', $content);
            $message = 'API settings updated successfully!';
        }

        include __DIR__ . '/../../config/api-keys.php';

        $this->render('pages/admin/api-settings', compact('user', 'message', 'api_keys'));
    }

    public function detailedAssessment(): void
    {
        User::ensureRosterSynced();
        $selCourseId = (int)($_GET['course_id'] ?? 0);
        $selBatch = clean($_GET['batch'] ?? '');
        $selSemester = (int)($_GET['semester'] ?? 0);

        if (!$selCourseId || !$selBatch) {
            $this->session->setFlash('error', 'Select a course and batch to view the full assessment grid.');
            redirect('/admin/attendance');
        }

        $course = Course::findById($selCourseId);
        $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourseId);
        
        $dates = Attendance::getUniqueDatesForCourse($selCourseId, $selBatch);
        $attendanceGrid = Attendance::getGridReport($selCourseId, $selBatch);
        
        // Fetch grades for each student for this specific course
        $grades = [];
        foreach ($students as $s) {
            // Find the subject entry for this student and course code
            $db = getDB();
            $subjStmt = $db->prepare("SELECT id FROM subjects WHERE user_id = ? AND code = ?");
            $subjStmt->execute([$s['id'], $course['code']]);
            $subjectId = (int)$subjStmt->fetchColumn();
            
            if ($subjectId) {
                $grades[$s['id']] = Grade::findBySubject($subjectId);
            } else {
                $grades[$s['id']] = [];
            }
        }

        $this->render('pages/admin/continuous-assessment', compact(
            'course', 'students', 'selBatch', 'selSemester', 'dates', 'attendanceGrid', 'grades'
        ));
    }
}
