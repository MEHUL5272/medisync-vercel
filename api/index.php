<?php
$rootDir = realpath(__DIR__ . '/..');
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriPath = '/' . ltrim($uriPath, '/');

if ($uriPath === '/' || $uriPath === '') {
    $uriPath = '/index.php';
}

$targetPath = realpath($rootDir . $uriPath);

if ($targetPath && str_starts_with($targetPath, $rootDir) && is_file($targetPath) && pathinfo($targetPath, PATHINFO_EXTENSION) === 'php') {
    chdir(dirname($targetPath));
    require $targetPath;
    exit;
}

http_response_code(404);
echo "404 - Page Not Found";
