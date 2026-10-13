<?php
/**
 * Admin Bookings Management Controller - Orion Advent
 * Clean, Box-Type, Sage & Cream Palette (Zero Heavy Drop-Shadows)
 * Full Package & Customer Dossier Inspector with Live Payment & Status Tracking
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();
$siteName = getSetting('site_name', 'Orion Advent');
$alertMessage = '';
$alertType = 'success';

// Handle booking status update
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['status'])) {
    if ($pdo) {
        try {
            $bookingId = (int)$_POST['booking_id'];
            $newStatus = trim($_POST['status']);
            $allowed = ['confirmed', 'pending', 'completed', 'cancelled'];
            if (in_array($newStatus, $allowed)) {
                $stmt = $pdo->prepare("UPDATE `bookings` SET `status` = ? WHERE `id` = ?");
                $stmt->execute([$newStatus, $bookingId]);
                $alertMessage = "Booking #{$bookingId} status updated to " . ucfirst($newStatus);
            }
        } catch (Exception $e) {
            $alertMessage = "Failed to update status: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// Fetch bookings & stats
$bookings = [];
$totalBookings = 0;
$confirmedCount = 0;
$pendingCount = 0;
$completedCount = 0;
$cancelledCount = 0;
$totalSales = 0.0;
$totalAdvancePaid = 0.0;
$totalBalanceDue = 0.0;

if ($pdo) {
    try {
        $totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
        $confirmedCount = (int)$pdo->query("SELECT COUNT(*) FROM `bookings` WHERE `status` = 'confirmed'")->fetchColumn();
        $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM `bookings` WHERE `status` = 'pending'")->fetchColumn();
        $completedCount = (int)$pdo->query("SELECT COUNT(*) FROM `bookings` WHERE `status` = 'completed'")->fetchColumn();
        $cancelledCount = (int)$pdo->query("SELECT COUNT(*) FROM `bookings` WHERE `status` = 'cancelled'")->fetchColumn();
        $totalSales = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `bookings` WHERE `status` != 'cancelled'")->fetchColumn();
        $totalAdvancePaid = (float)$pdo->query("SELECT COALESCE(SUM(advance_amount), 0) FROM `bookings` WHERE `status` != 'cancelled'")->fetchColumn();
        $totalBalanceDue = (float)$pdo->query("SELECT COALESCE(SUM(balance_amount), 0) FROM `bookings` WHERE `status` != 'cancelled'")->fetchColumn();

        $filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
        $filterType = isset($_GET['type']) ? trim($_GET['type']) : '';
        $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

        // Load all packages for fast lookup & enrichment
        $packagesMap = [];
        try {
            $pkgStmt = $pdo->query("SELECT id, title, slug, location, duration_days, duration_nights, badge, image FROM `packages`");
            while ($p = $pkgStmt->fetch(PDO::FETCH_ASSOC)) {
                $packagesMap['id_' . $p['id']] = $p;
                $cleanTitle = strtolower(preg_replace('/[^a-z0-9]/', '', $p['title']));
                $packagesMap['title_' . $cleanTitle] = $p;
            }
        } catch (Exception $pe) {}

        // Load all cruises for fast lookup & enrichment
        $cruisesMap = [];
        try {
            $cruStmt = $pdo->query("SELECT id, title, slug, departure_port, ship_name, duration_days, duration_nights, badge, featured_image FROM `cruises`");
            while ($c = $cruStmt->fetch(PDO::FETCH_ASSOC)) {
                $cruisesMap['id_' . $c['id']] = $c;
            }
        } catch (Exception $ce) {}

        $sql = "SELECT b.*, 
                       u.name AS user_account_name, u.email AS user_account_email, u.phone AS user_account_phone, u.status AS user_account_status
                FROM `bookings` b
                LEFT JOIN `users` u ON b.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filterStatus)) {
            $sql .= " AND b.`status` = ?";
            $params[] = $filterStatus;
        }
        if (!empty($filterType)) {
            $sql .= " AND b.`booking_type` = ?";
            $params[] = $filterType;
        }
        if (!empty($searchQuery)) {
            $sql .= " AND (b.`booking_code` LIKE ? OR b.`customer_name` LIKE ? OR b.`customer_email` LIKE ? OR b.`customer_phone` LIKE ? OR b.`package_title` LIKE ? OR b.`payment_id` LIKE ? OR b.`order_id` LIKE ?)";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }

        $sql .= " ORDER BY b.`id` DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rawBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enrich bookings with package details
        foreach ($rawBookings as $rb) {
            $meta = [];
            if (!empty($rb['booking_details'])) {
                $meta = is_array($rb['booking_details']) ? $rb['booking_details'] : json_decode($rb['booking_details'], true);
            }

            $matchedPkg = null;
            if (!empty($meta['package_id'])) {
                $pkgKey = 'id_' . str_replace('PKG-DB-', '', $meta['package_id']);
                if (isset($packagesMap[$pkgKey])) $matchedPkg = $packagesMap[$pkgKey];
            }
            if (!$matchedPkg && !empty($rb['package_title'])) {
                $cleanBTitle = strtolower(preg_replace('/[^a-z0-9]/', '', explode('(', $rb['package_title'])[0]));
                foreach ($packagesMap as $k => $v) {
                    if (str_starts_with($k, 'title_') && (str_contains($cleanBTitle, substr($k, 6)) || str_contains(substr($k, 6), $cleanBTitle))) {
                        $matchedPkg = $v;
                        break;
                    }
                }
            }

            if ($rb['booking_type'] === 'cruise') {
                $matchedCruise = null;
                if (!empty($meta['cruise_id']) && isset($cruisesMap['id_' . $meta['cruise_id']])) {
                    $matchedCruise = $cruisesMap['id_' . $meta['cruise_id']];
                }
                if ($matchedCruise) {
                    $rb['pkg_image'] = $matchedCruise['featured_image'];
                    $rb['pkg_location'] = 'Port: ' . $matchedCruise['departure_port'];
                    $rb['pkg_days'] = $matchedCruise['duration_days'];
                    $rb['pkg_nights'] = $matchedCruise['duration_nights'];
                    $rb['pkg_slug'] = $matchedCruise['slug'];
                    $rb['pkg_tag'] = $matchedCruise['badge'];
                } else {
                    $rb['pkg_image'] = $meta['cruise_image'] ?? null;
                    $rb['pkg_location'] = !empty($meta['ship_name']) ? ('Ship: ' . $meta['ship_name']) : 'Ocean Cruise';
                }
            } else {
                $rb['pkg_image'] = $matchedPkg['image'] ?? ($meta['hotel_image'] ?? null);
                $rb['pkg_location'] = $matchedPkg['location'] ?? ($meta['destination'] ?? ($meta['city'] ?? ''));
                $rb['pkg_days'] = $matchedPkg['duration_days'] ?? null;
                $rb['pkg_nights'] = $matchedPkg['duration_nights'] ?? null;
                $rb['pkg_slug'] = $matchedPkg['slug'] ?? null;
                $rb['pkg_tag'] = $matchedPkg['badge'] ?? null;
                $rb['package_id'] = $matchedPkg['id'] ?? null;
            }

            $bookings[] = $rb;
        }
    } catch (Exception $e) {
        $bookings = [];
    }
}

$pageTitle = "Bookings & Reservations";
include 'components/head.php';
?>

<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        <!-- Header -->
        <?php include 'components/header.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full space-y-5">
            
            <!-- Page Header / Top Action Strip -->
            <div class="bg-white border border-[#e5e4dc] p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 bg-sage-600"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5">Reservations Desk</span>
                        <span class="text-xs text-slate-400">• Total <?php echo number_format($totalBookings); ?> orders recorded</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        Customer Bookings &amp; Itineraries
                    </h1>
                    <p class="text-xs text-slate-500">
                        Inspect detailed package details, customer credentials, advance token payments, balance due, and booking history.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="packages.php" class="px-3.5 py-1.5 text-xs font-semibold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-map-location-dot text-[10px]"></i>
                        <span>Manage Packages</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 border text-xs font-semibold flex items-center justify-between <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border-sage-200' : 'bg-rose-50 text-rose-800 border-rose-200'; ?>">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?> text-sm"></i>
                        <span><?php echo htmlspecialchars($alertMessage); ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Metrics Cards (Sharp Border-First Sage & Cream Boxes) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Bookings</div>
                        <div class="text-2xl font-extrabold font-space text-slate-900 mt-1"><?php echo number_format($totalBookings); ?></div>
                        <div class="text-[10px] text-slate-400 mt-0.5"><?php echo number_format($confirmedCount); ?> confirmed orders</div>
                    </div>
                    <div class="w-10 h-10 bg-cream-100 text-sage-700 border border-[#e5e4dc] flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>

                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-sage-800">Advance Collected</div>
                        <div class="text-2xl font-extrabold font-space text-emerald-700 mt-1">₹<?php echo number_format($totalAdvancePaid > 0 ? $totalAdvancePaid : $totalSales); ?></div>
                        <div class="text-[10px] text-emerald-700 mt-0.5 font-medium">Received via Gateway</div>
                    </div>
                    <div class="w-10 h-10 bg-sage-50 text-sage-700 border border-sage-200 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>

                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Pending Balance Due</div>
                        <div class="text-2xl font-extrabold font-space text-amber-700 mt-1">₹<?php echo number_format($totalBalanceDue); ?></div>
                        <div class="text-[10px] text-amber-700 mt-0.5 font-medium">Payable on Arrival</div>
                    </div>
                    <div class="w-10 h-10 bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>

                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Vacation Value</div>
                        <div class="text-2xl font-extrabold font-space text-slate-900 mt-1">₹<?php echo number_format($totalSales); ?></div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Excl. cancelled bookings</div>
                    </div>
                    <div class="w-10 h-10 bg-cream-100 text-slate-700 border border-[#e5e4dc] flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Bar Strip -->
            <div class="bg-white p-3.5 border border-[#e5e4dc] flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-1.5 w-full md:w-auto">
                    <a href="bookings.php" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo (empty($filterStatus) && empty($filterType)) ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        All (<?php echo $totalBookings; ?>)
                    </a>
                    <a href="bookings.php?status=confirmed" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo $filterStatus === 'confirmed' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        Confirmed (<?php echo $confirmedCount; ?>)
                    </a>
                    <a href="bookings.php?status=pending" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo $filterStatus === 'pending' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        Pending (<?php echo $pendingCount; ?>)
                    </a>
                    <a href="bookings.php?status=completed" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo $filterStatus === 'completed' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        Completed (<?php echo $completedCount; ?>)
                    </a>
                    <a href="bookings.php?type=package" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo $filterType === 'package' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-map-location-dot text-[10px] mr-1"></i> Tours
                    </a>
                    <a href="bookings.php?type=hotel" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo $filterType === 'hotel' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-hotel text-[10px] mr-1"></i> Hotels
                    </a>
                    <a href="bookings.php?type=cruise" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo $filterType === 'cruise' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-ship text-[10px] mr-1"></i> Cruises
                    </a>
                    <a href="bookings.php?type=flight_inquiry" class="px-3 py-1.5 text-xs font-bold transition-all border <?php echo $filterType === 'flight_inquiry' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-600 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-plane-departure text-[10px] mr-1"></i> Flights
                    </a>
                </div>

                <form method="GET" action="bookings.php" class="flex items-center gap-2 w-full md:w-80">
                    <?php if (!empty($filterStatus)): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
                    <?php endif; ?>
                    <?php if (!empty($filterType)): ?>
                        <input type="hidden" name="type" value="<?php echo htmlspecialchars($filterType); ?>">
                    <?php endif; ?>
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search code, customer, tour, payment ID..." class="w-full pl-8 pr-3 py-1.5 bg-cream-50/50 border border-[#e5e4dc] text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sage-700 focus:bg-white transition-all font-medium">
                    </div>
                </form>
            </div>

            <!-- Bookings Table (Sharp & Clean) -->
            <div class="bg-white border border-[#e5e4dc] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-cream-100/70 border-b border-[#e5e4dc] text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4">Booking Reference</th>
                                <th class="py-3 px-4">Customer Details</th>
                                <th class="py-3 px-4">Service / Package</th>
                                <th class="py-3 px-4">Travel Date</th>
                                <th class="py-3 px-4">Payment Breakdown</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e4dc]">
                            <?php if (!empty($bookings)): ?>
                                <?php foreach ($bookings as $b): ?>
                                    <?php
                                    $st = strtolower($b['status']);
                                    $bType = $b['booking_type'] ?? 'package';
                                    $totalAmt = (float)$b['amount'];
                                    $advAmt = (float)($b['advance_amount'] > 0 ? $b['advance_amount'] : $totalAmt);
                                    $balAmt = (float)$b['balance_amount'];

                                    $bJson = htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr class="hover:bg-cream-50/80 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" 
                                                        onclick="openBookingDossier(<?php echo $bJson; ?>)"
                                                        class="font-mono font-bold text-sage-900 bg-sage-50 hover:bg-sage-100 px-2 py-0.5 border border-sage-200 transition text-left cursor-pointer inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-receipt text-[10px] text-sage-600"></i>
                                                    <span><?php echo htmlspecialchars($b['booking_code']); ?></span>
                                                </button>
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                <?php echo date('M d, Y • h:i A', strtotime($b['created_at'] ?? 'now')); ?>
                                            </div>
                                        </td>

                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 flex items-center gap-1">
                                                <span><?php echo htmlspecialchars($b['customer_name']); ?></span>
                                                <?php if (!empty($b['user_id'])): ?>
                                                    <span class="text-[9px] font-bold bg-sage-50 text-sage-800 border border-sage-200 px-1" title="Registered User">User #<?php echo (int)$b['user_id']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[11px] text-slate-500">
                                                <a href="mailto:<?php echo htmlspecialchars($b['customer_email']); ?>" class="hover:text-sage-700 hover:underline"><?php echo htmlspecialchars($b['customer_email']); ?></a>
                                            </div>
                                            <div class="text-[11px] text-slate-400">
                                                <a href="tel:<?php echo htmlspecialchars($b['customer_phone']); ?>" class="hover:text-sage-700"><?php echo htmlspecialchars($b['customer_phone']); ?></a>
                                            </div>
                                        </td>

                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <?php if ($bType === 'hotel'): ?>
                                                    <span class="text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-200 px-1.5 py-0.2">Hotel Stay</span>
                                                <?php elseif ($bType === 'cruise'): ?>
                                                    <span class="text-[9px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 px-1.5 py-0.2">Ocean Cruise</span>
                                                <?php elseif ($bType === 'flight_inquiry'): ?>
                                                    <span class="text-[9px] font-bold bg-sky-50 text-sky-800 border border-sky-200 px-1.5 py-0.2">Flight Query</span>
                                                <?php else: ?>
                                                    <span class="text-[9px] font-bold bg-teal-50 text-teal-800 border border-teal-200 px-1.5 py-0.2">Tour Package</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="font-medium text-slate-800 max-w-xs truncate cursor-pointer hover:text-sage-700 font-semibold" onclick="openBookingDossier(<?php echo $bJson; ?>)">
                                                <?php echo htmlspecialchars($b['package_title']); ?>
                                            </div>
                                            <?php if (!empty($b['pkg_location'])): ?>
                                                <div class="text-[10px] text-slate-400">
                                                    <i class="fa-solid fa-location-dot text-[9px] text-slate-400 mr-0.5"></i>
                                                    <?php echo htmlspecialchars($b['pkg_location']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3 px-4 text-slate-600 font-medium">
                                            <div class="flex items-center gap-1">
                                                <i class="fa-regular fa-calendar text-slate-400 text-xs"></i>
                                                <span><?php echo date('M d, Y', strtotime($b['travel_date'])); ?></span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                <i class="fa-solid fa-users text-[9px] mr-1"></i><?php echo (int)($b['guests_count'] ?? 1); ?> Guest<?php echo (int)($b['guests_count'] ?? 1) > 1 ? 's' : ''; ?>
                                            </div>
                                        </td>

                                        <td class="py-3 px-4 font-space">
                                            <div class="font-bold text-slate-900 text-xs">
                                                Total: ₹<?php echo number_format($totalAmt); ?>
                                            </div>
                                            <div class="text-[11px] text-emerald-700 font-semibold">
                                                Paid: ₹<?php echo number_format($advAmt); ?>
                                            </div>
                                            <?php if ($balAmt > 0): ?>
                                                <div class="text-[10px] text-amber-700 font-semibold">
                                                    Due: ₹<?php echo number_format($balAmt); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3 px-4">
                                            <?php
                                            $badgeClass = 'bg-cream-100 text-slate-700 border-[#e5e4dc]';
                                            if ($st === 'confirmed') $badgeClass = 'bg-sage-50 text-sage-900 border-sage-300';
                                            elseif ($st === 'pending') $badgeClass = 'bg-amber-50 text-amber-900 border-amber-300';
                                            elseif ($st === 'completed') $badgeClass = 'bg-sky-50 text-sky-900 border-sky-300';
                                            elseif ($st === 'cancelled') $badgeClass = 'bg-rose-50 text-rose-900 border-rose-300';
                                            ?>
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[11px] font-bold border <?php echo $badgeClass; ?>">
                                                <span class="w-1.5 h-1.5 <?php echo $st === 'confirmed' ? 'bg-sage-700' : ($st === 'pending' ? 'bg-amber-600' : ($st === 'completed' ? 'bg-sky-600' : 'bg-rose-600')); ?>"></span>
                                                <?php echo ucfirst($st); ?>
                                            </span>
                                        </td>

                                        <td class="py-3 px-4 text-right">
                                            <div class="inline-flex items-center gap-1.5 justify-end">
                                                <!-- Eye Button: Inspect Package & Booking Dossier -->
                                                <button type="button" 
                                                        onclick="openBookingDossier(<?php echo $bJson; ?>)"
                                                        title="View Complete Package & Payment Dossier"
                                                        class="w-7 h-7 bg-sage-50 hover:bg-sage-700 text-sage-700 hover:text-white border border-sage-300 transition-colors flex items-center justify-center text-xs font-bold cursor-pointer">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>

                                                <!-- Status Quick Changer Form -->
                                                <form method="POST" action="bookings.php" class="inline-flex items-center">
                                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                    <input type="hidden" name="update_status" value="1">
                                                    <select name="status" onchange="this.form.submit()" class="text-[11px] py-1 px-1.5 border border-[#e5e4dc] bg-white text-slate-700 hover:border-slate-400 focus:outline-none focus:border-sage-700 cursor-pointer font-semibold">
                                                        <option value="confirmed" <?php echo $st === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="pending" <?php echo $st === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="completed" <?php echo $st === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="cancelled" <?php echo $st === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </form>

                                                <!-- Print / Live Voucher View -->
                                                <a href="../booking-success.php?code=<?php echo urlencode($b['booking_code']); ?>" target="_blank" title="Print Travel Voucher" class="w-7 h-7 bg-cream-50 hover:bg-cream-200 text-slate-600 border border-[#e5e4dc] flex items-center justify-center text-xs transition-colors">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 space-y-2">
                                        <div class="w-10 h-10 bg-cream-100 border border-[#e5e4dc] flex items-center justify-center mx-auto text-slate-400">
                                            <i class="fa-solid fa-calendar-xmark text-lg"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-600">No bookings match the filter criteria</p>
                                        <a href="bookings.php" class="inline-block text-[11px] text-sage-700 underline font-bold">Clear Filters</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- ========================================================================= -->
<!-- COMPREHENSIVE BOOKING DOSSIER & PACKAGE INSPECTOR MODAL (ZERO SHADOW)     -->
<!-- ========================================================================= -->
<div id="bookingDossierModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6 overflow-y-auto">
    <div class="bg-white border border-[#e5e4dc] w-full max-w-3xl my-auto overflow-hidden animate-in fade-in zoom-in-95 duration-150 flex flex-col max-h-[90vh]">
        
        <!-- Modal Top Bar -->
        <div class="bg-cream-100/90 px-5 py-4 border-b border-[#e5e4dc] flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-sage-700 text-white flex items-center justify-center text-sm font-bold border border-sage-800">
                    <i class="fa-solid fa-passport"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span id="modalBookingCode" class="font-mono font-extrabold text-sm text-slate-900">#GF-BOOKING</span>
                        <span id="modalTypeBadge" class="text-[10px] font-bold px-2 py-0.5 bg-sage-50 text-sage-800 border border-sage-200 uppercase">Tour Package</span>
                        <span id="modalStatusBadge" class="text-[10px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200">Confirmed</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Booking Placed on: <span id="modalCreatedAt" class="font-semibold text-slate-700">Oct 06, 2026</span></p>
                </div>
            </div>

            <button type="button" onclick="closeBookingDossier()" class="w-8 h-8 flex items-center justify-center bg-white border border-[#e5e4dc] text-slate-500 hover:text-slate-900 hover:bg-cream-50 transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Modal Scrollable Content Body -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-5 divide-y divide-[#e5e4dc]">
            
            <!-- SECTION 1: PACKAGE & SERVICE INSPECTOR CARD -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-map-location-dot text-sage-700"></i>
                        <span>Reserved Travel Package / Service Details</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <a id="modalPackageLiveLink" href="#" target="_blank" class="text-[11px] font-bold text-sage-700 hover:underline inline-flex items-center gap-1">
                            <span>Open Live Page</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                        </a>
                        <span class="text-slate-300">|</span>
                        <a id="modalPackageAdminEditLink" href="#" class="text-[11px] font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
                            <i class="fa-solid fa-pen-to-square text-[9px]"></i>
                            <span>Edit in Admin</span>
                        </a>
                    </div>
                </div>

                <div class="bg-cream-50/60 border border-[#e5e4dc] p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                    <div class="w-full sm:w-28 h-24 bg-slate-100 border border-[#e5e4dc] shrink-0 overflow-hidden relative">
                        <img id="modalPackageImage" src="" alt="Tour Thumbnail" class="w-full h-full object-cover" onerror="this.src='../assets/images/placeholder-tour.webp'">
                        <span id="modalDurationTag" class="absolute bottom-1 right-1 px-1.5 py-0.5 bg-slate-900/80 text-white font-mono text-[9px] font-bold">5D/4N</span>
                    </div>

                    <div class="flex-1 min-w-0 space-y-1.5">
                        <h4 id="modalPackageTitle" class="text-base font-extrabold text-slate-900 leading-snug">
                            Kashmir Paradise &amp; Gulmarg Tour
                        </h4>
                        
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                            <span class="inline-flex items-center gap-1 text-slate-700">
                                <i class="fa-solid fa-location-dot text-sage-600 text-[10px]"></i>
                                <span id="modalPackageLocation">Srinagar, Kashmir, India</span>
                            </span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="inline-flex items-center gap-1 text-slate-700">
                                <i class="fa-regular fa-calendar-check text-slate-400 text-[10px]"></i>
                                <span>Travel: <strong id="modalTravelDate" class="text-slate-900 font-bold">Nov 15, 2026</strong></span>
                            </span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="inline-flex items-center gap-1 text-slate-700">
                                <i class="fa-solid fa-users text-slate-400 text-[10px]"></i>
                                <span><strong id="modalGuestsCount" class="text-slate-900 font-bold">2</strong> Travelers</span>
                            </span>
                        </div>

                        <div id="modalDetailsExtraWrap" class="text-[11px] text-slate-500 bg-white border border-[#e5e4dc] p-2 mt-2">
                            <strong class="text-slate-700">Booking Meta / Room: </strong>
                            <span id="modalDetailsExtra">—</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: CUSTOMER / TRAVELER CONTACT INFORMATION -->
            <div class="pt-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-check text-sage-700"></i>
                    <span>Customer &amp; Traveler Information</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3 bg-cream-50/60 border border-[#e5e4dc]">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Primary Contact</div>
                        <div id="modalCustomerName" class="font-bold text-sm text-slate-900 mt-1">John Doe</div>
                        <div id="modalUserAccountBadge" class="text-[10px] text-sage-800 font-bold mt-0.5">Registered User #1</div>
                    </div>

                    <div class="p-3 bg-cream-50/60 border border-[#e5e4dc]">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email Address</div>
                        <div class="mt-1">
                            <a id="modalCustomerEmail" href="mailto:name@example.com" class="font-bold text-xs text-sage-700 hover:underline">name@example.com</a>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Confirmation email dispatched</div>
                    </div>

                    <div class="p-3 bg-cream-50/60 border border-[#e5e4dc]">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Phone &amp; WhatsApp</div>
                        <div class="flex items-center justify-between mt-1">
                            <a id="modalCustomerPhone" href="tel:+919876543210" class="font-bold text-xs text-slate-900 hover:text-sage-700">+91 98765 43210</a>
                            <a id="modalWhatsappLink" href="#" target="_blank" class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] font-bold hover:bg-emerald-100 transition inline-flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp text-xs"></i>
                                <span>Chat</span>
                            </a>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Direct traveler helpline</div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: FINANCIAL BREAKDOWN & PAYMENT TRANSACTION AUDIT -->
            <div class="pt-5 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-money-bill-wave text-sage-700"></i>
                    <span>Payment Breakdown &amp; Transaction Details</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 bg-white border border-[#e5e4dc]">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Vacation Value</div>
                        <div id="modalTotalAmount" class="text-xl font-extrabold font-space text-slate-900 mt-1">₹35,000</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Full package rate</div>
                    </div>

                    <div class="p-3.5 bg-emerald-50/50 border border-emerald-200">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Advance Token Paid</div>
                        <div id="modalAdvancePaid" class="text-xl font-extrabold font-space text-emerald-700 mt-1">₹10,500</div>
                        <div class="text-[10px] text-emerald-700 mt-0.5 font-semibold">Payment Status: Settled</div>
                    </div>

                    <div class="p-3.5 bg-amber-50/50 border border-amber-200">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Balance Due on Arrival</div>
                        <div id="modalBalanceDue" class="text-xl font-extrabold font-space text-amber-700 mt-1">₹24,500</div>
                        <div class="text-[10px] text-amber-700 mt-0.5 font-semibold">Payable at destination</div>
                    </div>
                </div>

                <!-- Transaction Reference IDs -->
                <div class="bg-cream-50/60 border border-[#e5e4dc] p-3.5 space-y-2">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Payment Method / Gateway</span>
                            <span id="modalPaymentGateway" class="font-bold text-slate-800 font-mono">Razorpay Online</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Payment Transaction ID</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span id="modalPaymentId" class="font-bold text-slate-900 font-mono text-[11px] select-all truncate">pay_Ok123456789</span>
                                <button type="button" onclick="copyToClipboard(document.getElementById('modalPaymentId').textContent)" class="text-slate-400 hover:text-slate-700" title="Copy">
                                    <i class="fa-regular fa-copy text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Gateway Order ID</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span id="modalOrderId" class="font-bold text-slate-900 font-mono text-[11px] select-all truncate">order_Abc123456</span>
                                <button type="button" onclick="copyToClipboard(document.getElementById('modalOrderId').textContent)" class="text-slate-400 hover:text-slate-700" title="Copy">
                                    <i class="fa-regular fa-copy text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Bottom Actions Strip -->
        <div class="bg-cream-100/90 px-5 py-3.5 border-t border-[#e5e4dc] flex flex-wrap items-center justify-between gap-3 shrink-0">
            <!-- Update Status in Modal -->
            <form method="POST" action="bookings.php" class="flex items-center gap-2">
                <input type="hidden" id="modalFormBookingId" name="booking_id" value="">
                <input type="hidden" name="update_status" value="1">
                <label class="text-[11px] font-bold text-slate-600 uppercase">Change Status:</label>
                <select id="modalFormStatusSelect" name="status" class="text-xs py-1.5 px-3 border border-[#e5e4dc] bg-white text-slate-800 font-bold focus:outline-none focus:border-sage-700">
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-sage-700 hover:bg-sage-800 text-white text-xs font-bold border border-sage-800 transition">
                    Save
                </button>
            </form>

            <div class="flex items-center gap-2 ml-auto">
                <a id="modalPrintVoucherBtn" href="#" target="_blank" class="px-3.5 py-1.5 bg-white hover:bg-cream-50 text-slate-700 border border-[#e5e4dc] text-xs font-bold inline-flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Print Voucher</span>
                </a>
                <button type="button" onclick="closeBookingDossier()" class="px-4 py-1.5 bg-cream-200 hover:bg-cream-300 text-slate-800 text-xs font-bold border border-[#e5e4dc] transition">
                    Close
                </button>
            </div>
        </div>

    </div>
</div>

<script>
function openBookingDossier(b) {
    if (!b) return;

    // 1. Reference & Meta
    document.getElementById('modalBookingCode').textContent = '#' + (b.booking_code || 'GF-ORDER');
    document.getElementById('modalCreatedAt').textContent = b.created_at ? new Date(b.created_at).toLocaleString() : 'N/A';
    
    // Status Badge
    const st = (b.status || 'pending').toLowerCase();
    const stBadge = document.getElementById('modalStatusBadge');
    stBadge.textContent = st.charAt(0).toUpperCase() + st.slice(1);
    stBadge.className = 'text-[10px] font-bold px-2 py-0.5 border ' + 
        (st === 'confirmed' ? 'bg-sage-50 text-sage-900 border-sage-300' :
         st === 'completed' ? 'bg-sky-50 text-sky-900 border-sky-300' :
         st === 'cancelled' ? 'bg-rose-50 text-rose-900 border-rose-300' : 'bg-amber-50 text-amber-900 border-amber-300');

    // Type Badge
    const bType = b.booking_type || 'package';
    const typeBadge = document.getElementById('modalTypeBadge');
    if (bType === 'hotel') {
        typeBadge.textContent = 'Hotel Stay';
        typeBadge.className = 'text-[10px] font-bold px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 uppercase';
    } else if (bType === 'flight_inquiry') {
        typeBadge.textContent = 'Flight Inquiry';
        typeBadge.className = 'text-[10px] font-bold px-2 py-0.5 bg-sky-50 text-sky-800 border border-sky-200 uppercase';
    } else {
        typeBadge.textContent = 'Tour Package';
        typeBadge.className = 'text-[10px] font-bold px-2 py-0.5 bg-teal-50 text-teal-800 border border-teal-200 uppercase';
    }

    // 2. Package / Service Details
    document.getElementById('modalPackageTitle').textContent = b.package_title || 'Custom Tour Service';
    document.getElementById('modalPackageLocation').textContent = b.pkg_location || 'All India / International';
    document.getElementById('modalTravelDate').textContent = b.travel_date ? new Date(b.travel_date).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
    document.getElementById('modalGuestsCount').textContent = b.guests_count || '1';

    // Package Image & Duration
    const imgEl = document.getElementById('modalPackageImage');
    if (b.pkg_image) {
        imgEl.src = b.pkg_image.startsWith('http') ? b.pkg_image : '../' + b.pkg_image;
    } else {
        imgEl.src = '../assets/images/placeholder-tour.webp';
    }

    const durationTag = document.getElementById('modalDurationTag');
    if (b.pkg_days || b.pkg_nights) {
        durationTag.textContent = `${b.pkg_days || 0}D/${b.pkg_nights || 0}N`;
        durationTag.classList.remove('hidden');
    } else {
        durationTag.classList.add('hidden');
    }

    // Links to live page and admin edit
    const liveLink = document.getElementById('modalPackageLiveLink');
    const adminEditLink = document.getElementById('modalPackageAdminEditLink');
    if (bType === 'hotel') {
        liveLink.href = '../search.php?type=hotel';
        adminEditLink.href = 'hotels.php';
        adminEditLink.classList.remove('hidden');
    } else if (bType === 'flight_inquiry') {
        liveLink.href = '../flights.php';
        adminEditLink.classList.add('hidden');
    } else {
        liveLink.href = b.pkg_slug ? `../package-details.php?slug=${encodeURIComponent(b.pkg_slug)}` : `../package-details.php?id=${b.package_id || 0}`;
        adminEditLink.href = `packages-add.php?id=${b.package_id || 0}`;
        adminEditLink.classList.remove('hidden');
    }

    // Extra details / Room meta
    const extraWrap = document.getElementById('modalDetailsExtraWrap');
    const extraEl = document.getElementById('modalDetailsExtra');
    if (b.booking_details && b.booking_details !== '{}') {
        try {
            const parsed = typeof b.booking_details === 'string' ? JSON.parse(b.booking_details) : b.booking_details;
            let metaHtml = '';
            for (let k in parsed) {
                if (parsed[k]) metaHtml += `<span class="mr-2"><strong>${k.replace('_', ' ')}:</strong> ${parsed[k]}</span>`;
            }
            extraEl.innerHTML = metaHtml || 'Standard Reservation';
            extraWrap.classList.remove('hidden');
        } catch(e) {
            extraEl.textContent = b.booking_details;
            extraWrap.classList.remove('hidden');
        }
    } else {
        extraWrap.classList.add('hidden');
    }

    // 3. Customer Info
    document.getElementById('modalCustomerName').textContent = b.customer_name || 'Guest Traveler';
    const emailEl = document.getElementById('modalCustomerEmail');
    emailEl.textContent = b.customer_email || 'N/A';
    emailEl.href = 'mailto:' + (b.customer_email || '');

    const phoneEl = document.getElementById('modalCustomerPhone');
    phoneEl.textContent = b.customer_phone || 'N/A';
    phoneEl.href = 'tel:' + (b.customer_phone || '');

    const cleanPhone = (b.customer_phone || '').replace(/[^0-9]/g, '');
    const waLink = document.getElementById('modalWhatsappLink');
    if (cleanPhone) {
        const waMsg = encodeURIComponent(`Hi ${b.customer_name}, regards from <?php echo htmlspecialchars($siteName); ?> regarding your booking #${b.booking_code} for ${b.package_title}!`);
        waLink.href = `https://wa.me/${cleanPhone}?text=${waMsg}`;
        waLink.classList.remove('hidden');
    } else {
        waLink.classList.add('hidden');
    }

    const userBadge = document.getElementById('modalUserAccountBadge');
    if (b.user_id) {
        userBadge.textContent = `Registered Member • User Account #${b.user_id}`;
        userBadge.classList.remove('hidden');
    } else {
        userBadge.textContent = 'Guest Booking';
        userBadge.classList.remove('hidden');
    }

    // 4. Financial Breakdown
    const total = parseFloat(b.amount || 0);
    const advance = parseFloat(b.advance_amount || (total > 0 ? total : 0));
    const balance = parseFloat(b.balance_amount || 0);

    document.getElementById('modalTotalAmount').textContent = '₹' + total.toLocaleString();
    document.getElementById('modalAdvancePaid').textContent = '₹' + advance.toLocaleString();
    document.getElementById('modalBalanceDue').textContent = '₹' + balance.toLocaleString();

    document.getElementById('modalPaymentGateway').textContent = b.payment_gateway || 'Razorpay / Online';
    document.getElementById('modalPaymentId').textContent = b.payment_id || 'N/A';
    document.getElementById('modalOrderId').textContent = b.order_id || 'N/A';

    // 5. Actions
    document.getElementById('modalFormBookingId').value = b.id;
    document.getElementById('modalFormStatusSelect').value = st;
    document.getElementById('modalPrintVoucherBtn').href = `../booking-success.php?code=${encodeURIComponent(b.booking_code || '')}`;

    // Open Modal
    document.getElementById('bookingDossierModal').classList.remove('hidden');
}

function closeBookingDossier() {
    document.getElementById('bookingDossierModal').classList.add('hidden');
}

function copyToClipboard(text) {
    if (!text || text === 'N/A') return;
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard: ' + text);
    });
}

// Close on backdrop click or ESC
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeBookingDossier();
});
document.getElementById('bookingDossierModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('bookingDossierModal')) closeBookingDossier();
});
</script>

<?php include 'components/footer.php'; ?>
