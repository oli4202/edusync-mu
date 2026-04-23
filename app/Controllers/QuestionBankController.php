<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;
use function App\redirect;
use function App\clean;
use function App\getDB;
use function App\callAI;

class QuestionBankController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $filters = [
            'course' => $_GET['course'] ?? '',
            'topic' => $_GET['topic'] ?? '',
            'type' => $_GET['type'] ?? '',
            'q' => $_GET['q'] ?? ''
        ];

        $usingDb = Question::hasApprovedQuestions();

        if ($usingDb) {
            $courses = Question::getCourseSummariesFromDb();
            $filteredQs = Question::findFilteredFromDb($filters);
            $hotTopics = Question::getHotTopicsFromDb();
            $allTypes = Question::getTypesFromDb();
            $examHistory = !empty($filters['course']) ? Question::getExamHistoryByCourseCode($filters['course']) : [];
            $totalQuestionCount = array_sum(array_column($courses, 'question_count'));
        } else {
            $legacyQuestions = Question::getAll();
            $filteredQs = Question::findFiltered($filters);
            $hotTopics = Question::getHotTopics();
            $allTypes = Question::getTypes();
            $courses = [];
            foreach ($legacyQuestions as $code => $courseData) {
                $courses[$code] = [
                    'id' => null,
                    'code' => $code,
                    'name' => $courseData['name'],
                    'question_count' => count($courseData['questions']),
                ];
            }
            $examHistory = !empty($filters['course']) && isset($legacyQuestions[$filters['course']]['exams'])
                ? $legacyQuestions[$filters['course']]['exams']
                : [];
            $totalQuestionCount = array_sum(array_column($courses, 'question_count'));
        }

        $totalQ = count($filteredQs);

        $this->render('pages/question-bank', compact(
            'user',
            'courses',
            'filteredQs',
            'hotTopics',
            'allTypes',
            'filters',
            'totalQ',
            'totalQuestionCount',
            'examHistory',
            'usingDb'
        ));
    }

    public function detail(int $id): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);

        $question = Question::findApprovedById($id);
        if (!$question) {
            redirect('/question-bank');
        }

        $submitError = '';
        $formData = [
            'answer_text' => '',
            'solution_steps' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_answer'])) {
            $formData['answer_text'] = trim($_POST['answer_text'] ?? '');
            $formData['solution_steps'] = trim($_POST['solution_steps'] ?? '');

            if (strlen($formData['answer_text']) < 20) {
                $submitError = 'Answer must be at least 20 characters.';
            } else {
                Answer::create($id, $userId, $formData['answer_text'], $formData['solution_steps']);
                $this->session->setFlash('success', 'Answer submitted! It will appear after admin approval.');
                redirect("/question-bank/$id");
            }
        }

        Question::incrementViewCount($id);
        $question['view_count'] = (int) ($question['view_count'] ?? 0) + 1;

        $answers = Answer::findApprovedByQuestionId($id);
        $isBookmarked = Question::isBookmarkedByUser($userId, $id);
        $relatedQs = Question::findRelatedApproved((int) $question['course_id'], $id);
        $submitSuccess = $this->session->getFlash('success');

        $this->render('pages/question-detail', compact(
            'user',
            'question',
            'answers',
            'isBookmarked',
            'relatedQs',
            'submitError',
            'submitSuccess',
            'formData'
        ));
    }

    public function submit(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $courses = Course::getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $courseCode = strtoupper(trim($_POST['course_code'] ?? ''));
            $questionText = trim($_POST['question_text'] ?? '');

            if ($courseCode === '' || $questionText === '') {
                $error = 'Course code and question text are required.';
                $old = ['course_code' => $courseCode, 'question_text' => $questionText];
                $this->render('pages/submit-question', compact('user', 'courses', 'error', 'old'));
                return;
            }

            $course = Course::findByCode($courseCode);
            if (!$course) {
                $error = 'Course code not found. Please choose a valid course.';
                $old = ['course_code' => $courseCode, 'question_text' => $questionText];
                $this->render('pages/submit-question', compact('user', 'courses', 'error', 'old'));
                return;
            }

            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO questions (course_id, submitted_by, question_text, is_approved)
                VALUES (?, ?, ?, 0)
            ");
            $stmt->execute([(int) $course['id'], $userId, clean($questionText)]);

            $this->session->setFlash('success', 'Question submitted for review. Thank you.');
            redirect('/question-bank');
        }

        $error = '';
        $old = ['course_code' => '', 'question_text' => ''];
        $this->render('pages/submit-question', compact('user', 'courses', 'error', 'old'));
    }

    public function bookmark(): void
    {
        $this->requireLogin();
        $body = json_decode(file_get_contents('php://input'), true);
        $questionId = (int) ($body['question_id'] ?? 0);

        if (!$questionId) {
            $this->json(['error' => 'Invalid question.'], 400);
        }

        $bookmarked = Question::toggleBookmarkForUser($this->session->userId(), $questionId);
        $this->json(['bookmarked' => $bookmarked]);
    }

    public function upvote(): void
    {
        $this->requireLogin();
        $body = json_decode(file_get_contents('php://input'), true);
        $answerId = (int) ($body['answer_id'] ?? 0);

        if (!$answerId) {
            $this->json(['error' => 'Invalid answer.'], 400);
        }

        $upvotes = Answer::incrementUpvotes($answerId);
        if ($upvotes === null) {
            $this->json(['error' => 'Answer not found.'], 404);
        }

        $this->json(['upvotes' => $upvotes]);
    }

    public function compactAnswer(): void
    {
        $this->requireLogin();
        $body = json_decode(file_get_contents('php://input'), true);
        $question = trim($body['question'] ?? '');
        $answers = trim($body['answers'] ?? '');

        if ($question === '') {
            $this->json(['text' => 'No question provided.'], 400);
        }

        $prompt = "You are an exam preparation assistant for Metropolitan University Sylhet, Software Engineering department.

Question: $question

" . ($answers ? "Available answers from students:\n$answers\n\n" : "") . "

Write a compact, exam-ready answer in maximum 10 lines.
- Include key definitions, steps, or formulas
- Use bullet points for clarity
- Focus only on what an examiner wants to see
- Do not repeat yourself
Start directly with the answer content.";

        $result = callAI($prompt);
        $this->json(['text' => $result['text'] ?? 'Could not generate answer.']);
    }
}
