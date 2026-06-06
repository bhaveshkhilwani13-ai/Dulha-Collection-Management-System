<?php
// product-view.php - Single Product Detail (No login required)
require_once 'includes/user_header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: catalog.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) { header("Location: catalog.php"); exit(); }
?>

<div style="padding: 3rem 5%;">
    <a href="catalog.php" style="color:var(--text-secondary);text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:0.5rem;margin-bottom:2.5rem;">
        <i class="fa-solid fa-arrow-left"></i> Back to Catalog
    </a>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: start;">
        <!-- Product Visual -->
        <div class="glass-panel" style="height:480px;display:flex;align-items:center;justify-content:center;font-size:8rem;color:var(--border-color);position:relative;">
            <i class="fa-solid fa-shirt"></i>
            <span class="status-badge <?php echo $product['status']==='Available' ? 'status-available' : 'status-rented'; ?>"
                  style="position:absolute;top:1.5rem;right:1.5rem;font-size:0.9rem;">
                <?php echo $product['status']; ?>
            </span>
        </div>

        <!-- Product Info -->
        <div>
            <span class="category-tag" style="font-size:0.88rem;"><?php echo $product['category']; ?></span>
            <h1 class="brand-heading" style="font-size:2.3rem;color:var(--accent-color);margin-bottom:1rem;line-height:1.2;">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>

            <div style="display:flex;gap:2.5rem;margin-bottom:2rem;">
                <div>
                    <span style="font-size:0.75rem;color:var(--text-secondary);display:block;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Size</span>
                    <span style="font-size:1.4rem;font-weight:800;"><?php echo $product['size']; ?></span>
                </div>
                <div>
                    <span style="font-size:0.75rem;color:var(--text-secondary);display:block;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Color / Fabric</span>
                    <span style="font-size:1.1rem;font-weight:700;"><?php echo htmlspecialchars($product['color']); ?></span>
                </div>
            </div>

            <!-- Pricing Box -->
            <div style="background:#fffcf8;border:1px solid var(--border-color);border-radius:16px;padding:2rem;margin-bottom:2rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
                    <div>
                        <span style="font-size:0.75rem;color:var(--text-secondary);display:block;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Rental Price</span>
                        <span style="font-size:2.5rem;font-weight:900;color:var(--accent-color);">₹<?php echo number_format($product['rental_price'], 0); ?></span>
                    </div>
                    <div>
                        <span style="font-size:0.75rem;color:var(--text-secondary);display:block;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Security Deposit</span>
                        <span style="font-size:1.8rem;font-weight:800;color:var(--gold-color);">₹<?php echo number_format($product['security_deposit'], 0); ?></span>
                        <p style="font-size:0.75rem;color:var(--text-secondary);margin-top:0.2rem;">Fully refundable on return</p>
                    </div>
                </div>
            </div>

            <!-- Includes -->
            <div style="background:#faf7f2;border-radius:12px;padding:1.5rem;margin-bottom:2rem;border:1px solid var(--border-color);">
                <h4 style="color:var(--accent-color);margin-bottom:1rem;"><i class="fa-solid fa-circle-check" style="color:var(--gold-color);margin-right:0.5rem;"></i>This rental includes</h4>
                <ul style="list-style:none;display:flex;flex-direction:column;gap:0.5rem;color:var(--text-secondary);font-size:0.92rem;font-weight:500;">
                    <li><i class="fa-solid fa-check text-gold" style="margin-right:0.5rem;"></i> Free alteration & fitting session</li>
                    <li><i class="fa-solid fa-check text-gold" style="margin-right:0.5rem;"></i> Professional dry cleaning before pickup</li>
                    <li><i class="fa-solid fa-check text-gold" style="margin-right:0.5rem;"></i> Trial appointment scheduling</li>
                    <li><i class="fa-solid fa-check text-gold" style="margin-right:0.5rem;"></i> Printed rental invoice on booking</li>
                </ul>
            </div>

            <!-- CTA -->
            <?php if ($product['status'] === 'Available'): ?>
            <div style="background:var(--success-bg);color:var(--success);border:1px solid rgba(46,125,50,0.2);border-radius:12px;padding:1.2rem;font-size:0.9rem;font-weight:600;">
                <i class="fa-solid fa-phone" style="margin-right:0.5rem;"></i>
                Call us to book: <strong>+91 98765 43210</strong> &mdash; Our team will handle your fitting and reservation.
            </div>
            <?php else: ?>
            <div style="background:var(--danger-bg);color:var(--danger);border:1px solid rgba(211,47,47,0.15);border-radius:12px;padding:1.2rem;font-size:0.92rem;font-weight:700;">
                <i class="fa-solid fa-circle-xmark" style="margin-right:0.5rem;"></i> Currently Unavailable &mdash; Check back after <?php echo date('M d', strtotime('+7 days')); ?>.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/user_footer.php'; ?>
