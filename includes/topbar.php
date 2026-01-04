<?php
// includes/topbar.php
$user = getUserInfo();
?>
<div class="topbar">
    <div class="topbar-left">
        <div class="search-bar">
            <!-- <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Cari data..."> -->
        </div>
    </div>
    
    <div class="topbar-right">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
            </div>
        </div>
        
        <div class="notifications">
            <!-- <i class="fas fa-bell"></i>
            <span class="notification-count">3</span> -->
        </div>
    </div>
</div>