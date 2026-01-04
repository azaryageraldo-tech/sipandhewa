<?php
session_start();

// Include database configuration
$db_config_path = __DIR__ . '/config/database.php';
if (file_exists($db_config_path)) {
    require_once $db_config_path;
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: Dashboard.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi.';
    } else {
        // Check if database functions are available
        if (function_exists('fetchOne') && function_exists('verifyPassword') && function_exists('executeQuery')) {
            try {
                // Check if user exists (by username or email)
                $user = fetchOne(
                    "SELECT id, fullname, username, email, password, role, is_active
                     FROM users
                     WHERE (username = ? OR email = ?) AND is_active = 1
                     LIMIT 1",
                    [$username, $username]
                );

                if ($user && verifyPassword($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['fullname'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['login_time'] = date('Y-m-d H:i:s');

                    // Update last login time
                    executeQuery(
                        "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                        [$user['id']]
                    );

                    // Redirect to dashboard
                    header("Location: Dashboard.php");
                    exit();
                } else {
                    $error_message = 'Username atau password salah.';
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            }
        } else {
            // Database not available, demo login
            if ($username === 'admin' && $password === 'admin123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['user_name'] = 'Administrator';
                $_SESSION['username'] = 'admin';
                $_SESSION['user_role'] = 'admin';
                $_SESSION['login_time'] = date('Y-m-d H:i:s');
                header("Location: Dashboard.php");
                exit();
            } else {
                $error_message = 'Database belum di-setup. Gunakan admin/admin123 untuk demo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SI TERNAK</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <i class="fas fa-paw"></i>
            </div>
            <h1>SI TERNAK</h1>
            <p>Sistem Input Data Peternakan Hewan</p>

            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: red; text-align: center; margin-bottom: 15px; padding: 10px; background-color: #ffe6e6; border-radius: 5px;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                <div class="input-group">
                    <i class="fas fa-user icon"></i>
                    <input type="text" id="username" name="username" placeholder="Nama Pengguna atau Email"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Kata Sandi" required>
                </div>

                <div class="options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>> Ingat Saya
                    </label>
                    <a href="#" class="forgot-password">Lupa Kata Sandi?</a>
                </div>

                <button type="submit" class="login-button">MASUK</button>
            </form>

            <div class="register-link">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            </div>

            <div class="footer-text">
                <p>&copy; <?php echo date('Y'); ?> SI TERNAK. Semua Hak Dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>