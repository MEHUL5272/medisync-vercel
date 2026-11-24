<?php

require_once __DIR__ . "/../vendor/autoload.php";

// Load local .env if present (for local development)
$envFile = __DIR__ . "/../.env";
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (getenv($name) === false) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

$mongoUri = getenv("MONGODB_URI") ?: ($_ENV["MONGODB_URI"] ?? "mongodb://localhost:27017");
$dbName = getenv("MONGODB_DB") ?: ($_ENV["MONGODB_DB"] ?? "medisync");

$client = new MongoDB\Client($mongoUri);
$db = $client->selectDatabase($dbName);

?>
