<?php
// rentals/view_rentals.php
require_once '../../includes/header.php';

$success = '';
$error = '';

// Handle Status Changes (e.g. Pickup) directly
if (isset($_GET['action']) && isset($_GET['id'])) {
    $rental_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'pickup') {
        // Change status to Picked Up
        $stmt = $conn->prepare("UPDATE rentals SET status = 'Picked Up', balance_pending = 0 WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $rental_id);
            if ($stmt->execute()) {
                $success = "Trousseau marked as Picked Up! Balance pending cleared.";
            } else {
                $error = "Failed to mark as Picked Up.";
            }
            $stmt->close();
        }
    } elseif ($action == 'cancel') {
        // Start transaction to cancel booking and set product back to Available
        $conn->begin_transaction();
        try {
            // Get product ID first
            $p_res = $conn->query("SELECT product_id FROM rentals WHERE id = $rental_id");
            if ($p_res && $p_res->num_rows > 0) {
                $p_id = $p_res->fetch_assoc()['product_id'];
                
                // Cancel rental
                $conn->query("UPDATE rentals SET status = 'Cancelled' WHERE id = $rental_id");
                
                // Set product back to Available
                $conn->query("UPDATE products SET status = 'Available' WHERE id = $p_id");
                
                $conn->commit();
                $success = "Booking cancelled. Garment is now available back in inventory.";
            } else {
                throw new Exception("Rental record not found.");
            }
        } catch(Exception $e) {
            $conn->rollback();
            $error = "Cancellation failed: " . $e->getMessage();
        }
    }
}

// Build Search and Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$q = "SELECT r.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address, 
             p.name as product_name, p.category as product_category, p.size as product_size, p.color as product_color 
      FROM rentals r 
      JOIN customers c ON r.customer_id = c.id 
      JOIN products p ON r.product_id = p.id 
      WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $q .= " AND (c.name LIKE ? OR p.name LIKE ? OR c.phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($status_filter)) {
    $q .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$q .= " ORDER BY r.id DESC";

$stmt = $conn->prepare($q);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($q);
}

// Check if specific invoice modal is requested to load in JS
$invoice_id = isset($_GET['invoice']) ? intval($_GET['invoice']) : 0;
?>

<div class="page-header">
    <div class="page-title">
        <h1>Wedding Bookings Ledger</h1>
        <p>Track groom trials, collect pickup balances, release wedding apparel, and manage returns.</p>
    </div>
    <a href="<?php echo $base_url; ?>/admin/rentals/add_rental.php" class="btn btn-gold">
        <i class="fa-solid fa-calendar-plus"></i> Book Trousseau
    </a>
</div>

<!-- Success and Error Alerts -->
<?php if($success): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 1rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(46, 125, 50, 0.2); font-weight: 600;">
        <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div style="background: var(--danger-bg); color: var(--danger); padding: 1rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(211, 47, 47, 0.2); font-weight: 600;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Filters and Search panel -->
<div class="glass-panel" style="padding: 1.5rem 2rem; margin-bottom: 2rem;">
    <form action="" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="search" style="font-size: 0.85rem; color: var(--text-secondary);"><i class="fa-solid fa-magnifying-glass"></i> Keyword Search</label>
            <input type="text" id="search" name="search" class="form-control" style="padding: 0.8rem 1rem;" placeholder="Search groom name, apparel, phone..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="status" style="font-size: 0.85rem; color: var(--text-secondary);"><i class="fa-solid fa-filter"></i> Booking Status</label>
            <select id="status" name="status" class="form-control" style="padding: 0.8rem 1rem; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23b5924d%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.1em;">
                <option value="">All Bookings</option>
                <option value="Booked" <?php echo ($status_filter == 'Booked') ? 'selected' : ''; ?>>Booked (Trial Pending)</option>
                <option value="Picked Up" <?php echo ($status_filter == 'Picked Up') ? 'selected' : ''; ?>>Picked Up</option>
                <option value="Returned" <?php echo ($status_filter == 'Returned') ? 'selected' : ''; ?>>Returned</option>
                <option value="Cancelled" <?php echo ($status_filter == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.8rem;"><i class="fa-solid fa-filter"></i> Filter</button>
            <a href="view_rentals.php" class="btn btn-outline" style="padding: 0.8rem;" title="Reset filters"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>
</div>

<!-- Bookings Ledger Table -->
<div class="glass-panel table-container">
    <table>
        <thead>
            <tr>
                <th>Receipt ID</th>
                <th>Groom Details</th>
                <th>Apparel Details</th>
                <th>Dates Schedule</th>
                <th>Advance / Security</th>
                <th>Balance Due</th>
                <th>Status</th>
                <th class="no-print" style="text-align: center; width: 220px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $status_text = $row['status'];
                    $status_class = 'status-booked';
                    if ($status_text === 'Picked Up') $status_class = 'status-picked-up';
                    if ($status_text === 'Returned') $status_class = 'status-returned';
                    if ($status_text === 'Cancelled') $status_class = 'status-cancelled';
                    
                    $trial_date_f = date('M. d, Y', strtotime($row['trial_date']));
                    $event_date_f = date('M. d, Y', strtotime($row['event_date']));
                    
                    // Action button logic
                    $action_buttons = "";
                    if ($status_text == 'Booked') {
                        $action_buttons .= "<a href='view_rentals.php?action=pickup&id={$row['id']}' class='btn btn-outline' style='padding: 0.4rem 0.7rem; font-size: 0.75rem; border-color: #3f51b5; color: #3f51b5; border-radius: 6px; font-weight: 700;' title='Confirm Pickup'><i class='fa-solid fa-truck-ramp-box'></i> Pickup</a> ";
                        $action_buttons .= "<a href='return_product.php?id={$row['id']}' class='btn btn-gold' style='padding: 0.4rem 0.7rem; font-size: 0.75rem; border-radius: 6px; font-weight: 700;' title='Process Return'><i class='fa-solid fa-rotate-left'></i> Return</a> ";
                        $action_buttons .= "<a href='view_rentals.php?action=cancel&id={$row['id']}' class='btn btn-danger' style='padding: 0.4rem 0.7rem; font-size: 0.75rem; border-radius: 6px; font-weight: 700;' title='Cancel' onclick='return confirm(\"Cancel this booking?\");'><i class='fa-solid fa-ban'></i></a>";
                    } elseif ($status_text == 'Picked Up') {
                        $action_buttons .= "<a href='return_product.php?id={$row['id']}' class='btn btn-gold' style='padding: 0.4rem 0.7rem; font-size: 0.75rem; border-radius: 6px; font-weight: 700;'><i class='fa-solid fa-rotate-left'></i> Return & Refund</a> ";
                    } else {
                        $action_buttons .= "<span style='color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; font-style: italic;'>Completed Ledger</span>";
                    }
                    
                    echo "<tr>
                            <td style='font-weight: 700;'>
                                <a href='view_rentals.php?invoice={$row['id']}' style='color: var(--gold-color); text-decoration: none;' title='View Invoice'>
                                    #DC-00{$row['id']} <i class='fa-solid fa-up-right-from-square' style='font-size: 0.7rem;'></i>
                                </a>
                            </td>
                            <td>
                                <div style='font-weight: 700; color: var(--accent-color);'>{$row['customer_name']}</div>
                                <span style='font-size: 0.78rem; color: var(--text-secondary); font-weight: 600;'><i class='fa-solid fa-phone'></i> {$row['customer_phone']}</span>
                            </td>
                            <td>
                                <div style='font-weight: 600; font-size: 0.92rem;'>{$row['product_name']}</div>
                                <span style='font-size: 0.75rem; color: var(--text-secondary);'>[{$row['product_category']}] Size: {$row['product_size']} | {$row['product_color']}</span>
                            </td>
                            <td style='font-size: 0.85rem;'>
                                <div>Trial: <strong>{$trial_date_f}</strong></div>
                                <div style='color: var(--gold-color); font-weight: 600;'>Event: <strong>{$event_date_f}</strong></div>
                            </td>
                            <td>
                                <div>Adv: ₹" . number_format($row['advance_paid'], 0) . "</div>
                                <div style='font-size: 0.78rem; color: var(--text-secondary);'>Sec: ₹" . number_format($row['security_deposit_paid'], 0) . "</div>
                            </td>
                            <td style='font-weight: 700; color: " . ($row['balance_pending'] > 0 ? 'var(--danger)' : 'var(--success)') . ";'>
                                ₹" . number_format($row['balance_pending'], 2) . "
                            </td>
                            <td><span class='status-badge $status_class'>$status_text</span></td>
                            <td class='no-print' style='text-align: right;'>
                                {$action_buttons}
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center text-secondary' style='padding: 3rem;'>No wedding wear bookings matches your parameters.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- ============================================== -->
<!-- LUXURY PRINTABLE INVOICE MODAL -->
<!-- ============================================== -->
<div class="modal-overlay <?php echo ($invoice_id > 0) ? 'active' : ''; ?>" id="invoiceModalOverlay">
    <div class="modal print-invoice" style="max-width: 650px;">
        <div class="modal-header no-print">
            <h3 style="color: var(--accent-color);"><i class="fa-solid fa-crown text-gold"></i> Royal Billing Receipt</h3>
            <button class="close-btn" id="closeInvoiceBtn">&times;</button>
        </div>
        
        <?php
        if ($invoice_id > 0) {
            $invoice_q = "SELECT r.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address, 
                                 p.name as product_name, p.category as product_category, p.size as product_size, p.color as product_color 
                          FROM rentals r 
                          JOIN customers c ON r.customer_id = c.id 
                          JOIN products p ON r.product_id = p.id 
                          WHERE r.id = $invoice_id LIMIT 1";
            $invoice_res = $conn->query($invoice_q);
            if ($invoice_res && $invoice_res->num_rows > 0) {
                $inv = $invoice_res->fetch_assoc();
                $booking_date_formatted = date('M. d, Y', strtotime($inv['created_at']));
                $trial_formatted = date('D, M. d, Y', strtotime($inv['trial_date']));
                $event_formatted = date('D, M. d, Y', strtotime($inv['event_date']));
                $pickup_formatted = date('D, M. d, Y', strtotime($inv['pickup_date']));
                $return_formatted = date('D, M. d, Y', strtotime($inv['return_date']));
                ?>
                
                <div class="invoice-box" id="printArea">
                    <!-- Invoice Header Brand -->
                    <div class="invoice-header">
                        <div>
                            <h1 style="color: var(--accent-color); font-size: 1.8rem; font-weight: 700; margin-bottom: 0.2rem;">DULHA COLLECTION</h1>
                            <p style="color: var(--gold-color); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 0.5rem;">Bespoke Groom Wear Rentals</p>
                            <p style="font-size: 0.78rem; color: var(--text-secondary);">Premium Wedding Wear & Accessories</p>
                        </div>
                        <div style="text-align: right;">
                            <h2 style="color: var(--accent-color); font-size: 1.25rem; font-weight: 700; margin-bottom: 0.2rem;">RECEIPT</h2>
                            <p style="font-size: 0.85rem; font-weight: 700; color: var(--gold-color);">Invoice: #DC-00<?php echo $inv['id']; ?></p>
                            <p style="font-size: 0.78rem; color: var(--text-secondary);">Date: <?php echo $booking_date_formatted; ?></p>
                        </div>
                    </div>
                    
                    <!-- Customer Details Panel -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; font-size: 0.88rem;">
                        <div>
                            <h4 style="color: var(--accent-color); border-bottom: 1px solid #ebdccb; padding-bottom: 0.3rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Billed To (Groom)</h4>
                            <strong><?php echo htmlspecialchars($inv['customer_name']); ?></strong><br>
                            Phone: <?php echo htmlspecialchars($inv['customer_phone']); ?><br>
                            Email: <?php echo htmlspecialchars($inv['customer_email'] ?: 'N/A'); ?><br>
                            Address: <?php echo htmlspecialchars($inv['customer_address'] ?: 'N/A'); ?>
                        </div>
                        <div style="text-align: right;">
                            <h4 style="color: var(--accent-color); border-bottom: 1px solid #ebdccb; padding-bottom: 0.3rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Bespoke fitting details</h4>
                            Trial Date: <strong><?php echo $trial_formatted; ?></strong><br>
                            Wedding Event: <strong style="color: var(--accent-color);"><?php echo $event_formatted; ?></strong><br>
                            Pickup Scheduled: <?php echo $pickup_formatted; ?><br>
                            Return Due: <?php echo $return_formatted; ?>
                        </div>
                    </div>
                    
                    <!-- Alteration Notes Box -->
                    <div style="background: #faf7f2; border: 1px dashed var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.85rem;">
                        <strong style="color: var(--accent-color);"><i class="fa-solid fa-scissors"></i> Alteration Instructions for Tailor:</strong>
                        <p style="margin-top: 0.4rem; color: var(--text-primary); font-style: italic; line-height: 1.4;">
                            <?php echo !empty($inv['alteration_notes']) ? htmlspecialchars($inv['alteration_notes']) : "Standard size fit - no custom fitting alterations required."; ?>
                        </p>
                    </div>

                    <!-- Items Detail Table -->
                    <table class="invoice-table" style="font-size: 0.88rem;">
                        <thead>
                            <tr>
                                <th style="padding: 0.6rem 1rem;">Garment / Category</th>
                                <th style="padding: 0.6rem 1rem;">Color Spec</th>
                                <th style="padding: 0.6rem 1rem; text-align: center;">Size</th>
                                <th style="padding: 0.6rem 1rem; text-align: right;">Rental Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 0.8rem 1rem; font-weight: 700; border-bottom: 1.5px solid var(--border-color);">
                                    <?php echo htmlspecialchars($inv['product_name']); ?><br>
                                    <span style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 600;">Category: <?php echo $inv['product_category']; ?></span>
                                </td>
                                <td style="padding: 0.8rem 1rem; border-bottom: 1.5px solid var(--border-color);"><?php echo htmlspecialchars($inv['product_color']); ?></td>
                                <td style="padding: 0.8rem 1rem; text-align: center; border-bottom: 1.5px solid var(--border-color); font-weight: 700;">Size <?php echo $inv['product_size']; ?></td>
                                <td style="padding: 0.8rem 1rem; text-align: right; border-bottom: 1.5px solid var(--border-color); font-weight: 700;">₹<?php echo number_format($inv['rental_price'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Financial Ledger splits -->
                    <div style="display: flex; justify-content: flex-end; font-size: 0.9rem; margin-bottom: 2rem;">
                        <div style="width: 250px; line-height: 1.8;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Rental Charges:</span>
                                <strong>₹<?php echo number_format($inv['rental_price'], 2); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #ebdccb; padding-bottom: 0.3rem;">
                                <span>Security Deposit:</span>
                                <strong>₹<?php echo number_format($inv['security_deposit_paid'], 2); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; color: var(--success); font-weight: 700;">
                                <span>Advance Paid:</span>
                                <span>-₹<?php echo number_format($inv['advance_paid'], 2); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 2px solid var(--accent-color); padding-top: 0.4rem; font-size: 1.05rem; font-weight: 800; color: var(--accent-color);">
                                <span>Balance Pending:</span>
                                <span>₹<?php echo number_format($inv['balance_pending'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Invoice Footer -->
                    <div class="invoice-footer">
                        <crown-icon style="font-size: 1.2rem; display: block; margin-bottom: 0.4rem; color: var(--gold-color);">👑</crown-icon>
                        <p style="font-weight: 700; margin-bottom: 0.2rem; color: var(--accent-color);">Thank you for choosing Dulha Collection!</p>
                        <p style="font-size: 0.75rem; font-weight: 500;">Please preserve this receipt for trousseau pickup and deposit refund processing.</p>
                    </div>
                </div>
                
                <!-- Print Triggers (modal action only) -->
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;" class="no-print">
                    <button class="btn btn-outline" id="modalCloseAction"><i class="fa-solid fa-xmark"></i> Close</button>
                    <button class="btn btn-gold" onclick="window.print();"><i class="fa-solid fa-print"></i> Print Receipt</button>
                </div>
                
                <?php
            }
        } else {
            echo "<p class='text-center text-secondary no-print'>Select a receipt to load invoice billing panel.</p>";
        }
        ?>
    </div>
</div>

<!-- JavaScript to control the luxury receipt modal overlay dynamically -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('invoiceModalOverlay');
    const closeBtn = document.getElementById('closeInvoiceBtn');
    const closeAction = document.getElementById('modalCloseAction');
    
    function closeModal() {
        modalOverlay.classList.remove('active');
        // Clear query parameter to clean URL
        window.history.pushState({}, document.title, window.location.pathname + window.location.search.replace(/&?invoice=\d+/g, ''));
    }
    
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (closeAction) closeAction.addEventListener('click', closeModal);
    
    // Close on overlay clicking
    modalOverlay.addEventListener('click', function(e) {
        if(e.target === modalOverlay) {
            closeModal();
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
