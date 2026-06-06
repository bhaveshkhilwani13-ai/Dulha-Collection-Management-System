<?php
// index.php - Public Landing Page (Shopkeeper's Storefront Showcase)
require_once 'includes/user_header.php';

// Stats for display
$total_bookings  = $conn->query("SELECT COUNT(*) as c FROM rentals WHERE status != 'Cancelled'")->fetch_assoc()['c'];
$avail_products  = $conn->query("SELECT COUNT(*) as c FROM products WHERE status = 'Available'")->fetch_assoc()['c'];
$total_customers = $conn->query("SELECT COUNT(*) as c FROM customers")->fetch_assoc()['c'];
?>

<!-- Hero -->
<section class="hero-section">
    <div class="hero-content">
        <h1 class="brand-heading">Your Perfect Wedding Look Awaits</h1>
        <p>Premium Groom Wear Rentals for a Royal Celebration</p>
        <a href="catalog.php" class="btn btn-gold" style="padding: 1.1rem 2.5rem; font-size: 1rem; color: white;">
            <i class="fa-solid fa-shirt"></i> Browse Our Collection
        </a>
    </div>
</section>

<!-- Quick Stats Bar -->
<section style="background: var(--accent-color); padding: 2.5rem 5%;">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; text-align: center; max-width: 800px; margin: 0 auto;">
        <div>
            <p style="font-size: 3rem; font-weight: 900; color: var(--gold-color); line-height: 1;"><?php echo $total_bookings; ?></p>
            <p style="color: rgba(255,255,255,0.8); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.82rem;">Happy Bookings</p>
        </div>
        <div>
            <p style="font-size: 3rem; font-weight: 900; color: var(--gold-color); line-height: 1;"><?php echo $avail_products; ?></p>
            <p style="color: rgba(255,255,255,0.8); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.82rem;">Items Available</p>
        </div>
        <div>
            <p style="font-size: 3rem; font-weight: 900; color: var(--gold-color); line-height: 1;"><?php echo $total_customers; ?></p>
            <p style="color: rgba(255,255,255,0.8); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.82rem;">Grooms Served</p>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section style="padding: 5rem 5%; text-align: center; background: #fdfaf5;">
    <h2 class="brand-heading" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 1rem;">Experience Royalty</h2>
    <p style="color: var(--text-secondary); font-size: 1rem; max-width: 600px; margin: 0 auto 3.5rem;">
        From traditional maharajas to modern gentlemen, our collection reflects heritage, elegance, and royalty.
    </p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem;">
        <div class="glass-panel" style="padding: 2.5rem; text-align: center;">
            <i class="fa-solid fa-crown" style="font-size: 2.5rem; color: var(--gold-color); margin-bottom: 1rem;"></i>
            <h3 style="margin-bottom: 0.8rem; color: var(--accent-color);">Designer Collections</h3>
            <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6;">Curated premium apparel from top wedding couturiers across the country.</p>
        </div>
        <div class="glass-panel" style="padding: 2.5rem; text-align: center;">
            <i class="fa-solid fa-scissors" style="font-size: 2.5rem; color: var(--gold-color); margin-bottom: 1rem;"></i>
            <h3 style="margin-bottom: 0.8rem; color: var(--accent-color);">Bespoke Fitting</h3>
            <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6;">Personal tailoring sessions to ensure you look perfect on your big day.</p>
        </div>
        <div class="glass-panel" style="padding: 2.5rem; text-align: center;">
            <i class="fa-solid fa-calendar-check" style="font-size: 2.5rem; color: var(--gold-color); margin-bottom: 1rem;"></i>
            <h3 style="margin-bottom: 0.8rem; color: var(--accent-color);">Easy Rentals</h3>
            <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6;">Seamless booking, pickup, and return process for a stress-free wedding.</p>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section style="padding: 5rem 5%;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 class="brand-heading" style="font-size: 2.2rem; color: var(--accent-color); margin-bottom: 0.3rem;">Featured Masterpieces</h2>
            <p style="color: var(--text-secondary);">Our top-rated sherwanis and suits of the season.</p>
        </div>
        <a href="catalog.php" style="color: var(--accent-color); font-weight: 700; text-decoration: none; border-bottom: 2px solid var(--gold-color); padding-bottom: 3px;">View Full Catalog →</a>
    </div>
    <div class="product-grid">
        <?php
        $featured = $conn->query("SELECT * FROM products WHERE status = 'Available' ORDER BY RAND() LIMIT 4");
        if ($featured && $featured->num_rows > 0) {
            while($item = $featured->fetch_assoc()) { ?>
            <div class="product-card">
                <div class="product-img">
                    <i class="fa-solid fa-shirt"></i>
                    <span class="status-badge status-available" style="position:absolute;top:1rem;right:1rem;font-size:0.75rem;">Available</span>
                </div>
                <div class="product-info">
                    <span class="category-tag"><?php echo $item['category']; ?></span>
                    <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <div style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:1rem;">
                        Size: <strong><?php echo $item['size']; ?></strong> &bull; <?php echo htmlspecialchars($item['color']); ?>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="product-price">₹<?php echo number_format($item['rental_price'], 0); ?></span>
                        <a href="product-view.php?id=<?php echo $item['id']; ?>" class="btn btn-outline" style="padding:0.5rem 1rem;font-size:0.82rem;">View Details</a>
                    </div>
                </div>
            </div>
            <?php }
        } ?>
    </div>
</section>

<?php require_once 'includes/user_footer.php'; ?>
