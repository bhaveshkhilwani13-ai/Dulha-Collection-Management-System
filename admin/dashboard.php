<?php
// dashboard.php
require_once '../includes/header.php';

// Fetch operational summary stats
$p_res = $conn->query("SELECT COUNT(*) as c FROM products");
$products_count = $p_res ? $p_res->fetch_assoc()['c'] : 0;

$c_res = $conn->query("SELECT COUNT(*) as c FROM customers");
$customers_count = $c_res ? $c_res->fetch_assoc()['c'] : 0;

$active_res = $conn->query("SELECT COUNT(*) as c FROM rentals WHERE status IN ('Booked', 'Picked Up')");
$active_bookings = $active_res ? $active_res->fetch_assoc()['c'] : 0;

// Wedding specific fitting metrics: Trials scheduled for today or coming up!
$today_str = date('Y-m-d');
$trial_res = $conn->query("SELECT COUNT(*) as c FROM rentals WHERE status = 'Booked' AND trial_date >= '$today_str'");
$pending_trials = $trial_res ? $trial_res->fetch_assoc()['c'] : 0;

// Financial metrics
$rev_res = $conn->query("SELECT SUM(rental_price) as total FROM rentals WHERE status != 'Cancelled'");
$total_revenue = $rev_res ? $rev_res->fetch_assoc()['total'] : 0;
?>

<div class="page-header">
    <div class="page-title">
        <h1>Dashboard Command Center</h1>
        <p>Operational review of weddings, apparel scheduling, and bespoke fittings.</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="<?php echo $base_url; ?>/admin/rentals/add_rental.php" class="btn btn-primary">
            <i class="fa-solid fa-calendar-plus"></i> Book Trousseau
        </a>
        <a href="<?php echo $base_url; ?>/admin/products/add_product.php" class="btn btn-outline">
            <i class="fa-solid fa-shirt"></i> Add Apparel
        </a>
    </div>
</div>

<!-- Modern Metric Snapshot Grid -->
<section class="dashboard-grid">
    <div class="stat-card glass-panel">
        <div class="stat-icon" style="background: #faf6f0; color: var(--accent-color);">
            <i class="fa-solid fa-shirt"></i>
        </div>
        <div class="stat-info">
            <h3>Apparel Items</h3>
            <p><?php echo $products_count; ?></p>
        </div>
    </div>
    
    <div class="stat-card glass-panel">
        <div class="stat-icon" style="background: #eef2ff; color: #3f51b5;">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="stat-info">
            <h3>Registered Grooms</h3>
            <p><?php echo $customers_count; ?></p>
        </div>
    </div>

    <div class="stat-card glass-panel">
        <div class="stat-icon" style="background: #e0f2f1; color: #00796b;">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div class="stat-info">
            <h3>Active Bookings</h3>
            <p><?php echo $active_bookings; ?></p>
        </div>
    </div>

    <div class="stat-card glass-panel">
        <div class="stat-icon" style="background: #fff8e1; color: #ff8f00;">
            <i class="fa-solid fa-scissors"></i>
        </div>
        <div class="stat-info">
            <h3>Fitting Trials</h3>
            <p><?php echo $pending_trials; ?></p>
        </div>
    </div>

    <div class="stat-card glass-panel">
        <div class="stat-icon" style="background: var(--success-bg); color: var(--success);">
            <i class="fa-solid fa-indian-rupee-sign"></i>
        </div>
        <div class="stat-info">
            <h3>Rental Revenue</h3>
            <p>₹<?php echo number_format($total_revenue ?? 0, 2); ?></p>
        </div>
    </div>
</section>

<!-- Split view for Bookings and Trials schedules -->
<div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    
    <!-- Recent Rentals Panel -->
    <div class="glass-panel table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="color: var(--accent-color); font-size: 1.3rem; font-weight: 700;">
                <i class="fa-solid fa-clock-rotate-left" style="margin-right: 0.5rem; color: var(--gold-color);"></i> Recent Booking Requests
            </h2>
            <a href="<?php echo $base_url; ?>/rentals/view_rentals.php" style="color: var(--gold-color); font-weight: 700; text-decoration: none; font-size: 0.9rem;">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Groom</th>
                    <th>Apparel</th>
                    <th>Wedding Event</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = "SELECT r.id, r.event_date, r.status, c.name as customer_name, p.name as product_name 
                      FROM rentals r 
                      JOIN customers c ON r.customer_id = c.id 
                      JOIN products p ON r.product_id = p.id 
                      ORDER BY r.id DESC LIMIT 5";
                $res = $conn->query($q);
                if ($res && $res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        $status_text = $row['status'];
                        // Standardize class based on custom statuses
                        $status_class = 'status-booked';
                        if ($status_text === 'Picked Up') $status_class = 'status-picked-up';
                        if ($status_text === 'Returned') $status_class = 'status-returned';
                        if ($status_text === 'Cancelled') $status_class = 'status-cancelled';
                        
                        $event_date = date('M. d, Y', strtotime($row['event_date']));
                        
                        echo "<tr>
                                <td style='font-weight: 600;'>{$row['customer_name']}</td>
                                <td style='color: var(--text-secondary);'>{$row['product_name']}</td>
                                <td><i class='fa-regular fa-calendar-days text-gold' style='margin-right: 0.4rem;'></i> {$event_date}</td>
                                <td><span class='status-badge $status_class'>{$status_text}</span></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-secondary'>No recent wedding bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Upcoming Tailoring Trials schedules -->
    <div class="glass-panel" style="padding: 2rem; border: 1px solid var(--border-color); display: flex; flex-direction: column;">
        <h2 style="color: var(--accent-color); font-size: 1.3rem; font-weight: 700; margin-bottom: 1.5rem;">
            <i class="fa-solid fa-scissors" style="margin-right: 0.5rem; color: var(--gold-color);"></i> Tailor Fit Trials
        </h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem; overflow-y: auto; max-height: 320px; padding-right: 0.5rem;">
            <?php
            $trial_q = "SELECT r.trial_date, r.alteration_notes, c.name as customer_name, p.name as product_name, p.size
                        FROM rentals r 
                        JOIN customers c ON r.customer_id = c.id 
                        JOIN products p ON r.product_id = p.id 
                        WHERE r.status = 'Booked' AND r.trial_date >= '$today_str'
                        ORDER BY r.trial_date ASC LIMIT 4";
            $trial_res = $conn->query($trial_q);
            if ($trial_res && $trial_res->num_rows > 0) {
                while($t_row = $trial_res->fetch_assoc()) {
                    $trial_date_formatted = date('D, M. d, Y', strtotime($t_row['trial_date']));
                    $notes = !empty($t_row['alteration_notes']) ? htmlspecialchars($t_row['alteration_notes']) : "Standard size fit - no alterations specified.";
                    
                    echo "<div style='background: #fffcf8; border-left: 4px solid var(--gold-color); padding: 1.2rem; border-radius: 8px; border-top: 1px solid #ebdccb; border-right: 1px solid #ebdccb; border-bottom: 1px solid #ebdccb;'>
                            <div style='display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem;'>
                                <strong style='font-size: 0.95rem; color: var(--accent-color);'>{$t_row['customer_name']}</strong>
                                <span style='font-size: 0.8rem; background: var(--accent-color); color: #fff; padding: 0.2rem 0.6rem; border-radius: 4px; font-weight: 600;'>Size {$t_row['size']}</span>
                            </div>
                            <p style='font-size: 0.88rem; color: var(--text-primary); margin-bottom: 0.4rem; font-weight: 600;'>{$t_row['product_name']}</p>
                            <div style='display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.6rem;'>
                                <i class='fa-regular fa-clock text-gold'></i>
                                <span>Fitting Date: <strong>{$trial_date_formatted}</strong></span>
                            </div>
                            <div style='font-size: 0.8rem; background: #faf7f2; padding: 0.5rem; border-radius: 6px; border: 1px dashed #ebdccb; color: var(--text-secondary); line-height: 1.3;'>
                                <i class='fa-solid fa-pencil' style='font-size: 0.72rem; margin-right: 0.2rem;'></i> {$notes}
                            </div>
                          </div>";
                }
            } else {
                echo "<div style='text-align: center; color: var(--text-secondary); margin: auto; padding: 2rem;'>
                        <i class='fa-regular fa-calendar-check' style='font-size: 2.2rem; color: var(--border-color); margin-bottom: 0.8rem; display: block;'></i>
                        No pending tailor fitting trials scheduled.
                      </div>";
            }
            ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
