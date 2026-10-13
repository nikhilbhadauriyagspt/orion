<?php
/**
 * Admin Travelers & Customer Intelligence Controller - Orion Advent
 * Shows full customer booking logs, repeat frequency, lifetime value, and order breakdowns.
 * Sage & Cream Minimal Zero-Shadow Design
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

// Handle user status actions (Block, Activate, Delete)
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
    $act = $_GET['action'];

    if ($pdo && $userId > 0) {
        try {
            if ($act === 'block') {
                $pdo->prepare("UPDATE `users` SET `status` = 'blocked' WHERE `id` = ?")->execute([$userId]);
                $alertMessage = "User has been blocked successfully.";
            } elseif ($act === 'activate') {
                $pdo->prepare("UPDATE `users` SET `status` = 'active', `is_verified` = 1 WHERE `id` = ?")->execute([$userId]);
                $alertMessage = "User account has been activated and verified.";
            } elseif ($act === 'delete') {
                $pdo->prepare("DELETE FROM `users` WHERE `id` = ?")->execute([$userId]);
                $alertMessage = "User account deleted permanently.";
            }
        } catch (Exception $e) {
            $alertMessage = "Action failed: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// Fetch users with aggregated booking statistics (Count, Total Spent, Last Booking Date)
$users = [];
$totalUsers = 0;
$verifiedUsers = 0;
$activeBookersCount = 0;
$totalLifetimeBookings = 0;

if ($pdo) {
    try {
        $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
        $verifiedUsers = (int)$pdo->query("SELECT COUNT(*) FROM `users` WHERE `is_verified` = 1")->fetchColumn();
        $totalLifetimeBookings = (int)$pdo->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();

        $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
        $filterBooking = isset($_GET['has_bookings']) ? trim($_GET['has_bookings']) : '';

        $sql = "
            SELECT 
                u.*,
                COUNT(b.id) AS total_bookings,
                COALESCE(SUM(b.amount), 0) AS total_spent,
                COALESCE(SUM(b.advance_amount), 0) AS total_advance_paid,
                COALESCE(SUM(b.balance_amount), 0) AS total_balance_due,
                MAX(b.created_at) AS last_booking_date
            FROM `users` u
            LEFT JOIN `bookings` b ON (b.user_id = u.id OR b.customer_email = u.email)
            WHERE 1=1
        ";
        $params = [];

        if (!empty($searchQuery)) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }

        $sql .= " GROUP BY u.id";

        if ($filterBooking === '1') {
            $sql .= " HAVING total_bookings > 0";
        } elseif ($filterBooking === '0') {
            $sql .= " HAVING total_bookings = 0";
        }

        $sql .= " ORDER BY total_bookings DESC, u.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        foreach ($users as $u) {
            if ((int)$u['total_bookings'] > 0) {
                $activeBookersCount++;
            }
        }
    } catch (Exception $e) {
        $users = [];
    }
}

// Fetch all bookings for all users to enable instant modal preview without round-trip lag
$userBookingsMap = [];
if ($pdo) {
    try {
        $allBkgStmt = $pdo->query("SELECT * FROM `bookings` ORDER BY `id` DESC");
        while ($bkg = $allBkgStmt->fetch()) {
            $uIdKey = (string)($bkg['user_id'] ?? '');
            $uEmailKey = strtolower(trim($bkg['customer_email'] ?? ''));

            if (!empty($uIdKey)) {
                $userBookingsMap['id_' . $uIdKey][] = $bkg;
            }
            if (!empty($uEmailKey)) {
                $userBookingsMap['email_' . $uEmailKey][] = $bkg;
            }
        }
    } catch (Exception $e) {
        $userBookingsMap = [];
    }
}

$pageTitle = "Travelers & Customer Bookings History";
include 'components/head.php';
?>

<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        
        <!-- Header -->
        <?php include 'components/header.php'; ?>

        <!-- Content Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full space-y-5">

            <!-- Top Header Action Strip -->
            <div class="bg-white border border-[#e5e4dc] p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 bg-sage-600"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5">Customer Intelligence</span>
                        <span class="text-xs text-slate-400">• Total <?php echo number_format($totalUsers); ?> registered travelers</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        Traveler Booking Frequency &amp; Account Logs
                    </h1>
                    <p class="text-xs text-slate-500">
                        View how many times each user has booked, exact booking dates, total vacation spend, advance paid vs balance due, and full itinerary breakdown.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="bookings.php" class="px-3.5 py-1.5 text-xs font-semibold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-check text-[10px]"></i>
                        <span>All Reservations Desk</span>
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
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Travelers</div>
                        <div class="text-2xl font-extrabold font-space text-slate-900 mt-1"><?php echo number_format($totalUsers); ?></div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Registered accounts</div>
                    </div>
                    <div class="w-10 h-10 bg-cream-100 text-sage-700 border border-[#e5e4dc] flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-sage-800">Active Bookers</div>
                        <div class="text-2xl font-extrabold font-space text-sage-700 mt-1"><?php echo number_format($activeBookersCount); ?></div>
                        <div class="text-[10px] text-sage-700 mt-0.5 font-medium">Placed ≥1 bookings</div>
                    </div>
                    <div class="w-10 h-10 bg-sage-50 text-sage-700 border border-sage-200 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-suitcase-rolling"></i>
                    </div>
                </div>

                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Lifetime Bookings</div>
                        <div class="text-2xl font-extrabold font-space text-amber-700 mt-1"><?php echo number_format($totalLifetimeBookings); ?></div>
                        <div class="text-[10px] text-amber-700 mt-0.5 font-medium">Tours + Hotels + Flights</div>
                    </div>
                    <div class="w-10 h-10 bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>

                <div class="bg-white p-4 border border-[#e5e4dc] flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Verified Profiles</div>
                        <div class="text-2xl font-extrabold font-space text-slate-900 mt-1"><?php echo number_format($verifiedUsers); ?></div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Email verified</div>
                    </div>
                    <div class="w-10 h-10 bg-cream-100 text-slate-700 border border-[#e5e4dc] flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Strip -->
            <div class="bg-white p-3.5 border border-[#e5e4dc] flex flex-col md:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto">
                    <a href="users.php" class="px-3 py-1.5 text-xs font-bold border transition-colors <?php echo empty($filterBooking) ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-700 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        All Customers (<?php echo $totalUsers; ?>)
                    </a>
                    <a href="users.php?has_bookings=1" class="px-3 py-1.5 text-xs font-bold border transition-colors <?php echo $filterBooking === '1' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-700 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-bag-shopping text-[10px] mr-1"></i>
                        <span>With Bookings (<?php echo $activeBookersCount; ?>)</span>
                    </a>
                    <a href="users.php?has_bookings=0" class="px-3 py-1.5 text-xs font-bold border transition-colors <?php echo $filterBooking === '0' ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 text-slate-700 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <span>No Bookings Yet</span>
                    </a>
                </div>

                <form method="GET" action="users.php" class="flex items-center gap-2 w-full md:w-80">
                    <?php if (!empty($filterBooking)): ?>
                        <input type="hidden" name="has_bookings" value="<?php echo htmlspecialchars($filterBooking); ?>">
                    <?php endif; ?>
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>" placeholder="Search name, email, phone..." class="w-full pl-8 pr-3 py-1.5 bg-cream-50/50 border border-[#e5e4dc] text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sage-700 focus:bg-white transition-all font-medium">
                    </div>
                </form>
            </div>

            <!-- Users Directory Table with Booking Stats -->
            <div class="bg-white border border-[#e5e4dc] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-cream-100/70 border-b border-[#e5e4dc] text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4">Traveler Profile</th>
                                <th class="py-3 px-4">Total Bookings</th>
                                <th class="py-3 px-4">Total Spend (LTV)</th>
                                <th class="py-3 px-4">Advance Paid / Balance</th>
                                <th class="py-3 px-4">Last Booking Date</th>
                                <th class="py-3 px-4">Account Status</th>
                                <th class="py-3 px-4 text-right">View History / Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e4dc]">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                    <?php 
                                    $bCount = (int)$u['total_bookings'];
                                    $uSpent = (float)$u['total_spent'];
                                    $uAdv = (float)$u['total_advance_paid'];
                                    $uBal = (float)$u['total_balance_due'];
                                    $lastDate = !empty($u['last_booking_date']) ? date('M d, Y', strtotime($u['last_booking_date'])) : null;
                                    $ust = strtolower($u['status'] ?? 'active');

                                    // Get user specific bookings array for JS payload
                                    $myBookings = [];
                                    $idKey = 'id_' . $u['id'];
                                    $emKey = 'email_' . strtolower(trim($u['email']));
                                    if (isset($userBookingsMap[$idKey])) {
                                        $myBookings = array_merge($myBookings, $userBookingsMap[$idKey]);
                                    }
                                    if (isset($userBookingsMap[$emKey])) {
                                        foreach ($userBookingsMap[$emKey] as $eb) {
                                            $already = false;
                                            foreach ($myBookings as $mb) {
                                                if ($mb['id'] == $eb['id']) { $already = true; break; }
                                            }
                                            if (!$already) $myBookings[] = $eb;
                                        }
                                    }
                                    $bookingsJson = htmlspecialchars(json_encode($myBookings), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr class="hover:bg-cream-50/80 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 bg-sage-100 text-sage-800 font-bold text-xs flex items-center justify-center border border-sage-300 shrink-0">
                                                    <?php echo strtoupper(substr($u['name'] ?? 'U', 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                        <span><?php echo htmlspecialchars($u['name'] ?? 'User'); ?></span>
                                                        <?php if ($bCount >= 3): ?>
                                                            <span class="text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-200 px-1 py-0.2" title="Frequent Traveler">VIP ⭐</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">
                                                        <?php echo htmlspecialchars($u['email']); ?>
                                                        <?php echo !empty($u['phone']) ? ' • ' . htmlspecialchars($u['phone']) : ''; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="py-3 px-4">
                                            <?php if ($bCount > 0): ?>
                                                <button type="button" 
                                                        onclick="openUserBookingsModal(<?php echo htmlspecialchars(json_encode($u['name'])); ?>, <?php echo htmlspecialchars(json_encode($u['email'])); ?>, <?php echo htmlspecialchars(json_encode($u['phone'])); ?>, <?php echo $bookingsJson; ?>)"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-extrabold bg-sage-50 text-sage-800 border border-sage-300 hover:bg-sage-100 transition-colors cursor-pointer">
                                                    <i class="fa-solid fa-receipt text-[10px] text-sage-700"></i>
                                                    <span><?php echo $bCount; ?> Booking<?php echo $bCount > 1 ? 's' : ''; ?></span>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-400 font-medium text-[11px]">0 Bookings</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3 px-4 font-bold text-slate-900 font-space text-xs">
                                            <?php if ($uSpent > 0): ?>
                                                ₹<?php echo number_format($uSpent); ?>
                                            <?php else: ?>
                                                <span class="text-slate-400 font-normal">₹0</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3 px-4 text-xs">
                                            <?php if ($bCount > 0): ?>
                                                <div class="text-emerald-700 font-bold">Paid: ₹<?php echo number_format($uAdv); ?></div>
                                                <?php if ($uBal > 0): ?>
                                                    <div class="text-amber-700 text-[11px] font-semibold">Due: ₹<?php echo number_format($uBal); ?></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-[11px]">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                                            <?php if ($lastDate): ?>
                                                <span class="text-slate-800"><?php echo $lastDate; ?></span>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-[11px]">Never booked</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3 px-4">
                                            <?php
                                            $stClass = ($ust === 'blocked') ? 'bg-rose-50 text-rose-900 border-rose-300' : 'bg-cream-100 text-slate-700 border-[#e5e4dc]';
                                            ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border <?php echo $stClass; ?>">
                                                <span class="w-1.5 h-1.5 <?php echo ($ust === 'blocked') ? 'bg-rose-600' : 'bg-sage-600'; ?>"></span>
                                                <?php echo ucfirst($ust); ?>
                                            </span>
                                        </td>

                                        <td class="py-3 px-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <?php if ($bCount > 0): ?>
                                                    <button type="button" 
                                                            onclick="openUserBookingsModal(<?php echo htmlspecialchars(json_encode($u['name'])); ?>, <?php echo htmlspecialchars(json_encode($u['email'])); ?>, <?php echo htmlspecialchars(json_encode($u['phone'])); ?>, <?php echo $bookingsJson; ?>)"
                                                            class="px-2.5 py-1 bg-white hover:bg-cream-100 text-sage-900 font-bold text-[11px] border border-[#e5e4dc] transition-colors"
                                                            title="Inspect all bookings">
                                                        <i class="fa-solid fa-list-check text-[10px] text-sage-700 mr-1"></i>
                                                        <span>History</span>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($ust === 'blocked'): ?>
                                                    <a href="users.php?action=activate&user_id=<?php echo $u['id']; ?>" class="px-2 py-1 bg-sage-700 hover:bg-sage-800 text-white font-bold text-[11px] border border-sage-800 transition-colors">
                                                        Unblock
                                                    </a>
                                                <?php else: ?>
                                                    <a href="users.php?action=block&user_id=<?php echo $u['id']; ?>" onclick="return confirm('Block this customer account?');" class="p-1 text-slate-400 hover:text-amber-700 transition-colors" title="Block User">
                                                        <i class="fa-solid fa-ban text-xs"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="users.php?action=delete&user_id=<?php echo $u['id']; ?>" onclick="return confirm('Delete customer account permanently?');" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Delete User">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 space-y-2">
                                        <div class="w-10 h-10 bg-cream-100 border border-[#e5e4dc] flex items-center justify-center mx-auto text-slate-400">
                                            <i class="fa-solid fa-user-slash text-lg"></i>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-600">No customer records found</p>
                                        <a href="users.php" class="inline-block text-[11px] text-sage-700 underline font-bold">Clear Filters</a>
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

<!-- ===================================================================
     MODAL: CUSTOMER COMPLETE BOOKING HISTORY & SPEND LOG
==================================================================== -->
<div id="userBookingsModalOverlay" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-[#e5e4dc] max-w-3xl w-full p-6 space-y-5 animate-in zoom-in-95 duration-200 max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-3 border-b border-[#e5e4dc]">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5">Customer Booking Dossier</span>
                </div>
                <h3 class="text-lg font-bold font-space text-slate-900" id="ub_modal_customer_name">Traveler Name</h3>
                <p class="text-xs text-slate-500" id="ub_modal_customer_contact">email@example.com • +91 9876543210</p>
            </div>
            <button type="button" onclick="closeUserBookingsModal()" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 border border-[#e5e4dc] hover:bg-cream-100">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Metrics Overview Strip -->
        <div class="grid grid-cols-3 gap-3 bg-cream-50 p-3.5 border border-[#e5e4dc]">
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Lifetime Trips</span>
                <span class="text-base font-extrabold text-slate-900 font-space" id="ub_modal_count">0</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Spend</span>
                <span class="text-base font-extrabold text-slate-900 font-space" id="ub_modal_spent">₹0</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Advance Paid</span>
                <span class="text-base font-extrabold text-sage-700 font-space" id="ub_modal_advance">₹0</span>
            </div>
        </div>

        <!-- Bookings Detailed List -->
        <div class="space-y-3" id="ub_modal_bookings_container">
            <!-- Dynamically populated via JS -->
        </div>

        <div class="pt-3 border-t border-[#e5e4dc] flex items-center justify-between">
            <span class="text-xs text-slate-400"><?php echo htmlspecialchars($siteName); ?> Travel Management System</span>
            <button type="button" onclick="closeUserBookingsModal()" class="px-4 py-2 bg-cream-100 hover:bg-cream-200 text-slate-700 text-xs font-bold border border-[#e5e4dc]">
                Close Window
            </button>
        </div>

    </div>
</div>

<script>
function openUserBookingsModal(name, email, phone, bookings) {
    document.getElementById('ub_modal_customer_name').textContent = name || 'Traveler';
    document.getElementById('ub_modal_customer_contact').textContent = (email || '') + (phone ? ' • ' + phone : '');
    
    let totalSpent = 0;
    let totalAdvance = 0;

    const container = document.getElementById('ub_modal_bookings_container');
    container.innerHTML = '';

    if (!bookings || bookings.length === 0) {
        container.innerHTML = '<div class="p-8 text-center text-xs text-slate-400 bg-cream-50 border border-[#e5e4dc]">No booking records found for this traveler.</div>';
    } else {
        bookings.forEach((b, idx) => {
            const amount = parseFloat(b.amount) || 0;
            const advance = parseFloat(b.advance_amount) || 0;
            const balance = parseFloat(b.balance_amount) || 0;
            totalSpent += amount;
            totalAdvance += advance;

            const st = (b.status || 'confirmed').toLowerCase();
            let stBadge = 'bg-cream-100 text-slate-700 border-[#e5e4dc]';
            if (st === 'confirmed') stBadge = 'bg-sage-50 text-sage-900 border-sage-300';
            else if (st === 'pending') stBadge = 'bg-amber-50 text-amber-900 border-amber-300';
            else if (st === 'completed') stBadge = 'bg-sky-50 text-sky-900 border-sky-300';
            else if (st === 'cancelled') stBadge = 'bg-rose-50 text-rose-900 border-rose-300';

            const bType = (b.booking_type || 'package').toLowerCase();
            let typeLabel = 'Tour Package';
            let icon = 'fa-map-location-dot text-sage-700';
            if (bType === 'hotel') {
                typeLabel = 'Hotel & Resort Stay';
                icon = 'fa-hotel text-emerald-700';
            } else if (bType === 'flight_inquiry') {
                typeLabel = 'Flight Reservation Query';
                icon = 'fa-plane-departure text-sky-700';
            }

            const bookedDate = b.created_at ? new Date(b.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
            const travelDate = b.travel_date ? new Date(b.travel_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Flexible';

            const card = document.createElement('div');
            card.className = 'p-4 bg-white border border-[#e5e4dc] space-y-3';
            card.innerHTML = `
                <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">${typeLabel}</span>
                        <span class="font-mono text-xs font-bold text-sage-900 bg-sage-50 px-2 py-0.5 border border-sage-200">${b.booking_code}</span>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold border ${stBadge}">
                        ${st.toUpperCase()}
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1 flex-1">
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">${b.package_title}</h4>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
                            <span><i class="fa-regular fa-calendar mr-1"></i> Travel Date: <strong class="text-slate-700">${travelDate}</strong></span>
                            <span><i class="fa-regular fa-clock mr-1"></i> Booked on: ${bookedDate}</span>
                            ${b.payment_id && b.payment_id !== 'inquiry' ? `<span class="font-mono text-[10px] bg-cream-50 px-1 border border-[#e5e4dc]">Txn: ${b.payment_id}</span>` : ''}
                        </div>
                    </div>

                    <div class="bg-cream-50 p-2.5 border border-[#e5e4dc] text-right shrink-0 space-y-0.5 min-w-[140px]">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Amount</span>
                        <div class="text-sm font-extrabold font-space text-slate-900">₹${amount.toLocaleString('en-IN')}</div>
                        ${bType !== 'flight_inquiry' ? `
                            <div class="text-[10px] text-emerald-800 font-bold">Advance: ₹${advance.toLocaleString('en-IN')}</div>
                            ${balance > 0 ? `<div class="text-[10px] text-amber-800 font-bold">Balance: ₹${balance.toLocaleString('en-IN')}</div>` : ''}
                        ` : ''}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-[#e5e4dc] text-[11px]">
                    <span class="text-slate-400">Gateway: <strong class="text-slate-600 uppercase font-mono">${b.payment_gateway || 'razorpay'}</strong></span>
                    <a href="../booking-success.php?code=${encodeURIComponent(b.booking_code)}${bType === 'flight_inquiry' ? '&type=flight' : ''}" target="_blank" class="text-sage-700 font-bold hover:underline inline-flex items-center gap-1">
                        <span>Open Travel Voucher</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>
            `;
            container.appendChild(card);
        });
    }

    document.getElementById('ub_modal_count').textContent = bookings ? bookings.length : 0;
    document.getElementById('ub_modal_spent').textContent = '₹' + totalSpent.toLocaleString('en-IN');
    document.getElementById('ub_modal_advance').textContent = '₹' + totalAdvance.toLocaleString('en-IN');

    document.getElementById('userBookingsModalOverlay').classList.remove('hidden');
}

function closeUserBookingsModal() {
    document.getElementById('userBookingsModalOverlay').classList.add('hidden');
}
</script>

<?php include 'components/footer.php'; ?>
