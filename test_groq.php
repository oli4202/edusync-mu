<?php
require 'app/helpers.php';
require 'config/database.php';

$testImage = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';
$prompt = "What is in this image?";
$groqKey = getenv('GROQ_API_KEY');
$result = callGroqAI_Internal($prompt, "", $groqKey, $testImage);
print_r($result);
