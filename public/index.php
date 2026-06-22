<?php
/**
 * IRECSTEM 2026 - Single Page Application Entry Point
 * Handles routing for both HTML pages and PHP pages
 */

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Remove the base path from the URI
$path = str_replace($scriptName, '', $requestUri);
$path = trim($path, '/');

// Route to the appropriate file
$routes = [
    '' => 'index.html',
    'index' => 'index.html',
    'about' => 'about.html',
    'call-for-papers' => 'call-for-papers.html',
    'speakers' => 'speakers.html',
    'program' => 'program.html',
    'registration' => 'registration.html',
    'venue' => 'venue.html',
    'committee' => 'committee.html',
    'contact' => 'contact.html',
    'sponsors' => 'sponsors.html',
    'auth' => 'auth.php',
    'dashboard' => 'dashboard.php',
    'logout' => 'logout.php',
];

// Get file extension
$pathParts = pathinfo($path);
$extension = $pathParts['extension'] ?? '';

// If no extension, try to find a route
if (empty($extension)) {
    if (isset($routes[$path])) {
        $file = $routes[$path];
        $extension = pathinfo($file, PATHINFO_EXTENSION);
    } else {
        // Check if there's an .html file with that name
        $file = $path . '.html';
        if (file_exists(__DIR__ . '/' . $file)) {
            $extension = 'html';
        } else {
            // Default to index.html
            $file = 'index.html';
            $extension = 'html';
        }
    }
} else {
    $file = $path;
}

// Security: prevent directory traversal
$file = str_replace(['../', '..', './', '.php'], '', $file);
$file = basename($file);

// Check if file exists
$fullPath = __DIR__ . '/' . $file;
if (!file_exists($fullPath)) {
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
        <a href="/">Go to Homepage</a>
    </body>
    </html>';
    exit;
}

// Set content type based on extension
$contentTypes = [
    'html' => 'text/html; charset=UTF-8',
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
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
];

if (isset($contentTypes[$extension])) {
    header('Content-Type: ' . $contentTypes[$extension]);
}

// For HTML files, add security headers
if ($extension === 'html') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}

// For PHP files, ensure proper content type
if ($extension === 'php') {
    header('Content-Type: text/html; charset=UTF-8');
}

// Serve the file
readfile($fullPath);
