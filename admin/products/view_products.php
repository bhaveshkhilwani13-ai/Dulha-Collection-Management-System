<?php
// products/view_products.php
require_once '../../includes/header.php';

$success = '';
$error = '';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    
    // Check if product is currently rented (has an active rental)
    $check_stmt = $conn->prepare("SELECT id FROM rentals WHERE product_id = ? AND status IN ('Booked', 'Picked Up') LIMIT 1");
    if ($check_stmt) {
        $check_stmt->bind_param("i", $del_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Cannot delete this apparel! It is currently booked/rented for a wedding.";
        } else {
            // Delete product
            $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            if ($del_stmt) {
                $del_stmt->bind_param("i", $del_id);
                if ($del_stmt->execute()) {
                    $success = "Garment successfully deleted from inventory.";
                } else {
                    $error = "Failed to delete garment. Please check references.";
                }
                $del_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Build Search and Category Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

$query_str = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query_str .= " AND (name LIKE ? OR color LIKE ? OR size LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($category_filter)) {
    $query_str .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

$query_str .= " ORDER BY id DESC";

$stmt = $conn->prepare($query_str);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query_str);
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Apparel Catalog</h1>
        <p>Manage Royal Sherwanis, Indo-Western suits, Safas, and designer Mojaris.</p>
    </div>
    <a href="<?php echo $base_url; ?>/admin/products/add_product.php" class="btn btn-gold">
        <i class="fa-solid fa-plus"></i> Add Apparel
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
            <input type="text" id="search" name="search" class="form-control" style="padding: 0.8rem 1rem;" placeholder="Search apparel name, color, size..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="category" style="font-size: 0.85rem; color: var(--text-secondary);"><i class="fa-solid fa-filter"></i> Category</label>
            <select id="category" name="category" class="form-control" style="padding: 0.8rem 1rem; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23b5924d%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.1em;">
                <option value="">All Categories</option>
                <option value="Sherwani" <?php echo ($category_filter == 'Sherwani') ? 'selected' : ''; ?>>Sherwani</option>
                <option value="Indo-Western" <?php echo ($category_filter == 'Indo-Western') ? 'selected' : ''; ?>>Indo-Western</option>
                <option value="Suit & Tuxedo" <?php echo ($category_filter == 'Suit & Tuxedo') ? 'selected' : ''; ?>>Suit & Tuxedo</option>
                <option value="Jodhpuri Suit" <?php echo ($category_filter == 'Jodhpuri Suit') ? 'selected' : ''; ?>>Jodhpuri Suit</option>
                <option value="Kurta Pajama" <?php echo ($category_filter == 'Kurta Pajama') ? 'selected' : ''; ?>>Kurta Pajama</option>
                <option value="Safa & Turban" <?php echo ($category_filter == 'Safa & Turban') ? 'selected' : ''; ?>>Safa & Turban</option>
                <option value="Mojari & Shoes" <?php echo ($category_filter == 'Mojari & Shoes') ? 'selected' : ''; ?>>Mojari & Shoes</option>
                <option value="Accessories" <?php echo ($category_filter == 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.8rem;"><i class="fa-solid fa-filter"></i> Filter</button>
            <a href="view_products.php" class="btn btn-outline" style="padding: 0.8rem;" title="Reset filters"><i class="fa-solid fa-rotate-right"></i></a>
        </div>
    </form>
</div>

<!-- Visual Inventory List Table -->
<div class="glass-panel table-container">
    <table>
        <thead>
            <tr>
                <th>Apparel Detail</th>
                <th>Category</th>
                <th>Size</th>
                <th>Color Detail</th>
                <th>Rental Fee (₹)</th>
                <th>Security (₹)</th>
                <th>Status</th>
                <th class="no-print" style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $status_text = $row['status'];
                    $status_class = 'status-available';
                    if ($status_text === 'Rented') $status_class = 'status-rented';
                    if ($status_text === 'Maintenance') $status_class = 'status-maintenance';
                    
                    echo "<tr>
                            <td>
                                <div style='font-weight: 700; color: var(--accent-color);'>{$row['name']}</div>
                                <span style='font-size: 0.75rem; color: var(--text-secondary);'>SKU ID: #00{$row['id']}</span>
                            </td>
                            <td style='font-weight: 600; color: var(--text-secondary);'>{$row['category']}</td>
                            <td><span style='background: #fffcf8; border: 1px solid #ebdccb; padding: 0.3rem 0.7rem; border-radius: 4px; font-weight: 700;'>Size {$row['size']}</span></td>
                            <td style='color: var(--text-secondary); font-size: 0.92rem;'>{$row['color']}</td>
                            <td style='font-weight: 700;'>₹" . number_format($row['rental_price'], 2) . "</td>
                            <td style='font-weight: 600; color: var(--text-secondary);'>₹" . number_format($row['security_deposit'], 2) . "</td>
                            <td><span class='status-badge $status_class'>$status_text</span></td>
                            <td class='no-print' style='text-align: center;'>
                                <a href='view_products.php?action=delete&id={$row['id']}' class='btn btn-danger' style='padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px;' onclick='return confirm(\"Are you absolutely sure you want to remove this royal garment from the inventory?\");'>
                                    <i class='fa-solid fa-trash-can'></i> Delete
                                </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center text-secondary' style='padding: 3rem;'>No apparel inventory matches your query criteria.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
