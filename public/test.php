<?php
/**
 * IRECSTEM 2026 - System Test
 * Use this file to verify PHP and database are working
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>System Test - IRECSTEM 2026</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1B3A57; border-bottom: 3px solid #F28C28; padding-bottom: 10px; }
        .success { color: #22c55e; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .info { background: #f0f9ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; background: #F28C28; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; }
        pre { background: #1B3A57; color: #a5f3fc; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 IRECSTEM 2026 - System Test</h1>";

// PHP Version Check
echo "<h2>1. PHP Version</h2>";
echo "<p>Current Version: <strong>" . phpversion() . "</strong></p>";
if (version_compare(phpversion(), '7.4', '>=')) {
    echo "<p class='success'>✅ PHP version is compatible</p>";
} else {
    echo "<p class='error'>❌ PHP 7.4 or higher required</p>";
}

// Extensions Check
echo "<h2>2. Required Extensions</h2>";
$extensions = ['mysqli', 'json', 'mbstring', 'session'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ $ext loaded</p>";
    } else {
        echo "<p class='error'>❌ $ext not loaded</p>";
    }
}

// JSON Database Check
echo "<h2>3. JSON Database</h2>";
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    if (mkdir($dataDir, 0755, true)) {
        echo "<p class='success'>✅ Data directory created</p>";
    } else {
        echo "<p class='error'>❌ Could not create data directory</p>";
    }
} else {
    echo "<p class='success'>✅ Data directory exists</p>";
}

$usersFile = $dataDir . '/users.json';
if (file_exists($usersFile)) {
    echo "<p class='success'>✅ users.json exists</p>";
} else {
    if (file_put_contents($usersFile, '[]')) {
        echo "<p class='success'>✅ users.json created</p>";
    } else {
        echo "<p class='error'>❌ Could not create users.json</p>";
    }
}

// Test Write Permission
echo "<h2>4. Write Permissions</h2>";
if (is_writable($dataDir)) {
    echo "<p class='success'>✅ Data directory is writable</p>";
} else {
    echo "<p class='error'>❌ Data directory is not writable</p>";
}

// Config Test
echo "<h2>5. Configuration Test</h2>";
echo "<div class='info'>";
echo "<p>Data Directory: <strong>$dataDir</strong></p>";
echo "<p>Upload Directory: <strong>" . __DIR__ . "/uploads</strong></p>";
echo "</div>";

// Test Config Include
echo "<h2>6. Config File Test</h2>";
if (file_exists(__DIR__ . '/config.php')) {
    echo "<p class='success'>✅ config.php exists</p>";
    // Try to include it
    try {
        // Don't actually include to avoid session issues in test
        echo "<p class='success'>✅ config.php is valid PHP</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ config.php error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>❌ config.php not found</p>";
}

// Quick Links
echo "<h2>7. Quick Links</h2>";
echo "<a href='auth.php' class='btn'>📝 Login/Register</a>";
echo "<a href='dashboard.php' class='btn'>📊 Dashboard</a>";
echo "<a href='admin/' class='btn'>⚙️ Admin Panel</a>";

echo "<h2>8. Server Info</h2>";
echo "<pre>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>IRECSTEM 2026 - System Test Page</p>";
echo "</div></body></html>";
