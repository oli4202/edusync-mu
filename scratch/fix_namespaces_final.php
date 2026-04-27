<?php
$dir = __DIR__ . '/../edusync/app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    
    $content = file_get_contents($file->getPathname());
    
    // Remove use function App\...
    $content = preg_replace('/use function App\\\\.*;\n/', '', $content);
    
    // Remove double backslashes before functions but KEEP single backslash
    // (Wait, some calls already have single backslash \getDB())
    // Let's ensure they have exactly one backslash if they are in a namespace
    
    // Replace calls with global calls if they don't have a backslash
    $funcs = ['getDB', 'clean', 'redirect', 'callAI', 'url'];
    foreach ($funcs as $f) {
        // Replace getDB() with \getDB() if not preceded by \
        $content = preg_replace('/(?<!\\\\|function )\b' . $f . '\(/', '\\\\' . $f . '(', $content);
        // Replace \\getDB() with \getDB()
        $content = preg_replace('/\\\\\\\\' . $f . '\(/', '\\\\' . $f . '(', $content);
    }
    
    file_put_contents($file->getPathname(), $content);
}
echo "Done fixing edusync/app namespaces!\n";
