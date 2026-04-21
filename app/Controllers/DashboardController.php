<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Subject, Task, Grade, Attendance, User;
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

        $db = getDB();

        // Get stats
        $tasksDueStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status!='done' AND due_date <= DATE_ADD(NOW(), INTERVAL 3 DAY)");
        $tasksDueStmt->execute([$userId]);
        $tasksDueCount = $tasksDueStmt->fetchColumn();

        $doneStmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND status='done'");
        $doneStmt->execute([$userId]);
        $doneCount = $doneStmt->fetchColumn();

        $weekStmt = $db->prepare("SELECT COALESCE(SUM(hours),0) FROM study_logs WHERE user_id=? AND WEEK(logged_date)=WEEK(NOW())");
        $weekStmt->execute([$userId]);
        $weekHours = $weekStmt->fetchColumn();

        // Upcoming tasks
        $upcomingStmt = $db->prepare("SELECT t.*, s.name AS subject_name, s.color FROM tasks t LEFT JOIN subjects s ON t.subject_id=s.id WHERE t.user_id=? AND t.status!='done' ORDER BY t.due_date ASC LIMIT 5");
        $upcomingStmt->execute([$userId]);
        $upcomingTasks = $upcomingStmt->fetchAll();

        // Recent questions
        $questionsStmt = $db->query("SELECT * FROM questions WHERE is_approved=1 ORDER BY created_at DESC LIMIT 4");
        $recentQuestions = $questionsStmt->fetchAll();

        $this->render('dashboard/index', compact('user', 'tasksDueCount', 'doneCount', 'weekHours', 'upcomingTasks', 'recentQuestions'));
    }

    public function analytics(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $db = getDB();

        // Study logs - weekly breakdown
        $logsStmt = $db->prepare(
            "SELECT DATE(logged_date) as date, SUM(hours) as total 
             FROM study_logs 
             WHERE user_id=? AND logged_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
             GROUP BY DATE(logged_date) 
             ORDER BY date DESC"
        );
        $logsStmt->execute([$userId]);
        $studyLogs = $logsStmt->fetchAll();

        // Grade trends
        $gradesStmt = $db->prepare(
            "SELECT subject_id, AVG(marks_obtained / total_marks * 100) as avg_score 
             FROM grades 
             WHERE user_id=? 
             GROUP BY subject_id"
        );
        $gradesStmt->execute([$userId]);
        $grades = $gradesStmt->fetchAll();

        $this->render('dashboard/analytics', compact('user', 'studyLogs', 'grades'));
    }
}

/**
 * PageController — Renders static and dynamic pages
 */
class PageController extends Controller
{
    public function page(string $name): void
    {
        $allowedPages = [
            'subjects', 'tasks', 'grades', 'attendance',
            'flashcards', 'groups', 'announcements', 'calendar',
            'fees', 'question-bank', 'learn', 'jobs', 'partners'
        ];

        if (!in_array($name, $allowedPages)) {
            http_response_code(404);
            die('Page not found');
        }

        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        // Pass data based on page
        $data = compact('user', 'userId');

        // Load specific data for page
        if ($name === 'subjects') {
            $data['subjects'] = Subject::findByUser($userId);
        } elseif ($name === 'tasks') {
            $data['tasks'] = Task::findByUser($userId);
        } elseif ($name === 'grades') {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM grades WHERE user_id=? ORDER BY exam_date DESC");
            $stmt->execute([$userId]);
            $data['grades'] = $stmt->fetchAll();
        } elseif ($name === 'attendance') {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id=? ORDER BY class_date DESC");
            $stmt->execute([$userId]);
            $data['attendance'] = $stmt->fetchAll();
        }

        $this->render('pages/' . $name, $data);
    }
}
