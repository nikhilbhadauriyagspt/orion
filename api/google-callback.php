<?php
/**
 * Google OAuth 2.0 Login / Signup Redirection & Callback Controller
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

$settings = getGlobalSettings();
$clientId = isset($settings['google_client_id']) ? trim($settings['google_client_id']) : '';
$clientSecret = isset($settings['google_client_secret']) ? trim($settings['google_client_secret']) : '';
$isActive = isset($settings['google_login_active']) && $settings['google_login_active'] == '1';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$redirectUri = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/google-callback.php';

// If called directly without 'code' parameter, redirect user to Google Login Screen
if (!isset($_GET['code'])) {
    if (!empty($_GET['redirect'])) {
        $_SESSION['auth_redirect'] = trim($_GET['redirect']);
    }
    if (!$isActive || empty($clientId)) {
        header("Location: ../login.php?error=" . urlencode("Google Login is not configured or disabled in Admin Settings."));
        exit();
    }

    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;

    $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'state' => $state,
        'prompt' => 'select_account'
    ]);

    header("Location: " . $googleAuthUrl);
    exit();
}

// -------------------------------------------------------------
// OAUTH CALLBACK PROCESSING
// -------------------------------------------------------------
$code = $_GET['code'];
$returnedState = isset($_GET['state']) ? $_GET['state'] : '';

if (empty($returnedState) || !isset($_SESSION['google_oauth_state']) || $returnedState !== $_SESSION['google_oauth_state']) {
    header("Location: ../login.php?error=" . urlencode("Invalid OAuth state validation. Please try again."));
    exit();
}
unset($_SESSION['google_oauth_state']);

// Exchange Auth Code for Access Token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenPostData = [
    'code' => $code,
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenPostData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    header("Location: ../login.php?error=" . urlencode("Failed to authenticate with Google."));
    exit();
}

// Fetch User Profile with Access Token
$userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
$ch = curl_init($userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$userResponse = curl_exec($ch);
curl_close($ch);

$googleUser = json_decode($userResponse, true);
if (!isset($googleUser['email'])) {
    header("Location: ../login.php?error=" . urlencode("Could not retrieve email from Google profile."));
    exit();
}

$googleEmail = strtolower(trim($googleUser['email']));
$googleName = isset($googleUser['name']) ? trim($googleUser['name']) : 'Google Traveler';
$googleUid = isset($googleUser['sub']) ? trim($googleUser['sub']) : '';
$googleAvatar = isset($googleUser['picture']) ? trim($googleUser['picture']) : '';

$pdo = getDBConnection();
if (!$pdo) {
    header("Location: ../login.php?error=" . urlencode("Database connection offline."));
    exit();
}

// Check if user exists in Database
$stmt = $pdo->prepare("SELECT id, name, email, phone, is_verified, status FROM `users` WHERE `email` = ?");
$stmt->execute([$googleEmail]);
$existingUser = $stmt->fetch();

if ($existingUser) {
    if ($existingUser['status'] === 'blocked') {
        header("Location: ../login.php?error=" . urlencode("Your account has been suspended. Please contact support."));
        exit();
    }

    // Update existing user with Google details and verify
    $update = $pdo->prepare("UPDATE `users` SET `oauth_provider` = 'google', `oauth_uid` = ?, `avatar` = COALESCE(`avatar`, ?), `is_verified` = 1, `last_login` = NOW() WHERE `id` = ?");
    $update->execute([$googleUid, $googleAvatar, $existingUser['id']]);

    $_SESSION['user_id'] = $existingUser['id'];
    $_SESSION['user_name'] = $existingUser['name'];
    $_SESSION['user_email'] = $existingUser['email'];
    $_SESSION['user_phone'] = $existingUser['phone'];

    require_once __DIR__ . '/../includes/notifications.php';
    createAdminNotification(
        'user_login',
        'Google Login',
        $existingUser['name'] . ' (' . $existingUser['email'] . ') logged in via Google OAuth.',
        'users.php'
    );

} else {
    // Register new verified Google user
    $insert = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `avatar`, `oauth_provider`, `oauth_uid`, `is_verified`, `status`, `last_login`) VALUES (?, ?, ?, 'google', ?, 1, 'active', NOW())");
    $insert->execute([$googleName, $googleEmail, $googleAvatar, $googleUid]);
    $newUserId = $pdo->lastInsertId();

    $_SESSION['user_id'] = $newUserId;
    $_SESSION['user_name'] = $googleName;
    $_SESSION['user_email'] = $googleEmail;
    $_SESSION['user_phone'] = '';

    require_once __DIR__ . '/../includes/notifications.php';
    createAdminNotification(
        'user_register',
        'New Google User Registered',
        $googleName . ' (' . $googleEmail . ') registered using Google OAuth.',
        'users.php'
    );
}

// Redirect to Target Page / Homepage
$returnTarget = !empty($_SESSION['auth_redirect']) ? $_SESSION['auth_redirect'] : '../index.php';
unset($_SESSION['auth_redirect']);
header("Location: " . $returnTarget);
exit();
