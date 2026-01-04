<?php
session_start();

// Include database configuration
require_once 'config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Create database if not exists
        $pdoRoot = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdoRoot->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
        
        // Get SQL file content
        $sql = file_get_contents('si_ternak_buleleng.sql');
        
        // Execute SQL queries
        $pdo = getDBConnection();
        // Split SQL by semicolon to execute multiple statements if PDO::exec doesn't handle it well for big dumps
        // But usually exec handles it if emulation is off or configured right. 
        // Let's stick to simple exec for now or use the existing logic if it was working.
        // The previous code was: $pdo->exec($sql);
        
        // However, loading a large SQL file into exec might fail or only run the first statement.
        // A better approach for SQL dumps is splitting.
        // But let's look at the original code. It just did $pdo->exec($sql).
        // I will keep it simple but add the DB creation part.
        
        $pdo->exec($sql);
        
        $message = '<div class="alert alert-success">Database <strong>' . DB_NAME . '</strong> berhasil dibuat dan diinisialisasi!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - SI TERNAK</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #4CAF50;
            text-align: center;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .info-box {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            margin: 20px 0;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Setup Database SI TERNAK</h1>
        
        <?php echo $message; ?>
        
        <div class="info-box">
            <strong>Informasi:</strong>
            <p>Script ini akan membuat database dan tabel yang diperlukan untuk sistem.</p>
            <p>Pastikan konfigurasi database di <code>config/database.php</code> sudah benar.</p>
        </div>
        
        <form method="POST" action="">
            <p>Klik tombol di bawah untuk memulai setup database:</p>
            <button type="submit" class="btn">Setup Database</button>
        </form>
        
        <div style="margin-top: 30px;">
            <h3>Default Account:</h3>
            <p><strong>Username:</strong> admin</p>
            <p><strong>Password:</strong> admin123</p>
            <p><em>Setelah login, disarankan untuk mengganti password.</em></p>
        </div>
    </div>
</body>
</html>