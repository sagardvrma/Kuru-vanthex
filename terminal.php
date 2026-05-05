<?php
require_once 'includes/config.php';
if(!isLoggedIn()) { header("Location: login.php"); exit(); }
$plan = getUserPlan($_SESSION['user_id']);
if($plan == 'free') { header("Location: premium.php?msg=terminal_requires_premium"); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Web Terminal - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0a0e27; font-family: 'Courier New', monospace; }
        .terminal-container { background: #000; border-radius: 15px; padding: 20px; height: calc(100vh - 100px); overflow-y: auto; }
        .terminal-output { color: #00ff00; white-space: pre-wrap; }
        .terminal-input-line { display: flex; align-items: center; background: #000; padding: 10px 0; border-top: 1px solid #333; }
        .terminal-prompt { color: #00ff00; margin-right: 10px; }
        .terminal-input { background: transparent; border: none; color: #00ff00; flex: 1; outline: none; }
        .command-btn { background: #2d2d44; border: none; color: #fff; padding: 8px 15px; margin: 5px; border-radius: 8px; cursor: pointer; }
        .command-btn:hover { background: #6366f1; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 p-3" style="background: #1e1e2f; min-height: 100vh;">
            <h4><i class="fas fa-terminal"></i> Terminal</h4><hr>
            <div id="quickCommands"></div><hr>
            <div class="d-grid gap-2"><a href="dashboard.php" class="btn btn-secondary">Back</a><button class="btn btn-danger" id="clearTerminal">Clear</button></div>
        </div>
        <div class="col-md-9 p-3">
            <div class="terminal-container">
                <div class="terminal-output" id="terminalOutput">
                    <span>═══════════════════════════════════════════════</span><br>
                    <span>Welcome to VANTEX Terminal</span><br>
                    <span>Plan: <?php echo strtoupper($plan); ?> | User: <?php echo $_SESSION['username']; ?></span><br>
                    <span>$ system_ready</span><br>
                </div>
                <div class="terminal-input-line">
                    <span class="terminal-prompt"><?php echo $_SESSION['username']; ?>@vantex:~$</span>
                    <input type="text" class="terminal-input" id="terminalInput" autofocus>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const quickCommands = ['ls -la', 'pwd', 'whoami', 'uptime', 'df -h', 'free -m', 'python3 --version', 'node --version', 'php --version', 'help'];
$(document).ready(function(){ let html=''; quickCommands.forEach(cmd=>{ html+=`<button class="command-btn" onclick="executeCommand('${cmd}')">${cmd}</button>`; }); $('#quickCommands').html(html); $('#terminalInput').focus(); });
$('#terminalInput').on('keypress',function(e){ if(e.which===13){ executeCommand($(this).val()); $(this).val(''); } });
$('#clearTerminal').click(function(){ $('#terminalOutput').html(''); executeCommand('clear'); });
function executeCommand(cmd){ if(cmd.trim()==='') return; if(cmd==='clear'){ $('#terminalOutput').html(''); return; } $('#terminalOutput').append(`<br><span style="color:#ffd700;">${$('#terminalInput').parent().find('.terminal-prompt').text()} ${cmd}</span><br>`); $.post('api/terminal-exec.php',{cmd:cmd},function(res){ $('#terminalOutput').append(`<span>${res}</span><br>`); $('#terminalOutput').scrollTop($('#terminalOutput')[0].scrollHeight); }); }
</script>
</body></html>