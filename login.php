<?php require_once 'includes/config.php';
if(isLoggedIn()) { header("Location: dashboard.php"); exit(); }
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $result = $conn->query("SELECT * FROM users WHERE username = '$username' OR email = '$username'");
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['is_admin'] = $user['is_admin'];
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");
            header("Location: dashboard.php"); exit();
        } else $error = "Invalid password!";
    } else $error = "User not found!";
}
?>
<!DOCTYPE html>
<html>
<head><title>Login - <?php echo SITE_NAME; ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:100vh;display:flex;align-items:center;justify-content:center;}.card{background:#1e293b;border-radius:20px;padding:40px;width:400px;}</style></head>
<body>
<div class="card"><h3 class="text-center text-white mb-4">Login to <?php echo SITE_NAME; ?></h3>
<?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST"><div class="mb-3"><input type="text" name="username" class="form-control bg-dark text-white border-secondary" placeholder="Username or Email" required></div>
<div class="mb-3"><input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Password" required></div>
<button type="submit" class="btn btn-primary w-100">Login</button></form>
<div class="text-center mt-3"><a href="register.php" class="text-white">Create Account</a> | <a href="google-login.php" class="text-white">Google Login</a></div>
</div></body></html>