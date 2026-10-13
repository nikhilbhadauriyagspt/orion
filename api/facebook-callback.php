<?php
/**
 * Facebook OAuth Login / Signup Redirection & Callback Controller
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

$settings = getGlobalSettings();
$appId = isset($settings['facebook_app_id']) ? trim($settings['facebook_app_id']) : '';
$appSecret = isset($settings['facebook_app_secret']) ? trim($settings['facebook_app_secret']) : '';
$isActive = isset($settings['facebook_login_active']) && $settings['facebook_login_active'] == '1';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$redirectUri = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/facebook-callback.php';

// If called directly without 'code' parameter, redirect user to Facebook Login Screen
if (!isset($_GET['code'])) {
    if (!empty($_GET['redirect'])) {
        $_SESSION['auth_redirect'] = trim($_GET['redirect']);
    }
    if (!$isActive || empty($appId)) {
        header("Location: ../login.php?error=" . urlencode("Facebook Login is not configured or disabled in Admin Settings."));
        exit();
    }

    $state = bin2hex(random_bytes(16));
    $_SESSION['fb_oauth_state'] = $state;

    $fbAuthUrl = 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
        'client_id' => $appId,
        'redirect_uri' => $redirectUri,
        'state' => $state,
        'scope' => 'email,public_profile'
    ]);

    header("Location: " . $fbAuthUrl);
    exit();
}

// -------------------------------------------------------------
// OAUTH CALLBACK PROCESSING
// -------------------------------------------------------------
$code = $_GET['code'];
$returnedState = isset($_GET['state']) ? $_GET['state'] : '';

if (empty($returnedState) || !isset($_SESSION['fb_oauth_state']) || $returnedState !== $_SESSION['fb_oauth_state']) {
    header("Location: ../login.php?error=" . urlencode("Invalid Facebook OAuth state validation. Please try again."));
    exit();
}
unset($_SESSION['fb_oauth_state']);

// Exchange Auth Code for Access Token
$tokenUrl = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
    'client_id' => $appId,
    'client_secret' => $appSecret,
    'redirect_uri' => $redirectUri,
    'code' => $code
]);

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    header("Location: ../login.php?error=" . urlencode("Failed to authenticate with Facebook."));
    exit();
}

// Fetch User Profile with Access Token
$graphUrl = 'https://graph.facebook.com/v19.0/me?fields=id,name,email,picture.type(large)&access_token=' . $tokenData['access_token'];
$ch = curl_init($graphUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$userResponse = curl_exec($ch);
curl_close($ch);

$fbUser = json_decode($userResponse, true);
if (!isset($fbUser['id'])) {
    header("Location: ../login.php?error=" . urlencode("Could not retrieve profile from Facebook."));
    exit();
}

$fbUid = trim($fbUser['id']);
$fbName = isset($fbUser['name']) ? trim($fbUser['name']) : 'Facebook Traveler';
$fbEmail = isset($fbUser['email']) ? strtolower(trim($fbUser['email'])) : ($fbUid . '@facebook.orionadvent.com');
$fbAvatar = isset($fbUser['picture']['data']['url']) ? trim($fbUser['picture']['data']['url']) : '';

$pdo = getDBConnection();
if (!$pdo) {
    header("Location: ../login.php?error=" . urlencode("Database connection offline."));
    exit();
}

// Check if user exists in Database
$stmt = $pdo->prepare("SELECT id, name, email, phone, is_verified, status FROM `users` WHERE `email` = ? OR (`oauth_provider` = 'facebook' AND `oauth_uid` = ?)");
$stmt->execute([$fbEmail, $fbUid]);
$existingUser = $stmt->fetch();

if ($existingUser) {
    if ($existingUser['status'] === 'blocked') {
        header("Location: ../login.php?error=" . urlencode("Your account has been suspended. Please contact support."));
        exit();
    }

    $update = $pdo->prepare("UPDATE `users` SET `oauth_provider` = 'facebook', `oauth_uid` = ?, `avatar` = COALESCE(`avatar`, ?), `is_verified` = 1, `last_login` = NOW() WHERE `id` = ?");
    $update->execute([$fbUid, $fbAvatar, $existingUser['id']]);

    $_SESSION['user_id'] = $existingUser['id'];
    $_SESSION['user_name'] = $existingUser['name'];
    $_SESSION['user_email'] = $existingUser['email'];
    $_SESSION['user_phone'] = $existingUser['phone'];

} else {
    $insert = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `avatar`, `oauth_provider`, `oauth_uid`, `is_verified`, `status`, `last_login`) VALUES (?, ?, ?, 'facebook', ?, 1, 'active', NOW())");
    $insert->execute([$fbName, $fbEmail, $fbAvatar, $fbUid]);
    $newUserId = $pdo->lastInsertId();

    $_SESSION['user_id'] = $newUserId;
    $_SESSION['user_name'] = $fbName;
    $_SESSION['user_email'] = $fbEmail;
    $_SESSION['user_phone'] = '';
}

// Redirect to Target Page / Homepage
$returnTarget = !empty($_SESSION['auth_redirect']) ? $_SESSION['auth_redirect'] : '../index.php';
unset($_SESSION['auth_redirect']);
header("Location: " . $returnTarget);
exit();
