<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;
use App\Models\Course;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Grade;
use App\Models\Subject;
use function getDB;

class AdminController extends Controller
{
    private function saveRoutineData(array $routineData): bool
    {
        $path = __DIR__ . '/../Data/routine_data.php';
        $content = "<?php\n\nreturn " . var_export($routineData, true) . ";\n";
        return file_put_contents($path, $content) !== false;
    }

    public function __construct()
    {
        parent::__construct();
        $this->requireFaculty();
    }

    public function index(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $pendingQuestions = Question::findPending();
        $pendingAnswers = Answer::findPending();

        $stats = [
            'users' => User::getStudentCount(),
            'questions' => Question::getCount(true),
            'answers' => Answer::getCount(true),
            'groups' => Group::getCount(),
        ];

        $this->render('pages/admin/index', compact(
            'user', 'pendingQuestions', 'pendingAnswers', 'stats'
        ));
    }

    public function approveQuestion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Question::approve($id, $this->session->userId());
        }
        redirect('/admin');
    }

    public function rejectQuestion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Question::delete($id);
        }
        redirect('/admin');
    }

    public function approveAnswer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Answer::approve($id, $this->session->userId());
        }
        redirect('/admin');
    }

    public function manageSystem(): void
    {
        $this->requireAdmin();
        User::ensureRosterSynced();

        $userId = $this->session->userId();
        $user = User::findById($userId);
        $db = getDB();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = clean($_POST['action'] ?? '');

            if ($action === 'add_faculty') {
                $name = clean($_POST['name'] ?? '');
                $email = clean($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';

                if ($name === '' || $email === '' || strlen($password) < 6) {
                    $this->session->setFlash('error', 'Faculty name, valid email, and min 6-char password are required.');
                } else {
                    $result = User::register($name, $email, $password, 'faculty');
                    $this->session->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Faculty account created/updated.' : ($result['message'] ?? 'Failed to add faculty.'));
                }
            }

            if ($action === 'add_course') {
                $code = clean($_POST['code'] ?? '');
                $name = clean($_POST['name'] ?? '');
                $year = (int)($_POST['year'] ?? 1);
                $semester = (int)($_POST['semester'] ?? 1);
                $batch = clean($_POST['batch'] ?? '');
                $status = clean($_POST['status'] ?? 'active');

                if ($code === '' || $name === '' || $year <= 0 || $semester <= 0) {
                    $this->session->setFlash('error', 'Course code, name, year, and semester are required.');
                } else {
                    try {
                        $stmt = $db->prepare("
                            INSERT INTO courses (name, code, year, semester, batch, status)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$name, $code, $year, $semester, $batch !== '' ? $batch : null, in_array($status, ['active', 'reserved']) ? $status : 'active']);
                        $this->session->setFlash('success', 'Course added successfully.');
                    } catch (\Exception $e) {
                        $this->session->setFlash('error', 'Failed to add course: ' . $e->getMessage());
                    }
                }
            }

            if ($action === 'assign_faculty') {
                $teacherId = (int)($_POST['teacher_id'] ?? 0);
                $courseId = (int)($_POST['course_id'] ?? 0);
                $batch = preg_replace('/[^0-9]/', '', clean($_POST['batch'] ?? ''));
                $semester = (int)($_POST['semester'] ?? 0);

                if ($teacherId <= 0 || $courseId <= 0 || $batch === '' || $semester <= 0) {
                    $this->session->setFlash('error', 'Select faculty, course, batch, and semester for assignment.');
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO teacher_course_assignments (teacher_id, course_id, batch, semester)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE semester = VALUES(semester)
                    ");
                    $stmt->execute([$teacherId, $courseId, $batch, $semester]);
                    $this->session->setFlash('success', 'Faculty assigned to course and batch.');
                }
            }

            if ($action === 'add_routine_slot') {
                $day = clean($_POST['day'] ?? '');
                $batchLabel = clean($_POST['batch_label'] ?? '');
                $slotIndex = (int)($_POST['slot_index'] ?? -1);
                $courseText = clean($_POST['course_text'] ?? '');
                $room = clean($_POST['room'] ?? '');
                $facultyShort = strtoupper(clean($_POST['faculty_short'] ?? ''));

                $routineData = require __DIR__ . '/../Data/routine_data.php';
                $validDays = $routineData['days'] ?? [];
                $validBatches = $routineData['batches'] ?? [];

                if (!in_array($day, $validDays, true) || !in_array($batchLabel, $validBatches, true) || $slotIndex < 0 || $courseText === '') {
                    $this->session->setFlash('error', 'Routine slot requires valid day, batch, time slot, and course text.');
                } else {
                    if (!isset($routineData['schedule'][$day])) {
                        $routineData['schedule'][$day] = [];
                    }
                    if (!isset($routineData['schedule'][$day][$batchLabel])) {
                        $routineData['schedule'][$day][$batchLabel] = [];
                    }

                    $routineData['schedule'][$day][$batchLabel][] = [$slotIndex, $courseText, $room, $facultyShort];
                    usort($routineData['schedule'][$day][$batchLabel], static fn (array $a, array $b): int => ((int)$a[0]) <=> ((int)$b[0]));

                    if ($this->saveRoutineData($routineData)) {
                        $this->session->setFlash('success', 'Routine slot added successfully.');
                    } else {
                        $this->session->setFlash('error', 'Failed to save routine file.');
                    }
                }
            }

            redirect('/admin/manage');
        }

        $faculties = $db->query("SELECT id, name, email FROM users WHERE role = 'faculty' ORDER BY name ASC")->fetchAll();
        $courses = $db->query("SELECT id, code, name, semester, batch, status FROM courses ORDER BY year, semester, code")->fetchAll();
        $assignments = $db->query("
            SELECT tca.id, u.name AS faculty_name, c.code, c.name AS course_name, tca.batch, tca.semester
            FROM teacher_course_assignments tca
            JOIN users u ON u.id = tca.teacher_id
            JOIN courses c ON c.id = tca.course_id
            ORDER BY tca.batch, tca.semester, c.code, u.name
            LIMIT 300
        ")->fetchAll();
        $routineData = require __DIR__ . '/../Data/routine_data.php';

        $currentPage = 'admin-manage';
        $pageTitle = 'Admin Management - EduSync MU';
        $this->render('pages/admin/manage', compact(
            'user',
            'faculties',
            'courses',
            'assignments',
            'routineData',
            'currentPage',
            'pageTitle'
        ));
    }

    public function rejectAnswer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            Answer::delete($id);
        }
        redirect('/admin');
    }

    public function manageAttendance(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $msg = $this->session->getFlash('success');
        $err = $this->session->getFlash('error');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'bulk_mark') {
                $courseId = (int)$_POST['course_id'];
                $classDate = clean($_POST['class_date']);
                $batch = clean($_POST['batch'] ?? '');
                $statuses = $_POST['status'] ?? [];
                $notes = $_POST['note'] ?? [];

                // Validate teacher permission for this course (skip for admins)
                if ($this->session->userRole() !== 'admin' && !Course::isTeacherAssigned($userId, $courseId, $batch)) {
                    $this->session->setFlash('error', 'You are not authorized to mark attendance for this course.');
                    redirect('/admin/attendance');
                }

                if (!$courseId || !$classDate) {
                    $this->session->setFlash('error', 'Select a course and date.');
                } else {
                    $count = 0;
                    foreach ($statuses as $studentId => $status) {
                        $note = clean($notes[$studentId] ?? '');
                        Attendance::record((int)$studentId, $courseId, $classDate, $status, $note);
                        $count++;
                    }
                    $this->session->setFlash('success', "Attendance marked for $count students!");
                }
                $redirectUrl = "/admin/attendance?course_id=$courseId&class_date=$classDate";
                $redirectUrl .= '&batch=' . urlencode($batch);
                $redirectUrl .= '&semester=' . urlencode((string) ($_POST['semester'] ?? ''));
                redirect($redirectUrl);
            } elseif ($action === 'random_results') {
                $batch = clean($_POST['batch'] ?? '');
                $semester = (int)($_POST['semester'] ?? 0);

                if ($batch === '' || $semester <= 0) {
                    $this->session->setFlash('error', 'Select batch and semester before generating random results.');
                } else {
                    $students = User::getStudentsForAttendance($batch, $semester, 0);
                    $generated = 0;
                    $db = getDB();
                    $existsStmt = $db->prepare("SELECT COUNT(*) FROM grades WHERE user_id = ? AND subject_id = ? AND title = ?");
                    $components = [
                        ['title' => 'Attendance', 'max' => 10, 'min' => 6],
                        ['title' => 'Class Test 1', 'max' => 15, 'min' => 7],
                        ['title' => 'Class Test 2', 'max' => 15, 'min' => 7],
                        ['title' => 'Assignment/Presentation', 'max' => 10, 'min' => 5],
                        ['title' => 'Viva', 'max' => 10, 'min' => 5],
                        ['title' => 'Final', 'max' => 40, 'min' => 16],
                    ];

                    foreach ($students as $student) {
                        $studentId = (int)($student['id'] ?? 0);
                        if ($studentId <= 0) {
                            continue;
                        }

                        Subject::syncForUserBatchSemester($studentId, $batch, $semester);
                        $subjects = Subject::findByUser($studentId);
                        foreach ($subjects as $subject) {
                            if ((int)($subject['semester'] ?? 0) !== $semester) {
                                continue;
                            }
                            $subjectId = (int)$subject['id'];
                            foreach ($components as $component) {
                                $title = 'Semester ' . $semester . ' ' . $component['title'];
                                $existsStmt->execute([$studentId, $subjectId, $title]);
                                if ((int)$existsStmt->fetchColumn() > 0) {
                                    continue;
                                }

                                Grade::create($studentId, [
                                    'subject_id' => $subjectId,
                                    'title' => $title,
                                    'score' => (float)random_int((int)$component['min'], (int)$component['max']),
                                    'max_score' => (float)$component['max'],
                                    'exam_date' => date('Y-m-d'),
                                ]);
                                $generated++;
                            }
                        }
                    }

                    $this->session->setFlash('success', "Random results generated: $generated records.");
                }

                $redirectUrl = '/admin/attendance';
                $redirectUrl .= '?batch=' . urlencode($batch);
                $redirectUrl .= '&semester=' . urlencode((string)$semester);
                redirect($redirectUrl);
            }
        }

        // Get courses: if teacher, only get assigned courses; if admin, get all
        $userRole = $this->session->userRole();
        if ($userRole === 'faculty') {
            $courses = Course::getTeacherCourses($userId);
        } else {
            $courses = Course::getAll();
        }
        
        $selCourse = (int)($_GET['course_id'] ?? 0);
        $selBatch = clean($_GET['batch'] ?? '');
        $selSemester = (int)($_GET['semester'] ?? 0);
        $selDate = clean($_GET['class_date'] ?? date('Y-m-d'));

        $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourse);

        $availableBatches = User::getBatchOptions();

        $existingAtt = [];
        if ($selCourse && $selDate) {
            $existingAtt = Attendance::getExistingForCourseAndDate($selCourse, $selDate);
        }

        $recentHistory = [];
        if ($selCourse) {
            $recentHistory = Attendance::getRecentHistoryForCourse($selCourse);
        }

        $stats = [
            'totalRecords' => Attendance::getTotalCount(),
            'todayCount' => Attendance::getTodayCount(),
        ];

        $this->render('pages/admin/attendance', compact(
            'courses', 'students', 'selCourse', 'selDate', 'selBatch', 'selSemester',
            'existingAtt', 'recentHistory', 'stats', 'availableBatches'
        ));
    }

    public function attendanceSheet(): void
    {
        User::ensureRosterSynced();
        $selCourseId = (int)($_GET['course_id'] ?? 0);
        $selBatch = clean($_GET['batch'] ?? '');
        $selSemester = (int)($_GET['semester'] ?? 0);

        $course = $selCourseId ? Course::findById($selCourseId) : null;
        $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourseId);

        $this->render('pages/admin/attendance-sheet', compact(
            'course', 'students', 'selBatch', 'selSemester'
        ));
    }

    public function studentAttendanceHistory(): void
    {
        User::ensureRosterSynced();
        $userId = (int)$this->session->userId();
        $userRole = (string)$this->session->userRole();
        $user = User::findById($userId);

        $courseId = (int)($_GET['course_id'] ?? 0);
        $studentId = (int)($_GET['student_id'] ?? 0);
        $batch = clean($_GET['batch'] ?? '');
        $semester = (int)($_GET['semester'] ?? 0);

        if ($courseId <= 0 || $studentId <= 0) {
            $this->session->setFlash('error', 'Course and student are required.');
            redirect('/admin/attendance');
        }

        if ($userRole === 'faculty' && !Course::isTeacherAssigned($userId, $courseId, $batch)) {
            $this->session->setFlash('error', 'You are not authorized to view this student history.');
            redirect('/admin/attendance');
        }

        $course = Course::findById($courseId);
        $student = User::findById($studentId);
        if (!$course || !$student || ($student['role'] ?? '') !== 'student') {
            $this->session->setFlash('error', 'Invalid course or student selected.');
            redirect('/admin/attendance');
        }

        $history = Attendance::getStudentHistoryForCourse($studentId, $courseId);
        $totalClasses = count($history);
        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;
        $excusedCount = 0;
        foreach ($history as $row) {
            $status = (string)($row['status'] ?? '');
            if ($status === 'present') $presentCount++;
            if ($status === 'late') $lateCount++;
            if ($status === 'absent') $absentCount++;
            if ($status === 'excused') $excusedCount++;
        }
        $attendanceRate = $totalClasses > 0 ? round((($presentCount + $lateCount) / $totalClasses) * 100, 1) : 0.0;

        $currentPage = 'attendance';
        $pageTitle = 'Student Attendance History - EduSync MU';
        $this->render('pages/admin/student-attendance-history', compact(
            'user',
            'course',
            'student',
            'history',
            'batch',
            'semester',
            'totalClasses',
            'presentCount',
            'lateCount',
            'absentCount',
            'excusedCount',
            'attendanceRate',
            'currentPage',
            'pageTitle'
        ));
    }

    public function studentDirectory(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $studentId = clean($_GET['student_id'] ?? '');
        $overview = $studentId !== '' ? User::getStudentOverviewByStudentId($studentId) : null;
        $notFound = $studentId !== '' && $overview === null;
        
        $allStudents = [];
        if ($studentId === '') {
            $allStudents = User::getAllStudentsWithStats();
        }

        $this->render('pages/admin/student-directory', compact('user', 'studentId', 'overview', 'notFound', 'allStudents'));
    }

    public function apiSettings(): void
    {
        $this->requireAdmin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groq = trim($_POST['groq'] ?? '');
            $gemini = trim($_POST['gemini'] ?? '');
            $preferred = trim($_POST['preferred_vision'] ?? 'groq');

            $content = "<?php\n\n\$api_keys = [\n";
            $content .= "    'GROQ_API_KEY' => '" . addslashes($groq) . "',\n";
            $content .= "    'GEMINI_API_KEY' => '" . addslashes($gemini) . "',\n";
            $content .= "    'PREFERRED_VISION_MODEL' => '" . addslashes($preferred) . "',\n";
            $content .= "];";
            
            file_put_contents(__DIR__ . '/../../config/api-keys.php', $content);
            $message = 'API settings updated successfully!';
        }

        include __DIR__ . '/../../config/api-keys.php';

        $this->render('pages/admin/api-settings', compact('user', 'message', 'api_keys'));
    }

    public function batchSemesterResults(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $selBatch = clean($_GET['batch'] ?? '');
        $selCourse = (int)($_GET['course_id'] ?? 0);
        $selSemester = (int)($_GET['semester'] ?? 1);

        $availableBatches = User::getBatchOptions();
        
        $courses = [];
        if ($selBatch) {
            $courses = Course::findByBatch($selBatch);
        }

        $course = $selCourse ? Course::findById($selCourse) : null;
        $students = [];
        $grades = [];

        if ($selBatch && $selCourse) {
            $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourse);
            
            foreach ($students as $s) {
                $db = getDB();
                $subjStmt = $db->prepare("SELECT id FROM subjects WHERE user_id = ? AND code = ?");
                $subjStmt->execute([$s['id'], $course['code']]);
                $subjectId = (int)$subjStmt->fetchColumn();
                
                if ($subjectId) {
                    $grades[$s['id']] = Grade::findBySubject($subjectId);
                }
            }
        }

        $this->render('pages/admin/result-sheet', compact(
            'user', 'availableBatches', 'courses', 'students', 'grades', 
            'selBatch', 'selCourse', 'selSemester', 'course'
        ));
    }

    public function detailedAssessment(): void
    {
        User::ensureRosterSynced();
        $selCourseId = (int)($_GET['course_id'] ?? 0);
        $selBatch = clean($_GET['batch'] ?? '');
        $selSemester = (int)($_GET['semester'] ?? 0);

        if (!$selCourseId || !$selBatch) {
            $this->session->setFlash('error', 'Select a course and batch to view the full assessment grid.');
            redirect('/admin/attendance');
        }

        $course = Course::findById($selCourseId);
        $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourseId);
        
        $dates = Attendance::getUniqueDatesForCourse($selCourseId, $selBatch);
        $attendanceGrid = Attendance::getGridReport($selCourseId, $selBatch);
        
        // Fetch grades for each student for this specific course
        $grades = [];
        foreach ($students as $s) {
            // Find the subject entry for this student and course code
            $db = getDB();
            $subjStmt = $db->prepare("SELECT id FROM subjects WHERE user_id = ? AND code = ?");
            $subjStmt->execute([$s['id'], $course['code']]);
            $subjectId = (int)$subjStmt->fetchColumn();
            
            if ($subjectId) {
                $grades[$s['id']] = Grade::findBySubject($subjectId);
            } else {
                $grades[$s['id']] = [];
            }
        }

        $this->render('pages/admin/continuous-assessment', compact(
            'course', 'students', 'selBatch', 'selSemester', 'dates', 'attendanceGrid', 'grades'
        ));
    }

    public function facultyResults(): void
    {
        User::ensureRosterSynced();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $isFaculty = $this->session->userRole() === 'faculty';

        $courses = $isFaculty ? Course::getTeacherCourses($userId) : Course::getAll();

        $selCourse = (int)($_REQUEST['course_id'] ?? 0);
        $selBatch = clean($_REQUEST['batch'] ?? '');
        $selSemester = (int)($_REQUEST['semester'] ?? 0);
        $selDate = clean($_REQUEST['exam_date'] ?? date('Y-m-d'));

        $selectedCourse = $selCourse ? Course::findById($selCourse) : null;
        if ($selectedCourse && $selBatch === '') {
            $selBatch = (string)($selectedCourse['batch'] ?? '');
        }
        if ($selectedCourse && $selSemester <= 0) {
            $selSemester = (int)($selectedCourse['semester'] ?? 0);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_results') {
            if (!$selCourse || !$selectedCourse) {
                $this->session->setFlash('error', 'Select a valid course before saving results.');
                redirect('/faculty/results');
            }

            if ($isFaculty && !Course::isTeacherAssigned($userId, $selCourse, $selBatch)) {
                $this->session->setFlash('error', 'You are not assigned to evaluate this course.');
                redirect('/faculty/results');
            }

            $componentNames = $_POST['component_name'] ?? [];
            $componentMax = $_POST['component_max'] ?? [];
            $scores = $_POST['scores'] ?? [];
            $components = [];

            foreach ($componentNames as $idx => $nameRaw) {
                $name = trim((string)$nameRaw);
                $max = (float)($componentMax[$idx] ?? 0);
                if ($name === '' || $max <= 0) {
                    continue;
                }
                $components[] = [
                    'index' => (int)$idx,
                    'name' => $name,
                    'max' => $max,
                ];
            }

            if (empty($components)) {
                $this->session->setFlash('error', 'Add at least one evaluation component with marks.');
                redirect('/faculty/results?course_id=' . $selCourse . '&batch=' . urlencode($selBatch) . '&semester=' . $selSemester);
            }

            $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourse);
            $db = getDB();
            $savedRows = 0;

            foreach ($students as $student) {
                $studentId = (int)($student['id'] ?? 0);
                if ($studentId <= 0) {
                    continue;
                }

                $subjectStmt = $db->prepare("SELECT id FROM subjects WHERE user_id = ? AND code = ? LIMIT 1");
                $subjectStmt->execute([$studentId, $selectedCourse['code']]);
                $subjectId = (int)$subjectStmt->fetchColumn();

                if ($subjectId <= 0) {
                    $insertSubject = $db->prepare("
                        INSERT INTO subjects (user_id, name, code, color, year, semester, target_hours_per_week)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insertSubject->execute([
                        $studentId,
                        (string)$selectedCourse['name'],
                        (string)$selectedCourse['code'],
                        '#22d3ee',
                        (int)($selectedCourse['year'] ?? date('Y')),
                        (int)($selSemester ?: ($selectedCourse['semester'] ?? 1)),
                        5.0,
                    ]);
                    $subjectId = (int)$db->lastInsertId();
                }

                $deleteStmt = $db->prepare("
                    DELETE FROM grades
                    WHERE user_id = ? AND subject_id = ? AND title LIKE 'Faculty Eval:%'
                ");
                $deleteStmt->execute([$studentId, $subjectId]);

                foreach ($components as $component) {
                    $rawScore = $scores[$studentId][$component['index']] ?? null;
                    if ($rawScore === null || $rawScore === '') {
                        continue;
                    }

                    $score = (float)$rawScore;
                    $score = max(0, min($score, $component['max']));
                    $title = 'Faculty Eval: ' . $component['name'];

                    $insertGrade = $db->prepare("
                        INSERT INTO grades (user_id, subject_id, title, score, max_score, exam_date)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $insertGrade->execute([$studentId, $subjectId, $title, $score, $component['max'], $selDate ?: date('Y-m-d')]);
                    $savedRows++;
                }
            }

            $this->session->setFlash('success', "Results saved successfully ({$savedRows} evaluation entries).");
            redirect('/faculty/results?course_id=' . $selCourse . '&batch=' . urlencode($selBatch) . '&semester=' . $selSemester);
        }

        $students = [];
        $components = [];
        $scoreMap = [];

        if ($selCourse && $selectedCourse) {
            $students = User::getStudentsForAttendance($selBatch, $selSemester, $selCourse);
            $db = getDB();

            foreach ($students as $student) {
                $studentId = (int)($student['id'] ?? 0);
                if ($studentId <= 0) {
                    continue;
                }

                $subjectStmt = $db->prepare("SELECT id FROM subjects WHERE user_id = ? AND code = ? LIMIT 1");
                $subjectStmt->execute([$studentId, $selectedCourse['code']]);
                $subjectId = (int)$subjectStmt->fetchColumn();
                if ($subjectId <= 0) {
                    continue;
                }

                $gradeStmt = $db->prepare("
                    SELECT title, score, max_score
                    FROM grades
                    WHERE user_id = ? AND subject_id = ? AND title LIKE 'Faculty Eval:%'
                    ORDER BY id ASC
                ");
                $gradeStmt->execute([$studentId, $subjectId]);
                $rows = $gradeStmt->fetchAll();

                foreach ($rows as $row) {
                    $componentName = trim(str_replace('Faculty Eval:', '', (string)$row['title']));
                    if ($componentName === '') {
                        continue;
                    }
                    if (!isset($components[$componentName])) {
                        $components[$componentName] = [
                            'name' => $componentName,
                            'max' => (float)($row['max_score'] ?? 0),
                        ];
                    }
                    $scoreMap[$studentId][$componentName] = (float)($row['score'] ?? 0);
                }
            }
        }

        $components = array_values($components);
        $currentPage = 'faculty-results';
        $pageTitle = 'Faculty Result Evaluation - EduSync MU';

        $this->render('pages/faculty/results', compact(
            'user',
            'courses',
            'students',
            'components',
            'scoreMap',
            'selCourse',
            'selBatch',
            'selSemester',
            'selDate',
            'selectedCourse',
            'currentPage',
            'pageTitle'
        ));
    }
}
