<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use function App\callAI;

class AiController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $this->render('pages/ai', compact('user'));
    }

    public function playground(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $this->render('pages/playground', compact('user'));
    }

    public function suggestions(): void
    {
        $this->requireLogin();
        $userId = $this->session->userId();
        $user = User::findById($userId);
        $this->render('pages/suggestions', compact('user'));
    }

    public function chat(): void
    {
        $this->requireLogin();
        $json = json_decode(file_get_contents('php://input'), true);
        $prompt = $json['prompt'] ?? '';
        $system = $json['system'] ?? 'You are a helpful assistant for MU SWE students.';
        
        $result = callAI($prompt, $system);
        $this->json($result);
    }

    public function suggest(): void
    {
        $this->requireLogin();
        $json = json_decode(file_get_contents('php://input'), true);
        $prompt = trim($json['prompt'] ?? '');

        if ($prompt === '') {
            $this->json(['text' => 'No prompt provided.'], 400);
        }

        $result = callAI(
            $prompt,
            'You are a helpful academic assistant for Metropolitan University Sylhet, Software Engineering department students.'
        );

        $this->json(['text' => $result['text'] ?? '']);
    }

    public function summarize(): void
    {
        $this->requireLogin();
        $json = json_decode(file_get_contents('php://input'), true);
        $text = $json['text'] ?? '';

        if (strlen($text) < 20) {
            $this->json(['success' => false, 'message' => 'Text is too short to summarize'], 400);
            return;
        }

        $prompt = "Please summarize the following text for a student. Use bullet points if appropriate:\n\n" . $text;
        $system = "You are an expert academic summarizer. Provide concise, clear summaries of educational content.";

        $result = callAI($prompt, $system);
        $this->json($result);
    }

    public function generateQuiz(): void
    {
        $this->requireLogin();
        $json = json_decode(file_get_contents('php://input'), true);
        $topic = $json['topic'] ?? '';
        $count = (int)($json['count'] ?? 5);

        if (!$topic) {
            $this->json(['success' => false, 'message' => 'Topic is required'], 400);
            return;
        }

        $prompt = "Generate $count multiple-choice questions about '$topic'. Return ONLY a JSON array of objects. Each object should have 'question', 'options' (array of 4 strings), and 'answer' (the correct string).";
        $system = "You are a quiz generator. Output only valid JSON.";

        $result = callAI($prompt, $system);
        
        // Try to parse the AI output as JSON if it's just a text field
        if ($result['success']) {
            $cleanJson = preg_replace('/```json|```/', '', $result['text']);
            $quizData = json_decode(trim($cleanJson), true);
            if ($quizData) {
                $this->json(['success' => true, 'quiz' => $quizData]);
                return;
            }
        }
        
        $this->json($result);
    }

    public function ocr(): void
    {
        $this->requireLogin();
        $json = json_decode(file_get_contents('php://input'), true);
        $image = $json['image'] ?? '';

        if (!$image) {
            $this->json(['success' => false, 'message' => 'No image data provided'], 400);
            return;
        }

        $prompt = "Please extract the text from this image. It is an image of a handwritten or printed exam question. Just provide the extracted text, nothing else.";
        $system = "You are an expert OCR assistant. Extract text exactly as it appears in the image.";

        $result = callAI($prompt, $system, $image);
        $this->json($result);
    }

    public function runPython(): void
    {
        $this->requireLogin();
        $json = json_decode(file_get_contents('php://input'), true);
        $code = $json['code'] ?? '';
        $stdin = $json['input'] ?? '';

        if (trim($code) === '') {
            $this->json(['success' => false, 'error' => 'No code provided.'], 400);
        }

        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'edusync_playground';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $fileId = uniqid('py_', true);
        $pyFile = $tmpDir . DIRECTORY_SEPARATOR . $fileId . '.py';
        $inputFile = $tmpDir . DIRECTORY_SEPARATOR . $fileId . '.txt';

        file_put_contents($pyFile, $code);
        file_put_contents($inputFile, $stdin);

        $command = 'python ' . escapeshellarg($pyFile) . ' < ' . escapeshellarg($inputFile) . ' 2>&1';
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        @unlink($pyFile);
        @unlink($inputFile);

        $this->json([
            'success' => true,
            'output' => implode("\n", $output),
            'returnCode' => $returnCode,
        ]);
    }

    public function runSql(): void
    {
        $this->requireLogin();
        $json = json_decode(file_get_contents('php://input'), true);
        $code = trim($json['code'] ?? '');

        if ($code === '') {
            $this->json(['success' => false, 'error' => 'No SQL code provided.'], 400);
        }

        $db = getDB();
        $results = [];

        // Divide by semicolon but try to be smart about it (basic version)
        $statements = array_filter(array_map('trim', explode(';', $code)));

        try {
            foreach ($statements as $stmtText) {
                if (empty($stmtText)) continue;
                
                $isQuery = preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)/i', $stmtText);
                
                // Restriction: Only faculty/admin can run non-SELECT queries
                if (!$isQuery && !$this->session->isFaculty()) {
                    $results[] = [
                        'type' => 'error',
                        'stmt' => substr($stmtText, 0, 100),
                        'message' => 'Restricted: Only faculty can perform data-modifying operations.'
                    ];
                    continue;
                }
                
                $stmt = $db->prepare($stmtText);
                $stmt->execute();
                
                if ($isQuery) {
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    $columns = [];
                    if (!empty($rows)) {
                        $columns = array_keys($rows[0]);
                    }
                    $results[] = [
                        'type' => 'table',
                        'stmt' => substr($stmtText, 0, 100),
                        'columns' => $columns,
                        'rows' => $rows,
                        'rowCount' => count($rows)
                    ];
                } else {
                    $results[] = [
                        'type' => 'status',
                        'stmt' => substr($stmtText, 0, 100),
                        'affectedRows' => $stmt->rowCount(),
                        'message' => 'Success'
                    ];
                }
            }
            $this->json(['success' => true, 'results' => $results]);
        } catch (\PDOException $e) {
            $this->json([
                'success' => false, 
                'error' => 'SQL Error: ' . $e->getMessage(),
                'stmt' => $stmtText ?? 'unknown'
            ], 400);
        }
    }
}
