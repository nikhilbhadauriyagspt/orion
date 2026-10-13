<?php
/**
 * Real-time Tour Package Booking API Endpoint with Login Enforce & Token Advance
 * Orion Advent Travel Portal
 */
header('Content-Type: application/json; charset=UTF-8');
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$siteName = getSetting('site_name', 'Orion Advent');

// 1. Mandatory User Login Check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'login_required' => true,
        'message' => 'Please sign in to your ' . $siteName . ' account to complete your tour booking.'
    ]);
    exit();
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$leadName = trim($_POST['lead_name'] ?? ($_SESSION['user_name'] ?? ''));
$leadEmail = trim($_POST['lead_email'] ?? ($_SESSION['user_email'] ?? ''));
$leadPhone = trim($_POST['lead_phone'] ?? ($_SESSION['user_phone'] ?? ''));

$packageId = trim($_POST['package_id'] ?? '');
$packageTitle = trim($_POST['package_title'] ?? 'Holiday Tour');
$travelDate = trim($_POST['travel_date'] ?? date('Y-m-d', strtotime('+7 days')));
$adultsCount = max(1, (int)($_POST['adults_count'] ?? 2));
$childrenCount = max(0, (int)($_POST['children_count'] ?? 0));
$specialNotes = trim($_POST['special_notes'] ?? '');

$perPersonPrice = (float)($_POST['per_person_price'] ?? 15000);
$totalAmount = (float)($_POST['total_amount'] ?? ($perPersonPrice * $adultsCount));

if (empty($leadName) || empty($leadPhone)) {
    echo json_encode(['success' => false, 'message' => 'Please provide your full traveler name and contact phone number.']);
    exit();
}

// 2. Calculate Partial / Advance Token Amount based on Admin Settings
$settings = getGlobalSettings();
$advType = $settings['booking_advance_type'] ?? 'percentage';
$advPercent = (float)($settings['booking_advance_percent'] ?? 30);
$advFixed = (float)($settings['booking_advance_fixed'] ?? 2500);

// If package has a specific token advance, allow it or use percentage
$advanceAmount = 0.0;
if ($advType === 'fixed') {
    $advanceAmount = min($totalAmount, $advFixed * $adultsCount);
} else {
    $advanceAmount = round(($totalAmount * ($advPercent / 100)), 2);
}

// Guarantee advance amount is at least 1 and does not exceed total
if ($advanceAmount <= 0) $advanceAmount = $totalAmount;
if ($advanceAmount > $totalAmount) $advanceAmount = $totalAmount;
$balanceAmount = max(0, round($totalAmount - $advanceAmount, 2));

// Payment details
$paymentGateway = trim($_POST['payment_gateway'] ?? 'razorpay');
$paymentId = trim($_POST['razorpay_payment_id'] ?? ('PAY_' . strtoupper(substr(md5(uniqid()), 0, 10))));

try {
    $bookingCode = 'GFX-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    $fullPackageTitle = $packageTitle . ' (' . $adultsCount . ' Adult' . ($adultsCount > 1 ? 's' : '') . ($childrenCount > 0 ? ', ' . $childrenCount . ' Child' : '') . ')';

    $detailsJson = json_encode([
        'package_id' => $packageId,
        'adults' => $adultsCount,
        'children' => $childrenCount,
        'per_person_price' => $perPersonPrice,
        'advance_type' => $advType,
        'advance_percent' => $advPercent,
        'special_notes' => $specialNotes,
        'gateway' => $paymentGateway,
        'booked_at' => date('Y-m-d H:i:s')
    ]);

    $stmt = $pdo->prepare("INSERT INTO `bookings` (
        `booking_code`, `user_id`, `booking_type`, `customer_name`, `customer_email`, `customer_phone`, 
        `package_title`, `travel_date`, `amount`, `advance_amount`, `balance_amount`,
        `payment_gateway`, `payment_id`, `booking_details`, `status`
    ) VALUES (?, ?, 'package', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");

    $stmt->execute([
        $bookingCode,
        $userId,
        $leadName,
        $leadEmail,
        $leadPhone,
        $fullPackageTitle,
        $travelDate,
        $totalAmount,
        $advanceAmount,
        $balanceAmount,
        $paymentGateway,
        $paymentId,
        $detailsJson
    ]);

    // 3. Send Rich Confirmation Email
    $bookingData = [
        'booking_code' => $bookingCode,
        'customer_name' => $leadName,
        'customer_email' => $leadEmail,
        'customer_phone' => $leadPhone,
        'package_title' => $packageTitle,
        'travel_date' => $travelDate,
        'amount' => $totalAmount,
        'advance_amount' => $advanceAmount,
        'balance_amount' => $balanceAmount,
        'payment_id' => $paymentId,
        'booking_type' => 'package'
    ];

    $emailHtml = generateBookingConfirmationEmailHtml($bookingData);
    @sendPortalMail($leadEmail, $leadName, "Booking Confirmation: " . $packageTitle . " (" . $bookingCode . ")", $emailHtml);

    // 4. Trigger Real-Time Admin Notification
    require_once __DIR__ . '/../includes/notifications.php';
    createAdminNotification(
        'booking',
        'New Tour Booking & Payment',
        $leadName . ' booked "' . $packageTitle . '" - Advance token ₹' . number_format($advanceAmount) . ' received via ' . ucfirst($paymentGateway) . '.',
        'bookings.php?code=' . urlencode($bookingCode),
        ['booking_code' => $bookingCode, 'amount' => $totalAmount, 'advance' => $advanceAmount, 'gateway' => $paymentGateway]
    );

    echo json_encode([
        'success' => true,
        'booking_code' => $bookingCode,
        'package_title' => $packageTitle,
        'lead_name' => $leadName,
        'travel_date' => $travelDate,
        'total_amount' => $totalAmount,
        'advance_amount' => $advanceAmount,
        'balance_amount' => $balanceAmount,
        'redirect' => 'booking-success.php?code=' . urlencode($bookingCode),
        'message' => 'Tour package booked successfully! Check your email for full voucher.'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);
}
