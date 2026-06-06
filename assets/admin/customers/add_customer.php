<?php
// customers/add_customer.php
require_once '../../includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $alt_phone = trim($_POST['alt_phone']);
    $address = trim($_POST['address']);
    
    if (empty($name) || empty($phone)) {
        $error = "Groom's name and primary contact phone number are required fields!";
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, alt_phone, address) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $name, $email, $phone, $alt_phone, $address);
            if ($stmt->execute()) {
                $success = "Groom customer profile successfully created in registry!";
                $_POST = array(); // Clear inputs
            } else {
                $error = "Failed to register groom. Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database prepare query statement failed.";
        }
    }
}
?>

<div class="page-header">
    <div class="page-title">
        <h1>Register New Groom</h1>
        <p>Record client credentials, size charts, and secondary contact details for upcoming weddings.</p>
    </div>
    <a href="<?php echo $base_url; ?>/admin/customers/view_customers.php" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> View Grooms Registry
    </a>
</div>

<div class="glass-panel" style="max-width: 800px; margin: 0 auto; padding: 3rem;">
    <h2 style="color: var(--accent-color); font-size: 1.5rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <i class="fa-solid fa-user-plus text-gold" style="margin-right: 0.5rem;"></i> Groom Profile Card
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
        <div class="form-grid-2">
            <div class="form-group">
                <label for="name">Groom Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Bhavesh Patel" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. bhavesh@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="phone">Primary Phone Contact *</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. +91 98765 43210" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="alt_phone">Alternate Contact (Brother/Relative) / Alt Phone</label>
                <input type="tel" id="alt_phone" name="alt_phone" class="form-control" placeholder="e.g. +91 98765 01234" value="<?php echo isset($_POST['alt_phone']) ? htmlspecialchars($_POST['alt_phone']) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Delivery / Home Address</label>
            <textarea id="address" name="address" class="form-control" placeholder="Enter complete residential address..."><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="reset" class="btn btn-outline">Reset Card</button>
            <button type="submit" class="btn btn-gold">
                <i class="fa-solid fa-circle-user"></i> Create Profile
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
