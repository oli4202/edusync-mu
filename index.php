<?php
/**
 * index.php — Front Controller
 * Single entry point for all requests
 * Handles routing to controllers based on URL
 */

// Autoload classes via Composer
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
require_once __DIR__ . '/config/database.php';

// Load helpers
require_once __DIR__ . '/app/helpers.php';

// Use namespaces
use App\Core\Router;

// Initialize router
$router = new Router();

// ============================================================
// ROUTE DEFINITIONS
// ============================================================

// Authentication routes
$router->get('/login', 'App\Controllers\AuthController@login');
$router->post('/auth/login', 'App\Controllers\AuthController@doLogin');
$router->get('/signup', 'App\Controllers\AuthController@signup');
$router->post('/auth/signup', 'App\Controllers\AuthController@doSignup');
$router->get('/logout', 'App\Controllers\AuthController@logout');

// Dashboard routes
$router->get('/dashboard', 'App\Controllers\DashboardController@index');
$router->get('/analytics', 'App\Controllers\DashboardController@analytics');
$router->get('/subjects', 'App\Controllers\DashboardController@subjects');
$router->post('/subjects', 'App\Controllers\DashboardController@subjects');
$router->get('/tasks', 'App\Controllers\DashboardController@tasks');
$router->post('/tasks', 'App\Controllers\DashboardController@tasks');
$router->get('/grades', 'App\Controllers\DashboardController@grades');
$router->post('/grades', 'App\Controllers\DashboardController@grades');
$router->get('/attendance', 'App\Controllers\DashboardController@attendance');
$router->post('/attendance', 'App\Controllers\DashboardController@attendance');
$router->get('/calendar', 'App\Controllers\DashboardController@calendar');

// Flashcard routes
$router->get('/flashcards', 'App\Controllers\FlashcardController@index');
$router->post('/flashcards/add', 'App\Controllers\FlashcardController@add');
$router->post('/flashcards/delete', 'App\Controllers\FlashcardController@delete');
$router->post('/flashcards/generate', 'App\Controllers\FlashcardController@generate');

// Group routes
$router->get('/groups', 'App\Controllers\GroupController@index');
$router->post('/groups/create', 'App\Controllers\GroupController@create');
$router->post('/groups/join', 'App\Controllers\GroupController@join');
$router->post('/groups/leave', 'App\Controllers\GroupController@leave');

// Announcement routes
$router->get('/announcements', 'App\Controllers\AnnouncementController@index');
$router->post('/announcements/post', 'App\Controllers\AnnouncementController@post');
$router->post('/announcements/delete', 'App\Controllers\AnnouncementController@delete');
$router->post('/announcements/pin', 'App\Controllers\AnnouncementController@pin');

// Fee routes
$router->get('/fees', 'App\Controllers\FeeController@index');
$router->post('/fees/add', 'App\Controllers\FeeController@add');
$router->post('/fees/delete', 'App\Controllers\FeeController@delete');

// Question Bank routes
$router->get('/question-bank', 'App\Controllers\QuestionBankController@index');
$router->get('/question-bank/:id', 'App\Controllers\QuestionBankController@detail');
$router->post('/question-bank/:id', 'App\Controllers\QuestionBankController@detail');
$router->get('/question-bank/submit', 'App\Controllers\QuestionBankController@submit');
$router->post('/question-bank/submit', 'App\Controllers\QuestionBankController@submit');
$router->post('/api/question-bank/bookmark', 'App\Controllers\QuestionBankController@bookmark');
$router->post('/api/question-bank/upvote', 'App\Controllers\QuestionBankController@upvote');
$router->post('/api/question-bank/compact-answer', 'App\Controllers\QuestionBankController@compactAnswer');

// Learn routes
$router->get('/learn', 'App\Controllers\LearnController@index');

// Job & Partner routes
$router->get('/jobs', 'App\Controllers\JobController@index');
$router->post('/jobs/post', 'App\Controllers\JobController@post');
$router->post('/jobs/save', 'App\Controllers\JobController@save');
$router->get('/partners', 'App\Controllers\JobController@partners');
$router->post('/partners/follow', 'App\Controllers\JobController@follow');
$router->post('/partners/unfollow', 'App\Controllers\JobController@unfollow');

// AI routes
$router->get('/ai', 'App\Controllers\AiController@index');
$router->get('/playground', 'App\Controllers\AiController@playground');
$router->get('/suggestions', 'App\Controllers\AiController@suggestions');
$router->post('/api/ai/chat', 'App\Controllers\AiController@chat');
$router->post('/api/ai/suggest', 'App\Controllers\AiController@suggest');
$router->post('/api/ai/summarize', 'App\Controllers\AiController@summarize');
$router->post('/api/ai/quiz', 'App\Controllers\AiController@generateQuiz');
$router->post('/api/ai/ocr', 'App\Controllers\AiController@ocr');
$router->post('/api/playground/run-python', 'App\Controllers\AiController@runPython');
$router->post('/api/playground/run-sql', 'App\Controllers\AiController@runSql');

// Miscellaneous routes
$router->get('/prospectus', 'App\Controllers\MiscController@prospectus');
$router->get('/result-lookup', 'App\Controllers\MiscController@resultLookup');
$router->post('/api/result-lookup', 'App\Controllers\MiscController@lookupOfficialResult');

// Admin routes
$router->get('/admin', 'App\Controllers\AdminController@index');
$router->get('/admin/attendance', 'App\Controllers\AdminController@manageAttendance');
$router->post('/admin/attendance', 'App\Controllers\AdminController@manageAttendance');
$router->get('/admin/attendance/sheet', 'App\Controllers\AdminController@attendanceSheet');
$router->get('/admin/students', 'App\Controllers\AdminController@studentDirectory');
$router->get('/admin/api-settings', 'App\Controllers\AdminController@apiSettings');
$router->post('/admin/api-settings', 'App\Controllers\AdminController@apiSettings');
$router->post('/admin/questions/approve', 'App\Controllers\AdminController@approveQuestion');
$router->post('/admin/questions/reject', 'App\Controllers\AdminController@rejectQuestion');
$router->post('/admin/answers/approve', 'App\Controllers\AdminController@approveAnswer');
$router->post('/admin/answers/reject', 'App\Controllers\AdminController@rejectAnswer');

// Course API routes
$router->get('/api/courses/semesters', 'App\Controllers\CourseController@semesters');
$router->get('/api/courses/filter', 'App\Controllers\CourseController@filter');
$router->get('/api/students/lookup', 'App\Controllers\AuthController@lookupStudent');

// Page routes (dynamic)
$router->get('/:page', 'App\Controllers\PageController@page');
$router->get('/pages/:page', 'App\Controllers\PageController@page');
$router->get('/admin/:page', 'App\Controllers\PageController@page');

// Legacy AJAX redirects (for compatibility with old JS)
$router->get('/ajax/:page', 'App\Controllers\PageController@page');
$router->post('/ajax/:page', 'App\Controllers\PageController@page');

// Explicit legacy mappings for POST AJAX calls
$router->post('/ajax/ai-suggest.php', 'App\Controllers\AiController@suggest');
$router->post('/ajax/ai-compact.php', 'App\Controllers\QuestionBankController@compactAnswer');
$router->post('/ajax/bookmark.php', 'App\Controllers\QuestionBankController@bookmark');
$router->post('/ajax/upvote.php', 'App\Controllers\QuestionBankController@upvote');
$router->post('/ajax/mu-result.php', 'App\Controllers\MiscController@lookupOfficialResult');
$router->post('/ajax/run-python.php', 'App\Controllers\AiController@runPython');
$router->post('/ajax/run-sql.php', 'App\Controllers\AiController@runSql');

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];

// Get the request URI, prioritizing the 'url' parameter from .htaccess
$uri = $_GET['url'] ?? $_SERVER['REQUEST_URI'] ?? '/';

// If we got it from REQUEST_URI, we might need to remove the query string
if (!isset($_GET['url'])) {
    $uri = parse_url($uri, PHP_URL_PATH);
}

// Add leading slash if missing
if (empty($uri) || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

// Handle root redirect
if ($uri === '/' || $uri === '/index.php') {
    $session = new \App\Core\Session();
    if ($session->isLoggedIn()) {
        redirect('/dashboard');
    } else {
        redirect('/login');
    }
    exit;
}

// Dispatch to router
$dispatched = $router->dispatch($method, $uri);

if (!$dispatched) {
    // Route not found — show 404
    http_response_code(404);
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>404 Not Found</title>
        <style>
            body { font-family: sans-serif; background: #0a0e1a; color: #e2e8f0; padding: 50px; text-align: center; }
            h1 { color: #f87171; }
        </style>
    </head>
    <body>
        <h1>404 — Page Not Found</h1>
        <p>The requested page does not exist.</p>
        <a href="/login">← Back to Login</a>
    </body>
    </html>';
}
