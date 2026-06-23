<?php
/**
 * IRECSTEM 2026 - Single Page Application Entry Point
 * Handles routing for the conference website
 */

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';

// Determine the base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . $host;

// Remove the base path from the URI to get the clean path
$basePath = dirname($scriptName);
if ($basePath === '\\' || $basePath === '/') {
    $basePath = '';
}
$path = str_replace($basePath, '', $requestUri);
$path = trim($path, '/');

// If path still starts with 'akogwapo', strip it
if (strpos($path, 'akogwapo/') === 0) {
    $path = substr($path, strlen('akogwapo/'));
}
if ($path === 'akogwapo') {
    $path = '';
}

// Strip query string from path
$path = strtok($path, '?');

// Calculate the public base URL (for injecting into HTML files)
// Files are in public/ folder, so base should point there
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$publicBase = $baseUrl . $scriptDir . '/public/';

// Route to the appropriate file
$routes = [
    '' => 'public/index.html',
    'index' => 'public/index.html',
    'index.html' => 'public/index.html',
    'about' => 'public/about.html',
    'about.html' => 'public/about.html',
    'call-for-papers' => 'public/call-for-papers.html',
    'call-for-papers.html' => 'public/call-for-papers.html',
    'dates' => 'public/dates.html',
    'dates.html' => 'public/dates.html',
    'contact' => 'public/contact.html',
    'contact.html' => 'public/contact.html',
    'venue' => 'public/venue.html',
    'venue.html' => 'public/venue.html',
    'speakers' => 'public/speakers.html',
    'speakers.html' => 'public/speakers.html',
    'program' => 'public/program.html',
    'program.html' => 'public/program.html',
    'committee' => 'public/committee.html',
    'committee.html' => 'public/committee.html',
    'sponsors' => 'public/sponsors.html',
    'sponsors.html' => 'public/sponsors.html',
    'registration' => 'public/registration.html',
    'registration.html' => 'public/registration.html',
    'auth' => 'public/auth.php',
    'auth.php' => 'public/auth.php',
    'dashboard' => 'public/dashboard.php',
    'dashboard.php' => 'public/dashboard.php',
    'logout' => 'public/logout.php',
    'logout.php' => 'public/logout.php',
    'admin' => 'public/admin/index.php',
    'admin/index' => 'public/admin/index.php',
    'admin/index.php' => 'public/admin/index.php',
    'setup-admin' => 'public/setup-admin.php',
    'setup-admin.php' => 'public/setup-admin.php',
    'paper_file' => 'public/paper_file.php',
    'paper_file.php' => 'public/paper_file.php',
    'submit_paper' => 'public/submit_paper.php',
    'submit_paper.php' => 'public/submit_paper.php',
    'register_conference' => 'public/register_conference.php',
    'register_conference.php' => 'public/register_conference.php',
    'verify_login' => 'public/verify_login.php',
    'verify_login.php' => 'public/verify_login.php',
];

// Find the file to serve
$file = null;

// Check for direct file access (uploads, assets, etc.)
if (preg_match('/^(uploads|public\/|assets\/)/', $path)) {
    $file = $path;
} elseif (isset($routes[$path])) {
    $file = $routes[$path];
} elseif ($path) {
    // Try to find the file directly
    $possibleFiles = [
        'public/' . $path,
        'public/' . $path . '.html',
        'public/' . $path . '.php',
    ];
    foreach ($possibleFiles as $pf) {
        if (file_exists(__DIR__ . '/' . $pf)) {
            $file = $pf;
            break;
        }
    }
}

// Default to index.html if no file found
if (!$file) {
    $file = 'public/index.html';
}

// Build full path
$fullPath = __DIR__ . '/' . $file;

// Check if it's a file (not a directory)
if (!is_file($fullPath)) {
    http_response_code(404);
    echo '<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        h1 { color: #1B3A57; }
        a { color: #F28C28; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for does not exist.</p>
    <p>Looking for: ' . htmlspecialchars($file) . '</p>
    <a href="/">Go to Homepage</a>
</body>
</html>';
    exit;
}

// Get the file extension
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

// For HTML files, inject the correct base URL
if ($extension === 'html') {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');

    // Read the file content
    $content = file_get_contents($fullPath);

    // Replace or add the base tag
    if (preg_match('/<base[^>]*>/i', $content)) {
        $content = preg_replace('/<base[^>]*>/i', '<base href="' . $publicBase . '">', $content);
    } else {
        $content = preg_replace('/(<head[^>]*>)/i', '$1' . "\n    <base href=\"" . $publicBase . "\">", $content);
    }

    echo $content;
}
// For PHP files, include and execute
elseif ($extension === 'php') {
    include $fullPath;
}
// For other files (CSS, JS, images), serve directly
else {
    $contentTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
    ];
    if (isset($contentTypes[$extension])) {
        header('Content-Type: ' . $contentTypes[$extension]);
    }
    readfile($fullPath);
}
