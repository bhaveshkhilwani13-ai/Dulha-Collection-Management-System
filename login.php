<?php
// login.php - User (Customer) Login
require_once 'includes/db.php';

// If already logged in as customer, redirect to bookings
if (isset($_SESSION['customer_id'])) {
    header("Location: my-bookings.php");
    exit();
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = "Registration successful! Please login with your credentials.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password_hash FROM customers WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();
            if ($customer['password_hash'] && password_verify($password, $customer['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['customer_id']   = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                header("Location: my-bookings.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Dulha Collection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-card glass-panel">
        <div style="display: flex; justify-content: center; margin-bottom: 1.5rem;">
            <div class="logo-icon" style="width:60px;height:60px;font-size:1.8rem;border-radius:18px;">D</div>
        </div>
        <h2 style="margin-bottom:0.3rem;font-weight:700;color:var(--accent-color);">Welcome Back</h2>
        <p style="color:var(--gold-color);font-weight:600;text-transform:uppercase;letter-spacing:2px;font-size:0.75rem;margin-bottom:2rem;">Customer Portal</p>

        <?php if($success): ?>
            <div style="background:var(--success-bg);color:var(--success);padding:0.9rem 1.2rem;border-radius:12px;margin-bottom:1.5rem;border:1px solid rgba(46,125,50,0.2);font-weight:600;font-size:0.9rem;">
                <i class="fa-solid fa-circle-check" style="margin-right:0.5rem;"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background:var(--danger-bg);color:var(--danger);padding:0.9rem 1.2rem;border-radius:12px;margin-bottom:1.5rem;border:1px solid rgba(211,47,47,0.2);font-weight:600;font-size:0.9rem;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:0.5rem;"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email" style="color:var(--text-secondary);"><i class="fa-regular fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="your@email.com">
            </div>
            <div class="form-group" style="margin-bottom:2.2rem;">
                <label for="password" style="color:var(--text-secondary);"><i class="fa-solid fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:1rem;font-size:1.05rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Login to My Account
            </button>
            <div style="margin-top:1.8rem;color:var(--text-secondary);font-size:0.92rem;">
                New here? <a href="register.php" style="color:var(--gold-color);text-decoration:none;font-weight:700;">Create an account</a>
            </div>
            <div style="margin-top:0.8rem;color:var(--text-secondary);font-size:0.85rem;">
                <a href="index.php" style="color:var(--text-secondary);text-decoration:none;"><i class="fa-solid fa-arrow-left" style="margin-right:0.4rem;"></i>Back to Home</a>
            </div>
        </form>
    </div>
</body>
</html>
