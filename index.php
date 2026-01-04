<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: Dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI TERNAK - Sistem Informasi Peternakan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        .logo {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .features {
            margin-top: 30px;
            text-align: left;
        }

        .features h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .features ul {
            list-style: none;
            padding: 0;
        }

        .features li {
            padding: 5px 0;
            color: #666;
            position: relative;
            padding-left: 20px;
        }

        .features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="welcome-container">
        <div class="logo">
            <i class="fas fa-paw"></i>
        </div>
        <h1>SI TERNAK</h1>
        <p class="subtitle">Sistem Informasi Pengelolaan Ternak Modern</p>

        <div class="buttons">
            <a href="login.php" class="btn btn-primary">Masuk</a>
            <a href="register.php" class="btn btn-secondary">Daftar</a>
        </div>

        <div class="features">
            <h3>Fitur Utama:</h3>
            <ul>
                <li>Manajemen Data Ternak Lengkap</li>
                <li>Tracking Produksi Harian</li>
                <li>Rekam Medis & Vaksinasi</li>
                <li>Dashboard Real-time</li>
                <li>Laporan & Analitik</li>
                <li>Survei Harga Pasar</li>
            </ul>
        </div>

        <div class="footer">
            <p>&copy; 2025 SI TERNAK - Sistem Peternakan Modern</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

</body>

</html>