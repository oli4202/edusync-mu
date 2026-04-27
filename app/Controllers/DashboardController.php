<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\{Subject, Task, Grade, Attendance, User, StudyLog, Question, Course};
use function getDB;
use function App\redirect;
use function App\clean;

/**
 * DashboardController — Main dashboard and analytics
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        if (($user['role'] ?? 'student') === 'student') {
            Subject::syncForUserBatchSemester((int) $userId, (string) ($user['batch'] ?? ''), (int) ($user['semester'] ?? 1));
            $user = User::findById($userId);
        }

        if ($this->session->isFaculty()) {
            // Faculty Dashboard
            $pendingQuestions = Question::findPending();
            $pendingAnswers = \App\Models\Answer::findPending();
            
            $todayAttendanceCount = Attendance::getTodayCount();

            $studentCount = User::getStudentCount();

            $this->render('dashboard/faculty', compact('user', 'pendingQuestions', 'pendingAnswers', 'todayAttendanceCount', 'studentCount'));
        } else {
            // Get stats
            $tasksDueCount = Task::getTasksDueSoon($userId);
            $doneCount = Task::getDoneCount($userId);
            $weekHours = StudyLog::getWeeklyHours($userId);

            // Upcoming tasks
            $upcomingTasks = Task::getUpcomingTasks($userId);

            // Recent questions
            $recentQuestions = Question::getRecentApproved();

            $this->render('dashboard/index', compact('user', 'tasksDueCount', 'doneCount', 'weekHours', 'upcomingTasks', 'recentQuestions'));
        }
    }

    public function analytics(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        // Study logs - weekly breakdown
        $studyLogs = StudyLog::getRecentLogs($userId);

        // Grade trends
        $grades = Grade::getAverageScoresBySubject($userId);

        $this->render('dashboard/analytics', compact('user', 'studyLogs', 'grades'));
    }

    public function subjects(): void
    {
        $this->requireLogin();
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add') {
                $name = clean($_POST['name'] ?? '');
                $code = clean($_POST['code'] ?? '');
                $color = clean($_POST['color'] ?? '#4f46e5');
                $semester = (int)($_POST['semester'] ?? 1);
                $year = (int)($_POST['year'] ?? date('Y'));
                $target_hours = (float)($_POST['target_hours'] ?? 5.0);

                if ($name) {
                    Subject::create($userId, [
                        'name' => $name,
                        'code' => $code,
                        'color' => $color,
                        'semester' => $semester,
                        'year' => $year,
                        'target_hours_per_week' => $target_hours
                    ]);
                    $this->session->setFlash('success', 'Subject added successfully!');
                }
            } elseif ($action === 'delete') {
                $subjectId = (int)($_POST['subject_id'] ?? 0);
                if ($subjectId) {
                    Subject::delete($subjectId);
                    $this->session->setFlash('success', 'Subject removed.');
                }
            }
            redirect('/subjects');
        }

        $user = User::findById($userId);
        Subject::syncForUserBatchSemester((int) $userId, (string) ($user['batch'] ?? ''), (int) ($user['semester'] ?? 1));
        $user = User::findById($userId);

        // Fetch subjects with stats (like pending tasks, done tasks)
        $subjects = Subject::findByUserWithStats($userId);

        // Get all courses to populate datalist (filtered by user's batch)
        $courses = \App\Models\Course::findByBatch($user['batch'] ?? '');
        if (empty($courses)) {
            $courses = \App\Models\Course::getAll(); // Fallback if no specific batch courses found
        }
        $courseCatalog = [];
        foreach ($courses as $course) {
            $displayName = trim($course['code'] . ': ' . $course['name']);
            $courseCatalog[$displayName] = [
                'code' => $course['code'],
                'semester' => (int) $course['semester'],
                'year' => (int) $course['year'],
            ];
        }

        $this->render('pages/subjects', compact('user', 'subjects', 'courses', 'courseCatalog'));
    }

    public function tasks(): void
    {
        $this->requireLogin();
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add') {
                $title = clean($_POST['title'] ?? '');
                $subjectId = (int)($_POST['subject_id'] ?? 0);
                $type = clean($_POST['type'] ?? 'assignment');
                $dueDate = clean($_POST['due_date'] ?? '');
                
                if ($title && $subjectId && $dueDate) {
                    Task::create($userId, [
                        'title' => $title,
                        'subject_id' => $subjectId,
                        'type' => $type,
                        'due_date' => $dueDate
                    ]);
                    $this->session->setFlash('success', 'Task created successfully!');
                }
            } elseif ($action === 'delete') {
                $taskId = (int)($_POST['task_id'] ?? 0);
                if ($taskId) {
                    Task::delete($taskId);
                    $this->session->setFlash('success', 'Task deleted.');
                }
            } elseif ($action === 'toggle') {
                $taskId = (int)($_POST['task_id'] ?? 0);
                $status = clean($_POST['status'] ?? 'pending');
                if ($taskId) {
                    Task::updateStatus($taskId, $status);
                }
            }
            redirect('/tasks');
        }

        $user = User::findById($userId);
        $tasks = Task::findByUser($userId);
        $subjects = Subject::findByUser($userId);
        
        $this->render('pages/tasks', compact('user', 'tasks', 'subjects'));
    }

    public function grades(): void
    {
        $this->requireLogin();
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add') {
                $subjectId = (int)($_POST['subject_id'] ?? 0);
                $examName = clean($_POST['exam_name'] ?? '');
                $marks = (float)($_POST['marks_obtained'] ?? 0);
                $total = (float)($_POST['total_marks'] ?? 100);
                $date = clean($_POST['exam_date'] ?? date('Y-m-d'));
                
                if ($subjectId && $examName && $total > 0) {
                    Grade::create($userId, [
                        'subject_id' => $subjectId,
                        'title' => $examName,
                        'score' => $marks,
                        'max_score' => $total,
                        'exam_date' => $date
                    ]);
                    $this->session->setFlash('success', 'Grade added successfully!');
                }
            }
            redirect('/grades');
        }

        $user = User::findById($userId);
        $grades = Grade::findByUser($userId);
        $subjects = Subject::findByUser($userId);
        
        $this->render('pages/grades', compact('user', 'grades', 'subjects'));
    }

    public function attendance(): void
    {
        $this->requireLogin();
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        
        $myAttendance = Attendance::findByUser($userId);
        $batchAttendance = Attendance::findByBatch($user['batch'] ?? '');
        $batchStats = Attendance::getBatchStats($user['batch'] ?? '');

        $this->render('pages/attendance', compact('user', 'myAttendance', 'batchAttendance', 'batchStats'));
    }

    public function calendar(): void
    {
        $this->requireLogin();
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $viewYear = 2026;
        $viewMonth = (int)($_GET['month'] ?? date('n'));
        $viewMonth = max(1, min(12, $viewMonth));
        $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $viewMonth, $viewYear);
        $firstDayOfWeek = (int)date('w', mktime(0, 0, 0, $viewMonth, 1, $viewYear));
        $prevMonth = $viewMonth - 1 < 1 ? 12 : $viewMonth - 1;
        $nextMonth = $viewMonth + 1 > 12 ? 1 : $viewMonth + 1;
        $today = date('Y-m-d');

        $holidays = [
            '2026-02-04' => ['Shab-e-Barat (Based on the moon sighting)', 'islamic'],
            '2026-02-21' => ['International Mother Language Day', 'national'],
            '2026-03-17' => ['Shab-e-Qadr (Based on the moon sighting)', 'islamic'],
            '2026-03-19' => ['Eid-ul-Fitr (Based on the moon sighting)', 'islamic'],
            '2026-03-20' => ['Eid-ul-Fitr', 'islamic'],
            '2026-03-21' => ['Eid-ul-Fitr', 'islamic'],
            '2026-03-22' => ['Eid-ul-Fitr', 'islamic'],
            '2026-03-23' => ['Eid-ul-Fitr', 'islamic'],
            '2026-03-26' => ['Independence & National Day', 'national'],
            '2024-04-14' => ["Bengali New Year's Day", 'national'],
            '2026-05-01' => ['May Day & Buddha Purnima (Based on the moon sighting)', 'national'],
            '2026-05-26' => ['Eid-ul-Adha (Based on the moon sighting)', 'islamic'],
            '2026-05-27' => ['Eid-ul-Adha', 'islamic'],
            '2026-05-28' => ['Eid-ul-Adha', 'islamic'],
            '2026-05-29' => ['Eid-ul-Adha', 'islamic'],
            '2026-05-30' => ['Eid-ul-Adha', 'islamic'],
            '2026-05-31' => ['Eid-ul-Adha (End)', 'islamic'],
            '2026-06-26' => ['Ashura (Based on the moon sighting)', 'islamic'],
            '2026-08-05' => ['July Mass Uprising Day', 'national'],
            '2026-08-26' => ['Eid-e-Milad-Un-Nabi (Based on the moon sighting)', 'islamic'],
            '2026-09-04' => ['Janmashtami', 'religious'],
            '2026-10-20' => ['Durga Puja (Nabami)', 'religious'],
            '2026-10-21' => ['Durga Puja (Vijaya Dashami)', 'religious'],
            '2026-12-16' => ['Victory Day', 'national'],
            '2026-12-25' => ['Christmas Day', 'religious'],
        ];

        $monthNotes = [
            2 => ['04 February : Shab-e-Barat (Based on the moon sighting)', '21 February : International Mother Language Day'],
            3 => ['17 March : Shab-e-Qadr (Based on the moon sighting)  20 March : Jumatul Bidah', '19 March - 23 March : Eid-ul-Fitr (Based on the moon sighting)', '26 March : Independence and National Day'],
            4 => ["14 April : Bengali New Year's Day"],
            5 => ['01 May : May Day & Buddha Purnima (Based on the moon sighting)', '26-31 May : Eid-ul-Adha (Based on the moon sighting)'],
            6 => ['26 June : Ashura (Based on the moon sighting)'],
            8 => ['05 August : July Mass Uprising Day', '26 August : Eid-e-Milad-Un-Nabi (Based on the moon sighting)'],
            9 => ['04 September : Janmashtami'],
            10 => ['20-21 October : Durga Puja (Nabami, Vijaya Dashami)'],
            12 => ['16 December : Victory Day', '25 December : Christmas Day'],
        ];

        $this->render('pages/calendar', compact(
            'user', 'viewYear', 'viewMonth', 'monthNames', 'daysInMonth', 'firstDayOfWeek', 
            'prevMonth', 'nextMonth', 'today', 'holidays', 'monthNotes'
        ));
    }
}
