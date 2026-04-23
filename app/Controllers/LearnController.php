<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Learn;
use App\Models\User;

class LearnController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $courses = Learn::getAllCourses();
        $selectedCourseId = (int)($_GET['course'] ?? 0);
        $courseData = $selectedCourseId ? Learn::getCourseById($selectedCourseId) : null;
        $youtubeResources = Learn::getYoutubeResources();

        $this->render('pages/learn', compact('user', 'courses', 'courseData', 'youtubeResources', 'selectedCourseId'));
    }
}
