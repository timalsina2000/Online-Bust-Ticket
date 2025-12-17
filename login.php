<?php
$s_path = __DIR__ . '/sessions';
if (!file_exists($s_path)) { @mkdir($s_path, 0777, true); }
session_save_path($s_path);

session_start();
include 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if ($role == 'admin') {
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $email); // Using email field as username for admin for simplicity or dual-use
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['role'] = $role;
            $_SESSION['user_id'] = $user['id'];
            if($role == 'user') $_SESSION['name'] = $user['name'];
            
            header("Location: ". ($role == 'admin' ? 'admin/dashboard.php' : 'index.php'));
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Mero Bus</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <a href="index.php" class="nav-brand">Mero Bus</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="signup.php">Sign Up</a>
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-box card">
            <h2 class="text-center mb-4">Welcome Back</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email / Username</label>
                    <input type="text" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Login As</label>
                    <select name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                
                <p class="text-center mt-4" style="color: var(--text-muted);">
                    Don't have an account? <a href="signup.php" style="color: var(--primary-color);">Sign Up</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
