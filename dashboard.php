<?php
// dashboard.php
session_start();
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = getUserInfo();

// Handle routing
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? 'data'; // Default ke 'data' untuk tampilan tabel

// Determine which content to show
if ($module && $action && file_exists("modules/$module/$action.php")) {
    $contentFile = "modules/$module/$action.php";
    $pageTitle = ucfirst(str_replace('_', ' ', $module)) . " - SI TERNAK ";
} elseif ($module && file_exists("modules/$module/data.php")) {
    // Jika module ada tapi action tidak, default ke data
    $contentFile = "modules/$module/data.php";
    $pageTitle = ucfirst(str_replace('_', ' ', $module)) . " - SI TERNAK ";
} else {
    $contentFile = "modules/dashboard/main.php";
    $pageTitle = "Dashboard - SI TERNAK ";
}


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js (hanya untuk dashboard) -->
    <?php if (!$module): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <?php include 'includes/topbar.php'; ?>

            <!-- Breadcrumb Navigation -->
            <?php if ($module): ?>
                <div class="breadcrumb">
                    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a> &gt;
                    <span><?php echo ucfirst(str_replace('_', ' ', $module)); ?></span>
                </div>
            <?php endif; ?>

            <!-- Main Content Area -->
            <div class="content-area">
                <?php include $contentFile; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>