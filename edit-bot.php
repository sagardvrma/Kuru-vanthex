<?php
require_once 'includes/config.php';
require_once 'includes/BotRunner.php';
if(!isLoggedIn()) { header("Location: login.php"); exit(); }
$bot_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$bot = $conn->query("SELECT * FROM bots WHERE id = $bot_id AND user_id = $user_id")->fetch_assoc();
if(!$bot) { header("Location: dashboard.php"); exit(); }
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $conn->real_escape_string($_POST['code']);
    $conn->query("UPDATE bots SET code = '$code' WHERE id = $bot_id");
    $runner = new BotRunner($bot_id, $bot['bot_token'], $bot['bot_name'], $bot['language'], $code);
    if($bot['status'] == 'running') $runner->restart();
    header("Location: dashboard.php?success=updated");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Bot - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/theme/dracula.css" rel="stylesheet">
    <style>body{background:#0f172a;color:white;}.card{background:#1e293b;border-radius:20px;}.CodeMirror{height:500px;}</style>
</head>
<body>
<div class="container py-5"><div class="d-flex justify-content-between"><h2><i class="fas fa-edit"></i> Edit Bot: <?php echo htmlspecialchars($bot['bot_name']); ?></h2><a href="dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card mt-4"><div class="card-body"><form method="POST"><textarea name="code" id="codeEditor" style="display:none;"><?php echo htmlspecialchars($bot['code']); ?></textarea>
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
<button type="button" class="btn btn-danger" onclick="window.location.href='api/delete.php?id=<?php echo $bot_id; ?>'"><i class="fas fa-trash"></i> Delete</button>
<button type="button" class="btn btn-success" onclick="window.location.href='api/start.php?id=<?php echo $bot_id; ?>'"><i class="fas fa-play"></i> Start</button>
<button type="button" class="btn btn-warning" onclick="window.location.href='api/stop.php?id=<?php echo $bot_id; ?>'"><i class="fas fa-stop"></i> Stop</button>
<button type="button" class="btn btn-info" onclick="window.location.href='api/restart.php?id=<?php echo $bot_id; ?>'"><i class="fas fa-sync"></i> Restart</button>
<button type="button" class="btn btn-secondary" onclick="window.open('api/get-logs.php?id=<?php echo $bot_id; ?>','_blank')"><i class="fas fa-terminal"></i> Logs</button></div></form></div></div></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/python/python.js"></script>
<script>
let editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {lineNumbers:true, mode:'python', theme:'dracula', autoCloseBrackets:true, lineWrapping:true});
</script>
</body></html>