<?php
/**
 * Create Razorpay Order API Endpoint
 * Handles creating order via Razorpay API when keys are configured
 * Orion Advent Travel Portal
 */
header('Content-Type: application/json; charset=UTF-8');
session_start();
require_once __DIR__ . '/../config/settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'login_required' => true, 'message' => 'Please sign in to proceed with payment.']);
    exit();
}

$settings = getGlobalSettings();
$keyId = trim($settings['razorpay_key_id'] ?? '');
$keySecret = trim($settings['razorpay_key_secret'] ?? '');
$paymentProvider = trim($settings['payment_gateway_provider'] ?? 'razorpay');

$amount = (float)($_POST['amount'] ?? 0); // In INR
$currency = 'INR';
$receipt = 'rcpt_' . substr(md5(uniqid(mt_rand(), true)), 0, 10);

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking advance amount.']);
    exit();
}

$amountInPaise = (int)round($amount * 100);

// Check if valid keys exist
$hasValidKeys = !empty($keyId) && !empty($keySecret) && !str_contains($keyId, 'placeholder');

if ($hasValidKeys && function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'amount' => $amountInPaise,
        'currency' => $currency,
        'receipt' => $receipt,
        'payment_capture' => 1
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        $orderData = json_decode($response, true);
        if (!empty($orderData['id'])) {
            echo json_encode([
                'success' => true,
                'order_id' => $orderData['id'],
                'key_id' => $keyId,
                'amount' => $amountInPaise,
                'currency' => $currency,
                'mode' => 'live_order'
            ]);
            exit();
        }
    }
}

// Fallback for client-side standard or simulation
echo json_encode([
    'success' => true,
    'order_id' => null,
    'key_id' => $keyId,
    'amount' => $amountInPaise,
    'currency' => $currency,
    'mode' => $hasValidKeys ? 'standard' : 'simulation'
]);
