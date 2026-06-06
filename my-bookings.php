<?php
// my-bookings.php - Customer Booking History & Bill Download
require_once 'includes/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id   = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

$stmt = $conn->prepare("
    SELECT r.*, p.name as product_name, p.category, p.size, p.color
    FROM rentals r
    JOIN products p ON r.product_id = p.id
    WHERE r.customer_id = ?
    ORDER BY r.id DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();
?>
<?php include 'includes/user_header.php'; ?>

<div style="padding: 4rem 5%;">
    <div class="page-header" style="margin-bottom: 3rem;">
        <div class="page-title">
            <h1 class="brand-heading" style="font-size: 2.5rem; color: var(--accent-color);">My Bookings</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($customer_name); ?></strong>. Here are all your rental bookings.</p>
        </div>
        <a href="catalog.php" class="btn btn-gold" style="color: white;">
            <i class="fa-solid fa-shirt"></i> Browse Catalog
        </a>
    </div>

    <?php if ($bookings && $bookings->num_rows > 0): ?>
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php while($b = $bookings->fetch_assoc()):
                $status_class = 'status-booked';
                if ($b['status'] === 'Picked Up') $status_class = 'status-picked-up';
                if ($b['status'] === 'Returned')  $status_class = 'status-returned';
                if ($b['status'] === 'Cancelled') $status_class = 'status-cancelled';
            ?>
            <div class="glass-panel" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.8rem;">
                            <span style="font-size: 1.1rem; font-weight: 800; color: var(--gold-color);">#DC-00<?php echo $b['id']; ?></span>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $b['status']; ?></span>
                        </div>
                        <h3 style="font-size: 1.3rem; color: var(--accent-color); margin-bottom: 0.4rem;"><?php echo htmlspecialchars($b['product_name']); ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.9rem; font-weight: 600;">
                            <?php echo $b['category']; ?> &bull; Size <?php echo $b['size']; ?> &bull; <?php echo htmlspecialchars($b['color']); ?>
                        </p>
                    </div>
                    <a href="invoice.php?id=<?php echo $b['id']; ?>" class="btn btn-outline" style="padding: 0.6rem 1.4rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-file-invoice"></i> View Bill / Invoice
                    </a>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Trial/Fitting Date</span>
                        <p style="font-weight: 700; color: var(--text-primary);"><?php echo date('M d, Y', strtotime($b['trial_date'])); ?></p>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Wedding Date</span>
                        <p style="font-weight: 700; color: var(--accent-color);"><?php echo date('M d, Y', strtotime($b['event_date'])); ?></p>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Pickup Date</span>
                        <p style="font-weight: 700;"><?php echo date('M d, Y', strtotime($b['pickup_date'])); ?></p>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Return Due</span>
                        <p style="font-weight: 700;"><?php echo date('M d, Y', strtotime($b['return_date'])); ?></p>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Rental Price</span>
                        <p style="font-weight: 800; font-size: 1.1rem;">₹<?php echo number_format($b['rental_price'], 2); ?></p>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Balance Due</span>
                        <p style="font-weight: 800; font-size: 1.1rem; color: <?php echo $b['balance_pending'] > 0 ? 'var(--danger)' : 'var(--success)'; ?>;">
                            ₹<?php echo number_format($b['balance_pending'], 2); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 6rem 2rem; background: white; border-radius: 24px; border: 1px dashed var(--border-color);">
            <i class="fa-solid fa-calendar-xmark" style="font-size: 4rem; color: var(--border-color); margin-bottom: 1.5rem; display: block;"></i>
            <h3 style="color: var(--text-secondary); font-size: 1.5rem; margin-bottom: 1rem;">No Bookings Yet</h3>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">Browse our luxury collection to find your perfect wedding look.</p>
            <a href="catalog.php" class="btn btn-gold" style="color: white;">Explore the Catalog</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/user_footer.php'; ?>
