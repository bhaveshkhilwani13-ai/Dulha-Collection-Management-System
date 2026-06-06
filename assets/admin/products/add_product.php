<?php
// products/add_product.php
require_once '../../includes/header.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $size = trim($_POST['size']);
    $color = trim($_POST['color']);
    $rental_price = floatval($_POST['rental_price']);
    $security_deposit = floatval($_POST['security_deposit']);
    $status = $_POST['status'];
    
    if(empty($name) || empty($category) || empty($size) || empty($color) || $rental_price <= 0) {
        $error = "Please fill in all required fields and enter valid pricing values.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, category, size, color, rental_price, security_deposit, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if($stmt) {
            $stmt->bind_param("ssssdds", $name, $category, $size, $color, $rental_price, $security_deposit, $status);
            if($stmt->execute()) {
                $success = "Royal apparel item successfully added to inventory!";
                // Clear inputs
                $_POST = array();
            } else {
                $error = "Failed to write product to database. Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database prepare statement failed.";
        }
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Add Royal Apparel</h1>
        <p>Register new premium groom garments, turbans, shoes, or accessories into the catalog.</p>
    </div>
    <a href="<?php echo $base_url; ?>/admin/products/view_products.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> View Inventory
    </a>
</div>

<div class="glass-panel" style="max-width: 800px; margin: 0 auto; padding: 3rem;">
    <h2 style="color: var(--accent-color); font-size: 1.5rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <i class="fa-solid fa-crown text-gold" style="margin-right: 0.5rem;"></i> Garment Information
    </h2>
    
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

    <form action="" method="POST">
        <div class="form-group">
            <label for="name">Apparel Title / Name *</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Royal Maharaja Golden Zardozi Sherwani" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        
        <div class="form-grid-2">
            <div class="form-group">
                <label for="category">Apparel Category *</label>
                <select id="category" name="category" class="form-control" required style="appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23b5924d%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 class=%22feather feather-chevron-down%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-repeat: no-repeat; background-position: right 1.2rem center; background-size: 1.2em;">
                    <option value="">-- Choose Category --</option>
                    <option value="Sherwani" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Sherwani') ? 'selected' : ''; ?>>Sherwani</option>
                    <option value="Indo-Western" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Indo-Western') ? 'selected' : ''; ?>>Indo-Western</option>
                    <option value="Suit & Tuxedo" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Suit & Tuxedo') ? 'selected' : ''; ?>>Suit & Tuxedo</option>
                    <option value="Jodhpuri Suit" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Jodhpuri Suit') ? 'selected' : ''; ?>>Jodhpuri Suit</option>
                    <option value="Kurta Pajama" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Kurta Pajama') ? 'selected' : ''; ?>>Kurta Pajama</option>
                    <option value="Safa & Turban" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Safa & Turban') ? 'selected' : ''; ?>>Safa & Turban</option>
                    <option value="Mojari & Shoes" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Mojari & Shoes') ? 'selected' : ''; ?>>Mojari & Shoes</option>
                    <option value="Accessories" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Accessories') ? 'selected' : ''; ?>>Accessories (Mala, Kalgi, Brooch)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="size">Apparel Size (or Shoes Size) *</label>
                <input type="text" id="size" name="size" class="form-control" placeholder="e.g. 40, 42, 9, Free Size" required value="<?php echo isset($_POST['size']) ? htmlspecialchars($_POST['size']) : ''; ?>">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="color">Garment Color / Fabric Detail *</label>
                <input type="text" id="color" name="color" class="form-control" placeholder="e.g. Cream Ivory with Zardozi Work" required value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="status">Availability Status</label>
                <select id="status" name="status" class="form-control" style="appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23b5924d%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 class=%22feather feather-chevron-down%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-repeat: no-repeat; background-position: right 1.2rem center; background-size: 1.2em;">
                    <option value="Available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                    <option value="Rented" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Rented') ? 'selected' : ''; ?>>Rented / Booked</option>
                    <option value="Maintenance" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Maintenance') ? 'selected' : ''; ?>>Dry Cleaning / Maintenance</option>
                </select>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="rental_price">Daily Rental Price (₹) *</label>
                <input type="number" step="0.01" min="0" id="rental_price" name="rental_price" class="form-control" placeholder="e.g. 4500.00" required value="<?php echo isset($_POST['rental_price']) ? htmlspecialchars($_POST['rental_price']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="security_deposit">Refundable Security Deposit (₹) *</label>
                <input type="number" step="0.01" min="0" id="security_deposit" name="security_deposit" class="form-control" placeholder="e.g. 3000.00" required value="<?php echo isset($_POST['security_deposit']) ? htmlspecialchars($_POST['security_deposit']) : ''; ?>">
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="reset" class="btn btn-outline">Reset Form</button>
            <button type="submit" class="btn btn-gold">
                <i class="fa-solid fa-crown"></i> Save to Collection
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
