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
$router->get('/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@doLogin');
$router->get('/signup', 'AuthController@signup');
$router->post('/auth/signup', 'AuthController@doSignup');
$router->get('/logout', 'AuthController@logout');

// Dashboard routes
$router->get('/dashboard', 'DashboardController@index');
$router->get('/analytics', 'DashboardController@analytics');
$router->get('/subjects', 'DashboardController@subjects');
$router->get('/tasks', 'DashboardController@tasks');
$router->get('/grades', 'DashboardController@grades');
$router->get('/attendance', 'DashboardController@attendance');
$router->get('/calendar', 'DashboardController@calendar');

// Flashcard routes
$router->get('/flashcards', 'FlashcardController@index');
$router->post('/flashcards/add', 'FlashcardController@add');
$router->post('/flashcards/delete', 'FlashcardController@delete');
$router->post('/flashcards/generate', 'FlashcardController@generate');

// Group routes
$router->get('/groups', 'GroupController@index');
$router->post('/groups/create', 'GroupController@create');
$router->post('/groups/join', 'GroupController@join');
$router->post('/groups/leave', 'GroupController@leave');

// Announcement routes
$router->get('/announcements', 'AnnouncementController@index');
$router->post('/announcements/post', 'AnnouncementController@post');
$router->post('/announcements/delete', 'AnnouncementController@delete');
$router->post('/announcements/pin', 'AnnouncementController@pin');

// Fee routes
$router->get('/fees', 'FeeController@index');
$router->post('/fees/add', 'FeeController@add');
$router->post('/fees/delete', 'FeeController@delete');

// Question Bank routes
$router->get('/question-bank', 'QuestionBankController@index');
$router->get('/question-bank/:id', 'QuestionBankController@detail');
$router->post('/question-bank/:id', 'QuestionBankController@detail');
$router->get('/question-bank/submit', 'QuestionBankController@submit');
$router->post('/question-bank/submit', 'QuestionBankController@submit');
$router->post('/api/question-bank/bookmark', 'QuestionBankController@bookmark');
$router->post('/api/question-bank/upvote', 'QuestionBankController@upvote');
$router->post('/api/question-bank/compact-answer', 'QuestionBankController@compactAnswer');

// Learn routes
$router->get('/learn', 'LearnController@index');

// Job & Partner routes
$router->get('/jobs', 'JobController@index');
$router->post('/jobs/post', 'JobController@post');
$router->post('/jobs/save', 'JobController@save');
$router->get('/partners', 'JobController@partners');
$router->post('/partners/follow', 'JobController@follow');
$router->post('/partners/unfollow', 'JobController@unfollow');

// AI routes
$router->get('/ai', 'AiController@index');
$router->get('/playground', 'AiController@playground');
$router->get('/suggestions', 'AiController@suggestions');
$router->post('/api/ai/chat', 'AiController@chat');
$router->post('/api/ai/suggest', 'AiController@suggest');
$router->post('/api/playground/run-python', 'AiController@runPython');
$router->post('/api/playground/run-sql', 'AiController@runSql');

// Miscellaneous routes
$router->get('/prospectus', 'MiscController@prospectus');
$router->get('/result-lookup', 'MiscController@resultLookup');
$router->post('/api/result-lookup', 'MiscController@lookupOfficialResult');

// Admin routes
$router->get('/admin', 'AdminController@index');
$router->get('/admin/attendance', 'AdminController@manageAttendance');
$router->post('/admin/attendance', 'AdminController@manageAttendance');
$router->get('/admin/api-settings', 'AdminController@apiSettings');
$router->post('/admin/api-settings', 'AdminController@apiSettings');
$router->post('/admin/questions/approve', 'AdminController@approveQuestion');
$router->post('/admin/questions/reject', 'AdminController@rejectQuestion');
$router->post('/admin/answers/approve', 'AdminController@approveAnswer');
$router->post('/admin/answers/reject', 'AdminController@rejectAnswer');

// Page routes (dynamic)

$router->get('/:page', 'PageController@page');

// API/AJAX routes (if needed)
// $router->post('/api/task/create', 'AjaxController@createTask');
// $router->post('/api/task/update/:id', 'AjaxController@updateTask');

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];

// Get the request URI, prioritizing the 'url' parameter from .htaccess
// This helps when the app is installed in a subdirectory
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
    if ((new \App\Core\Session())->isLoggedIn()) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
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
