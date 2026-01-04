<?php
// includes/sidebar.php
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';
$userRole = $user['role'] ?? 'user'; // Mengambil role user dari variabel global $user

// Mapping module ke action
$moduleLinks = [
    'survei_pasar' => '?module=survei_pasar&action=data',
    'populasi' => '?module=populasi&action=data',
    'produksi' => '?module=produksi&action=data',
    'peternakan' => '?module=peternakan&action=data',
    'pemotongan' => '?module=pemotongan&action=data',
    'vaksinasi' => '?module=vaksinasi&action=data',
    'penyakit' => '?module=penyakit&action=data'
];

// Definisi akses menu berdasarkan role
// Jika role tidak ada di list, tampilkan semua (admin) atau default
$menuAccess = [
    'petugas_kesmavet' => ['survei_pasar', 'produksi', 'pemotongan'],
    'petugas_keswan' => ['vaksinasi', 'penyakit'],
    'petugas_bitpro' => ['peternakan', 'populasi'],
    // Role lain bisa ditambahkan di sini, misalnya:
];

// Fungsi untuk mengecek apakah menu boleh ditampilkan
function isMenuVisible($menuKey, $role, $accessList) {
    // Admin selalu bisa melihat semua
    if ($role === 'admin') {
        return true;
    }
    
    // Jika role terdefinisi di access list, cek apakah menu ada di array
    if (isset($accessList[$role])) {
        return in_array($menuKey, $accessList[$role]);
    }
    
    // Default: tampilkan semua jika role tidak dibatasi secara spesifik (atau ubah logic ini sesuai kebutuhan)
    return true; 
}
?>

<div class="sidebar">
    <div class="logo">
        <a href="dashboard.php">
            <i class="fas fa-paw"></i>
            <span>SI TERNAK</span>
        </a>
    </div>
    
    <nav class="nav-menu">
        <ul>
            <!-- Dashboard -->
            <li class="menu-item <?php echo (!$module) ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Survei Pasar -->
            <?php if (isMenuVisible('survei_pasar', $userRole, $menuAccess)): ?>
            <li class="menu-item <?php echo ($module == 'survei_pasar') ? 'active' : ''; ?>">
                <a href="<?php echo $moduleLinks['survei_pasar']; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Survei Pasar</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Populasi -->
            <?php if (isMenuVisible('populasi', $userRole, $menuAccess)): ?>
            <li class="menu-item <?php echo ($module == 'populasi') ? 'active' : ''; ?>">
                <a href="<?php echo $moduleLinks['populasi']; ?>">
                    <i class="fas fa-users"></i>
                    <span>Populasi</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Produksi -->
            <?php if (isMenuVisible('produksi', $userRole, $menuAccess)): ?>
            <li class="menu-item <?php echo ($module == 'produksi') ? 'active' : ''; ?>">
                <a href="<?php echo $moduleLinks['produksi']; ?>">
                    <i class="fas fa-industry"></i>
                    <span>Produksi</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Peternakan -->
            <?php if (isMenuVisible('peternakan', $userRole, $menuAccess)): ?>
            <li class="menu-item <?php echo ($module == 'peternakan') ? 'active' : ''; ?>">
                <a href="<?php echo $moduleLinks['peternakan']; ?>">
                    <i class="fas fa-tractor"></i>
                    <span>Peternakan</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Pemotongan -->
            <?php if (isMenuVisible('pemotongan', $userRole, $menuAccess)): ?>
            <li class="menu-item <?php echo ($module == 'pemotongan') ? 'active' : ''; ?>">
                <a href="<?php echo $moduleLinks['pemotongan']; ?>">
                    <i class="fas fa-cut"></i>
                    <span>Pemotongan</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Vaksinasi -->
            <?php if (isMenuVisible('vaksinasi', $userRole, $menuAccess)): ?>
            <li class="menu-item <?php echo ($module == 'vaksinasi') ? 'active' : ''; ?>">
                <a href="<?php echo $moduleLinks['vaksinasi']; ?>">
                    <i class="fas fa-syringe"></i>
                    <span>Vaksinasi</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Penyakit -->
            <?php if (isMenuVisible('penyakit', $userRole, $menuAccess)): ?>
            <li class="menu-item <?php echo ($module == 'penyakit') ? 'active' : ''; ?>">
                <a href="<?php echo $moduleLinks['penyakit']; ?>">
                    <i class="fas fa-virus"></i>
                    <span>Penyakit</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Logout -->
            <li class="menu-item">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>