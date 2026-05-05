<?php
require_once 'includes/config.php';
if(!isLoggedIn()) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$bot_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($action == 'start') {
    $bot = $conn->query("SELECT * FROM bots WHERE id = $bot_id AND user_id = $user_id")->fetch_assoc();
    if($bot) {
        require_once 'includes/BotRunner.php';
        $runner = new BotRunner($bot_id, $bot['bot_token'], $bot['bot_name'], $bot['language'], $bot['code']);
        $runner->start();
        $_SESSION['message'] = "Bot started successfully!";
    }
} elseif($action == 'stop') {
    $bot = $conn->query("SELECT * FROM bots WHERE id = $bot_id AND user_id = $user_id")->fetch_assoc();
    if($bot) {
        require_once 'includes/BotRunner.php';
        $runner = new BotRunner($bot_id, $bot['bot_token'], $bot['bot_name'], $bot['language'], $bot['code']);
        $runner->stop();
        $_SESSION['message'] = "Bot stopped successfully!";
    }
} elseif($action == 'restart') {
    $bot = $conn->query("SELECT * FROM bots WHERE id = $bot_id AND user_id = $user_id")->fetch_assoc();
    if($bot) {
        require_once 'includes/BotRunner.php';
        $runner = new BotRunner($bot_id, $bot['bot_token'], $bot['bot_name'], $bot['language'], $bot['code']);
        $runner->restart();
        $_SESSION['message'] = "Bot restarted successfully!";
    }
} elseif($action == 'delete') {
    $bot = $conn->query("SELECT * FROM bots WHERE id = $bot_id AND user_id = $user_id")->fetch_assoc();
    if($bot) {
        require_once 'includes/BotRunner.php';
        $runner = new BotRunner($bot_id, $bot['bot_token'], $bot['bot_name'], $bot['language'], $bot['code']);
        $runner->stop();
        $conn->query("DELETE FROM bots WHERE id = $bot_id");
        $_SESSION['message'] = "Bot deleted successfully!";
    }
}
header("Location: dashboard.php");
exit();
?>