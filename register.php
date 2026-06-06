<?php
// register.php - User (Customer) Registration
require_once 'includes/db.php';

if (isset($_SESSION['customer_id'])) {
    header("Location: my-bookings.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $address  = trim($_POST['address']);

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = "All required fields must be filled in.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match. Please try again.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already registered. Please login instead.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO customers (name, email, password_hash, phone, address) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $email, $hash, $phone, $address);
                if ($stmt->execute()) {
                    header("Location: login.php?registered=1");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
                $stmt->close();
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Club - Dulha Collection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-card glass-panel" style="max-width:520px;padding:3rem;">
        <div style="display:flex;justify-content:center;margin-bottom:1.5rem;">
            <div class="logo-icon" style="width:60px;height:60px;font-size:1.8rem;border-radius:18px;">D</div>
        </div>
        <h2 style="margin-bottom:0.3rem;font-weight:700;color:var(--accent-color);">Join Dulha Collection</h2>
        <p style="color:var(--gold-color);font-weight:600;text-transform:uppercase;letter-spacing:2px;font-size:0.75rem;margin-bottom:2rem;">Create Your Account</p>

        <?php if($error): ?>
            <div style="background:var(--danger-bg);color:var(--danger);padding:0.9rem 1.2rem;border-radius:12px;margin-bottom:1.5rem;border:1px solid rgba(211,47,47,0.2);font-weight:600;font-size:0.9rem;text-align:left;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:0.5rem;"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" style="text-align:left;">
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="name"><i class="fa-regular fa-user"></i> Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" required placeholder="Your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="phone"><i class="fa-solid fa-phone"></i> Phone Number *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required placeholder="+91 98765 43210" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email"><i class="fa-regular fa-envelope"></i> Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="your@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="password"><i class="fa-solid fa-lock"></i> Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Min. 6 characters">
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fa-solid fa-check-double"></i> Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:2rem;">
                <label for="address"><i class="fa-solid fa-location-dot"></i> Home Address</label>
                <textarea id="address" name="address" class="form-control" placeholder="Your full address (optional)" rows="2"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;padding:1rem;font-size:1.05rem;color:white;">
                <i class="fa-solid fa-crown"></i> Create My Account
            </button>
            <div style="margin-top:1.5rem;color:var(--text-secondary);font-size:0.92rem;text-align:center;">
                Already registered? <a href="login.php" style="color:var(--gold-color);text-decoration:none;font-weight:700;">Login here</a>
            </div>
        </form>
    </div>
</body>
</html>
