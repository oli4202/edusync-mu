<?php
$dir = __DIR__ . '/../edusync/app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    
    $content = file_get_contents($file->getPathname());
    
    // Remove use function App\...
    $content = preg_replace('/use function App\\\\.*;\n/', '', $content);
    
    // Replace calls with global calls
    $content = preg_replace('/\bgetDB\(\)/', '\\getDB()', $content);
    $content = preg_replace('/\bclean\(/', '\\clean(', $content);
    $content = preg_replace('/\bredirect\(/', '\\redirect(', $content);
    $content = preg_replace('/\bcallAI\(/', '\\callAI(', $content);
    
    file_put_contents($file->getPathname(), $content);
}
echo "Done!\n";
