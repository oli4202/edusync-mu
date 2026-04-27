<?php
$dir = __DIR__ . '/../edusync/app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    
    $content = file_get_contents($file->getPathname());
    
    // Remove double backslashes before functions
    $content = preg_replace('/\\\\\\\\(getDB|clean|redirect|callAI)\(/', '\\\\$1(', $content);
    
    // Also remove the prefix entirely if it's already in the global namespace but has App namespace import
    // (Actually the previous step is safer)
    
    file_put_contents($file->getPathname(), $content);
}
echo "Done cleaning edusync/app!\n";
