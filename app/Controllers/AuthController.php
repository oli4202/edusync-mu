<?php

namespace App\Controllers;

use App\Core\Controller;
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
        if ($this->session->isLoggedIn()) {
            redirect('/dashboard');
        }

        $error = '';
        $flash = $this->session->getFlash();

        $this->view('auth/login', compact('error', 'flash'));
    }

    /**
     * Handle login form submission
     */
    public function doLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
        }

        $email = clean($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->session->setFlash('error', 'Please fill in all fields.');
            redirect('/login');
        }

        $result = User::authenticate($email, $password);
        if ($result['success']) {
            $this->session->loginUser($result['user']);
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
        if ($this->session->isLoggedIn()) {
            redirect('/dashboard');
        }

        $error = '';
        $this->view('auth/signup', compact('error'));
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
        $batch = clean($_POST['batch'] ?? '');

        $errors = [];
        if (!$name) $errors[] = 'Name is required.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$password || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            $error = implode(' ', $errors);
            $this->view('auth/signup', compact('error', 'name', 'email'));
            return;
        }

        $result = User::register($name, $email, $password, $studentId, $batch);
        if ($result['success']) {
            // Auto-login after registration
            $this->session->loginUser($result['user']);
            $this->session->setFlash('success', 'Welcome to EduSync!');
            redirect('/dashboard');
        } else {
            $this->view('auth/signup', [
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
}
