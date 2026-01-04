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
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    // Allowed roles
    $allowed_roles = ['admin', 'petugas_kesmavet', 'petugas_keswan', 'petugas_bitpro', 'kepala_dinas'];

    // Validation
    if (empty($fullname) || empty($email) || empty($username) || empty($phone) || empty($password) || empty($confirm_password) || empty($role)) {
        $error_message = 'Semua field harus diisi.';
    } elseif (!in_array($role, $allowed_roles)) {
        $error_message = 'Role tidak valid.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password minimal 6 karakter.';
    } elseif (!$terms) {
        $error_message = 'Anda harus menyetujui syarat dan ketentuan.';
    } else {
        // Check if database functions are available
        if (function_exists('fetchOne') && function_exists('hashPassword') && function_exists('insertData')) {
            try {
                // Check if username or email already exists
                $existing_user = fetchOne(
                    "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
                    [$username, $email]
                );

                if ($existing_user) {
                    $error_message = 'Username atau email sudah terdaftar.';
                } else {
                    // Insert new user
                    $hashed_password = hashPassword($password);
                    $user_id = insertData(
                        "INSERT INTO users (fullname, email, username, password, phone, role, is_active)
                         VALUES (?, ?, ?, ?, ?, ?, 1)",
                        [$fullname, $email, $username, $hashed_password, $phone, $role]
                    );

                    if ($user_id) {
                        $success_message = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                        // Clear form data
                        $_POST = array();
                    } else {
                        $error_message = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
                    }
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            }
        } else {
            // Database not available, simulate success for demo
            $success_message = 'Registrasi berhasil! (Demo Mode) Silakan login dengan admin/admin123.';
            $_POST = array();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register SI TERNAK</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="logo">
                <i class="fas fa-paw"></i>
            </div>
            <h1>SI TERNAK</h1>
            <p>Sistem Input Data Peternakan Hewan</p>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="register-form">
                <div class="input-group">
                    <i class="fas fa-user icon"></i>
                    <input type="text" id="fullname" name="fullname" placeholder="Nama Lengkap"
                           value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" id="email" name="email" placeholder="Email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-user-circle icon"></i>
                    <input type="text" id="username" name="username" placeholder="Nama Pengguna"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-phone icon"></i>
                    <input type="tel" id="phone" name="phone" placeholder="Nomor Telepon"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-user-tag icon"></i>
                    <select name="role" id="role" required>
                        <option value="" disabled <?php echo empty($_POST['role']) ? 'selected' : ''; ?>>Pilih Peran</option>
                        <option value="petugas_kesmavet" <?php echo (($_POST['role'] ?? '') === 'petugas_kesmavet') ? 'selected' : ''; ?>>Petugas Kesmavet</option>
                        <option value="petugas_keswan" <?php echo (($_POST['role'] ?? '') === 'petugas_keswan') ? 'selected' : ''; ?>>Petugas Keswan</option>
                        <option value="petugas_bitpro" <?php echo (($_POST['role'] ?? '') === 'petugas_bitpro') ? 'selected' : ''; ?>>Petugas Bitpro</option>
                        <option value="kepala_dinas" <?php echo (($_POST['role'] ?? '') === 'kepala_dinas') ? 'selected' : ''; ?>>Kepala Dinas</option>
                        <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Kata Sandi" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Kata Sandi" required>
                </div>

                <div class="terms">
                    <label class="terms-label">
                        <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                        Saya setuju dengan <a href="#" class="terms-link">Syarat dan Ketentuan</a>
                    </label>
                </div>

                <button type="submit" class="register-button">DAFTAR</button>
            </form>

            <div class="login-link">
                <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
            </div>

            <div class="footer-text">
                <p>&copy; <?php echo date('Y'); ?> SI TERNAK. Semua Hak Dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>