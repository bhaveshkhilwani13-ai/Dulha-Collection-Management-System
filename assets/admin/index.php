<?php
// index.php
require_once '../includes/db.php';

// If already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if(isset($_GET['success']) && $_GET['success'] == 'registered') {
    $success = "Registration successful! Enter your credentials to login.";
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Secure Parameterized Query
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM login WHERE username = ? LIMIT 1");
    if($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify with secure BCRYPT
            if(password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password credentials.";
            }
        } else {
            $error = "Invalid username or password credentials.";
        }
        $stmt->close();
    } else {
        $error = "Database authentication query failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Portal Login - Dulha Collection</title>
    <!-- Use FontAwesome for beautiful, luxury system icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>
<body class="login-body">

    <div class="login-card glass-panel">
        <div style="display: flex; justify-content: center; margin-bottom: 1.5rem;">
            <div class="logo-icon" style="width: 60px; height: 60px; font-size: 1.8rem; border-radius: 18px;">D</div>
        </div>
        <h2 style="margin-bottom: 0.3rem; letter-spacing: 0.5px; font-weight: 700; color: var(--accent-color);">
            Dulha Collection
        </h2>
        <p style="color: var(--gold-color); font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 0.75rem; margin-bottom: 2rem;">
            Groom Wear Rental System
        </p>
        
        <?php if($success): ?>
            <div style="background: var(--success-bg); color: var(--success); padding: 0.9rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(46, 125, 50, 0.2); font-weight: 600; font-size: 0.9rem; text-align: left;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background: var(--danger-bg); color: var(--danger); padding: 0.9rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(211, 47, 47, 0.2); font-weight: 600; font-size: 0.9rem; text-align: left;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username" style="color: var(--text-secondary);"><i class="fa-regular fa-user"></i> Username</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Enter username (e.g. admin)">
            </div>
            <div class="form-group" style="margin-bottom: 2.2rem;">
                <label for="password" style="color: var(--text-secondary);"><i class="fa-solid fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.05rem; letter-spacing: 0.5px;">
                <i class="fa-solid fa-shield-halved"></i> Access Admin Portal
            </button>
            <div style="margin-top: 1.8rem; color: var(--text-secondary); font-size: 0.92rem; font-weight: 500;">
                Need to register a new admin? <a href="register.php" style="color: var(--gold-color); text-decoration: none; font-weight: 700;">Create Account</a>
            </div>
        </form>
    </div>

</body>
</html>
