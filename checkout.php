<?php
require_once 'includes/config.php';
if(!isLoggedIn()) { echo json_encode(['success'=>false]); exit(); }
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = $_POST['payment_id'];
    $plan = $_POST['plan'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id'];
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
    $conn->query("UPDATE users SET plan = '$plan', plan_expiry = '$expiry' WHERE id = $user_id");
    $conn->query("INSERT INTO payments (user_id, amount, plan, payment_id, status) VALUES ($user_id, $amount, '$plan', '$payment_id', 'completed')");
    echo json_encode(['success'=>true]);
}
?>