<?php
// includes/header.php
require_once __DIR__ . '/db.php';
check_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulha Collection - Groom Wear Rental Management</title>
    <!-- Use FontAwesome for beautiful, luxury system icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Style CSS Sheet -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Include Sidebar lateral navigation -->
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Panel Body -->
        <main class="main-content">
            <!-- Top Elegant Bar -->
            <header class="top-header glass-panel" style="display: flex; justify-content: space-between; padding: 1.2rem 2.5rem; margin-bottom: 2.5rem; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.8rem;">
                    <i class="fa-solid fa-gem text-gold" style="font-size: 1.2rem;"></i>
                    <span style="font-weight: 600; font-size: 0.95rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px;">
                        Luxury Groom's Couture Ledger
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <span style="color: var(--text-secondary); font-size: 0.95rem;">
                        <i class="fa-regular fa-circle-user text-accent" style="margin-right: 0.4rem;"></i>
                        Logged in as <strong style="color: var(--text-primary); font-weight: 700;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
                    </span>
                    <a href="<?php echo $base_url; ?>/admin/logout.php" class="btn btn-danger" style="padding: 0.5rem 1.2rem; font-size: 0.85rem; border-radius: 8px;">
                        <i class="fa-solid fa-power-off"></i> Logout
                    </a>
                </div>
            </header>
