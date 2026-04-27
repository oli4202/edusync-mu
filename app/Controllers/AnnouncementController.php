<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Announcement;
use App\Models\User;

class AnnouncementController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $isFaculty = $this->session->isFaculty();

        $announcements = Announcement::findAllForUser($user['semester'] ?? 0, $isFaculty);
        $flash = $this->session->getFlash();

        $this->render('pages/announcements', compact('user', 'announcements', 'flash', 'isFaculty'));
    }

    public function post(): void
    {
        $this->requireFaculty();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Announcement::create($this->session->userId(), $_POST)) {
                $this->session->setFlash('success', 'Announcement posted!');
            } else {
                $this->session->setFlash('error', 'Failed to post announcement.');
            }
        }
        redirect('/announcements');
    }

    public function delete(): void
    {
        $this->requireFaculty();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Announcement::delete((int)$_POST['ann_id']);
            $this->session->setFlash('success', 'Announcement deleted.');
        }
        redirect('/announcements');
    }

    public function pin(): void
    {
        $this->requireFaculty();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Announcement::togglePin((int)$_POST['ann_id']);
            $this->session->setFlash('success', 'Announcement updated.');
        }
        redirect('/announcements');
    }
}
