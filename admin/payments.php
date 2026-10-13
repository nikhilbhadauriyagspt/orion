<?php
/**
 * Admin Payments & Transactions Ledger Console
 * Orion Advent Travel Portal
 * 
 * Provides detailed logs of all customer advance payments, token transactions,
 * gateway IDs, remaining balances, customer credentials, and printable payment receipts.
 */
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

// Auth check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();
$siteSettings = getGlobalSettings();

// Filter & Search Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$gatewayFilter = isset($_GET['gateway']) ? trim($_GET['gateway']) : 'all';
$serviceFilter = isset($_GET['service']) ? trim($_GET['service']) : 'all';
$dateFilter = isset($_GET['date_range']) ? trim($_GET['date_range']) : 'all';

// Base Query
$whereConditions = [];
$queryParams = [];

if (!empty($search)) {
    $whereConditions[] = "(
        b.booking_code LIKE ? OR 
        b.payment_id LIKE ? OR 
        b.customer_name LIKE ? OR 
        b.customer_email LIKE ? OR 
        b.customer_phone LIKE ? OR
        b.package_title LIKE ?
    )";
    $term = "%{$search}%";
    $queryParams = array_merge($queryParams, [$term, $term, $term, $term, $term, $term]);
}

if ($statusFilter !== 'all' && in_array($statusFilter, ['confirmed', 'pending', 'completed', 'cancelled'])) {
    $whereConditions[] = "b.status = ?";
    $queryParams[] = $statusFilter;
}

if ($gatewayFilter !== 'all') {
    $whereConditions[] = "b.payment_gateway = ?";
    $queryParams[] = $gatewayFilter;
}

if ($serviceFilter !== 'all' && in_array($serviceFilter, ['package', 'hotel', 'flight_inquiry'])) {
    $whereConditions[] = "b.booking_type = ?";
    $queryParams[] = $serviceFilter;
}

if ($dateFilter === 'today') {
    $whereConditions[] = "DATE(b.created_at) = CURDATE()";
} elseif ($dateFilter === 'week') {
    $whereConditions[] = "b.created_at >= (NOW() - INTERVAL 7 DAY)";
} elseif ($dateFilter === 'month') {
    $whereConditions[] = "b.created_at >= (NOW() - INTERVAL 30 DAY)";
}

$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Financial Summary KPIs
$totalAdvanceCollected = 0;
$totalGrossPipeline = 0;
$totalBalanceReceivable = 0;
$totalTransactionsCount = 0;
$confirmedCount = 0;

if ($pdo) {
    try {
        $statsStmt = $pdo->query("
            SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(advance_amount), 0) as total_advance,
                COALESCE(SUM(amount), 0) as total_gross,
                COALESCE(SUM(balance_amount), 0) as total_balance,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
            FROM `bookings`
            WHERE `status` != 'cancelled'
        ");
        $stats = $statsStmt->fetch();
        if ($stats) {
            $totalTransactionsCount = (int)$stats['total_count'];
            $totalAdvanceCollected = (float)$stats['total_advance'];
            $totalGrossPipeline = (float)$stats['total_gross'];
            $totalBalanceReceivable = (float)$stats['total_balance'];
            $confirmedCount = (int)$stats['confirmed_count'];
        }
    } catch (Exception $e) {}
}

// Fetch Payments / Bookings List
$payments = [];
if ($pdo) {
    try {
        $sql = "
            SELECT 
                b.*,
                u.avatar as user_avatar,
                u.id as registered_user_id
            FROM `bookings` b
            LEFT JOIN `users` u ON b.user_id = u.id
            {$whereSql}
            ORDER BY b.id DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($queryParams);
        $payments = $stmt->fetchAll();
    } catch (Exception $e) {
        $payments = [];
    }
}

$pageTitle = "Payments & Transactions Ledger";
include 'components/head.php';
?>

<!-- App Wrapper -->
<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        
        <!-- Header / Topbar -->
        <?php include 'components/header.php'; ?>

        <!-- Content Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full space-y-6">

            <!-- 1. Top Control Bar -->
            <div class="bg-white border border-[#e5e4dc] p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 bg-emerald-600"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5">Financial Desk</span>
                        <span class="text-xs text-slate-400">• Total <?php echo count($payments); ?> Transactions</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        Payments &amp; Transaction Ledger
                    </h1>
                    <p class="text-xs text-slate-500">
                        Live breakdown of customer advance tokens, balance receivables, Razorpay payment IDs, and printable receipts.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="bookings.php" class="px-3.5 py-1.5 text-xs font-semibold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-check text-sage-700 text-[11px]"></i>
                        <span>Bookings Desk</span>
                    </a>
                    <button onclick="window.print()" class="px-3.5 py-1.5 text-xs font-semibold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-print text-[11px]"></i>
                        <span>Print Statement</span>
                    </button>
                </div>
            </div>

            <!-- 2. Financial KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                
                <!-- KPI 1: Advance Collected -->
                <div class="bg-white p-4 border border-[#e5e4dc] border-t-3 border-t-emerald-600">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Advance Received</span>
                            <div class="text-2xl font-bold font-space text-emerald-700 mt-1">
                                ₹<?php echo number_format($totalAdvanceCollected); ?>
                            </div>
                        </div>
                        <div class="w-8 h-8 bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-[#f0eee6] flex items-center justify-between text-[11px] text-slate-500">
                        <span class="font-semibold text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[10px]"></i> Captured via Gateway
                        </span>
                        <span class="font-mono text-[10px]">100% Real DB</span>
                    </div>
                </div>

                <!-- KPI 2: Gross Vacation Value -->
                <div class="bg-white p-4 border border-[#e5e4dc] border-t-3 border-t-sage-700">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gross Vacation Value</span>
                            <div class="text-2xl font-bold font-space text-slate-900 mt-1">
                                ₹<?php echo number_format($totalGrossPipeline); ?>
                            </div>
                        </div>
                        <div class="w-8 h-8 bg-sage-50 text-sage-700 border border-sage-200 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-[#f0eee6] flex items-center justify-between text-[11px] text-slate-500">
                        <span>Total Pipeline</span>
                        <span class="text-slate-400">Excl. cancelled</span>
                    </div>
                </div>

                <!-- KPI 3: Pending Balance Due -->
                <div class="bg-white p-4 border border-[#e5e4dc] border-t-3 border-t-amber-600">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Balance Receivable</span>
                            <div class="text-2xl font-bold font-space text-amber-700 mt-1">
                                ₹<?php echo number_format($totalBalanceReceivable); ?>
                            </div>
                        </div>
                        <div class="w-8 h-8 bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-[#f0eee6] flex items-center justify-between text-[11px] text-slate-500">
                        <span class="text-amber-800 font-semibold">Payable on Arrival / Stay</span>
                        <span class="font-mono text-[10px] text-amber-700">Pending Due</span>
                    </div>
                </div>

                <!-- KPI 4: Total Transactions -->
                <div class="bg-white p-4 border border-[#e5e4dc] border-t-3 border-t-sky-700">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Recorded Orders</span>
                            <div class="text-2xl font-bold font-space text-slate-900 mt-1">
                                <?php echo $totalTransactionsCount; ?>
                            </div>
                        </div>
                        <div class="w-8 h-8 bg-sky-50 text-sky-700 border border-sky-200 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-[#f0eee6] flex items-center justify-between text-[11px] text-slate-500">
                        <span class="text-emerald-700 font-bold"><?php echo $confirmedCount; ?> Confirmed</span>
                        <span class="text-slate-400">• Real DB Logs</span>
                    </div>
                </div>

            </div>

            <!-- 3. Search & Multi-Filter Bar -->
            <div class="bg-white border border-[#e5e4dc] p-4">
                <form method="GET" action="payments.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    
                    <!-- Search Input -->
                    <div class="lg:col-span-2 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search Txn ID, Booking Code, Customer..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none transition-all placeholder:text-slate-400 font-medium">
                    </div>

                    <!-- Service Filter -->
                    <div>
                        <select name="service" onchange="this.form.submit()" class="w-full px-3 py-1.5 text-xs bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none transition-all font-medium text-slate-700">
                            <option value="all" <?php echo $serviceFilter === 'all' ? 'selected' : ''; ?>>All Services</option>
                            <option value="package" <?php echo $serviceFilter === 'package' ? 'selected' : ''; ?>>Tour Packages</option>
                            <option value="hotel" <?php echo $serviceFilter === 'hotel' ? 'selected' : ''; ?>>Hotels &amp; Resorts</option>
                            <option value="flight_inquiry" <?php echo $serviceFilter === 'flight_inquiry' ? 'selected' : ''; ?>>Flight Inquiries</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <select name="status" onchange="this.form.submit()" class="w-full px-3 py-1.5 text-xs bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none transition-all font-medium text-slate-700">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed / Paid</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending Token</option>
                            <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 px-3 py-1.5 bg-sage-700 hover:bg-sage-800 text-white text-xs font-semibold border border-sage-800 transition-colors">
                            Filter
                        </button>
                        <?php if (!empty($search) || $statusFilter !== 'all' || $serviceFilter !== 'all' || $gatewayFilter !== 'all' || $dateFilter !== 'all'): ?>
                            <a href="payments.php" class="px-2.5 py-1.5 bg-cream-200 hover:bg-cream-300 text-slate-700 text-xs font-semibold border border-[#e5e4dc] transition-colors" title="Clear Filters">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </form>
            </div>

            <!-- 4. Payments Ledger Data Table -->
            <div class="bg-white border border-[#e5e4dc] overflow-hidden">
                <div class="p-4 border-b border-[#e5e4dc] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-cream-50/60">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-emerald-600"></span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-space">Customer Payment Ledger</h2>
                        <span class="text-[10px] font-bold bg-white text-slate-600 border border-[#e5e4dc] px-2 py-0.5"><?php echo count($payments); ?> Records</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-[#e5e4dc] text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">Txn / Payment ID</th>
                                <th class="py-3 px-4">Customer Credentials</th>
                                <th class="py-3 px-4">Booking Reference</th>
                                <th class="py-3 px-4">Service / Package</th>
                                <th class="py-3 px-4">Advance Paid</th>
                                <th class="py-3 px-4">Balance Due</th>
                                <th class="py-3 px-4">Total Value</th>
                                <th class="py-3 px-4">Gateway / Mode</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e4dc]">
                            <?php if (!empty($payments)): ?>
                                <?php foreach ($payments as $p): ?>
                                    <?php
                                    $bType = $p['booking_type'] ?? 'package';
                                    $advPaid = (float)($p['advance_amount'] ?? 0);
                                    $balDue = (float)($p['balance_amount'] ?? 0);
                                    $totVal = (float)($p['amount'] ?? 0);
                                    $st = strtolower($p['status'] ?? 'pending');
                                    $gw = strtolower($p['payment_gateway'] ?? 'razorpay');
                                    $txnId = !empty($p['payment_id']) ? $p['payment_id'] : 'PAY_' . strtoupper(substr(md5($p['booking_code']), 0, 10));

                                    // Status Badge styling
                                    $stBadge = 'bg-cream-100 text-slate-700 border-[#e5e4dc]';
                                    $stDot = 'bg-slate-400';
                                    if ($st === 'confirmed') {
                                        $stBadge = 'bg-emerald-50 text-emerald-800 border-emerald-300';
                                        $stDot = 'bg-emerald-600';
                                    } elseif ($st === 'pending') {
                                        $stBadge = 'bg-amber-50 text-amber-800 border-amber-300';
                                        $stDot = 'bg-amber-600';
                                    } elseif ($st === 'completed') {
                                        $stBadge = 'bg-sky-50 text-sky-800 border-sky-300';
                                        $stDot = 'bg-sky-600';
                                    } elseif ($st === 'cancelled') {
                                        $stBadge = 'bg-rose-50 text-rose-800 border-rose-300';
                                        $stDot = 'bg-rose-600';
                                    }

                                    // Service type icon
                                    $serviceIcon = 'fa-map-location-dot';
                                    $serviceColor = 'text-sage-700 bg-sage-50 border-sage-200';
                                    $serviceLabel = 'Tour';
                                    if ($bType === 'hotel') {
                                        $serviceIcon = 'fa-hotel';
                                        $serviceColor = 'text-amber-700 bg-amber-50 border-amber-200';
                                        $serviceLabel = 'Hotel';
                                    } elseif ($bType === 'flight_inquiry') {
                                        $serviceIcon = 'fa-plane-departure';
                                        $serviceColor = 'text-sky-700 bg-sky-50 border-sky-200';
                                        $serviceLabel = 'Flight';
                                    }

                                    // Safe JSON payload for modal
                                    $receiptJson = htmlspecialchars(json_encode([
                                        'txn_id' => $txnId,
                                        'booking_code' => $p['booking_code'],
                                        'customer_name' => $p['customer_name'],
                                        'customer_email' => $p['customer_email'],
                                        'customer_phone' => $p['customer_phone'],
                                        'package_title' => $p['package_title'],
                                        'service_type' => $serviceLabel,
                                        'travel_date' => $p['travel_date'],
                                        'advance_amount' => $advPaid,
                                        'balance_amount' => $balDue,
                                        'total_amount' => $totVal,
                                        'payment_gateway' => ucfirst($gw),
                                        'status' => ucfirst($st),
                                        'created_at' => date('d M Y, h:i A', strtotime($p['created_at']))
                                    ]), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr class="hover:bg-cream-50/70 transition-colors">
                                        
                                        <!-- 1. Txn / Payment ID -->
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono font-bold text-slate-800 text-[11px] bg-cream-100 px-1.5 py-0.5 border border-[#e5e4dc] select-all">
                                                    <?php echo htmlspecialchars($txnId); ?>
                                                </span>
                                                <button type="button" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($txnId); ?>'); alert('Copied Payment ID: <?php echo htmlspecialchars($txnId); ?>');" class="text-slate-400 hover:text-slate-700 text-[10px]" title="Copy ID">
                                                    <i class="fa-regular fa-copy"></i>
                                                </button>
                                            </div>
                                            <span class="text-[10px] text-slate-400 block mt-1 font-mono">
                                                <?php echo date('d M Y, h:i A', strtotime($p['created_at'])); ?>
                                            </span>
                                        </td>

                                        <!-- 2. Customer Credentials -->
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-circle-user text-slate-400 text-xs"></i>
                                                <span><?php echo htmlspecialchars($p['customer_name']); ?></span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                                <i class="fa-regular fa-envelope text-[10px] text-slate-400"></i> <?php echo htmlspecialchars($p['customer_email']); ?>
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">
                                                <i class="fa-solid fa-phone text-[9px] text-slate-400"></i> <?php echo htmlspecialchars($p['customer_phone']); ?>
                                            </div>
                                        </td>

                                        <!-- 3. Booking Reference -->
                                        <td class="py-3 px-4">
                                            <a href="bookings.php?search=<?php echo urlencode($p['booking_code']); ?>" class="font-mono font-bold text-sage-900 bg-sage-50 hover:bg-sage-100 px-2 py-0.5 border border-sage-300 transition-colors inline-flex items-center gap-1">
                                                <span><?php echo htmlspecialchars($p['booking_code']); ?></span>
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-sage-600"></i>
                                            </a>
                                        </td>

                                        <!-- 4. Service / Package -->
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.2 border <?php echo $serviceColor; ?>">
                                                    <i class="fa-solid <?php echo $serviceIcon; ?> text-[8px] mr-0.5"></i> <?php echo $serviceLabel; ?>
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-mono">Travel: <?php echo date('d M Y', strtotime($p['travel_date'])); ?></span>
                                            </div>
                                            <div class="font-medium text-slate-800 max-w-xs truncate" title="<?php echo htmlspecialchars($p['package_title']); ?>">
                                                <?php echo htmlspecialchars($p['package_title']); ?>
                                            </div>
                                        </td>

                                        <!-- 5. Advance Paid -->
                                        <td class="py-3 px-4 font-space">
                                            <div class="font-bold text-emerald-700 text-sm">
                                                ₹<?php echo number_format($advPaid); ?>
                                            </div>
                                            <span class="text-[9px] uppercase font-bold text-emerald-800 bg-emerald-50 px-1 py-0.2 border border-emerald-200">Advance Token</span>
                                        </td>

                                        <!-- 6. Balance Due -->
                                        <td class="py-3 px-4 font-space">
                                            <div class="font-bold text-amber-800">
                                                ₹<?php echo number_format($balDue); ?>
                                            </div>
                                            <span class="text-[9px] text-slate-400">Due at Check-in</span>
                                        </td>

                                        <!-- 7. Total Value -->
                                        <td class="py-3 px-4 font-space font-bold text-slate-900">
                                            ₹<?php echo number_format($totVal); ?>
                                        </td>

                                        <!-- 8. Gateway -->
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white border border-[#e5e4dc] text-slate-700">
                                                <i class="fa-solid fa-credit-card text-[9px] text-sage-700"></i>
                                                <span><?php echo htmlspecialchars(strtoupper($gw)); ?></span>
                                            </span>
                                        </td>

                                        <!-- 9. Status -->
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-bold border <?php echo $stBadge; ?>">
                                                <span class="w-1.5 h-1.5 <?php echo $stDot; ?> rounded-full"></span>
                                                <span class="capitalize"><?php echo htmlspecialchars($st); ?></span>
                                            </span>
                                        </td>

                                        <!-- 10. Actions -->
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" onclick="openReceiptModal(<?php echo $receiptJson; ?>)" class="px-2.5 py-1 text-xs font-semibold bg-white hover:bg-cream-100 text-slate-800 border border-[#e5e4dc] transition-colors flex items-center gap-1" title="View &amp; Print Receipt">
                                                    <i class="fa-solid fa-receipt text-sage-700 text-[10px]"></i>
                                                    <span>Receipt</span>
                                                </button>
                                                <a href="bookings.php?search=<?php echo urlencode($p['booking_code']); ?>" class="p-1 text-slate-400 hover:text-slate-800 transition-colors" title="View Full Booking">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </a>
                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="py-12 text-center text-slate-500">
                                        <div class="w-10 h-10 bg-cream-100 text-slate-400 border border-[#e5e4dc] flex items-center justify-center mx-auto mb-2 text-sm">
                                            <i class="fa-solid fa-receipt"></i>
                                        </div>
                                        <p class="font-bold text-slate-800">No payment transactions match criteria</p>
                                        <p class="text-xs text-slate-400 mt-1">Try clearing active search or filters.</p>
                                        <a href="payments.php" class="inline-block mt-3 px-3 py-1 bg-white border border-[#e5e4dc] text-xs font-semibold text-slate-700 hover:bg-cream-100">
                                            Clear Filters
                                        </a>
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

<!-- ============================================================
     PRINTABLE OFFICIAL PAYMENT RECEIPT MODAL
     ============================================================ -->
<div id="receiptModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white border-2 border-sage-800 w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Modal Top Bar -->
        <div class="bg-cream-50 border-b border-[#e5e4dc] p-4 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-sage-700 text-white flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <span class="font-space font-bold text-sm text-slate-900 uppercase tracking-wider">Payment Receipt</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="printReceiptContent()" class="px-2.5 py-1 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 flex items-center gap-1">
                    <i class="fa-solid fa-print text-[10px]"></i> Print
                </button>
                <button type="button" onclick="closeReceiptModal()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-800 border border-[#e5e4dc] hover:bg-cream-100">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Receipt Content Body (Print Target) -->
        <div id="printableReceiptArea" class="p-6 overflow-y-auto space-y-5 bg-[#fdfdfb] text-slate-800 text-xs">
            
            <!-- Branding Header -->
            <div class="flex items-start justify-between border-b border-[#e5e4dc] pb-4">
                <div>
                    <h2 class="text-xl font-bold font-space text-slate-900 tracking-tight"><?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Orion Advent'); ?></h2>
                    <p class="text-[11px] text-slate-400"><?php echo htmlspecialchars($siteSettings['site_tagline'] ?? 'Your Passport to Adventure'); ?></p>
                    <p class="text-[10px] text-slate-400 font-mono mt-1"><?php echo htmlspecialchars($siteSettings['site_email'] ?? 'support@orionadvent.com'); ?></p>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-300 px-2 py-0.5">OFFICIAL RECEIPT</span>
                    <div class="text-xs font-mono font-bold text-slate-900 mt-1" id="rec-txn-id">PAY_XXXXXXXX</div>
                    <div class="text-[10px] text-slate-400 font-mono" id="rec-date">01 Jan 2026</div>
                </div>
            </div>

            <!-- Customer & Booking Info Grid -->
            <div class="grid grid-cols-2 gap-4 bg-white p-3.5 border border-[#e5e4dc]">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Billed To (Traveler)</span>
                    <div class="font-bold text-slate-900" id="rec-customer-name">Arjun Mehta</div>
                    <div class="text-[11px] text-slate-500 font-mono" id="rec-customer-email">arjun@example.com</div>
                    <div class="text-[11px] text-slate-500 font-mono" id="rec-customer-phone">+91 9876543210</div>
                </div>
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Reservation Reference</span>
                    <div class="font-bold text-sage-900 font-mono" id="rec-booking-code">GFX-XXXXXX</div>
                    <div class="text-[11px] text-slate-600 mt-0.5" id="rec-service-type">Tour Package</div>
                    <div class="text-[11px] text-slate-500 font-mono">Travel: <span id="rec-travel-date">--</span></div>
                </div>
            </div>

            <!-- Service Item Line -->
            <div>
                <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Booked Service Details</span>
                <div class="bg-white p-3 border border-[#e5e4dc] font-medium text-slate-900" id="rec-package-title">
                    Dubai Luxury Holiday Package
                </div>
            </div>

            <!-- Financial Breakdown Table -->
            <div class="bg-white border border-[#e5e4dc] overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <tbody class="divide-y divide-[#e5e4dc]">
                        <tr>
                            <td class="p-2.5 text-slate-600">Total Vacation Package Value</td>
                            <td class="p-2.5 text-right font-space font-bold text-slate-900" id="rec-total-amount">₹0</td>
                        </tr>
                        <tr class="bg-emerald-50/50">
                            <td class="p-2.5 font-bold text-emerald-900">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-[10px] mr-1"></i> Advance Token Paid (<span id="rec-gateway">Razorpay</span>)
                            </td>
                            <td class="p-2.5 text-right font-space font-bold text-emerald-800 text-sm" id="rec-advance-amount">₹0</td>
                        </tr>
                        <tr class="bg-amber-50/40">
                            <td class="p-2.5 text-amber-900 font-medium">Remaining Balance Due (Upon Arrival / Check-in)</td>
                            <td class="p-2.5 text-right font-space font-bold text-amber-800" id="rec-balance-amount">₹0</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Security & Verification Seal -->
            <div class="pt-2 border-t border-[#e5e4dc] flex items-center justify-between text-[10px] text-slate-400">
                <span class="flex items-center gap-1 font-mono">
                    <i class="fa-solid fa-lock text-emerald-600"></i> SSL 256-Bit Encrypted Transaction
                </span>
                <span class="font-mono"><?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Orion Advent'); ?> Concierge Desk</span>
            </div>

        </div>

    </div>
</div>

<script>
// Receipt Modal Logic
function openReceiptModal(data) {
    document.getElementById('rec-txn-id').textContent = data.txn_id;
    document.getElementById('rec-date').textContent = data.created_at;
    document.getElementById('rec-customer-name').textContent = data.customer_name;
    document.getElementById('rec-customer-email').textContent = data.customer_email;
    document.getElementById('rec-customer-phone').textContent = data.customer_phone;
    document.getElementById('rec-booking-code').textContent = data.booking_code;
    document.getElementById('rec-service-type').textContent = data.service_type + ' Reservation';
    document.getElementById('rec-travel-date').textContent = data.travel_date;
    document.getElementById('rec-package-title').textContent = data.package_title;
    document.getElementById('rec-total-amount').textContent = '₹' + Number(data.total_amount).toLocaleString();
    document.getElementById('rec-advance-amount').textContent = '₹' + Number(data.advance_amount).toLocaleString();
    document.getElementById('rec-balance-amount').textContent = '₹' + Number(data.balance_amount).toLocaleString();
    document.getElementById('rec-gateway').textContent = data.payment_gateway;

    document.getElementById('receiptModal').classList.remove('hidden');
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
}

function printReceiptContent() {
    const printContent = document.getElementById('printableReceiptArea').innerHTML;
    const win = window.open('', '', 'height=650,width=800');
    win.document.write('<html><head><title><?php echo htmlspecialchars($siteSettings['site_name'] ?? 'Orion Advent'); ?> Payment Receipt</title>');
    win.document.write('<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">');
    win.document.write('<script src="https://cdn.tailwindcss.com"><\/script>');
    win.document.write('</head><body class="p-8 bg-white font-[\'Plus_Jakarta_Sans\']">');
    win.document.write(printContent);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(() => {
        win.print();
        win.close();
    }, 500);
}
</script>

<?php include 'components/footer.php'; ?>
