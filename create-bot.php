<?php
require_once 'includes/config.php';
require_once 'includes/BotRunner.php';
if(!isLoggedIn()) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$limits = getPlanLimits($user_id);
$current_bots = $conn->query("SELECT COUNT(*) FROM bots WHERE user_id = $user_id")->fetch_row()[0];
if($limits['bots'] != -1 && $current_bots >= $limits['bots']) { header("Location: dashboard.php?error=limit"); exit(); }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bot_token = $conn->real_escape_string($_POST['bot_token']);
    $bot_name = $conn->real_escape_string($_POST['bot_name']);
    $bot_username = $conn->real_escape_string($_POST['bot_username']);
    $language = $conn->real_escape_string($_POST['language']);
    $code = $conn->real_escape_string($_POST['code']);
    $url = "https://api.telegram.org/bot$bot_token/getMe";
    $response = @file_get_contents($url);
    $data = json_decode($response, true);
    if($data && $data['ok']) {
        $stmt = $conn->prepare("INSERT INTO bots (user_id, bot_token, bot_name, bot_username, language, code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $bot_token, $bot_name, $bot_username, $language, $code);
        if($stmt->execute()) {
            $bot_id = $conn->insert_id;
            $runner = new BotRunner($bot_id, $bot_token, $bot_name, $language, $code);
            $runner->deploy();
            header("Location: dashboard.php?success=created");
            exit();
        }
    } else $error = "Invalid bot token!";
}

$templates = [
    'python' => "import telebot\n\nbot = telebot.TeleBot('YOUR_BOT_TOKEN')\n\n@bot.message_handler(commands=['start'])\ndef start(message):\n    bot.reply_to(message, 'Hello! I am alive!')\n\nbot.infinity_polling()",
    'nodejs' => "const TelegramBot = require('node-telegram-bot-api');\nconst token = 'YOUR_BOT_TOKEN';\nconst bot = new TelegramBot(token, {polling: true});\n\nbot.onText(/\\/start/, (msg) => {\n    bot.sendMessage(msg.chat.id, 'Hello! I am alive!');\n});",
    'php' => "<?php\n$token = 'YOUR_BOT_TOKEN';\n$update = json_decode(file_get_contents('php://input'), true);\nif(isset($update['message'])) {\n    $chat_id = $update['message']['chat']['id'];\n    if($update['message']['text'] == '/start') {\n        file_get_contents(\"https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=Hello! I am alive!\");\n    }\n}",
    'bjs' => "// Bot Business JavaScript Mode\nBot.onText(/\\/start/, (msg) => {\n    Bot.sendMessage(msg.chat.id, 'Hello from BJS Bot!');\n});"
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Bot - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/theme/dracula.css" rel="stylesheet">
    <style>body{background:#0f172a;color:white;}.card{background:#1e293b;border-radius:20px;}.CodeMirror{height:400px;border-radius:15px;}</style>
</head>
<body>
<div class="container py-5"><div class="d-flex justify-content-between"><h2><i class="fas fa-plus-circle"></i> Create New Bot</h2><a href="dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="card mt-4"><div class="card-body"><form method="POST"><div class="row"><div class="col-md-6 mb-3"><label>Bot Token</label><input type="text" name="bot_token" class="form-control bg-dark text-white" required placeholder="Enter bot token from @BotFather"></div>
<div class="col-md-6 mb-3"><label>Bot Name</label><input type="text" name="bot_name" class="form-control bg-dark text-white" required></div>
<div class="col-md-6 mb-3"><label>Bot Username</label><input type="text" name="bot_username" class="form-control bg-dark text-white" required placeholder="@yourbot"></div>
<div class="col-md-6 mb-3"><label>Language</label><select name="language" id="language" class="form-select bg-dark text-white"><option value="python">🐍 Python</option><option value="nodejs">🟢 Node.js</option><option value="php">🐘 PHP</option><option value="bjs">⚡ Bot Business JS</option></select></div></div>
<div class="mb-3"><label>Bot Code</label><textarea name="code" id="codeEditor" style="display:none;"><?php echo htmlspecialchars($templates['python']); ?></textarea></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-rocket"></i> Deploy Bot</button></form></div></div></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/python/python.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/javascript/javascript.js"></script>
<script>
const templates = <?php echo json_encode($templates); ?>;
let editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {lineNumbers:true, mode:'python', theme:'dracula', autoCloseBrackets:true});
$('#language').change(function(){ let lang=$(this).val(); let modes={python:'python',nodejs:'javascript',php:'php',bjs:'javascript'}; editor.setOption('mode',modes[lang]); editor.setValue(templates[lang]); });
</script>
</body></html>