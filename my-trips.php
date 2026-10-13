<?php
/**
 * User Dashboard & My Trips / My Bookings Portal
 * Orion Advent Travel Portal
 */
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';

// Mandatory Auth Gate
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode('my-trips.php'));
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userEmail = $_SESSION['user_email'] ?? '';
$userName = $_SESSION['user_name'] ?? 'Traveler';
$userPhone = $_SESSION['user_phone'] ?? '';

$pdo = getDBConnection();
$bookings = [];
$totalTrips = 0;
$confirmedTrips = 0;
$pendingTrips = 0;
$totalSpent = 0.0;
$totalBalanceDue = 0.0;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `bookings` WHERE `user_id` = ? OR `customer_email` = ? ORDER BY `id` DESC");
        $stmt->execute([$userId, $userEmail]);
        $bookings = $stmt->fetchAll();

        foreach ($bookings as $b) {
            $totalTrips++;
            $st = strtolower($b['status']);
            if ($st === 'confirmed' || $st === 'completed') {
                $confirmedTrips++;
                $totalSpent += (float)$b['advance_amount'];
                $totalBalanceDue += (float)$b['balance_amount'];
            } elseif ($st === 'pending') {
                $pendingTrips++;
            }
        }
    } catch (Exception $e) {
        $bookings = [];
    }
}

$activeTab = trim($_GET['tab'] ?? 'all');
$siteName = getSetting('site_name', 'Orion Advent');
$siteWhatsapp = getSetting('site_whatsapp', '919876543210');
$pageTitle = "My Trips & Bookings - " . htmlspecialchars($siteName);

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<main class="w-full bg-[#f8fafc] py-8 sm:py-12 px-4 sm:px-8 xl:px-12 min-h-screen">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- User Profile Hero Header (100% Flat & Clean) -->
        <div class="bg-slate-900 text-white rounded-3xl border border-slate-800 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-brand-600 text-white font-black text-2xl flex items-center justify-center border-2 border-brand-400/40 shadow-sm shrink-0">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/10 text-teal-300 text-[11px] font-bold">
                        <i class="fa-solid fa-passport"></i>
                        <span>Verified Traveler</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black font-space tracking-tight">
                        Hello, <?php echo htmlspecialchars($userName); ?>!
                    </h1>
                    <p class="text-xs text-slate-400">
                        <?php echo htmlspecialchars($userEmail); ?> <?php echo !empty($userPhone) ? ' • ' . htmlspecialchars($userPhone) : ''; ?>
                    </p>
                </div>
            </div>

            <!-- Quick Trip Stats Counter Cards -->
            <div class="grid grid-cols-3 gap-3 text-center shrink-0">
                <div class="p-3 sm:px-4 sm:py-2.5 rounded-2xl bg-white/5 border border-white/10">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Trips</span>
                    <span class="text-lg sm:text-xl font-extrabold text-white font-space"><?php echo $totalTrips; ?></span>
                </div>
                <div class="p-3 sm:px-4 sm:py-2.5 rounded-2xl bg-white/5 border border-white/10">
                    <span class="text-[10px] uppercase font-bold text-emerald-400 block">Confirmed</span>
                    <span class="text-lg sm:text-xl font-extrabold text-emerald-300 font-space"><?php echo $confirmedTrips; ?></span>
                </div>
                <div class="p-3 sm:px-4 sm:py-2.5 rounded-2xl bg-white/5 border border-white/10">
                    <span class="text-[10px] uppercase font-bold text-amber-400 block">Balance Due</span>
                    <span class="text-base sm:text-lg font-extrabold text-amber-300 font-space">₹<?php echo number_format($totalBalanceDue); ?></span>
                </div>
            </div>
        </div>

        <!-- Trips Navigation Tabs Strip -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <a href="my-trips.php?tab=all" class="px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 <?php echo $activeTab === 'all' ? 'bg-brand-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'; ?>">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                    <span>All Trips (<?php echo $totalTrips; ?>)</span>
                </a>
                <a href="my-trips.php?tab=package" class="px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 <?php echo $activeTab === 'package' ? 'bg-brand-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'; ?>">
                    <i class="fa-solid fa-map-location-dot text-xs"></i>
                    <span>Tour Packages</span>
                </a>
                <a href="my-trips.php?tab=hotel" class="px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 <?php echo $activeTab === 'hotel' ? 'bg-brand-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'; ?>">
                    <i class="fa-solid fa-hotel text-xs"></i>
                    <span>Hotel Stays</span>
                </a>
                <a href="my-trips.php?tab=flight_inquiry" class="px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 <?php echo $activeTab === 'flight_inquiry' ? 'bg-brand-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'; ?>">
                    <i class="fa-solid fa-plane-departure text-xs"></i>
                    <span>Flight Queries</span>
                </a>
            </div>

            <a href="search.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white hover:bg-slate-50 border border-slate-200 text-xs font-bold text-brand-700 transition">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Book New Holiday</span>
            </a>
        </div>

        <!-- Bookings Feed -->
        <div class="space-y-4">
            <?php
            $filteredBookings = array_filter($bookings, function($b) use ($activeTab) {
                if ($activeTab === 'all') return true;
                return ($b['booking_type'] ?? 'package') === $activeTab;
            });
            ?>

            <?php if (!empty($filteredBookings)): ?>
                <?php foreach ($filteredBookings as $item): ?>
                    <?php
                    $bType = $item['booking_type'] ?? 'package';
                    $status = strtolower($item['status']);
                    $badgeBg = 'bg-slate-100 text-slate-700 border-slate-200';
                    $statusText = ucfirst($status);

                    if ($status === 'confirmed') {
                        $badgeBg = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                        $statusText = 'Confirmed &amp; Voucher Ready';
                    } elseif ($status === 'pending') {
                        $badgeBg = 'bg-amber-50 text-amber-800 border-amber-200';
                        $statusText = 'Under Concierge Review';
                    } elseif ($status === 'completed') {
                        $badgeBg = 'bg-blue-50 text-blue-800 border-blue-200';
                        $statusText = 'Trip Completed';
                    } elseif ($status === 'cancelled') {
                        $badgeBg = 'bg-rose-50 text-rose-800 border-rose-200';
                        $statusText = 'Cancelled';
                    }

                    $icon = match($bType) {
                        'hotel' => 'fa-hotel text-emerald-600 bg-emerald-50 border-emerald-200',
                        'flight_inquiry' => 'fa-plane-departure text-sky-600 bg-sky-50 border-sky-200',
                        default => 'fa-map-location-dot text-brand-600 bg-brand-50 border-brand-200'
                    };
                    $typeLabel = match($bType) {
                        'hotel' => 'Hotel &amp; Resort Stay',
                        'flight_inquiry' => 'Flight Reservation Query',
                        default => 'Tour Package'
                    };
                    ?>
                    
                    <div class="bg-white rounded-2xl border border-slate-200 hover:border-brand-400 p-5 sm:p-6 transition-all space-y-4 shadow-xs">
                        
                        <!-- Top Strip -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl border flex items-center justify-center text-sm <?php echo $icon; ?>">
                                    <i class="fa-solid <?php echo explode(' ', $icon)[0]; ?>"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block"><?php echo $typeLabel; ?></span>
                                    <span class="font-mono text-xs font-extrabold text-slate-900"><?php echo htmlspecialchars($item['booking_code']); ?></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $badgeBg; ?> flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full <?php echo $status === 'confirmed' ? 'bg-emerald-500' : ($status === 'pending' ? 'bg-amber-500' : 'bg-slate-400'); ?>"></span>
                                    <span><?php echo $statusText; ?></span>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body Details -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1.5 flex-1 max-w-2xl">
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                    <?php echo htmlspecialchars($item['package_title']); ?>
                                </h3>
                                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 font-medium">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-regular fa-calendar text-brand-600"></i>
                                        <span>Travel Date: <strong><?php echo date('M d, Y', strtotime($item['travel_date'])); ?></strong></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fa-regular fa-user text-slate-400"></i>
                                        <span>Lead Traveler: <?php echo htmlspecialchars($item['customer_name']); ?></span>
                                    </span>
                                    <?php if (!empty($item['payment_id']) && $item['payment_id'] !== 'inquiry'): ?>
                                        <span class="flex items-center gap-1 text-[11px] font-mono text-slate-400">
                                            <i class="fa-solid fa-receipt text-slate-400"></i>
                                            <span><?php echo htmlspecialchars($item['payment_id']); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Financial Matrix -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 sm:px-4 text-right shrink-0 space-y-0.5">
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Trip Cost</span>
                                <div class="text-base font-extrabold font-space text-slate-900">
                                    ₹<?php echo number_format((float)$item['amount']); ?>
                                </div>
                                <?php if ($bType !== 'flight_inquiry'): ?>
                                    <div class="text-[11px] text-emerald-700 font-bold">
                                        Advance Paid: ₹<?php echo number_format((float)$item['advance_amount']); ?>
                                    </div>
                                    <?php if ((float)$item['balance_amount'] > 0): ?>
                                        <div class="text-[11px] text-amber-700 font-bold">
                                            Balance Due: ₹<?php echo number_format((float)$item['balance_amount']); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Footer Action Buttons -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                            <div class="text-[11px] text-slate-400">
                                Booked on <?php echo date('d M Y, h:i A', strtotime($item['created_at'] ?? 'now')); ?>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="booking-success.php?code=<?php echo urlencode($item['booking_code']); ?><?php echo $bType === 'flight_inquiry' ? '&type=flight' : ''; ?>" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-ticket text-brand-600"></i>
                                    <span>View Travel Voucher</span>
                                </a>
                                <a href="https://wa.me/<?php echo htmlspecialchars($siteWhatsapp); ?>?text=<?php echo urlencode("Hi " . $siteName . " Concierge, I need assistance regarding my booking: " . $item['package_title'] . " (Booking ID: " . $item['booking_code'] . ")"); ?>" target="_blank" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold rounded-xl border border-emerald-200 transition flex items-center gap-1.5">
                                    <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                    <span>WhatsApp Concierge</span>
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-4 shadow-xs">
                    <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto text-2xl">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h3 class="text-lg font-black text-slate-900 font-space">No Travel Bookings Yet</h3>
                        <p class="text-xs text-slate-500">
                            You haven't reserved any tour packages, hotel stays, or flights yet. Explore our handcrafted circuits and plan your next holiday!
                        </p>
                    </div>
                    <div>
                        <a href="search.php" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                            <i class="fa-solid fa-compass"></i>
                            <span>Explore Packages &amp; Stays</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php require_once 'components/footer.php'; ?>
