<?php
require_once 'includes/config.php';
if(!isLoggedIn()) { header("Location: login.php"); exit(); }

// Get server stats
$cpu = sys_getloadavg()[0];
$cpu_percent = round($cpu * 100 / 4, 1);
$memory_total = shell_exec("free -m | grep Mem | awk '{print $2}'");
$memory_used = shell_exec("free -m | grep Mem | awk '{print $3}'");
$memory_percent = round(($memory_used / $memory_total) * 100, 1);
$disk_total = shell_exec("df -h / | tail -1 | awk '{print $2}'");
$disk_used = shell_exec("df -h / | tail -1 | awk '{print $3}'");
$disk_percent = shell_exec("df -h / | tail -1 | awk '{print $5}'");
$uptime = shell_exec("uptime -p");
$last_backup = file_exists('../backup/last_backup.txt') ? file_get_contents('../backup/last_backup.txt') : 'Never';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Server Status - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; }
        .status-card { background: #1e293b; border-radius: 20px; padding: 20px; margin-bottom: 20px; }
        .progress { height: 10px; border-radius: 5px; }
        .progress-bar-success { background: #10b981; }
        .progress-bar-warning { background: #f59e0b; }
        .progress-bar-danger { background: #ef4444; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-server me-2"></i> Server Status</h2>
            <a href="dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        
        <!-- CPU Status -->
        <div class="status-card">
            <h5><i class="fas fa-microchip me-2"></i> CPU Usage</h5>
            <div class="progress">
                <div class="progress-bar <?php echo $cpu_percent < 70 ? 'progress-bar-success' : ($cpu_percent < 85 ? 'progress-bar-warning' : 'progress-bar-danger'); ?>" style="width: <?php echo $cpu_percent; ?>%"><?php echo $cpu_percent; ?>%</div>
            </div>
            <p class="mt-2 text-muted">Load Average: <?php echo $cpu; ?></p>
        </div>
        
        <!-- RAM Status -->
        <div class="status-card">
            <h5><i class="fas fa-memory me-2"></i> Memory Usage</h5>
            <div class="progress">
                <div class="progress-bar <?php echo $memory_percent < 70 ? 'progress-bar-success' : ($memory_percent < 85 ? 'progress-bar-warning' : 'progress-bar-danger'); ?>" style="width: <?php echo $memory_percent; ?>%"><?php echo $memory_percent; ?>%</div>
            </div>
            <p class="mt-2 text-muted">Used: <?php echo trim($memory_used); ?> MB / Total: <?php echo trim($memory_total); ?> MB</p>
        </div>
        
        <!-- Disk Status -->
        <div class="status-card">
            <h5><i class="fas fa-hdd me-2"></i> Disk Usage</h5>
            <div class="progress">
                <div class="progress-bar <?php echo rtrim($disk_percent,'%') < 70 ? 'progress-bar-success' : (rtrim($disk_percent,'%') < 85 ? 'progress-bar-warning' : 'progress-bar-danger'); ?>" style="width: <?php echo $disk_percent; ?>"><?php echo $disk_percent; ?></div>
            </div>
            <p class="mt-2 text-muted">Used: <?php echo trim($disk_used); ?> / Total: <?php echo trim($disk_total); ?></p>
        </div>
        
        <!-- System Info -->
        <div class="status-card">
            <h5><i class="fas fa-info-circle me-2"></i> System Information</h5>
            <p><strong>Uptime:</strong> <?php echo $uptime; ?></p>
            <p><strong>Last Backup:</strong> <?php echo $last_backup; ?></p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <!-- Database Stats -->
        <div class="status-card">
            <h5><i class="fas fa-database me-2"></i> Database Statistics</h5>
            <?php
            $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
            $total_bots = $conn->query("SELECT COUNT(*) FROM bots")->fetch_row()[0];
            $running_bots = $conn->query("SELECT COUNT(*) FROM bots WHERE status = 'running'")->fetch_row()[0];
            $total_payments = $conn->query("SELECT COUNT(*) FROM payments")->fetch_row()[0];
            $total_amount = $conn->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetch_row()[0];
            ?>
            <p><strong>Total Users:</strong> <?php echo $total_users; ?></p>
            <p><strong>Total Bots:</strong> <?php echo $total_bots; ?> (<?php echo $running_bots; ?> running)</p>
            <p><strong>Total Payments:</strong> <?php echo $total_payments; ?></p>
            <p><strong>Total Revenue:</strong> ₹<?php echo number_format($total_amount, 2); ?></p>
        </div>
        
        <!-- Actions -->
        <div class="status-card">
            <h5><i class="fas fa-tools me-2"></i> Actions</h5>
            <div class="d-flex gap-3">
                <button class="btn btn-primary" onclick="createBackup()"><i class="fas fa-database"></i> Create Backup</button>
                <button class="btn btn-warning" onclick="clearCache()"><i class="fas fa-trash"></i> Clear Cache</button>
                <button class="btn btn-danger" onclick="restartServer()"><i class="fas fa-sync"></i> Restart Services</button>
            </div>
        </div>
    </div>
    
    <script>
    function createBackup() {
        $.post('api/backup.php', function(data) {
            alert(data.message);
            location.reload();
        });
    }
    function clearCache() {
        $.post('api/clear-cache.php', function(data) {
            alert(data.message);
        });
    }
    function restartServer() {
        if(confirm('Are you sure you want to restart services?')) {
            $.post('api/restart-services.php', function(data) {
                alert(data.message);
            });
        }
    }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>