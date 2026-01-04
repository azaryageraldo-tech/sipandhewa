<?php
// Comprehensive server test
echo "<h1>🧪 SI TERNAK - Server Test</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "<p class='success'>✅ PHP Version: " . phpversion() . "</p>";

// Test 2: Server Info
echo "<h2>2. Server Information</h2>";
echo "<p class='info'>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p class='info'>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p class='info'>Current Directory: " . __DIR__ . "</p>";
echo "<p class='info'>Script Path: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";

// Test 3: File Permissions
echo "<h2>3. File Permissions Test</h2>";
$test_file = __DIR__ . '/test_write.txt';
$write_test = @file_put_contents($test_file, 'Test content');
if ($write_test !== false) {
    echo "<p class='success'>✅ Write permissions OK</p>";
    unlink($test_file);
} else {
    echo "<p class='error'>❌ Write permissions FAILED</p>";
}

// Test 4: Database Connection (if config exists)
echo "<h2>4. Database Connection Test</h2>";
$db_config = __DIR__ . '/config/database.php';
if (file_exists($db_config)) {
    echo "<p class='success'>✅ Database config file exists</p>";
    require_once $db_config;

    if (function_exists('getDBConnection')) {
        try {
            $pdo = getDBConnection();
            echo "<p class='success'>✅ Database connection successful</p>";

            // Test query
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            echo "<p class='success'>✅ Database query successful: " . $result['test'] . "</p>";

        } catch (Exception $e) {
            echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Database functions not available</p>";
    }
} else {
    echo "<p class='info'>ℹ️ Database config not found (normal for demo mode)</p>";
}

// Test 5: Application Files
echo "<h2>5. Application Files Check</h2>";
$required_files = [
    'index.php',
    'login.php',
    'register.php',
    'Dashboard.php',
    'config/database.php',
    'database_schema.sql'
];

foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='success'>✅ $file exists</p>";
    } else {
        echo "<p class='error'>❌ $file missing</p>";
    }
}

// Test 6: Recommendations
echo "<h2>6. Troubleshooting Guide</h2>";
echo "<div style='background:#f0f0f0;padding:15px;border-radius:5px;'>";

if (!file_exists(__DIR__ . '/config/database.php')) {
    echo "<p><strong>Database Setup:</strong> Run setup_database.php to initialize database</p>";
}

echo "<p><strong>Access URLs:</strong></p>";
echo "<ul>";
echo "<li><a href='index.php'>index.php</a> - Main page</li>";
echo "<li><a href='login.php'>login.php</a> - Login page</li>";
echo "<li><a href='register.php'>register.php</a> - Registration</li>";
echo "<li><a href='Dashboard.php'>Dashboard.php</a> - Dashboard</li>";
if (file_exists(__DIR__ . '/setup_database.php')) {
    echo "<li><a href='setup_database.php'>setup_database.php</a> - Database setup</li>";
}
echo "</ul>";

echo "<p><strong>If still getting 'Not Found':</strong></p>";
echo "<ul>";
echo "<li>Ensure folder is in C:\\xampp\\htdocs\\</li>";
echo "<li>Restart Apache & MySQL in XAMPP</li>";
echo "<li>Check folder permissions</li>";
echo "<li>Try accessing: http://localhost/" . basename(__DIR__) . "/</li>";
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Generated:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>