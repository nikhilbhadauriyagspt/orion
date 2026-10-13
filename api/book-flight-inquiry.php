<?php
/**
 * Flight Reservation Request / Instant Inquiry API Endpoint
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

// 1. Mandatory User Login Check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'login_required' => true,
        'message' => 'Please sign in to submit your flight booking request.'
    ]);
    exit();
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$passengerName = trim($_POST['passenger_name'] ?? ($_SESSION['user_name'] ?? ''));
$passengerEmail = trim($_POST['passenger_email'] ?? ($_SESSION['user_email'] ?? ''));
$passengerPhone = trim($_POST['passenger_phone'] ?? ($_SESSION['user_phone'] ?? ''));

$airline = trim($_POST['airline'] ?? 'Air India');
$flightNumber = trim($_POST['flight_number'] ?? 'AI-101');
$origin = trim($_POST['origin'] ?? 'DEL');
$destination = trim($_POST['destination'] ?? 'BOM');
$departureDate = trim($_POST['departure_date'] ?? date('Y-m-d', strtotime('+3 days')));
$passengersCount = max(1, (int)($_POST['passengers_count'] ?? 1));
$travelClass = trim($_POST['travel_class'] ?? 'Economy');
$quotedFare = (float)($_POST['total_fare'] ?? 4500);

if (empty($passengerName) || empty($passengerPhone)) {
    echo json_encode(['success' => false, 'message' => 'Please provide passenger name and contact phone number.']);
    exit();
}

try {
    $bookingCode = 'FLT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    $flightTitle = $airline . ' ' . $flightNumber . ' (' . $origin . ' → ' . $destination . ', ' . $passengersCount . ' Pax, ' . $travelClass . ')';

    $detailsJson = json_encode([
        'airline' => $airline,
        'flight_number' => $flightNumber,
        'origin' => $origin,
        'destination' => $destination,
        'departure_date' => $departureDate,
        'passengers_count' => $passengersCount,
        'travel_class' => $travelClass,
        'quoted_fare' => $quotedFare,
        'inquiry_date' => date('Y-m-d H:i:s')
    ]);

    $stmt = $pdo->prepare("INSERT INTO `bookings` (
        `booking_code`, `user_id`, `booking_type`, `customer_name`, `customer_email`, `customer_phone`, 
        `package_title`, `travel_date`, `amount`, `advance_amount`, `balance_amount`,
        `payment_gateway`, `booking_details`, `status`
    ) VALUES (?, ?, 'flight_inquiry', ?, ?, ?, ?, ?, ?, 0.00, ?, 'inquiry', ?, 'pending')");

    $stmt->execute([
        $bookingCode,
        $userId,
        $passengerName,
        $passengerEmail,
        $passengerPhone,
        $flightTitle,
        $departureDate,
        $quotedFare,
        $quotedFare,
        $detailsJson
    ]);

    // Send confirmation email
    $flightData = [
        'booking_code' => $bookingCode,
        'customer_name' => $passengerName,
        'customer_email' => $passengerEmail,
        'customer_phone' => $passengerPhone,
        'package_title' => $flightTitle,
        'travel_date' => $departureDate,
        'amount' => $quotedFare,
        'booking_type' => 'flight_inquiry'
    ];

    $emailHtml = generateFlightInquiryEmailHtml($flightData);
    @sendPortalMail($passengerEmail, $passengerName, "Flight Reservation Request Received: " . $flightNumber . " (" . $bookingCode . ")", $emailHtml);

    // Trigger Real-Time Admin Notification
    require_once __DIR__ . '/../includes/notifications.php';
    createAdminNotification(
        'flight_inquiry',
        'New Flight Booking Inquiry',
        $passengerName . ' submitted flight inquiry for ' . $origin . ' → ' . $destination . ' (' . $airline . ' ' . $flightNumber . ') - Est. ₹' . number_format($quotedFare),
        'bookings.php?filter=flight',
        ['booking_code' => $bookingCode, 'origin' => $origin, 'destination' => $destination, 'fare' => $quotedFare]
    );

    echo json_encode([
        'success' => true,
        'booking_code' => $bookingCode,
        'flight_title' => $flightTitle,
        'passenger_name' => $passengerName,
        'redirect' => 'booking-success.php?code=' . urlencode($bookingCode) . '&type=flight',
        'message' => 'Flight booking request submitted! Our ticketing desk will contact you within 15 minutes.'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Flight inquiry failed: ' . $e->getMessage()]);
}
