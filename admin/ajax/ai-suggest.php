<?php
// admin/ajax/ai-suggest.php
header('Content-Type: application/json');
require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';

if (!$prompt) {
    echo json_encode(['error' => 'No prompt provided', 'text' => 'Empty prompt sent.']);
    exit;
}

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
    echo json_encode(['error' => 'Gemini API key not configured', 'text' => '❌ Error: API key not configured. Go to Admin → API Settings to add your Gemini key.']);
    exit;
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode(GEMINI_API_KEY);
$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check for curl errors
if ($curlError) {
    echo json_encode(['error' => 'Network error: ' . $curlError, 'text' => '❌ Network error. Check your internet connection.']);
    exit;
}

if ($httpCode !== 200) {
    $errorMsg = 'API returned HTTP ' . $httpCode;
    if ($response) {
        $apiError = json_decode($response, true);
        if (isset($apiError['error']['message'])) {
            $errorMsg = $apiError['error']['message'];
        }
    }
    echo json_encode(['error' => $errorMsg, 'text' => '❌ API Error: ' . $errorMsg]);
    exit;
}

$result = json_decode($response, true);

if (!$result) {
    echo json_encode(['error' => 'Invalid JSON response', 'text' => '❌ Invalid API response. Try again.']);
    exit;
}

$text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$text) {
    echo json_encode(['error' => 'No content in response', 'text' => '❌ No response from API.']);
    exit;
}

echo json_encode(['text' => $text]);
?>