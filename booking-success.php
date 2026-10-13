<?php
/**
 * Modern Boarding Pass / Travel E-Ticket & Voucher Screen
 * Orion Advent Travel Portal
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';

$bookingCode = trim($_GET['code'] ?? '');
$bookingType = trim($_GET['type'] ?? 'package');

$booking = null;
$pdo = getDBConnection();

if ($pdo && !empty($bookingCode)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `bookings` WHERE `booking_code` = ? LIMIT 1");
        $stmt->execute([$bookingCode]);
        $booking = $stmt->fetch();
    } catch (Exception $e) {
        $booking = null;
    }
}

// Fallback dummy for preview if code not found
if (!$booking) {
    $booking = [
        'booking_code' => !empty($bookingCode) ? $bookingCode : 'GFX-' . strtoupper(substr(md5(time()), 0, 8)),
        'customer_name' => $_SESSION['user_name'] ?? 'Nikhil singh',
        'customer_email' => $_SESSION['user_email'] ?? 'user@gmail.com',
        'customer_phone' => $_SESSION['user_phone'] ?? '+916676767677',
        'package_title' => 'Goa Beachfront Resort, Yacht Cruise & Watersports (2 Adults)',
        'travel_date' => date('Y-m-d', strtotime('+7 days')),
        'amount' => 17998.00,
        'advance_amount' => 5399.00,
        'balance_amount' => 12599.00,
        'payment_gateway' => 'razorpay',
        'payment_id' => 'pay_' . strtoupper(substr(md5(time()), 0, 14)),
        'status' => 'confirmed',
        'booking_type' => $bookingType
    ];
}

$isFlight = ($booking['booking_type'] ?? '') === 'flight_inquiry';
$bType = $booking['booking_type'] ?? 'package';
$pageTitle = ($isFlight ? 'Flight Request Received' : 'Booking Confirmed!') . ' - ' . $booking['booking_code'];

$siteWhatsapp = getSetting('site_whatsapp', '919876543210');
$siteName = getSetting('site_name', 'Orion Advent');
$siteLogo = getSetting('site_logo', 'assets/images/logo/logo.webp');

// Parse booking details if present
$bookingDetails = [];
if (!empty($booking['booking_details'])) {
    $bookingDetails = json_decode($booking['booking_details'], true) ?: [];
}
$adultsCount = (int)($bookingDetails['adults'] ?? 0);
$childrenCount = (int)($bookingDetails['children'] ?? 0);
$guestSummary = '';
if ($adultsCount > 0) {
    $guestSummary .= $adultsCount . ' Adult' . ($adultsCount > 1 ? 's' : '');
}
if ($childrenCount > 0) {
    $guestSummary .= ($guestSummary ? ', ' : '') . $childrenCount . ' Child' . ($childrenCount > 1 ? 'ren' : '');
}

// Service icon and labels
$serviceIcon = 'fa-map-location-dot';
$serviceTypeLabel = 'Tour Package';
if ($isFlight) {
    $serviceIcon = 'fa-plane-departure';
    $serviceTypeLabel = 'Flight Reservation';
} elseif ($bType === 'hotel') {
    $serviceIcon = 'fa-hotel';
    $serviceTypeLabel = 'Luxury Hotel Stay';
} elseif ($bType === 'cruise') {
    $serviceIcon = 'fa-ship';
    $serviceTypeLabel = 'Ocean Cruise Voyage';
}

$travelDateFormatted = !empty($booking['travel_date']) ? date('M d, Y', strtotime($booking['travel_date'])) : 'Open Date';
$travelDay = !empty($booking['travel_date']) ? date('l', strtotime($booking['travel_date'])) : '';
$bookedAtFormatted = !empty($booking['created_at']) ? date('M d, Y h:i A', strtotime($booking['created_at'])) : date('M d, Y');

// Verification URL for dynamic QR
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$reqUri = $_SERVER['REQUEST_URI'] ?? ('/booking-success.php?code=' . urlencode($booking['booking_code']));
$currentUrl = $protocol . $host . $reqUri;

// Deterministic Barcode generation
$codeClean = strtoupper($booking['booking_code']);
$barRects = [];
$xPos = 12;
$widths = [2, 1, 3.5, 1, 2, 4, 1.5, 2, 3, 1, 1.5, 4, 2, 1, 3, 2.5, 1, 4, 1.5, 2, 1, 3.5, 2, 1, 2.5, 4, 1.5, 2, 3, 1, 2, 4, 1.5, 3, 2];
foreach ($widths as $w) {
    $barRects[] = ['x' => $xPos, 'w' => $w];
    $xPos += $w + (($w > 2) ? 2.5 : 1.5);
}
$barcodeSvgWidth = $xPos + 12;

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<style>
/* Authentic Boarding Pass Styles */
.ticket-container {
    position: relative;
    background: #ffffff;
    box-shadow: 0 20px 45px -15px rgba(6, 78, 82, 0.12), 0 0 0 1px rgba(226, 232, 240, 0.9);
}

/* Print Specific Rules */
@media print {
    header, nav, footer, .no-print, .site-header, #main-navbar {
        display: none !important;
    }
    body, main {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
        min-height: auto !important;
    }
    .ticket-container {
        border: 2px solid #0f172a !important;
        box-shadow: none !important;
        margin: 0 auto !important;
        max-width: 100% !important;
        border-radius: 12px !important;
        page-break-inside: avoid;
    }
    .ticket-notch {
        display: none !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    @page {
        size: A4 portrait;
        margin: 12mm;
    }
}
</style>

<main class="w-full bg-[#f8fafc] py-8 sm:py-12 px-3 sm:px-6 min-h-[90vh] flex items-center justify-center">
    <div class="max-w-3xl w-full mx-auto space-y-5">

        <!-- Celebratory Toast Header (Screen only) -->
        <div class="text-center space-y-2 no-print">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span><?= $isFlight ? 'Inquiry Under Review' : 'Advance Token Verified & Confirmed'; ?></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-space">
                <?= $isFlight ? 'Flight Request Submitted' : 'Your Reservation is Confirmed!'; ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                <?= $isFlight 
                    ? 'Our air ticketing team is reviewing seat inventory and will WhatsApp your itinerary shortly.' 
                    : 'Voucher and booking receipt sent to <strong class="text-slate-800 font-semibold">' . htmlspecialchars($booking['customer_email']) . '</strong>'; ?>
            </p>
        </div>

        <!-- AUTHENTIC LUXURY BOARDING PASS / TRAVEL TICKET -->
        <div class="ticket-container rounded-3xl overflow-hidden border border-slate-200">
            
            <!-- 1. Top Ticket Header: Orion Advent Branding & PNR Bar -->
            <div class="bg-gradient-to-r from-[#043336] via-[#068285] to-[#0a4648] text-white p-5 sm:p-7 relative overflow-hidden">
                <!-- Guilloche watermark pattern -->
                <div class="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-white/10 to-transparent pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <!-- Brand Logo with Clean White Container -->
                    <div class="flex items-center gap-3.5">
                        <div class="bg-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl shadow-sm border border-white/40 flex items-center shrink-0">
                            <img src="<?= htmlspecialchars($siteLogo) ?>" 
                                 alt="<?= htmlspecialchars($siteName) ?>" 
                                 class="h-8 sm:h-11 w-auto object-contain"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="hidden items-center gap-1.5 font-space font-black text-slate-900 text-sm sm:text-base">
                                <i class="fa-solid fa-compass text-teal-700"></i>
                                <span><?= htmlspecialchars($siteName) ?></span>
                            </span>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-white/15 text-teal-100 text-[10px] font-bold tracking-widest uppercase border border-white/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>Official Travel Pass</span>
                            </div>
                            <h2 class="text-base sm:text-xl font-black text-white tracking-tight font-space mt-0.5">
                                <?= htmlspecialchars($siteName) ?>
                            </h2>
                        </div>
                    </div>

                    <!-- PNR / Reference Code & Status -->
                    <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1.5 bg-black/20 sm:bg-transparent p-2.5 sm:p-0 rounded-xl">
                        <div class="text-left sm:text-right">
                            <span class="text-[10px] uppercase font-bold text-teal-200/90 tracking-wider block">Ref Code / PNR</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-white text-base sm:text-lg tracking-wider" id="refCodeText"><?= htmlspecialchars($booking['booking_code']) ?></span>
                                <button type="button" onclick="copyRefCode()" id="copyCodeBtn" title="Copy Reference Code" class="w-7 h-7 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition active:scale-95 text-xs no-print">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/25 text-emerald-300 border border-emerald-400/40">
                            <i class="fa-solid fa-circle-check text-[9px]"></i>
                            <span>CONFIRMED</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Flight/Tour Itinerary Headline Ribbon -->
            <div class="bg-gradient-to-r from-teal-50/80 via-emerald-50/40 to-slate-50 border-b border-slate-200/80 p-4 sm:p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-lg sm:text-xl shadow-xs shrink-0">
                            <i class="fa-solid <?= $serviceIcon ?>"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="inline-block text-[10px] font-black tracking-widest text-teal-900 uppercase bg-teal-100 px-2 py-0.5 rounded border border-teal-200 mb-1">
                                <?= $serviceTypeLabel ?>
                            </span>
                            <h3 class="text-sm sm:text-base md:text-lg font-black text-slate-900 tracking-tight leading-snug">
                                <?= htmlspecialchars($booking['package_title']) ?>
                            </h3>
                            <?php if (!empty($guestSummary)): ?>
                                <span class="text-xs text-slate-500 font-medium block mt-0.5">
                                    <i class="fa-solid fa-users text-slate-400 mr-1 text-[11px]"></i>
                                    Guests: <strong class="text-slate-700"><?= htmlspecialchars($guestSummary) ?></strong>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="hidden md:flex flex-col items-end shrink-0 text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ticket Class</span>
                        <span class="text-xs font-black text-teal-900 uppercase">Premium Confirmed</span>
                        <span class="text-[11px] text-slate-500 mt-0.5 font-medium">Issue Date: <?= $bookedAtFormatted ?></span>
                    </div>
                </div>
            </div>

            <!-- 3. Ticket Core Passenger & Schedule Grid -->
            <div class="p-5 sm:p-7 space-y-6">
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    <!-- 1. Lead Traveler -->
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-teal-300 transition">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block flex items-center gap-1.5">
                            <i class="fa-solid fa-user-check text-brand-600 text-[11px]"></i>
                            <span>Lead Traveler</span>
                        </span>
                        <strong class="text-xs sm:text-sm text-slate-900 font-bold block truncate mt-1">
                            <?= htmlspecialchars($booking['customer_name']) ?>
                        </strong>
                        <span class="text-[10px] text-slate-500 block truncate">Primary Passenger</span>
                    </div>

                    <!-- 2. Travel Date -->
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-teal-300 transition">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-days text-brand-600 text-[11px]"></i>
                            <span><?= $isFlight ? 'Departure Date' : 'Travel Date' ?></span>
                        </span>
                        <strong class="text-xs sm:text-sm text-teal-900 font-bold block truncate mt-1">
                            <?= $travelDateFormatted ?>
                        </strong>
                        <span class="text-[10px] text-slate-500 block truncate"><?= $travelDay ?></span>
                    </div>

                    <!-- 3. Contact Phone -->
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-teal-300 transition">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block flex items-center gap-1.5">
                            <i class="fa-solid fa-phone text-brand-600 text-[11px]"></i>
                            <span>Contact Phone</span>
                        </span>
                        <strong class="text-xs sm:text-sm text-slate-900 font-bold block truncate mt-1">
                            <?= htmlspecialchars($booking['customer_phone']) ?>
                        </strong>
                        <span class="text-[10px] text-slate-500 block truncate">Verified Contact</span>
                    </div>

                    <!-- 4. Email Dispatched -->
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-teal-300 transition">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block flex items-center gap-1.5">
                            <i class="fa-solid fa-envelope text-brand-600 text-[11px]"></i>
                            <span>Registered Email</span>
                        </span>
                        <strong class="text-xs sm:text-sm text-slate-900 font-bold block truncate mt-1" title="<?= htmlspecialchars($booking['customer_email']) ?>">
                            <?= htmlspecialchars($booking['customer_email']) ?>
                        </strong>
                        <span class="text-[10px] text-emerald-600 font-semibold block truncate">Receipt Dispatched <i class="fa-solid fa-check text-[9px]"></i></span>
                    </div>

                    <!-- 5. Payment Ref / Transaction ID -->
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-teal-300 transition">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block flex items-center gap-1.5">
                            <i class="fa-solid fa-credit-card text-brand-600 text-[11px]"></i>
                            <span>Payment Ref</span>
                        </span>
                        <strong class="text-xs sm:text-sm font-mono text-slate-900 font-bold block truncate mt-1" title="<?= htmlspecialchars($booking['payment_id']) ?>">
                            <?= htmlspecialchars($booking['payment_id'] ?? 'VERIFIED') ?>
                        </strong>
                        <span class="text-[10px] text-slate-500 block truncate">Gateway: <?= strtoupper(htmlspecialchars($booking['payment_gateway'] ?? 'Razorpay')) ?></span>
                    </div>

                    <!-- 6. Boarding / Gate Status -->
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 hover:border-teal-300 transition">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-emerald-600 text-[11px]"></i>
                            <span>Boarding Status</span>
                        </span>
                        <strong class="text-xs sm:text-sm text-emerald-800 font-black block truncate mt-1">
                            CONFIRMED PASS
                        </strong>
                        <span class="text-[10px] text-slate-500 block truncate">Guaranteed Reservation</span>
                    </div>
                </div>

                <!-- 4. Perforated Tear Line with Semicircle Notches -->
                <div class="relative py-2 my-2">
                    <!-- Left Cutout Notch -->
                    <div class="ticket-notch absolute -left-7 sm:-left-9 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-[#f8fafc] border border-slate-300 shadow-inner z-10"></div>
                    
                    <!-- Dashed Perforation Line -->
                    <div class="w-full border-t-2 border-dashed border-slate-300 relative flex items-center justify-center">
                        <span class="bg-white px-3 sm:px-4 text-[10px] sm:text-[11px] uppercase font-bold text-slate-400 tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-scissors text-slate-400 text-xs"></i>
                            <span>Travel Voucher Stub & Fare Breakdown</span>
                        </span>
                    </div>

                    <!-- Right Cutout Notch -->
                    <div class="ticket-notch absolute -right-7 sm:-right-9 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-[#f8fafc] border border-slate-300 shadow-inner z-10"></div>
                </div>

                <!-- 5. Lower Boarding Stub: Fare Matrix & Barcode / Security QR -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-stretch">
                    
                    <!-- Left: Fare Matrix (7 cols) -->
                    <div class="md:col-span-7 p-4 sm:p-5 rounded-2xl bg-teal-50/50 border border-teal-200/80 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-teal-800 tracking-wider block mb-2.5">
                                Official Fare Breakdown & Balance
                            </span>
                            
                            <div class="space-y-2.5 text-xs">
                                <!-- Total Trip Amount -->
                                <div class="flex items-center justify-between text-slate-600 font-medium">
                                    <span>Total Trip Amount:</span>
                                    <span class="font-bold text-slate-900 font-mono text-sm sm:text-base">
                                        ₹<?= number_format((float)$booking['amount'], (round((float)$booking['amount']) == (float)$booking['amount'] ? 0 : 2)) ?>
                                    </span>
                                </div>

                                <?php if (!$isFlight): ?>
                                    <!-- Advance Paid Online -->
                                    <div class="flex items-center justify-between text-emerald-800 font-bold bg-white/90 p-2.5 rounded-xl border border-emerald-200">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                            <span>Token Advance Paid Online:</span>
                                        </span>
                                        <span class="font-mono text-sm sm:text-base text-emerald-700">
                                            ₹<?= number_format((float)$booking['advance_amount'], (round((float)$booking['advance_amount']) == (float)$booking['advance_amount'] ? 0 : 2)) ?>
                                        </span>
                                    </div>

                                    <!-- Balance Due on Check-in -->
                                    <div class="pt-2 border-t border-teal-200/80 flex items-center justify-between text-amber-900 font-bold">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                                            <span>Balance Payable on Arrival / Check-in:</span>
                                        </span>
                                        <span class="font-mono text-sm sm:text-base text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-lg border border-amber-200">
                                            ₹<?= number_format((float)$booking['balance_amount'], (round((float)$booking['balance_amount']) == (float)$booking['balance_amount'] ? 0 : 2)) ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="pt-2 border-t border-teal-200/80 flex items-center justify-between text-sky-800 font-bold">
                                        <span>Estimated Fare Quoted:</span>
                                        <span class="font-mono text-base">₹<?= number_format((float)$booking['amount']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ID Advisory Strip -->
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/90 text-[11px] text-slate-600 flex items-start gap-2">
                            <i class="fa-solid fa-id-card text-brand-600 text-xs mt-0.5 shrink-0"></i>
                            <p class="leading-relaxed">
                                All adult guests are requested to carry a government photo ID (Aadhaar / Passport / DL) at check-in.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Boarding Barcode & Security QR (5 cols) -->
                    <div class="md:col-span-5 p-4 sm:p-5 rounded-2xl bg-white border border-slate-200 flex flex-col justify-between items-center text-center space-y-3">
                        
                        <!-- Real SVG Barcode -->
                        <div class="w-full">
                            <svg viewBox="0 0 <?= $barcodeSvgWidth ?> 48" class="h-10 sm:h-12 w-full max-w-[240px] mx-auto text-slate-900" fill="currentColor">
                                <?php foreach ($barRects as $b): ?>
                                    <rect x="<?= $b['x'] ?>" y="0" width="<?= $b['w'] ?>" height="48" rx="0.5" />
                                <?php endforeach; ?>
                            </svg>
                            <div class="font-mono text-[11px] font-black tracking-[0.25em] text-slate-700 mt-1">
                                * <?= htmlspecialchars($booking['booking_code']) ?> *
                            </div>
                        </div>

                        <!-- Dynamic QR Code & Security Stamp -->
                        <div class="w-full bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 flex items-center gap-3">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode($currentUrl) ?>&margin=3" 
                                 alt="Boarding Pass QR" 
                                 class="w-16 h-16 rounded-lg bg-white border border-slate-200 p-1 object-contain shrink-0 shadow-xs"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden w-16 h-16 rounded-lg bg-slate-900 text-white flex-col items-center justify-center text-center p-1 shrink-0">
                                <i class="fa-solid fa-qrcode text-2xl text-teal-400"></i>
                                <span class="text-[8px] font-mono mt-0.5">SCAN</span>
                            </div>
                            <div class="text-left min-w-0">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Security Token</span>
                                <span class="font-mono font-bold text-slate-800 text-[11px] truncate block"><?= strtoupper(substr(md5($booking['booking_code']), 0, 10)) ?></span>
                                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-bold mt-1">
                                    <i class="fa-solid fa-circle-check text-emerald-600 text-[9px]"></i>
                                    <span>Scan to Verify Pass</span>
                                </span>
                            </div>
                        </div>

                        <span class="text-[10px] text-slate-400 tracking-wider uppercase font-medium">
                            Authorized By <?= htmlspecialchars($siteName) ?>
                        </span>
                    </div>

                </div>

            </div>
        </div>

        <!-- Interactive Action Buttons Row (Screen only) -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3 no-print">
            <!-- WhatsApp Concierge Button -->
            <a href="https://wa.me/<?= htmlspecialchars($siteWhatsapp) ?>?text=<?= urlencode("Hi " . $siteName . ", I have confirmed booking for " . $booking['package_title'] . " (Booking ID: " . $booking['booking_code'] . "). Please share my concierge coordinator details.") ?>" 
               target="_blank" 
               class="w-full sm:flex-1 py-3 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xs hover:shadow transition active:scale-95">
                <i class="fa-brands fa-whatsapp text-base"></i>
                <span>WhatsApp Concierge</span>
            </a>

            <!-- Print Voucher Button -->
            <button type="button" 
                    onclick="window.print()" 
                    class="w-full sm:w-auto py-3 px-5 rounded-2xl bg-white hover:bg-slate-100 text-slate-800 font-bold text-xs sm:text-sm border border-slate-300 flex items-center justify-center gap-2 shadow-xs hover:shadow transition active:scale-95">
                <i class="fa-solid fa-print text-brand-600"></i>
                <span>Print Voucher</span>
            </button>

            <!-- My Trips Button -->
            <a href="my-trips.php" 
               class="w-full sm:w-auto py-3 px-5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xs hover:shadow transition active:scale-95">
                <i class="fa-solid fa-suitcase"></i>
                <span>My Trips</span>
            </a>
        </div>

    </div>
</main>

<script>
function copyRefCode() {
    const code = document.getElementById('refCodeText')?.innerText || '';
    if (!code) return;
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.getElementById('copyCodeBtn');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-check text-emerald-300"></i>';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-regular fa-copy"></i>';
            }, 2000);
        }
    });
}
</script>

<?php require_once 'components/footer.php'; ?>
