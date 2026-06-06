<?php
// invoice.php - User-side Printable Invoice/Bill
require_once 'includes/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$rental_id   = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($rental_id <= 0) {
    header("Location: my-bookings.php");
    exit();
}

// Fetch rental - ensure it belongs to this customer
$stmt = $conn->prepare("
    SELECT r.*, 
           c.name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address,
           p.name as product_name, p.category as product_category, p.size as product_size, p.color as product_color
    FROM rentals r
    JOIN customers c ON r.customer_id = c.id
    JOIN products p ON r.product_id = p.id
    WHERE r.id = ? AND r.customer_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $rental_id, $customer_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$inv) {
    header("Location: my-bookings.php");
    exit();
}

$booking_date = date('M d, Y', strtotime($inv['created_at']));
$trial_date   = date('D, M d, Y', strtotime($inv['trial_date']));
$event_date   = date('D, M d, Y', strtotime($inv['event_date']));
$pickup_date  = date('D, M d, Y', strtotime($inv['pickup_date']));
$return_date  = date('D, M d, Y', strtotime($inv['return_date']));

$status_class = 'status-booked';
if ($inv['status'] === 'Picked Up') $status_class = 'status-picked-up';
if ($inv['status'] === 'Returned')  $status_class = 'status-returned';
if ($inv['status'] === 'Cancelled') $status_class = 'status-cancelled';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #DC-00<?php echo $inv['id']; ?> - Dulha Collection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <style>
        body { background: #f5efe6; }
        .invoice-page { max-width: 800px; margin: 3rem auto; padding: 0 1.5rem; }
        .invoice-actions { display: flex; gap: 1rem; margin-bottom: 2rem; }
        @media print {
            .invoice-actions { display: none !important; }
            body { background: #fff; }
            .invoice-page { margin: 0; padding: 0; max-width: 100%; }
            .invoice-box { box-shadow: none; border: none; }
        }
        .invoice-box {
            background: #fff;
            border-radius: 20px;
            padding: 3.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 60px rgba(88,17,26,0.08);
        }
        .inv-divider { border: none; border-top: 1px solid #ebdccb; margin: 1.8rem 0; }
    </style>
</head>
<body>
    <div class="invoice-page">
        <div class="invoice-actions">
            <a href="my-bookings.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> My Bookings</a>
            <button onclick="window.print();" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print / Download</button>
        </div>

        <div class="invoice-box">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid var(--accent-color); padding-bottom: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <div class="logo-icon" style="width:50px;height:50px;font-size:1.5rem;border-radius:14px;">D</div>
                        <div>
                            <h1 style="font-size: 1.8rem; color: var(--accent-color); margin: 0;">DULHA COLLECTION</h1>
                            <p style="color: var(--gold-color); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 3px;">Bespoke Groom Wear Rentals</p>
                        </div>
                    </div>
                    <p style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 0.5rem;">
                        <i class="fa-solid fa-location-dot" style="color: var(--gold-color); margin-right: 0.4rem;"></i>123 Wedding Lane &bull;
                        <i class="fa-solid fa-phone" style="color: var(--gold-color); margin-left: 0.5rem; margin-right: 0.4rem;"></i>+91 98765 43210
                    </p>
                </div>
                <div style="text-align: right;">
                    <h2 style="color: var(--accent-color); font-size: 1.6rem; margin-bottom: 0.4rem;">INVOICE</h2>
                    <p style="font-size: 1rem; font-weight: 800; color: var(--gold-color);">#DC-00<?php echo $inv['id']; ?></p>
                    <p style="font-size: 0.82rem; color: var(--text-secondary);">Date: <?php echo $booking_date; ?></p>
                    <span class="status-badge <?php echo $status_class; ?>" style="margin-top: 0.5rem; display: inline-block;"><?php echo $inv['status']; ?></span>
                </div>
            </div>

            <!-- Billed To / Booking Details -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h4 style="color: var(--accent-color); text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; margin-bottom: 0.8rem; border-bottom: 1px solid #ebdccb; padding-bottom: 0.4rem;">Billed To</h4>
                    <p style="font-weight: 700; font-size: 1.1rem; color: var(--text-primary); margin-bottom: 0.3rem;"><?php echo htmlspecialchars($inv['customer_name']); ?></p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">📞 <?php echo htmlspecialchars($inv['customer_phone']); ?></p>
                    <?php if($inv['customer_email']): ?><p style="color: var(--text-secondary); font-size: 0.9rem;">✉ <?php echo htmlspecialchars($inv['customer_email']); ?></p><?php endif; ?>
                    <?php if($inv['customer_address']): ?><p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.3rem;">📍 <?php echo htmlspecialchars($inv['customer_address']); ?></p><?php endif; ?>
                </div>
                <div>
                    <h4 style="color: var(--accent-color); text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; margin-bottom: 0.8rem; border-bottom: 1px solid #ebdccb; padding-bottom: 0.4rem;">Schedule Details</h4>
                    <table style="width:100%; font-size: 0.88rem; border-collapse: collapse;">
                        <tr><td style="color: var(--text-secondary); padding: 0.2rem 0;">Trial / Fitting:</td><td style="font-weight: 700; text-align:right;"><?php echo $trial_date; ?></td></tr>
                        <tr><td style="color: var(--text-secondary); padding: 0.2rem 0;">Wedding Event:</td><td style="font-weight: 700; color: var(--accent-color); text-align:right;"><?php echo $event_date; ?></td></tr>
                        <tr><td style="color: var(--text-secondary); padding: 0.2rem 0;">Pickup From:</td><td style="font-weight: 700; text-align:right;"><?php echo $pickup_date; ?></td></tr>
                        <tr><td style="color: var(--text-secondary); padding: 0.2rem 0;">Return By:</td><td style="font-weight: 700; text-align:right;"><?php echo $return_date; ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- Item Table -->
            <table style="width:100%; border-collapse: collapse; margin-bottom: 1.5rem;">
                <thead>
                    <tr style="background: var(--accent-color); color: #fff;">
                        <th style="padding: 0.8rem 1.2rem; text-align:left; border-radius: 8px 0 0 8px;">Garment / Item</th>
                        <th style="padding: 0.8rem 1.2rem; text-align:left;">Category</th>
                        <th style="padding: 0.8rem 1.2rem; text-align:center;">Size</th>
                        <th style="padding: 0.8rem 1.2rem; text-align:right; border-radius: 0 8px 8px 0;">Rental Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #ebdccb;">
                        <td style="padding: 1.2rem; font-weight: 700;"><?php echo htmlspecialchars($inv['product_name']); ?><br>
                            <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 500;"><?php echo htmlspecialchars($inv['product_color']); ?></span>
                        </td>
                        <td style="padding: 1.2rem; color: var(--text-secondary);"><?php echo $inv['product_category']; ?></td>
                        <td style="padding: 1.2rem; text-align:center; font-weight: 700;"><?php echo $inv['product_size']; ?></td>
                        <td style="padding: 1.2rem; text-align:right; font-weight: 700; font-size: 1.1rem;">₹<?php echo number_format($inv['rental_price'], 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Alteration Notes -->
            <?php if (!empty($inv['alteration_notes'])): ?>
            <div style="background: #faf7f2; border: 1px dashed var(--border-color); padding: 1.2rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.88rem;">
                <strong style="color: var(--accent-color);"><i class="fa-solid fa-scissors" style="margin-right: 0.4rem;"></i>Bespoke Tailoring Notes:</strong>
                <p style="margin-top: 0.4rem; font-style: italic; color: var(--text-primary); line-height: 1.5;"><?php echo htmlspecialchars($inv['alteration_notes']); ?></p>
            </div>
            <?php endif; ?>

            <!-- Financial Summary -->
            <div style="display: flex; justify-content: flex-end;">
                <div style="width: 280px;">
                    <table style="width:100%; font-size: 0.9rem; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0.4rem 0; color: var(--text-secondary);">Rental Charges:</td>
                            <td style="text-align:right; font-weight: 700;">₹<?php echo number_format($inv['rental_price'], 2); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 0.4rem 0; color: var(--text-secondary);">Security Deposit:</td>
                            <td style="text-align:right; font-weight: 700;">₹<?php echo number_format($inv['security_deposit_paid'], 2); ?></td>
                        </tr>
                        <tr style="border-top: 1px solid #ebdccb;">
                            <td style="padding: 0.6rem 0; color: var(--success); font-weight: 600;">Advance Paid:</td>
                            <td style="text-align:right; font-weight: 700; color: var(--success);">- ₹<?php echo number_format($inv['advance_paid'], 2); ?></td>
                        </tr>
                        <tr style="border-top: 2px solid var(--accent-color);">
                            <td style="padding: 0.8rem 0; font-size: 1.1rem; font-weight: 800; color: var(--accent-color);">Balance Pending:</td>
                            <td style="text-align:right; font-size: 1.3rem; font-weight: 900; color: <?php echo $inv['balance_pending'] > 0 ? 'var(--danger)' : 'var(--success)'; ?>;">
                                ₹<?php echo number_format($inv['balance_pending'], 2); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <hr class="inv-divider">

            <!-- Footer -->
            <div style="text-align: center; color: var(--text-secondary); font-size: 0.85rem;">
                <p style="font-size: 1.3rem; margin-bottom: 0.4rem;">👑</p>
                <p style="font-weight: 700; color: var(--accent-color); margin-bottom: 0.3rem;">Thank you for choosing Dulha Collection!</p>
                <p>Please present this invoice at the time of pickup. Security deposit is fully refundable upon undamaged return.</p>
            </div>
        </div>
    </div>
</body>
</html>
