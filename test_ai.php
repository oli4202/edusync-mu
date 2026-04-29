<?php
require 'app/helpers.php';
require 'config/database.php'; // loads env

$testImage = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg=='; // Tiny black pixel
$prompt = "What is in this image?";
$result = callAI($prompt, "", $testImage);
print_r($result);
