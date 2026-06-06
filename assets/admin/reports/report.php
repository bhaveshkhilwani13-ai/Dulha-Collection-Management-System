<?php
// reports/report.php
require_once '../../includes/header.php';

// 1. General Financial totals
$rev_res = $conn->query("SELECT SUM(rental_price) as total_revenue, SUM(advance_paid) as total_advance, SUM(balance_pending) as total_balance FROM rentals WHERE status != 'Cancelled'");
$fin = $rev_res ? $rev_res->fetch_assoc() : null;

$total_rev = $fin['total_revenue'] ?? 0;
$total_adv = $fin['total_advance'] ?? 0;
$total_bal = $fin['total_balance'] ?? 0;

// Security deposit held currently
$dep_res = $conn->query("SELECT SUM(security_deposit_paid) as total_held FROM rentals WHERE status IN ('Booked', 'Picked Up')");
$dep_held = $dep_res ? $dep_res->fetch_assoc()['total_held'] : 0;

// 2. Count rentals by Category
$cat_q = "SELECT p.category, COUNT(r.id) as count, SUM(r.rental_price) as revenue 
          FROM rentals r 
          JOIN products p ON r.product_id = p.id 
          WHERE r.status != 'Cancelled' 
          GROUP BY p.category 
          ORDER BY count DESC";
$cat_res = $conn->query($cat_q);
$categories = [];
$max_count = 1;
if ($cat_res && $cat_res->num_rows > 0) {
    while($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
        if($row['count'] > $max_count) $max_count = $row['count'];
    }
}

// 3. Most Popular Outfits (Top 5 rented items)
$pop_q = "SELECT p.name, p.category, p.size, COUNT(r.id) as count 
          FROM rentals r 
          JOIN products p ON r.product_id = p.id 
          WHERE r.status != 'Cancelled' 
          GROUP BY p.id 
          ORDER BY count DESC LIMIT 5";
$pop_res = $conn->query($pop_q);
?>

<div class="page-header">
    <div class="page-title">
        <h1>Business Analytics & Reports</h1>
        <p>Analyze trousseau revenues, apparel category demand, popular designer items, and fit trials.</p>
    </div>
    <button onclick="window.print();" class="btn btn-outline">
        <i class="fa-solid fa-print"></i> Print Report
    </button>
</div>

<!-- Financial Summary Cards -->
<section class="dashboard-grid" style="margin-bottom: 3rem;">
    <div class="stat-card glass-panel" style="border-top: 4px solid var(--accent-color);">
        <div class="stat-icon" style="background: #faf6f0; color: var(--accent-color);">
            <i class="fa-solid fa-indian-rupee-sign"></i>
        </div>
        <div class="stat-info">
            <h3>Bespoke Bookings Revenue</h3>
            <p>₹<?php echo number_format($total_rev, 2); ?></p>
        </div>
    </div>
    
    <div class="stat-card glass-panel" style="border-top: 4px solid var(--gold-color);">
        <div class="stat-icon" style="background: #fff8e1; color: var(--gold-color);">
            <i class="fa-solid fa-vault"></i>
        </div>
        <div class="stat-info">
            <h3>Security Deposit Held</h3>
            <p>₹<?php echo number_format($dep_held ?? 0, 2); ?></p>
        </div>
    </div>

    <div class="stat-card glass-panel" style="border-top: 4px solid var(--success);">
        <div class="stat-icon" style="background: var(--success-bg); color: var(--success);">
            <i class="fa-solid fa-money-bill-trend-up"></i>
        </div>
        <div class="stat-info">
            <h3>Advance Collected</h3>
            <p>₹<?php echo number_format($total_adv, 2); ?></p>
        </div>
    </div>

    <div class="stat-card glass-panel" style="border-top: 4px solid var(--danger);">
        <div class="stat-icon" style="background: var(--danger-bg); color: var(--danger);">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <div class="stat-info">
            <h3>Outstanding Pending Balances</h3>
            <p>₹<?php echo number_format($total_bal, 2); ?></p>
        </div>
    </div>
</section>

<!-- Dual layouts for Demand graphs and Popular items -->
<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    
    <!-- Category Demand charts -->
    <div class="glass-panel" style="padding: 2.5rem; display: flex; flex-direction: column;">
        <h2 style="color: var(--accent-color); font-size: 1.3rem; font-weight: 700; margin-bottom: 1.8rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.6rem;">
            <i class="fa-solid fa-chart-bar text-gold" style="margin-right: 0.5rem;"></i> Category Volume & Demand Analysis
        </h2>
        
        <div style="display: flex; flex-direction: column; gap: 1.8rem; justify-content: center; flex: 1;">
            <?php if (!empty($categories)): ?>
                <?php foreach($categories as $cat): 
                    $percentage = round(($cat['count'] / $max_count) * 100);
                    ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.4rem;">
                            <span><?php echo htmlspecialchars($cat['category']); ?></span>
                            <span style="color: var(--text-secondary);"><?php echo $cat['count']; ?> Rentals (₹<?php echo number_format($cat['revenue'], 0); ?>)</span>
                        </div>
                        
                        <!-- Premium Self-contained Progress Bar -->
                        <div style="width: 100%; height: 12px; background: #faf7f2; border: 1px solid var(--border-color); border-radius: 30px; overflow: hidden; position: relative;">
                            <div style="width: <?php echo $percentage; ?>%; height: 100%; background: linear-gradient(90deg, var(--accent-color) 0%, var(--gold-color) 100%); border-radius: 30px; transition: width 0.8s ease;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-secondary">No booking statistics registered yet to render analytics charts.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Most Popular Apparel Catalog -->
    <div class="glass-panel table-container">
        <h2 style="color: var(--accent-color); font-size: 1.3rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.6rem;">
            <i class="fa-solid fa-fire text-gold" style="margin-right: 0.5rem;"></i> Popular Designer Items (Top 5)
        </h2>
        
        <table>
            <thead>
                <tr>
                    <th>Outfit Name</th>
                    <th>Category</th>
                    <th style="text-align: center;">Size</th>
                    <th style="text-align: center;">Booked Times</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($pop_res && $pop_res->num_rows > 0) {
                    while($row = $pop_res->fetch_assoc()) {
                        echo "<tr>
                                <td style='font-weight: 700; color: var(--accent-color);'>{$row['name']}</td>
                                <td style='font-weight: 600; color: var(--text-secondary);'>{$row['category']}</td>
                                <td style='text-align: center;'><span style='background: #fffcf8; border: 1px solid #ebdccb; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.85rem;'>Size {$row['size']}</span></td>
                                <td style='text-align: center; font-weight: 800; color: var(--gold-color); font-size: 1.1rem;'>{$row['count']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center text-secondary' style='padding: 2rem;'>No popular items found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scheduled trials calendar registry -->
<div class="glass-panel table-container" style="margin-bottom: 2rem;">
    <h2 style="color: var(--accent-color); font-size: 1.3rem; font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.6rem;">
        <i class="fa-solid fa-scissors text-gold" style="margin-right: 0.5rem;"></i> Master Tailor Trials Schedule (Upcoming)
    </h2>
    <table>
        <thead>
            <tr>
                <th>Fit Trial Date</th>
                <th>Groom Name</th>
                <th>Apparel Detail</th>
                <th>Wedding Date</th>
                <th>Bespoke Alteration Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $today_str = date('Y-m-d');
            $sch_q = "SELECT r.trial_date, r.event_date, r.alteration_notes, c.name as customer_name, p.name as product_name, p.size
                      FROM rentals r 
                      JOIN customers c ON r.customer_id = c.id 
                      JOIN products p ON r.product_id = p.id 
                      WHERE r.status = 'Booked' AND r.trial_date >= '$today_str'
                      ORDER BY r.trial_date ASC LIMIT 10";
            $sch_res = $conn->query($sch_q);
            if ($sch_res && $sch_res->num_rows > 0) {
                while($row = $sch_res->fetch_assoc()) {
                    $trial_f = date('D, M. d, Y', strtotime($row['trial_date']));
                    $event_f = date('M. d, Y', strtotime($row['event_date']));
                    $notes = !empty($row['alteration_notes']) ? htmlspecialchars($row['alteration_notes']) : "<span style='color: var(--text-secondary); font-style: italic;'>Standard fit - no custom modifications.</span>";
                    
                    echo "<tr>
                            <td style='font-weight: 700;'><i class='fa-regular fa-calendar-check text-gold' style='margin-right: 0.4rem;'></i> {$trial_f}</td>
                            <td style='font-weight: 600; color: var(--accent-color);'>{$row['customer_name']}</td>
                            <td>
                                <strong>{$row['product_name']}</strong>
                                <span style='font-size: 0.78rem; background: var(--accent-color); color: #fff; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600; margin-left: 0.4rem;'>Size {$row['size']}</span>
                            </td>
                            <td>{$event_f}</td>
                            <td style='font-size: 0.88rem; max-width: 300px; background: #fffcf8; border-left: 3px solid var(--gold-color); padding-left: 1rem;'>{$notes}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center text-secondary' style='padding: 2rem;'>No upcoming tailor fitting trials scheduled.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
