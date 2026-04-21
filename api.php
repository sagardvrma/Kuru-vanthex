<?php
// ============================================
// KURU PANEL API - RAILWAY VERSION
// ============================================

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Database setup
$db = new SQLite3('kuru_panel.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    key_code TEXT,
    vip INTEGER DEFAULT 0,
    expiry TEXT,
    last_login TEXT,
    last_ip TEXT,
    device TEXT,
    reseller_id INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active'
)");

$db->exec("CREATE TABLE IF NOT EXISTS resellers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT,
    balance INTEGER DEFAULT 0,
    commission INTEGER DEFAULT 30,
    total_sales INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active'
)");

$db->exec("CREATE TABLE IF NOT EXISTS keys_table (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE,
    status TEXT DEFAULT 'unused',
    used_by TEXT,
    created_by INTEGER,
    expiry TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user TEXT,
    action TEXT,
    ip TEXT,
    time TEXT DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY,
    setting_key TEXT UNIQUE,
    setting_value TEXT
)");

// Create owner
$check = $db->querySingle("SELECT COUNT(*) FROM resellers WHERE username = 'owner'");
if ($check == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO resellers (username, password, balance, commission) VALUES ('owner', '$hash', 999999, 100)");
}

$check = $db->querySingle("SELECT COUNT(*) FROM settings WHERE setting_key = 'maintenance'");
if ($check == 0) $db->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance', '0')");

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

function sendResponse($data) {
    echo json_encode($data);
    exit();
}

// ========== PING ==========
if ($action === 'ping') {
    sendResponse(['success' => true, 'message' => 'API is working on Railway!']);
}

// ========== LOGIN ==========
elseif ($action === 'login') {
    $username = $_GET['username'] ?? '';
    $password = $_GET['password'] ?? '';
    
    $stmt = $db->prepare("SELECT * FROM resellers WHERE username = ?");
    $stmt->bindValue(1, $username);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        sendResponse([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['username'] === 'owner' ? 'owner' : 'reseller',
                'balance' => $user['balance']
            ]
        ]);
    } else {
        sendResponse(['success' => false, 'error' => 'Invalid credentials']);
    }
}

// ========== GET STATS ==========
elseif ($action === 'getStats') {
    $role = $_GET['role'] ?? '';
    $id = $_GET['id'] ?? 0;
    
    if ($role === 'owner') {
        sendResponse([
            'totalUsers' => $db->querySingle("SELECT COUNT(*) FROM users"),
            'vipUsers' => $db->querySingle("SELECT COUNT(*) FROM users WHERE vip=1"),
            'totalKeys' => $db->querySingle("SELECT COUNT(*) FROM keys_table"),
            'activeUsers' => $db->querySingle("SELECT COUNT(*) FROM users WHERE last_login > datetime('now', '-1 day')")
        ]);
    } else {
        $balance = $db->querySingle("SELECT balance FROM resellers WHERE id=$id");
        sendResponse(['totalUsers' => 0, 'vipUsers' => 0, 'totalKeys' => 0, 'activeUsers' => 0, 'myBalance' => $balance]);
    }
}

// ========== GET USERS ==========
elseif ($action === 'getUsers') {
    $role = $_GET['role'] ?? '';
    $id = $_GET['id'] ?? 0;
    
    if ($role === 'owner') {
        $result = $db->query("SELECT id,username,key_code,vip,expiry,last_login,device,reseller_id,status FROM users ORDER BY id DESC");
    } else {
        $result = $db->query("SELECT id,username,key_code,vip,expiry,last_login,device,reseller_id,status FROM users WHERE reseller_id=$id ORDER BY id DESC");
    }
    
    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }
    sendResponse($users);
}

// ========== ADD USER ==========
elseif ($action === 'addUser') {
    $data = json_decode(file_get_contents('php://input'), true);
    $role = $_GET['role'] ?? '';
    $rid = $_GET['id'] ?? 0;
    $expiry = date('Y-m-d', strtotime("+" . intval($data['days']) . " days"));
    
    if ($role === 'reseller') {
        $balance = $db->querySingle("SELECT balance FROM resellers WHERE id=$rid");
        if ($balance < 1) sendResponse(['success' => false, 'error' => 'Insufficient credits!']);
        $db->exec("UPDATE resellers SET balance = balance - 1 WHERE id=$rid");
        $db->exec("UPDATE resellers SET total_sales = total_sales + 1 WHERE id=$rid");
    }
    
    $stmt = $db->prepare("INSERT INTO users (username, key_code, vip, expiry, reseller_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->bindValue(1, $data['username']);
    $stmt->bindValue(2, $data['key']);
    $stmt->bindValue(3, intval($data['vip']));
    $stmt->bindValue(4, $expiry);
    $stmt->bindValue(5, $role === 'owner' ? 0 : $rid);
    $stmt->execute();
    sendResponse(['success' => true]);
}

// ========== UPDATE USER ==========
elseif ($action === 'updateUser') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($_GET['id'] ?? 0);
    $updates = [];
    
    if (isset($data['username'])) $updates[] = "username = '{$data['username']}'";
    if (isset($data['key'])) $updates[] = "key_code = '{$data['key']}'";
    if (isset($data['vip'])) $updates[] = "vip = " . intval($data['vip']);
    if (isset($data['status'])) $updates[] = "status = '{$data['status']}'";
    if (isset($data['days']) && $data['days'] > 0) $updates[] = "expiry = date('now', '+{$data['days']} days')";
    
    if (!empty($updates)) {
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = $id";
        $db->exec($sql);
        sendResponse(['success' => true]);
    } else {
        sendResponse(['success' => false, 'error' => 'No updates']);
    }
}

// ========== DELETE USER ==========
elseif ($action === 'deleteUser') {
    $id = intval($_GET['id'] ?? 0);
    $db->exec("DELETE FROM users WHERE id=$id");
    sendResponse(['success' => true]);
}

// ========== GET RESELLERS ==========
elseif ($action === 'getResellers') {
    $result = $db->query("SELECT id,username,balance,total_sales,commission FROM resellers WHERE username!='owner' ORDER BY id DESC");
    $resellers = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $resellers[] = $row;
    }
    sendResponse($resellers);
}

// ========== ADD RESELLER ==========
elseif ($action === 'addReseller') {
    $data = json_decode(file_get_contents('php://input'), true);
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO resellers (username, password, balance, commission) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $data['username']);
    $stmt->bindValue(2, $hash);
    $stmt->bindValue(3, intval($data['credits']));
    $stmt->bindValue(4, intval($data['commission']));
    $stmt->execute();
    sendResponse(['success' => true]);
}

// ========== ADD CREDITS ==========
elseif ($action === 'addCredits') {
    $id = intval($_GET['id'] ?? 0);
    $credits = intval($_GET['credits'] ?? 0);
    $db->exec("UPDATE resellers SET balance = balance + $credits WHERE id=$id");
    sendResponse(['success' => true]);
}

// ========== DELETE RESELLER ==========
elseif ($action === 'deleteReseller') {
    $id = intval($_GET['id'] ?? 0);
    $db->exec("DELETE FROM resellers WHERE id=$id");
    sendResponse(['success' => true]);
}

// ========== GET KEYS ==========
elseif ($action === 'getKeys') {
    $result = $db->query("SELECT * FROM keys_table ORDER BY id DESC");
    $keys = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $keys[] = $row;
    }
    sendResponse($keys);
}

// ========== GENERATE KEY ==========
elseif ($action === 'generateKey') {
    $days = intval($_GET['days'] ?? 30);
    $role = $_GET['role'] ?? '';
    $id = intval($_GET['id'] ?? 0);
    
    if ($role === 'reseller') {
        $balance = $db->querySingle("SELECT balance FROM resellers WHERE id=$id");
        if ($balance < 1) sendResponse(['success' => false, 'error' => 'Insufficient credits!']);
        $db->exec("UPDATE resellers SET balance = balance - 1 WHERE id=$id");
    }
    
    $code = strtoupper(substr(bin2hex(random_bytes(8)), 0, 16));
    $expiry = date('Y-m-d', strtotime("+$days days"));
    
    $stmt = $db->prepare("INSERT INTO keys_table (code, status, created_by, expiry) VALUES (?, 'unused', ?, ?)");
    $stmt->bindValue(1, $code);
    $stmt->bindValue(2, $id);
    $stmt->bindValue(3, $expiry);
    $stmt->execute();
    sendResponse(['success' => true, 'key' => $code]);
}

// ========== DELETE KEY ==========
elseif ($action === 'deleteKey') {
    $code = $_GET['code'] ?? '';
    $db->exec("DELETE FROM keys_table WHERE code='$code'");
    sendResponse(['success' => true]);
}

// ========== GET LOGS ==========
elseif ($action === 'getLogs') {
    $result = $db->query("SELECT * FROM logs ORDER BY id DESC LIMIT 50");
    $logs = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $logs[] = $row;
    }
    sendResponse($logs);
}

// ========== CHANGE PASSWORD ==========
elseif ($action === 'changePassword') {
    $old = $_GET['old'] ?? '';
    $new = $_GET['new'] ?? '';
    $id = intval($_GET['id'] ?? 0);
    
    $currentHash = $db->querySingle("SELECT password FROM resellers WHERE id=$id");
    if (!password_verify($old, $currentHash)) {
        sendResponse(['success' => false, 'error' => 'Current password incorrect']);
    }
    if (strlen($new) < 4) {
        sendResponse(['success' => false, 'error' => 'Password too short']);
    }
    
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $db->exec("UPDATE resellers SET password='$newHash' WHERE id=$id");
    sendResponse(['success' => true]);
}

// ========== GET MAINTENANCE ==========
elseif ($action === 'getMaintenance') {
    $value = $db->querySingle("SELECT setting_value FROM settings WHERE setting_key='maintenance'");
    sendResponse(['maintenance' => ($value == '1')]);
}

// ========== SET MAINTENANCE ==========
elseif ($action === 'setMaintenance') {
    $enable = $_GET['enable'] ?? 'false';
    $value = ($enable === 'true') ? '1' : '0';
    $db->exec("INSERT OR REPLACE INTO settings (setting_key, setting_value) VALUES ('maintenance', '$value')");
    sendResponse(['success' => true]);
}

// ========== GET SETTINGS ==========
elseif ($action === 'getSettings') {
    $result = $db->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    sendResponse($settings);
}

// ========== REGENERATE API KEY ==========
elseif ($action === 'regenerateApiKey') {
    $apiKey = bin2hex(random_bytes(32));
    $db->exec("INSERT OR REPLACE INTO settings (setting_key, setting_value) VALUES ('api_key', '$apiKey')");
    sendResponse(['apiKey' => $apiKey]);
}

// ========== GET CUSTOMIZATION ==========
elseif ($action === 'getCustomization') {
    $link = $db->querySingle("SELECT setting_value FROM settings WHERE setting_key='panel_link'");
    $desc = $db->querySingle("SELECT setting_value FROM settings WHERE setting_key='panel_desc'");
    sendResponse(['panelLink' => $link ?: '', 'panelDesc' => $desc ?: '']);
}

// ========== SAVE CUSTOMIZATION ==========
elseif ($action === 'saveCustomization') {
    $link = $_GET['link'] ?? '';
    $desc = $_GET['desc'] ?? '';
    $db->exec("INSERT OR REPLACE INTO settings (setting_key, setting_value) VALUES ('panel_link', '$link')");
    $db->exec("INSERT OR REPLACE INTO settings (setting_key, setting_value) VALUES ('panel_desc', '$desc')");
    sendResponse(['success' => true]);
}

// ========== GET RECENT LOGINS ==========
elseif ($action === 'getRecentLogins') {
    $result = $db->query("SELECT username, last_ip, device, last_login FROM users WHERE last_login IS NOT NULL ORDER BY last_login DESC LIMIT 20");
    $logins = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $logins[] = ['username' => $row['username'], 'ip' => $row['last_ip'], 'device' => $row['device'], 'time' => $row['last_login']];
    }
    sendResponse($logins);
}

// ========== GET ONLINE USERS ==========
elseif ($action === 'getOnlineUsers') {
    $result = $db->query("SELECT username, last_ip, device, last_login as last_active FROM users WHERE last_login > datetime('now', '-5 minutes') ORDER BY last_login DESC");
    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }
    sendResponse($users);
}

// ========== CHECK UPDATE ==========
elseif ($action === 'checkUpdate') {
    sendResponse(['hasUpdate' => false, 'latestVersion' => 'v11.0', 'changelog' => ['Railway deployment successful!']]);
}

else {
    sendResponse(['error' => 'Invalid action: ' . $action, 'success' => false, 'available_actions' => ['ping', 'login', 'getStats', 'getUsers', 'addUser', 'deleteUser', 'getResellers', 'addReseller', 'addCredits', 'deleteReseller', 'getKeys', 'generateKey', 'deleteKey', 'getLogs', 'changePassword', 'getMaintenance', 'setMaintenance', 'getSettings', 'regenerateApiKey', 'getCustomization', 'saveCustomization', 'getRecentLogins', 'getOnlineUsers', 'checkUpdate']]);
}
?>
