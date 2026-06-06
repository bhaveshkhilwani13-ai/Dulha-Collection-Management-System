<?php
// rentals/return_product.php
require_once '../../includes/header.php';

$success = '';
$error = '';

$rental_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$rental = null;

if ($rental_id <= 0) {
    header("Location: view_rentals.php");
    exit();
}

// Fetch rental details
$q = "SELECT r.*, c.name as customer_name, c.phone as customer_phone, 
             p.name as product_name, p.category as product_category, p.size as product_size, p.color as product_color, p.id as product_id
      FROM rentals r 
      JOIN customers c ON r.customer_id = c.id 
      JOIN products p ON r.product_id = p.id 
      WHERE r.id = ? LIMIT 1";

$stmt = $conn->prepare($q);
if ($stmt) {
    $stmt->bind_param("i", $rental_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $rental = $res->fetch_assoc();
    }
    $stmt->close();
}

if (!$rental) {
    $error = "Rental record not found.";
}

// Handle Return Processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $rental) {
    $refund_deposit = isset($_POST['refund_deposit']) ? 1 : 0;
    
    // Start Transaction
    $conn->begin_transaction();
    try {
        // 1. Update rental status
        $upd_rent = $conn->prepare("UPDATE rentals SET status = 'Returned', balance_pending = 0 WHERE id = ?");
        if (!$upd_rent) {
            throw new Exception("Prepare rental return update failed.");
        }
        $upd_rent->bind_param("i", $rental_id);
        if (!$upd_rent->execute()) {
            throw new Exception("Failed to process return ledger: " . $upd_rent->error);
        }
        $upd_rent->close();
        
        // 2. Set product status back to Available
        $upd_prod = $conn->prepare("UPDATE products SET status = 'Available' WHERE id = ?");
        if (!$upd_prod) {
            throw new Exception("Prepare product status reset failed.");
        }
        $upd_prod->bind_param("i", $rental['product_id']);
        if (!$upd_prod->execute()) {
            throw new Exception("Failed to release apparel back to available inventory: " . $upd_prod->error);
        }
        $upd_prod->close();
        
        $conn->commit();
        header("Location: view_rentals.php?success=returned");
        exit();
        
    } catch(Exception $e) {
        $conn->rollback();
        $error = "Failed to complete return process: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Process Return</h1>
        <p>Verify outstanding fees, refund security deposits, and release garments back into available stock.</p>
    </div>
    <a href="<?php echo $base_url; ?>/admin/rentals/view_rentals.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> View Bookings
    </a>
</div>

<div class="glass-panel" style="max-width: 700px; margin: 0 auto; padding: 3rem;">
    <h2 style="color: var(--accent-color); font-size: 1.5rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <i class="fa-solid fa-rotate-left text-gold" style="margin-right: 0.5rem;"></i> Trousseau Return Verification
    </h2>

    <?php if($error): ?>
        <div style="background: var(--danger-bg); color: var(--danger); padding: 1rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(211, 47, 47, 0.2); font-weight: 600;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($rental): ?>
        <div style="background: #fffcf8; border: 1px solid var(--border-color); padding: 2rem; border-radius: 12px; margin-bottom: 2.2rem; line-height: 1.8;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; border-bottom: 1px dashed #ebdccb; padding-bottom: 1rem;">
                <div>
                    <span style="font-size: 0.85rem; color: var(--text-secondary); display: block; font-weight: 600; text-transform: uppercase;">Groom Client</span>
                    <strong style="font-size: 1.1rem; color: var(--accent-color);"><?php echo htmlspecialchars($rental['customer_name']); ?></strong><br>
                    Phone: <?php echo htmlspecialchars($rental['customer_phone']); ?>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.85rem; color: var(--text-secondary); display: block; font-weight: 600; text-transform: uppercase;">Receipt ID</span>
                    <strong style="color: var(--gold-color);">#DC-00<?php echo $rental['id']; ?></strong>
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <span style="font-size: 0.85rem; color: var(--text-secondary); display: block; font-weight: 600; text-transform: uppercase;">Rented Apparel</span>
                <strong><?php echo htmlspecialchars($rental['product_name']); ?></strong><br>
                <span style="font-size: 0.95rem; color: var(--text-secondary);">[<?php echo $rental['product_category']; ?>] Size: <?php echo $rental['product_size']; ?> | <?php echo $rental['product_color']; ?></span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: #faf7f2; padding: 1.2rem; border-radius: 8px; border: 1px solid #ebdccb;">
                <div>
                    <span style="font-size: 0.82rem; color: var(--text-secondary); display: block; font-weight: 600;">Refundable Security Deposit Held</span>
                    <strong style="font-size: 1.3rem; color: var(--accent-color);">₹<?php echo number_format($rental['security_deposit_paid'], 2); ?></strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.82rem; color: var(--text-secondary); display: block; font-weight: 600;">Outstanding Balance Due</span>
                    <strong style="font-size: 1.3rem; color: <?php echo ($rental['balance_pending'] > 0) ? 'var(--danger)' : 'var(--success)'; ?>;">
                        ₹<?php echo number_format($rental['balance_pending'], 2); ?>
                    </strong>
                </div>
            </div>
        </div>

        <form action="" method="POST">
            <div class="form-group" style="background: #faf6f0; padding: 1.5rem; border-radius: 10px; border: 1px solid var(--border-color); margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.8rem;">
                    <input type="checkbox" id="refund_deposit" name="refund_deposit" value="1" checked style="width: 20px; height: 20px; accent-color: var(--accent-color); cursor: pointer;">
                    <label for="refund_deposit" style="margin-bottom: 0; font-weight: 700; color: var(--accent-color); cursor: pointer;">
                        Refund Security Deposit In Full (₹<?php echo number_format($rental['security_deposit_paid'], 2); ?>)
                    </label>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-left: 1.8rem; margin-top: 0.4rem;">
                    Uncheck only if the garment sustained significant damage requiring dry cleaning or tailor repairs (which should be deducted from the security hold).
                </p>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="<?php echo $base_url; ?>/admin/rentals/view_rentals.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-gold" style="padding: 1rem 2rem;">
                    <i class="fa-solid fa-circle-check"></i> Complete Return & Refund Deposit
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
