<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\{Subject, Task, Grade, Attendance, User, StudyLog, Question};
use function App\getDB;

/**
 * DashboardController — Main dashboard and analytics
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

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
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $subjects = Subject::findByUser($userId);
        $this->render('pages/subjects', compact('user', 'subjects'));
    }

    public function tasks(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $tasks = Task::findByUser($userId);
        $this->render('pages/tasks', compact('user', 'tasks'));
    }

    public function grades(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $grades = Grade::findByUser($userId);
        $this->render('pages/grades', compact('user', 'grades'));
    }

    public function attendance(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $attendance = Attendance::findByUser($userId);
        $this->render('pages/attendance', compact('user', 'attendance'));
    }

    public function calendar(): void
    {
        $this->requireLogin();
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
