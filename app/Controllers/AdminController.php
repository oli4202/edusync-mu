<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;
use App\Models\Course;
use App\Models\Attendance;
use App\Models\Group;
use function App\redirect;
use function App\clean;
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

        $this->render('pages/admin/student-directory', compact('user', 'studentId', 'overview', 'notFound'));
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

            $content = "<?php\n\n\$api_keys = [\n";
            $content .= "    'GROQ_API_KEY' => '" . addslashes($groq) . "',\n";
            $content .= "    'GEMINI_API_KEY' => '" . addslashes($gemini) . "',\n";
            $content .= "];";
            
            file_put_contents(__DIR__ . '/../../config/api-keys.php', $content);
            $message = 'API keys updated successfully!';
        }

        include __DIR__ . '/../../config/api-keys.php';

        $this->render('pages/admin/api-settings', compact('user', 'message', 'api_keys'));
    }
}
