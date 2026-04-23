<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Job;
use App\Models\User;
use function App\redirect;

class JobController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $filters = [
            'type' => $_GET['type'] ?? '',
            'q' => $_GET['q'] ?? ''
        ];

        $jobList = Job::findAll($userId, $filters);
        $flash = $this->session->getFlash();

        $this->render('pages/jobs', compact('user', 'jobList', 'filters', 'flash'));
    }

    public function post(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = User::findById($this->session->userId());
            if (Job::create($this->session->userId(), $_POST, $user['role'] === 'admin')) {
                $this->session->setFlash('success', $user['role'] === 'admin' ? 'Job posted!' : 'Job submitted for review!');
            } else {
                $this->session->setFlash('error', 'Failed to post job.');
            }
        }
        redirect('/jobs');
    }

    public function save(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Job::toggleSave((int)$_POST['job_id'], $this->session->userId());
            $this->json(['ok' => 1]);
        }
    }

    public function approve(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Job::approve((int)$_POST['job_id'], $this->session->userId());
            $this->session->setFlash('success', 'Job approved!');
        }
        redirect('/jobs');
    }

    public function delete(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Job::delete((int)$_POST['job_id']);
            $this->session->setFlash('success', 'Job deleted.');
        }
        redirect('/jobs');
    }

    public function partners(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $search = $_GET['q'] ?? '';
        $students = User::findPartners($userId, $search);
        $counts = User::getConnectionCounts($userId);

        $this->render('pages/partners', compact('user', 'students', 'search', 'counts'));
    }

    public function follow(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            User::follow($this->session->userId(), (int)$_POST['user_id']);
        }
        redirect('/partners');
    }

    public function unfollow(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            User::unfollow($this->session->userId(), (int)$_POST['user_id']);
        }
        redirect('/partners');
    }
}
