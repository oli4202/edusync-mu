<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Models\Subject;
use App\Models\User;

class GroupController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $myGroupList = Group::findMyGroups($userId);
        $discoverList = Group::findDiscoverGroups($userId);
        $subjects = Subject::findByUser($userId);
        $classmateList = User::getClassmates($userId, $user['semester'] ?? 1);

        $this->render('pages/groups', compact(
            'user', 'userId', 'myGroupList', 'discoverList', 'subjects', 'classmateList'
        ));
    }

    public function create(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Group::create($this->session->userId(), $_POST);
        }
        redirect('/groups');
    }

    public function join(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Group::join((int)$_POST['group_id'], $this->session->userId());
        }
        redirect('/groups');
    }

    public function leave(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Group::leave((int)$_POST['group_id'], $this->session->userId());
        }
        redirect('/groups');
    }
}
