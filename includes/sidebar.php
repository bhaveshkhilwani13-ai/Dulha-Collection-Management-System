<?php
// includes/sidebar.php
$current_page = $_SERVER['SCRIPT_NAME'];
?>
<aside class="sidebar">
    <div class="logo-container">
        <div class="logo-icon">D</div>
        <div class="logo-text">
            <span class="main-title">Dulha Collection</span>
            <span class="sub-title">Groom Wear Rentals</span>
        </div>
    </div>
    
    <nav>
        <ul class="nav-menu">
            <li>
                <a href="<?php echo $base_url; ?>/admin/dashboard.php" 
                   class="<?php echo (strpos($current_page, 'dashboard.php') !== false) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>/admin/products/view_products.php" 
                   class="<?php echo (strpos($current_page, 'products/') !== false) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-shirt"></i> Apparel Inventory
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>/admin/customers/view_customers.php" 
                   class="<?php echo (strpos($current_page, 'customers/') !== false) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i> Grooms Ledger
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>/admin/rentals/view_rentals.php" 
                   class="<?php echo (strpos($current_page, 'rentals/') !== false) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-calendar-check"></i> Wedding Bookings
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>/admin/reports/report.php" 
                   class="<?php echo (strpos($current_page, 'reports/') !== false) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie"></i> Business Reports
                </a>
            </li>
        </ul>
    </nav>
    
    <div style="margin-top: auto; padding: 1.5rem; background: #faf7f2; border-radius: 12px; border: 1px solid var(--border-color); text-align: center;">
        <i class="fa-solid fa-crown text-gold" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
        <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--accent-color); margin-bottom: 0.2rem;">ROYAL EDITION</h4>
        <p style="font-size: 0.72rem; color: var(--text-secondary); font-weight: 600;">Secure Admin Portal</p>
    </div>
</aside>
