<?php require_once 'includes/config.php';
if(isLoggedIn()) { header("Location: dashboard.php"); exit(); }
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $api_key = generateAPIKey();
    if($conn->query("SELECT * FROM users WHERE username = '$username' OR email = '$email'")->num_rows > 0) $error = "Username or email already exists!";
    else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, api_key, last_login) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $username, $email, $password, $api_key);
        if($stmt->execute()) { $_SESSION['user_id'] = $conn->insert_id; $_SESSION['username'] = $username; header("Location: dashboard.php"); exit(); }
        else $error = "Registration failed!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register - <?php echo SITE_NAME; ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:100vh;display:flex;align-items:center;justify-content:center;}.card{background:#1e293b;border-radius:20px;padding:40px;width:400px;}</style></head>
<body>
<div class="card"><h3 class="text-center text-white mb-4">Create Account</h3>
<?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST"><div class="mb-3"><input type="text" name="username" class="form-control bg-dark text-white border-secondary" placeholder="Username" required></div>
<div class="mb-3"><input type="email" name="email" class="form-control bg-dark text-white border-secondary" placeholder="Email" required></div>
<div class="mb-3"><input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Password" required></div>
<button type="submit" class="btn btn-primary w-100">Sign Up</button></form>
<div class="text-center mt-3"><a href="login.php" class="text-white">Already have account? Login</a> | <a href="google-login.php" class="text-white">Google Sign Up</a></div>
</div></body></html>