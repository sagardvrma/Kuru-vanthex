<?php
require_once 'includes/config.php';
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

if(isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    $google_oauth = new Google_Service_Oauth2($client);
    $info = $google_oauth->userinfo->get();
    
    $email = $info->email;
    $name = $info->name;
    $google_id = $info->id;
    
    $result = $conn->query("SELECT * FROM users WHERE email = '$email' OR google_id = '$google_id'");
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
    } else {
        $username = explode('@', $email)[0];
        $api_key = generateAPIKey();
        $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, google_id, api_key) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $name, $google_id, $api_key);
        $stmt->execute();
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['username'] = $username;
    }
    header("Location: dashboard.php");
    exit();
}
header("Location: login.php");
?>