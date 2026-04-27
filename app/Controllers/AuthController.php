<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Subject;
use App\Models\User;
use function App\clean;
use function App\redirect;

/**
 * AuthController — Handles authentication (login, signup, logout)
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function login(): void
    {
        User::ensureRosterSynced();

        if ($this->session->isLoggedIn()) {
            redirect('/dashboard');
        }

        $error = '';
        $flash = $this->session->getFlash();

        $this->render('auth/login', compact('error', 'flash'));
    }

    /**
     * Handle login form submission
     */
    public function doLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
        }

        User::ensureRosterSynced();

        $identifier = clean($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            $this->session->setFlash('error', 'Please fill in all fields.');
            redirect('/login');
        }

        $result = User::authenticate($identifier, $password);
        if ($result['success']) {
            $loggedUser = $result['user'];
            if (($loggedUser['role'] ?? 'student') === 'student') {
                Subject::syncForUserBatchSemester((int) $loggedUser['id'], (string) ($loggedUser['batch'] ?? ''), (int) ($loggedUser['semester'] ?? 1));
                $loggedUser = User::findById((int) $loggedUser['id']) ?? $loggedUser;
            }

            $this->session->loginUser($loggedUser);
            redirect('/dashboard');
        } else {
            $this->session->setFlash('error', $result['message']);
            redirect('/login');
        }
    }

    /**
     * Show signup form
     */
    public function signup(): void
    {
        User::ensureRosterSynced();

        if ($this->session->isLoggedIn()) {
            redirect('/dashboard');
        }

        $error = '';
        $this->render('auth/signup', compact('error'));
    }

    /**
     * Handle signup form submission
     */
    public function doSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/signup');
        }

        $name = clean($_POST['name'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $studentId = clean($_POST['student_id'] ?? '');
        $facultySecret = $_POST['faculty_secret'] ?? '';
        $role = clean($_POST['role'] ?? 'student');
        if (!in_array($role, ['student', 'faculty'])) {
            $role = 'student'; // Default fallback, prevent 'admin' assignment
        }

        $errors = [];
        if (!$name) $errors[] = 'Name is required.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$password || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
        
        if ($role === 'faculty') {
            $requiredSecret = getenv('FACULTY_SECRET') ?: 'MU-FACULTY-2026';
            if ($facultySecret !== $requiredSecret) {
                $errors[] = 'Invalid faculty verification code.';
            }
        }

        if ($role === 'student' && !$studentId) $errors[] = 'Student ID is required for student accounts.';
        if ($role === 'student' && $studentId && !User::findByStudentId($studentId) && !\App\Support\StudentRoster::findPrimary($studentId)) {
            $errors[] = 'Student ID was not found in the official roster.';
        }

        if (!empty($errors)) {
            $error = implode(' ', $errors);
            $this->render('auth/signup', compact('error', 'name', 'email'));
            return;
        }

        $result = User::register($name, $email, $password, $role, $studentId);
        if ($result['success']) {
            if ($role === 'student') {
                Subject::syncForUserBatchSemester((int) $result['user']['id'], (string) ($result['user']['batch'] ?? ''), (int) ($result['user']['semester'] ?? 1));
                $result['user'] = User::findById((int) $result['user']['id']) ?? $result['user'];
            }

            // Auto-login after registration
            $this->session->loginUser($result['user']);
            $this->session->setFlash('success', 'Welcome to EduSync!');
            redirect('/dashboard');
        } else {
            $this->render('auth/signup', [
                'error' => $result['message'],
                'name' => $name,
                'email' => $email
            ]);
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $this->session->logout();
        redirect('/login');
    }

    /**
     * API: Lookup student by ID (used for auto-filling signup)
     */
    public function lookupStudent(): void
    {
        $studentId = clean($_GET['student_id'] ?? '');
        if (!$studentId) {
            $this->json(['success' => false, 'message' => 'Student ID is required'], 400);
        }

        $profile = \App\Support\StudentRoster::findPrimary($studentId);
        if ($profile) {
            $this->json(['success' => true, 'data' => $profile]);
        } else {
            $this->json(['success' => false, 'message' => 'Student not found in roster'], 404);
        }
    }
}
