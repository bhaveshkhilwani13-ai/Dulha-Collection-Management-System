<?php
// register.php
require_once '../includes/db.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(strlen($username) < 4) {
        $error = "Username must be at least 4 characters long.";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT id FROM login WHERE username = ? LIMIT 1");
        if($check_stmt) {
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if($check_stmt->num_rows > 0) {
                $error = "Username is already registered!";
            } else {
                $check_stmt->close();
                
                // Create secure hash
                $secure_hash = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO login (username, password_hash) VALUES (?, ?)");
                
                if($insert_stmt) {
                    $insert_stmt->bind_param("ss", $username, $secure_hash);
                    if($insert_stmt->execute()) {
                        header("Location: index.php?success=registered");
                        exit();
                    } else {
                        $error = "Failed to register. Please try again.";
                    }
                    $insert_stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register - Dulha Collection</title>
    <!-- Use FontAwesome for beautiful, luxury system icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>
<body class="login-body">

    <div class="login-card glass-panel" style="max-width: 460px;">
        <div style="display: flex; justify-content: center; margin-bottom: 1.5rem;">
            <div class="logo-icon" style="width: 60px; height: 60px; font-size: 1.8rem; border-radius: 18px;">D</div>
        </div>
        <h2 style="margin-bottom: 0.3rem; letter-spacing: 0.5px; font-weight: 700; color: var(--accent-color);">
            Create Admin Account
        </h2>
        <p style="color: var(--gold-color); font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 0.75rem; margin-bottom: 2rem;">
            Dulha Collection Registry
        </p>

        <?php if($error): ?>
            <div style="background: var(--danger-bg); color: var(--danger); padding: 0.9rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(211, 47, 47, 0.2); font-weight: 600; font-size: 0.9rem; text-align: left;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username" style="color: var(--text-secondary);"><i class="fa-regular fa-user"></i> Username</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="Choose username (min 4 chars)" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password" style="color: var(--text-secondary);"><i class="fa-solid fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Create password (min 6 chars)">
            </div>
            <div class="form-group" style="margin-bottom: 2.2rem;">
                <label for="confirm_password" style="color: var(--text-secondary);"><i class="fa-solid fa-circle-check"></i> Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Re-enter password">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.05rem; letter-spacing: 0.5px;">
                <i class="fa-solid fa-user-plus"></i> Register Admin
            </button>
            <div style="margin-top: 1.8rem; color: var(--text-secondary); font-size: 0.92rem; font-weight: 500;">
                Already have an account? <a href="index.php" style="color: var(--gold-color); text-decoration: none; font-weight: 700;">Login here</a>
            </div>
        </form>
    </div>

</body>
</html>
