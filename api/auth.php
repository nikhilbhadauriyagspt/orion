<?php
/**
 * Unified Auth API
 * Handles: Signup, Verify Signup OTP, Resend OTP, Login, Forgot Password OTP, Reset Password, Logout
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/mailer.php';

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please run admin/setup.php.']);
    exit();
}

$action = isset($_POST['action']) ? trim($_POST['action']) : (isset($_GET['action']) ? trim($_GET['action']) : '');

switch ($action) {

    // ============================================================
    // 1. SIGNUP & SEND OTP
    // ============================================================
    case 'signup':
        $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($fullname) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address format.']);
            exit();
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
            exit();
        }

        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id, is_verified FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();

        if ($existing && $existing['is_verified'] == 1) {
            echo json_encode(['success' => false, 'message' => 'An active account already exists with this email. Please Log In.']);
            exit();
        }

        // Generate OTP & Expiry (10 minutes)
        $settings = getGlobalSettings();
        $mailMode = isset($settings['mail_mode']) ? $settings['mail_mode'] : 'testing';
        $otp = ($mailMode === 'testing') ? $settings['testing_otp'] : str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if ($existing && $existing['is_verified'] == 0) {
            // Update existing unverified user
            $update = $pdo->prepare("UPDATE `users` SET `name` = ?, `phone` = ?, `password` = ?, `otp_code` = ?, `otp_expires_at` = ? WHERE `id` = ?");
            $update->execute([$fullname, $phone, $hashedPassword, $otp, $expiresAt, $existing['id']]);
        } else {
            // Insert new user
            $insert = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `phone`, `password`, `is_verified`, `otp_code`, `otp_expires_at`, `status`) VALUES (?, ?, ?, ?, 0, ?, ?, 'active')");
            $insert->execute([$fullname, $email, $phone, $hashedPassword, $otp, $expiresAt]);
        }

        // Send Email with OTP
        $emailHtml = getOtpEmailTemplate($fullname, $otp, 'signup');
        $mailRes = sendPortalMail($email, $fullname, "Your One-Time Verification Code for " . $settings['site_name'], $emailHtml);

        $_SESSION['temp_verify_email'] = $email;

        echo json_encode([
            'success' => true,
            'message' => ($mailMode === 'testing') ? "Testing Mode Active. Your OTP is: $otp" : "A 6-digit verification code has been sent to your email.",
            'email' => $email,
            'mail_mode' => $mailMode,
            'testing_otp' => ($mailMode === 'testing') ? $otp : null
        ]);
        break;

    // ============================================================
    // 2. VERIFY SIGNUP OTP & ACTIVATE ACCOUNT
    // ============================================================
    case 'verify_signup_otp':
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : (isset($_SESSION['temp_verify_email']) ? $_SESSION['temp_verify_email'] : '');
        $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

        if (empty($email) || empty($otp)) {
            echo json_encode(['success' => false, 'message' => 'Email and OTP code are required.']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, name, email, phone, otp_code, otp_expires_at FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found. Please sign up again.']);
            exit();
        }

        $settings = getGlobalSettings();
        $mailMode = isset($settings['mail_mode']) ? $settings['mail_mode'] : 'testing';
        $testingOtp = isset($settings['testing_otp']) ? $settings['testing_otp'] : '123456';

        $isValid = false;
        if ($mailMode === 'testing' && ($otp === $testingOtp || $otp === '1234' || $otp === '123456')) {
            $isValid = true;
        } elseif ($user['otp_code'] === $otp && strtotime($user['otp_expires_at']) >= time()) {
            $isValid = true;
        }

        if (!$isValid) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP code. Please try again or resend.']);
            exit();
        }

        // Activate User Account
        $activate = $pdo->prepare("UPDATE `users` SET `is_verified` = 1, `otp_code` = NULL, `otp_expires_at` = NULL, `last_login` = NOW() WHERE `id` = ?");
        $activate->execute([$user['id']]);

        // Auto-login session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        unset($_SESSION['temp_verify_email']);

        // Trigger Real-Time Admin Notification
        require_once __DIR__ . '/../includes/notifications.php';
        createAdminNotification(
            'user_register',
            'New Traveler Registration',
            $user['name'] . ' (' . $user['email'] . ') registered a new traveler account.',
            'users.php',
            ['user_id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]
        );

        $redirect = isset($_POST['redirect']) && !empty(trim($_POST['redirect'])) ? trim($_POST['redirect']) : 'index.php';
        // Sanitize redirect to prevent open redirect vulnerabilities
        if (preg_match('/^https?:\/\//i', $redirect)) {
            $parsed = parse_url($redirect);
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            if (isset($parsed['host']) && $parsed['host'] !== $currentHost) {
                $redirect = 'index.php';
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Your account has been verified and created successfully!',
            'redirect' => $redirect
        ]);
        break;

    // ============================================================
    // 3. LOGIN USER
    // ============================================================
    case 'login':
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, name, email, phone, password, is_verified, status FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address or password.']);
            exit();
        }

        if ($user['status'] === 'blocked') {
            echo json_encode(['success' => false, 'message' => 'Your account has been suspended. Please contact customer support.']);
            exit();
        }

        if ($user['is_verified'] == 0) {
            // Trigger new verification OTP
            $settings = getGlobalSettings();
            $mailMode = isset($settings['mail_mode']) ? $settings['mail_mode'] : 'testing';
            $otp = ($mailMode === 'testing') ? $settings['testing_otp'] : str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $update = $pdo->prepare("UPDATE `users` SET `otp_code` = ?, `otp_expires_at` = ? WHERE `id` = ?");
            $update->execute([$otp, $expiresAt, $user['id']]);

            $emailHtml = getOtpEmailTemplate($user['name'], $otp, 'signup');
            sendPortalMail($email, $user['name'], "Verify Your Account - " . $settings['site_name'], $emailHtml);

            $_SESSION['temp_verify_email'] = $email;

            echo json_encode([
                'success' => false,
                'require_verification' => true,
                'email' => $email,
                'message' => 'Your email is not verified yet. We have sent a verification code to your email.'
            ]);
            exit();
        }

        // Login Success
        $pdo->prepare("UPDATE `users` SET `last_login` = NOW() WHERE `id` = ?")->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];

        // Trigger Real-Time Admin Notification
        require_once __DIR__ . '/../includes/notifications.php';
        createAdminNotification(
            'user_login',
            'Traveler Logged In',
            $user['name'] . ' (' . $user['email'] . ') logged into the portal.',
            'users.php',
            ['user_id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]
        );

        $redirect = isset($_POST['redirect']) && !empty(trim($_POST['redirect'])) ? trim($_POST['redirect']) : 'index.php';
        // Sanitize redirect
        if (preg_match('/^https?:\/\//i', $redirect)) {
            $parsed = parse_url($redirect);
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            if (isset($parsed['host']) && $parsed['host'] !== $currentHost) {
                $redirect = 'index.php';
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Welcome back, ' . $user['name'] . '!',
            'redirect' => $redirect
        ]);
        break;

    // ============================================================
    // 4. FORGOT PASSWORD - SEND OTP
    // ============================================================
    case 'forgot_password_send_otp':
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Please enter your registered email address.']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, name, email FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'No account found with this email address.']);
            exit();
        }

        $settings = getGlobalSettings();
        $mailMode = isset($settings['mail_mode']) ? $settings['mail_mode'] : 'testing';
        $otp = ($mailMode === 'testing') ? $settings['testing_otp'] : str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $update = $pdo->prepare("UPDATE `users` SET `otp_code` = ?, `otp_expires_at` = ? WHERE `id` = ?");
        $update->execute([$otp, $expiresAt, $user['id']]);

        $emailHtml = getOtpEmailTemplate($user['name'], $otp, 'forgot');
        sendPortalMail($email, $user['name'], "Password Reset Code - " . $settings['site_name'], $emailHtml);

        $_SESSION['temp_forgot_email'] = $email;

        echo json_encode([
            'success' => true,
            'message' => ($mailMode === 'testing') ? "Testing Mode Active. Your Reset OTP is: $otp" : "Password reset verification code sent to your email.",
            'email' => $email,
            'mail_mode' => $mailMode,
            'testing_otp' => ($mailMode === 'testing') ? $otp : null
        ]);
        break;

    // ============================================================
    // 5. RESET PASSWORD WITH OTP
    // ============================================================
    case 'reset_password':
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : (isset($_SESSION['temp_forgot_email']) ? $_SESSION['temp_forgot_email'] : '');
        $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
        $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

        if (empty($email) || empty($otp) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields (Email, OTP, New Password) are required.']);
            exit();
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long.']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, name, otp_code, otp_expires_at FROM `users` WHERE `email` = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User account not found.']);
            exit();
        }

        $settings = getGlobalSettings();
        $mailMode = isset($settings['mail_mode']) ? $settings['mail_mode'] : 'testing';
        $testingOtp = isset($settings['testing_otp']) ? $settings['testing_otp'] : '123456';

        $isValid = false;
        if ($mailMode === 'testing' && ($otp === $testingOtp || $otp === '1234' || $otp === '123456')) {
            $isValid = true;
        } elseif ($user['otp_code'] === $otp && strtotime($user['otp_expires_at']) >= time()) {
            $isValid = true;
        }

        if (!$isValid) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP code. Please request a new code.']);
            exit();
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $pdo->prepare("UPDATE `users` SET `password` = ?, `otp_code` = NULL, `otp_expires_at` = NULL WHERE `id` = ?");
        $update->execute([$hashedPassword, $user['id']]);

        unset($_SESSION['temp_forgot_email']);

        echo json_encode([
            'success' => true,
            'message' => 'Your password has been reset successfully! Please sign in with your new password.',
            'redirect' => 'login.php'
        ]);
        break;

    // ============================================================
    // 6. LOGOUT
    // ============================================================
    case 'logout':
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_phone']);
        echo json_encode(['success' => true, 'redirect' => 'index.php']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid API Action.']);
        break;
}
