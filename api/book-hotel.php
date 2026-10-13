<?php
/**
 * Real-time Hotel & Room Booking API Endpoint with Login Enforce & Token Advance
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
        'message' => 'Please sign in to your ' . $siteName . ' account to complete your hotel reservation.'
    ]);
    exit();
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$guestName = trim($_POST['guest_name'] ?? ($_SESSION['user_name'] ?? ''));
$guestEmail = trim($_POST['guest_email'] ?? ($_SESSION['user_email'] ?? ''));
$guestPhone = trim($_POST['guest_phone'] ?? ($_SESSION['user_phone'] ?? ''));

$hotelId = trim($_POST['hotel_id'] ?? '');
$hotelName = trim($_POST['hotel_name'] ?? 'Hotel & Resort');
$roomName = trim($_POST['room_name'] ?? 'Deluxe Room');
$checkIn = trim($_POST['check_in'] ?? date('Y-m-d'));
$checkOut = trim($_POST['check_out'] ?? date('Y-m-d', strtotime('+2 days')));
$roomsCount = max(1, (int)($_POST['rooms_count'] ?? 1));
$guestsCount = max(1, (int)($_POST['guests_count'] ?? 2));
$totalAmount = (float)($_POST['total_amount'] ?? 5000);

if (empty($guestName) || empty($guestPhone)) {
    echo json_encode(['success' => false, 'message' => 'Please provide your Full Name and Contact Phone.']);
    exit();
}

// 2. Calculate Partial / Advance Token Amount based on Admin Settings
$settings = getGlobalSettings();
$advType = $settings['booking_advance_type'] ?? 'percentage';
$advPercent = (float)($settings['booking_advance_percent'] ?? 30);
$advFixed = (float)($settings['booking_advance_fixed'] ?? 2500);

$advanceAmount = 0.0;
if ($advType === 'fixed') {
    $advanceAmount = min($totalAmount, $advFixed * $roomsCount);
} else {
    $advanceAmount = round(($totalAmount * ($advPercent / 100)), 2);
}

if ($advanceAmount <= 0) $advanceAmount = $totalAmount;
if ($advanceAmount > $totalAmount) $advanceAmount = $totalAmount;
$balanceAmount = max(0, round($totalAmount - $advanceAmount, 2));

$paymentGateway = trim($_POST['payment_gateway'] ?? 'razorpay');
$paymentId = trim($_POST['razorpay_payment_id'] ?? ('PAY_' . strtoupper(substr(md5(uniqid()), 0, 10))));

try {
    $bookingCode = 'HTL-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    $packageTitle = $hotelName . ' - ' . $roomName . ' (' . $roomsCount . ' Room' . ($roomsCount > 1 ? 's' : '') . ', ' . $guestsCount . ' Guests)';

    $detailsJson = json_encode([
        'hotel_id' => $hotelId,
        'hotel_name' => $hotelName,
        'room_name' => $roomName,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'rooms_count' => $roomsCount,
        'guests_count' => $guestsCount,
        'advance_type' => $advType,
        'gateway' => $paymentGateway,
        'booked_at' => date('Y-m-d H:i:s')
    ]);

    $stmt = $pdo->prepare("INSERT INTO `bookings` (
        `booking_code`, `user_id`, `booking_type`, `customer_name`, `customer_email`, `customer_phone`, 
        `package_title`, `travel_date`, `amount`, `advance_amount`, `balance_amount`,
        `payment_gateway`, `payment_id`, `booking_details`, `status`
    ) VALUES (?, ?, 'hotel', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");

    $stmt->execute([
        $bookingCode,
        $userId,
        $guestName,
        $guestEmail,
        $guestPhone,
        $packageTitle,
        $checkIn,
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
        'customer_name' => $guestName,
        'customer_email' => $guestEmail,
        'customer_phone' => $guestPhone,
        'package_title' => $packageTitle,
        'travel_date' => $checkIn,
        'amount' => $totalAmount,
        'advance_amount' => $advanceAmount,
        'balance_amount' => $balanceAmount,
        'payment_id' => $paymentId,
        'booking_type' => 'hotel'
    ];

    $emailHtml = generateBookingConfirmationEmailHtml($bookingData);
    @sendPortalMail($guestEmail, $guestName, "Hotel Voucher: " . $hotelName . " (" . $bookingCode . ")", $emailHtml);

    // 4. Trigger Real-Time Admin Notification
    require_once __DIR__ . '/../includes/notifications.php';
    createAdminNotification(
        'booking',
        'New Hotel Stay Reservation',
        $guestName . ' reserved "' . $hotelName . ' - ' . $roomName . '" - Token ₹' . number_format($advanceAmount) . ' received via ' . ucfirst($paymentGateway) . '.',
        'bookings.php?code=' . urlencode($bookingCode),
        ['booking_code' => $bookingCode, 'amount' => $totalAmount, 'advance' => $advanceAmount, 'gateway' => $paymentGateway]
    );

    echo json_encode([
        'success' => true,
        'booking_code' => $bookingCode,
        'hotel_name' => $hotelName,
        'room_name' => $roomName,
        'guest_name' => $guestName,
        'total_amount' => $totalAmount,
        'advance_amount' => $advanceAmount,
        'balance_amount' => $balanceAmount,
        'redirect' => 'booking-success.php?code=' . urlencode($bookingCode) . '&type=hotel',
        'message' => 'Your room reservation is confirmed! Check your email for full voucher.'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Booking error: ' . $e->getMessage()]);
}
