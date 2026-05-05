<?php
require_once 'includes/config.php';
if(!isLoggedIn()) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$limits = getPlanLimits($user_id);
$bots = $conn->query("SELECT * FROM bots WHERE user_id = $user_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
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
        .bot-card { background: #1e293b; border-radius: 20px; padding: 20px; margin-bottom: 20px; transition: all 0.3s; }
        .bot-card:hover { transform: translateY(-5px); }
        .status-running { color: #10b981; font-weight: 600; }
        .status-stopped { color: #ef4444; font-weight: 600; }
        .btn-sm { border-radius: 10px; padding: 8px 15px; margin: 2px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="mb-4"><?php echo SITE_NAME; ?></h4>
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a class="nav-link" href="create-bot.php"><i class="fas fa-plus-circle"></i> Create Bot</a>
            <a class="nav-link" href="terminal.php"><i class="fas fa-terminal"></i> Web Terminal</a>
            <a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a class="nav-link" href="premium.php"><i class="fas fa-crown"></i> Upgrade Plan</a>
            <a class="nav-link" href="api-keys.php"><i class="fas fa-key"></i> API Keys</a>
            <a class="nav-link" href="support.php"><i class="fas fa-headset"></i> Support</a>
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <div><span class="badge bg-primary">Plan: <?php echo ucfirst($user['plan']); ?></span> <a href="premium.php" class="btn btn-sm btn-outline-primary ms-2">Upgrade</a></div>
        </div>
        
        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-robot fa-2x text-primary"></i><h3><?php echo $bots->num_rows; ?></h3><p>Total Bots</p></div></div>
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-microchip fa-2x text-success"></i><h3><?php echo $limits['cpu']; ?>%</h3><p>CPU Limit</p></div></div>
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-memory fa-2x text-warning"></i><h3><?php echo $limits['ram']; ?> MB</h3><p>RAM Limit</p></div></div>
            <div class="col-md-3"><div class="stat-card"><i class="fas fa-hdd fa-2x text-info"></i><h3><?php echo $limits['storage']; ?> MB</h3><p>Storage</p></div></div>
        </div>
        
        <div class="d-flex justify-content-between mb-3"><h4>Your Bots</h4><a href="create-bot.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Bot</a></div>
        
        <?php if($bots->num_rows == 0): ?>
            <div class="text-center py-5"><i class="fas fa-robot fa-4x text-muted mb-3"></i><p>You haven't created any bots yet!</p><a href="create-bot.php" class="btn btn-primary">Create Your First Bot</a></div>
        <?php else: ?>
            <?php while($bot = $bots->fetch_assoc()): ?>
            <div class="bot-card">
                <div class="row align-items-center">
                    <div class="col-md-3"><h5><?php echo htmlspecialchars($bot['bot_name']); ?></h5><small class="text-muted">@<?php echo $bot['bot_username']; ?></small></div>
                    <div class="col-md-2"><span class="status-<?php echo $bot['status']; ?>"><?php echo strtoupper($bot['status']); ?></span></div>
                    <div class="col-md-2"><small>Language: <?php echo strtoupper($bot['language']); ?></small></div>
                    <div class="col-md-5 text-end">
                        <a href="edit-bot.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Edit</a>
                        <?php if($bot['status'] == 'running'): ?>
                            <a href="api/stop.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-danger"><i class="fas fa-stop"></i> Stop</a>
                        <?php else: ?>
                            <a href="api/start.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-success"><i class="fas fa-play"></i> Start</a>
                        <?php endif; ?>
                        <a href="api/restart.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-sync"></i> Restart</a>
                        <a href="api/get-logs.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-terminal"></i> Logs</a>
                        <a href="api/delete.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-dark" onclick="return confirm('Delete this bot permanently?')"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html>