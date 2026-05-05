<?php
require_once 'includes/config.php';
if(!isLoggedIn() || !isAdmin()) { header("Location: login.php"); exit(); }

// Admin Login Check
if(!isset($_SESSION['admin_logged_in'])) {
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if($username === VANTHEX && password_verify($password, HOSTING)) {
            $_SESSION['admin_logged_in'] = true;
        } else $login_error = "Invalid credentials!";
    }
    if(!isset($_SESSION['admin_logged_in'])) { ?>
        <!DOCTYPE html><html><head><title>Admin Login</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:linear-gradient(135deg,#1e1e2f 0%,#2d2d44 100%);height:100vh;display:flex;align-items:center;justify-content:center;}.login-box{background:white;border-radius:20px;padding:40px;width:400px;}</style></head>
        <body><div class="login-box"><h3 class="text-center">Admin Login</h3><?php if(isset($login_error)) echo "<div class='alert alert-danger'>$login_error</div>"; ?>
        <form method="POST"><div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
        <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
        <button type="submit" name="admin_login" class="btn btn-primary w-100">Login</button></form></div></body></html>
        <?php exit();
    }
}

// Handle Broadcast
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['broadcast_msg'])) {
    $message = $conn->real_escape_string($_POST['message']);
    $conn->query("INSERT INTO announcements (title, message, created_by) VALUES ('Broadcast', '$message', {$_SESSION['user_id']})");
    $bots = $conn->query("SELECT bot_token FROM bots");
    $sent = 0;
    while($bot = $bots->fetch_assoc()) {
        $url = "https://api.telegram.org/bot{$bot['bot_token']}/sendMessage";
        $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['chat_id' => '@' . SITE_NAME, 'text' => $message, 'parse_mode' => 'HTML']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_exec($ch); curl_close($ch); $sent++;
    }
    $broadcast_success = "Broadcast sent to $sent bots!";
}

// Handle User Actions
if(isset($_GET['user_action']) && isset($_GET['user_id'])) {
    $uid = (int)$_GET['user_id'];
    if($_GET['user_action'] == 'ban') $conn->query("UPDATE users SET is_active = 0 WHERE id = $uid");
    elseif($_GET['user_action'] == 'unban') $conn->query("UPDATE users SET is_active = 1 WHERE id = $uid");
    elseif($_GET['user_action'] == 'delete') $conn->query("DELETE FROM users WHERE id = $uid");
    elseif($_GET['user_action'] == 'make_admin') $conn->query("UPDATE users SET is_admin = 1 WHERE id = $uid");
    elseif($_GET['user_action'] == 'remove_admin') $conn->query("UPDATE users SET is_admin = 0 WHERE id = $uid");
    logAdminAction($_SESSION['user_id'], 'user_' . $_GET['user_action'], "User ID: $uid");
    header("Location: admin.php?tab=users");
    exit();
}

// Handle Premium Upgrade
if(isset($_GET['upgrade']) && isset($_GET['user_id']) && isset($_GET['plan'])) {
    $uid = (int)$_GET['user_id'];
    $plan = $_GET['plan'];
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
    $conn->query("UPDATE users SET plan = '$plan', plan_expiry = '$expiry' WHERE id = $uid");
    logAdminAction($_SESSION['user_id'], 'upgrade_user', "User ID: $uid to $plan");
    header("Location: admin.php?tab=users");
    exit();
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_bots = $conn->query("SELECT COUNT(*) FROM bots")->fetch_row()[0];
$running_bots = $conn->query("SELECT COUNT(*) FROM bots WHERE status = 'running'")->fetch_row()[0];
$premium_users = $conn->query("SELECT COUNT(*) FROM users WHERE plan != 'free'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; }
        .sidebar { position: fixed; left: 0; top: 0; height: 100vh; width: 260px; background: #1e293b; padding: 20px; overflow-y: auto; }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 15px; border-radius: 12px; margin-bottom: 5px; transition: all 0.3s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: white; }
        .sidebar .nav-link i { width: 25px; margin-right: 10px; }
        .main-content { margin-left: 260px; padding: 20px; }
        .stat-card { background: #1e293b; border-radius: 20px; padding: 20px; text-align: center; }
        .table-container { background: #1e293b; border-radius: 20px; padding: 20px; }
        .btn-sm { border-radius: 10px; margin: 2px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="mb-4"><?php echo SITE_NAME; ?></h4>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo $tab == 'dashboard' ? 'active' : ''; ?>" href="?tab=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a class="nav-link <?php echo $tab == 'users' ? 'active' : ''; ?>" href="?tab=users"><i class="fas fa-users"></i> Users</a>
            <a class="nav-link <?php echo $tab == 'bots' ? 'active' : ''; ?>" href="?tab=bots"><i class="fas fa-robot"></i> Bots</a>
            <a class="nav-link <?php echo $tab == 'broadcast' ? 'active' : ''; ?>" href="?tab=broadcast"><i class="fas fa-bullhorn"></i> Broadcast</a>
            <a class="nav-link <?php echo $tab == 'payments' ? 'active' : ''; ?>" href="?tab=payments"><i class="fas fa-credit-card"></i> Payments</a>
            <a class="nav-link <?php echo $tab == 'logs' ? 'active' : ''; ?>" href="?tab=logs"><i class="fas fa-history"></i> Admin Logs</a>
            <a class="nav-link" href="terminal.php"><i class="fas fa-terminal"></i> Terminal</a>
            <a class="nav-link" href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
        </nav>
    </div>
    
    <div class="main-content">
        <?php if($tab == 'dashboard'): ?>
        <h2 class="mb-4">Admin Dashboard</h2>
        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-users fa-2x"></i><h3><?php echo $total_users; ?></h3><p>Total Users</p></div></div>
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-robot fa-2x"></i><h3><?php echo $total_bots; ?></h3><p>Total Bots</p></div></div>
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-play-circle fa-2x"></i><h3><?php echo $running_bots; ?></h3><p>Running Bots</p></div></div>
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-crown fa-2x"></i><h3><?php echo $premium_users; ?></h3><p>Premium Users</p></div></div>
        </div>
        <div class="row g-4">
            <div class="col-md-6"><div class="table-container"><h5>Recent Users</h5><table class="table table-dark table-sm"><?php $ru = $conn->query("SELECT id, username, plan, created_at FROM users ORDER BY id DESC LIMIT 10"); while($u = $ru->fetch_assoc()) echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['plan']}</td><td>" . date('d M', strtotime($u['created_at'])) . "</td></tr>"; ?></table></div></div>
            <div class="col-md-6"><div class="table-container"><h5>Recent Bots</h5><table class="table table-dark table-sm"><?php $rb = $conn->query("SELECT id, bot_name, language, status FROM bots ORDER BY id DESC LIMIT 10"); while($b = $rb->fetch_assoc()) echo "<tr><td>{$b['id']}</td><td>{$b['bot_name']}</td><td>{$b['language']}</td><td><span class='badge bg-".($b['status']=='running'?'success':'secondary')."'>{$b['status']}</span></td></tr>"; ?></table></div></div>
        </div>
        
        <?php elseif($tab == 'users'): ?>
        <h2 class="mb-4">User Management</h2>
        <div class="table-container"><table class="table table-dark" id="usersTable"><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Plan</th><th>Bots</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php $users = $conn->query("SELECT u.*, COUNT(b.id) as bot_count FROM users u LEFT JOIN bots b ON u.id = b.user_id GROUP BY u.id ORDER BY u.id DESC");
        while($user = $users->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><form method="GET" style="display:inline;"><input type="hidden" name="tab" value="users"><input type="hidden" name="user_id" value="<?php echo $user['id']; ?>"><select name="plan" onchange="this.form.submit()" class="form-select form-select-sm" style="width:100px;"><option value="free" <?php echo $user['plan']=='free'?'selected':''; ?>>Free</option><option value="basic" <?php echo $user['plan']=='basic'?'selected':''; ?>>Basic</option><option value="pro" <?php echo $user['plan']=='pro'?'selected':''; ?>>Pro</option><option value="business" <?php echo $user['plan']=='business'?'selected':''; ?>>Business</option></select><input type="submit" name="upgrade" value="Update" style="display:none;"></form></td>
            <td><?php echo $user['bot_count']; ?></td>
            <td><?php if($user['is_active']) echo '<span class="badge bg-success">Active</span>'; else echo '<span class="badge bg-danger">Banned</span>'; ?></td>
            <td>
                <?php if($user['is_active']) echo "<a href='?tab=users&user_action=ban&user_id={$user['id']}' class='btn btn-sm btn-warning' onclick='return confirm(\"Ban this user?\")'>Ban</a> "; else echo "<a href='?tab=users&user_action=unban&user_id={$user['id']}' class='btn btn-sm btn-success' onclick='return confirm(\"Unban this user?\")'>Unban</a> "; ?>
                <?php if(!$user['is_admin']) echo "<a href='?tab=users&user_action=make_admin&user_id={$user['id']}' class='btn btn-sm btn-info' onclick='return confirm(\"Make admin?\")'>Make Admin</a> "; else echo "<a href='?tab=users&user_action=remove_admin&user_id={$user['id']}' class='btn btn-sm btn-secondary' onclick='return confirm(\"Remove admin?\")'>Remove Admin</a> "; ?>
                <a href='?tab=users&user_action=delete&user_id=<?php echo $user['id']; ?>' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete user permanently?\")'>Delete</a>
            </td>
        </tr>
        <?php endwhile; ?></tbody></table></div>
        
        <?php elseif($tab == 'broadcast'): ?>
        <h2 class="mb-4">Broadcast Message</h2>
        <?php if(isset($broadcast_success)) echo "<div class='alert alert-success'>$broadcast_success</div>"; ?>
        <div class="table-container"><form method="POST"><div class="mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="5" required></textarea><small class="text-muted">HTML supported</small></div><button type="submit" name="broadcast_msg" class="btn btn-primary" onclick="return confirm('Send to all bots?')">Send Broadcast</button></form></div>
        <div class="table-container mt-4"><h5>Recent Broadcasts</h5><table class="table table-dark"><?php $ann = $conn->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 20"); while($a = $ann->fetch_assoc()): ?><tr><td><?php echo substr($a['message'],0,100); ?>...</td><td><?php echo date('d M H:i', strtotime($a['created_at'])); ?></td></tr><?php endwhile; ?></table></div>
        
        <?php elseif($tab == 'payments'): ?>
        <h2 class="mb-4">Payments</h2>
        <div class="table-container"><table class="table table-dark"><thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Plan</th><th>Status</th><th>Date</th></tr></thead><tbody>
        <?php $payments = $conn->query("SELECT p.*, u.username FROM payments p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC");
        while($pay = $payments->fetch_assoc()): ?>
        <tr><td><?php echo $pay['id']; ?></td><td><?php echo $pay['username']; ?></td><td>₹<?php echo $pay['amount']; ?></td><td><?php echo ucfirst($pay['plan']); ?></td><td><span class="badge bg-<?php echo $pay['status']=='completed'?'success':'warning'; ?>"><?php echo $pay['status']; ?></span></td><td><?php echo date('d M Y', strtotime($pay['created_at'])); ?></td></tr>
        <?php endwhile; ?></tbody></table></div>
        
        <?php elseif($tab == 'logs'): ?>
        <h2 class="mb-4">Admin Logs</h2>
        <div class="table-container"><table class="table table-dark"><thead><tr><th>ID</th><th>Action</th><th>Details</th><th>IP</th><th>Date</th></tr></thead><tbody>
        <?php $logs = $conn->query("SELECT * FROM admin_logs ORDER BY id DESC LIMIT 100");
        while($log = $logs->fetch_assoc()): ?>
        <tr><td><?php echo $log['id']; ?></td><td><?php echo $log['action']; ?></td><td><?php echo $log['details']; ?></td><td><?php echo $log['ip']; ?></td><td><?php echo date('d M H:i', strtotime($log['created_at'])); ?></td></tr>
        <?php endwhile; ?></tbody></table></div>
        <?php endif; ?>
    </div>
</body>
</html>