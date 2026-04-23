<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * Canonical fallback for single-segment routes that still appear in links or bookmarks.
 * We redirect known slugs to their explicit MVC routes and return a 404 for everything else.
 */
class PageController extends Controller
{
    private const CANONICAL_ROUTES = [
        'login' => '/login',
        'signup' => '/signup',
        'logout' => '/logout',
        'dashboard' => '/dashboard',
        'analytics' => '/analytics',
        'subjects' => '/subjects',
        'tasks' => '/tasks',
        'grades' => '/grades',
        'grade' => '/grades',
        'attendance' => '/attendance',
        'calendar' => '/calendar',
        'announcements' => '/announcements',
        'fees' => '/fees',
        'flashcards' => '/flashcards',
        'groups' => '/groups',
        'jobs' => '/jobs',
        'partners' => '/partners',
        'learn' => '/learn',
        'ai' => '/ai',
        'playground' => '/playground',
        'suggestions' => '/suggestions',
        'question-bank' => '/question-bank',
        'submit-question' => '/question-bank/submit',
        'question-detail' => '/question-bank',
        'prospectus' => '/prospectus',
        'result-lookup' => '/result-lookup',
        'result' => '/result-lookup',
        'admin' => '/admin',
    ];

    public function page(string $page): void
    {
        $slug = strtolower(trim($page));

        if (substr($slug, -4) === '.php') {
            $slug = substr($slug, 0, -4);
        }

        if (isset(self::CANONICAL_ROUTES[$slug])) {
            $this->redirect(self::CANONICAL_ROUTES[$slug]);
        }

        http_response_code(404);
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 Not Found</title>
            <style>
                body { font-family: sans-serif; background: #0a0e1a; color: #e2e8f0; padding: 50px; text-align: center; }
                h1 { color: #f87171; }
                a { color: #22d3ee; text-decoration: none; }
            </style>
        </head>
        <body>
            <h1>404 - Page Not Found</h1>
            <p>The requested page does not exist in the MVC router.</p>
            <a href="/dashboard">Back to Dashboard</a>
        </body>
        </html>';
    }
}
