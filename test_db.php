<?php
// Test database connection and functions
require_once __DIR__ . '/config/database.php';

echo "<h1>SI TERNAK - Database Test</h1>";

try {
    // Test connection
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";

    // Test fetchOne function
    $result = fetchOne("SELECT 1 as test");
    echo "<p style='color: green;'>✅ fetchOne() function works! Result: " . $result['test'] . "</p>";

    // Test fetchAll function
    $results = fetchAll("SELECT 2 as test");
    echo "<p style='color: green;'>✅ fetchAll() function works! Count: " . count($results) . "</p>";

    echo "<h2>Database Tables Check:</h2>";

    // Check if tables exist
    $tables = ['users', 'peternakan', 'produksi', 'vaksinasi', 'penyakit_hewan', 'survei_pasar', 'populasi_ternak', 'pemotongan', 'desa', 'kecamatan'];
    foreach ($tables as $table) {
        // Use information_schema instead of SHOW TABLES for better compatibility with prepared statements
        $sql = "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
        $result = fetchOne($sql, [$table]);
        
        if ($result && $result['count'] > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }

    // Check dashboard_stats view (if applicable)
    // $result = fetchOne("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_si_ternak = 'dashboard_stats'");
    
    echo "<h2>Sample Data Check:</h2>";

    // Check sample data
    $user_count = fetchOne("SELECT COUNT(*) as count FROM users");
    echo "<p>Users in database: " . $user_count['count'] . "</p>";

    $peternakan_count = fetchOne("SELECT COUNT(*) as count FROM peternakan");
    echo "<p>Peternakan records: " . $peternakan_count['count'] . "</p>";
    
    /*
    echo "<h2>Dashboard Stats Test:</h2>";

    // Test dashboard stats
    $stats = fetchOne("SELECT * FROM dashboard_stats");
    if ($stats) {
        echo "<pre>";
        print_r($stats);
        echo "</pre>";
        echo "<p style='color: green;'>✅ Dashboard stats working!</p>";
    } else {
        echo "<p style='color: red;'>❌ Dashboard stats failed</p>";
    }
    */

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please run setup_database.php first to initialize the database.</p>";
}

echo "<hr>";
echo "<p><a href='setup_database.php'>Run Database Setup</a> | <a href='index.php'>Go to Application</a></p>";
?>