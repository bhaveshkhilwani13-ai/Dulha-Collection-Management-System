<?php
// catalog.php (User Side Product Listing)
require_once 'includes/user_header.php';

// Handle Filter
$cat = isset($_GET['category']) ? $_GET['category'] : '';
$query = "SELECT * FROM products WHERE status = 'Available'";
if ($cat) {
    $query .= " AND category = '" . $conn->real_escape_string($cat) . "'";
}
$query .= " ORDER BY id DESC";
$result = $conn->query($query);
?>

<div style="background: #fdfaf5; padding: 4rem 5%; border-bottom: 1px solid var(--border-color);">
    <div style="max-width: 800px;">
        <h1 class="brand-heading" style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;">The Royal Inventory</h1>
        <p style="color: var(--text-secondary); font-size: 1.1rem;">Browse our exclusive collection of luxury groom wear, sorted by tradition and style.</p>
    </div>
</div>

<div style="padding: 2rem 5%; display: flex; gap: 3rem;">
    <!-- Sidebar Filters -->
    <aside style="width: 250px; flex-shrink: 0;">
        <div class="glass-panel" style="padding: 2rem; position: sticky; top: 120px;">
            <h3 style="color: var(--accent-color); font-size: 1.1rem; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 1px;">Categories</h3>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.8rem;">
                <li><a href="catalog.php" style="text-decoration: none; color: <?php echo !$cat ? 'var(--gold-color)' : 'var(--text-secondary)'; ?>; font-weight: 600;">All Collections</a></li>
                <?php
                $categories = ['Sherwani', 'Indo-Western', 'Suit & Tuxedo', 'Jodhpuri Suit', 'Kurta Pajama', 'Safa & Turban', 'Mojari & Shoes'];
                foreach($categories as $category) {
                    $active = ($cat == $category) ? 'color: var(--gold-color); font-weight: 700;' : 'color: var(--text-secondary);';
                    echo "<li><a href='catalog.php?category=$category' style='text-decoration: none; $active font-weight: 600;'>$category</a></li>";
                }
                ?>
            </ul>
        </div>
    </aside>

    <!-- Product Grid Content -->
    <div style="flex-grow: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <span style="color: var(--text-secondary); font-weight: 600;"><?php echo $result ? $result->num_rows : 0; ?> Masterpieces Found</span>
        </div>

        <div class="product-grid" style="padding: 0; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
            <?php
            if ($result && $result->num_rows > 0) {
                while($item = $result->fetch_assoc()) {
                    ?>
                    <div class="product-card">
                        <div class="product-img">
                            <i class="fa-solid fa-shirt"></i>
                        </div>
                        <div class="product-info">
                            <span class="category-tag"><?php echo $item['category']; ?></span>
                            <h3 class="product-name" style="font-size: 1.1rem;"><?php echo $item['name']; ?></h3>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 0.85rem; color: var(--text-secondary);">Size: <strong><?php echo $item['size']; ?></strong></span>
                                <span style="font-size: 0.85rem; color: var(--text-secondary);">Color: <strong><?php echo $item['color']; ?></strong></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="product-price" style="font-size: 1.25rem;">₹<?php echo number_format($item['rental_price'], 2); ?></span>
                                <a href="product-view.php?id=<?php echo $item['id']; ?>" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 8px;">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div style='grid-column: 1/-1; text-align: center; padding: 5rem; background: #fff; border-radius: 20px; border: 1px dashed var(--border-color);'>
                        <i class='fa-solid fa-folder-open' style='font-size: 3rem; color: var(--border-color); margin-bottom: 1rem;'></i>
                        <h3 style='color: var(--text-secondary);'>No masterpieces found in this category.</h3>
                      </div>";
            }
            ?>
        </div>
    </div>
</div>

<?php require_once 'includes/user_footer.php'; ?>
