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
use function App\getDB;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    public function index(): void
    {
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
                redirect("/admin/attendance?course_id=$courseId&class_date=$classDate");
            }
        }

        $courses = Course::getAll();
        $students = User::getAll(); // Should probably filter students only
        $students = array_filter($students, fn($u) => $u['role'] === 'student');

        $selCourse = (int)($_GET['course_id'] ?? 0);
        $selDate = clean($_GET['class_date'] ?? date('Y-m-d'));

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
        ];
        // Correct way for todayCount
        $stmt = getDB()->prepare("SELECT COUNT(*) FROM attendance WHERE class_date=?");
        $stmt->execute([date('Y-m-d')]);
        $stats['todayCount'] = (int)$stmt->fetchColumn();

        $this->render('pages/admin/attendance', compact(
            'user', 'msg', 'err', 'courses', 'students', 'selCourse', 'selDate', 'existingAtt', 'recentHistory', 'stats'
        ));
    }

    public function apiSettings(): void
    {
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $claude = $_POST['claude'] ?? '';
            $gemini = $_POST['gemini'] ?? '';
            $hf = $_POST['hf'] ?? '';

            $content = "<?php\n\n\$api_keys = [\n    'CLAUDE_API_KEY' => '" . addslashes($claude) . "',\n    'GEMINI_API_KEY' => '" . addslashes($gemini) . "',\n    'HF_API_KEY' => '" . addslashes($hf) . "',\n];";
            file_put_contents(__DIR__ . '/../../config/api-keys.php', $content);
            $message = 'API keys updated successfully!';
        }

        include __DIR__ . '/../../config/api-keys.php';

        $this->render('pages/admin/api-settings', compact('user', 'message', 'api_keys'));
    }
}
