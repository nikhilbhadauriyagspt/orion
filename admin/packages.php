<?php
/**
 * Admin Tour Packages Management Controller & View
 * Clean Sage & Warm Cream minimal aesthetic (zero shadow, flat border-first).
 * Dynamic destination tabs, stats summary, clean tour package card grid, and direct actions.
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();
$alertMessage = '';
$alertType = 'success';

if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $alertMessage = "Tour package saved successfully!";
    $alertType = 'success';
}

// 1. HANDLE DELETE ACTION
if (isset($_GET['delete_id']) && (int)$_GET['delete_id'] > 0) {
    $delId = (int)$_GET['delete_id'];
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `packages` WHERE `id` = ?");
            $stmt->execute([$delId]);
            $alertMessage = "Package #{$delId} removed successfully.";
            $alertType = 'success';
        } catch (Exception $e) {
            $alertMessage = "Error deleting package: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// 2. FETCH STATS & ALL PACKAGES
$packages = [];
$totalPackages = 0;
$activePackages = 0;
$domesticCount = 0;
$internationalCount = 0;
$locationCounts = [];

if ($pdo) {
    try {
        $totalPackages = (int)$pdo->query("SELECT COUNT(*) FROM `packages`")->fetchColumn();
        $activePackages = (int)$pdo->query("SELECT COUNT(*) FROM `packages` WHERE `status` = 'active'")->fetchColumn();
        $domesticCount = (int)$pdo->query("SELECT COUNT(*) FROM `packages` WHERE `category` = 'domestic'")->fetchColumn();
        $internationalCount = (int)$pdo->query("SELECT COUNT(*) FROM `packages` WHERE `category` = 'international'")->fetchColumn();

        // Location Counts for Dynamic Tabs
        $locStmt = $pdo->query("SELECT `location`, COUNT(*) as cnt FROM `packages` GROUP BY `location` ORDER BY cnt DESC");
        while ($row = $locStmt->fetch()) {
            $mainLoc = trim(explode('•', $row['location'])[0]);
            $mainLoc = trim(explode(',', $mainLoc)[0]);
            if (!empty($mainLoc)) {
                $locationCounts[$mainLoc] = ($locationCounts[$mainLoc] ?? 0) + (int)$row['cnt'];
            }
        }

        // Active Tab & Filters
        $currentLocTab = trim($_GET['loc'] ?? 'all');
        $search = trim($_GET['q'] ?? '');
        $filterCat = trim($_GET['cat'] ?? '');
        $filterMode = trim($_GET['mode'] ?? '');

        $query = "SELECT * FROM `packages` WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (title LIKE ? OR location LIKE ? OR circuit_type LIKE ? OR departure_city LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($currentLocTab !== 'all' && !empty($currentLocTab)) {
            $query .= " AND (location LIKE ?)";
            $params[] = "%$currentLocTab%";
        }

        if (!empty($filterCat)) {
            $query .= " AND category = ?";
            $params[] = $filterCat;
        }

        if (!empty($filterMode)) {
            $query .= " AND travel_mode = ?";
            $params[] = $filterMode;
        }

        $query .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $packages = $stmt->fetchAll();

    } catch (Exception $e) {
        $packages = [];
    }
}

$pageTitle = "Tour Packages Management";
include 'components/head.php';
?>

<!-- App Wrapper -->
<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        <!-- Header -->
        <?php include 'components/header.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full max-w-7xl space-y-6">

            <!-- Page Header / Top Action Strip -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-[#e5e4dc] p-4 sm:p-5">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                        <span>Admin</span>
                        <span>/</span>
                        <span class="text-sage-700 font-semibold">Tours &amp; Holiday Circuits</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">Tour Packages Management</h1>
                    <p class="text-xs text-slate-500">Manage holiday itineraries, travel modes (Flight/Train/Bus/Cab), token advance bookings, and hotel stays.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="package-edit.php" class="px-4 py-2 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>Add New Tour Package</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 text-xs font-semibold flex items-center justify-between <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                        <span><?php echo htmlspecialchars($alertMessage); ?></span>
                    </div>
                    <a href="packages.php" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></a>
                </div>
            <?php endif; ?>

            <!-- Metric Cards (Flat Border-First Grid) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Total Packages</span>
                        <i class="fa-solid fa-map-location-dot text-sage-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?php echo number_format($totalPackages); ?></div>
                    <div class="text-[10px] text-slate-400">All holiday circuits</div>
                </div>

                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Active Tours</span>
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?php echo number_format($activePackages); ?></div>
                    <div class="text-[10px] text-slate-400">Published online</div>
                </div>

                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Domestic Circuits</span>
                        <i class="fa-solid fa-mountain text-sage-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?php echo number_format($domesticCount); ?></div>
                    <div class="text-[10px] text-slate-400">India destinations</div>
                </div>

                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>International</span>
                        <i class="fa-solid fa-plane-departure text-sky-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?php echo number_format($internationalCount); ?></div>
                    <div class="text-[10px] text-slate-400">Global holiday tours</div>
                </div>
            </div>

            <!-- Dynamic Location & Filter Bar -->
            <div class="space-y-3">
                <!-- Location Tabs Strip -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 bg-white border border-[#e5e4dc] p-1.5">
                    <a href="packages.php" class="px-3 py-1.5 text-xs font-bold shrink-0 transition-colors <?php echo ($currentLocTab === 'all') ? 'bg-sage-700 text-white' : 'text-slate-600 hover:bg-cream-100'; ?>">
                        All Packages (<?php echo $totalPackages; ?>)
                    </a>
                    <?php foreach ($locationCounts as $locName => $cnt): ?>
                        <a href="packages.php?loc=<?php echo urlencode($locName); ?>" class="px-3 py-1.5 text-xs font-semibold shrink-0 transition-colors flex items-center gap-1.5 <?php echo (strtolower($currentLocTab) === strtolower($locName)) ? 'bg-sage-700 text-white font-bold' : 'text-slate-600 hover:bg-cream-100'; ?>">
                            <span><?php echo htmlspecialchars($locName); ?></span>
                            <span class="text-[10px] <?php echo (strtolower($currentLocTab) === strtolower($locName)) ? 'text-cream-200' : 'text-slate-400'; ?>">[<?php echo $cnt; ?>]</span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Search, Category & Travel Mode Filters -->
                <div class="bg-white border border-[#e5e4dc] p-3 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <form method="GET" action="packages.php" class="flex-1 w-full sm:w-auto flex flex-wrap items-center gap-2">
                        <?php if ($currentLocTab !== 'all'): ?>
                            <input type="hidden" name="loc" value="<?php echo htmlspecialchars($currentLocTab); ?>">
                        <?php endif; ?>
                        <div class="relative flex-1 min-w-[160px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search title, circuit, or city..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-cream-50 border border-[#e5e4dc] focus:border-sage-700 outline-none">
                        </div>
                        <select name="cat" onchange="this.form.submit()" class="text-xs py-1.5 px-2.5 bg-cream-50 border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                            <option value="">All Categories</option>
                            <option value="domestic" <?php echo ($filterCat === 'domestic') ? 'selected' : ''; ?>>Domestic</option>
                            <option value="international" <?php echo ($filterCat === 'international') ? 'selected' : ''; ?>>International</option>
                            <option value="adventure" <?php echo ($filterCat === 'adventure') ? 'selected' : ''; ?>>Adventure</option>
                            <option value="luxury" <?php echo ($filterCat === 'luxury') ? 'selected' : ''; ?>>Luxury</option>
                        </select>
                        <select name="mode" onchange="this.form.submit()" class="text-xs py-1.5 px-2.5 bg-cream-50 border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                            <option value="">All Travel Modes</option>
                            <option value="flight" <?php echo ($filterMode === 'flight') ? 'selected' : ''; ?>>✈️ By Flight</option>
                            <option value="train" <?php echo ($filterMode === 'train') ? 'selected' : ''; ?>>🚆 By Train</option>
                            <option value="bus" <?php echo ($filterMode === 'bus') ? 'selected' : ''; ?>>🚌 By Volvo Bus</option>
                            <option value="cab" <?php echo ($filterMode === 'cab') ? 'selected' : ''; ?>>🚗 By Private Cab</option>
                            <option value="land_only" <?php echo ($filterMode === 'land_only') ? 'selected' : ''; ?>>🏨 Land Only</option>
                        </select>
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800">
                            Search
                        </button>
                    </form>

                    <div class="text-[11px] text-slate-500 font-medium shrink-0">
                        Showing <strong><?php echo count($packages); ?></strong> packages
                    </div>
                </div>
            </div>

            <!-- Tour Packages Card Grid -->
            <?php if (!empty($packages)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($packages as $pkg): ?>
                        <?php
                            $coverImg = getAdminImageUrl($pkg['featured_image'] ?? '', 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=600&q=80');
                            $itDays = json_decode($pkg['itinerary'] ?? '[]', true) ?: [];
                            $itCount = count($itDays);
                            $htlIds = json_decode($pkg['hotel_ids'] ?? '[]', true) ?: [];
                            $cStays = json_decode($pkg['custom_stays'] ?? '[]', true) ?: [];
                            $htlCount = count($htlIds) + count($cStays);

                            $tMode = $pkg['travel_mode'] ?? 'flight';
                            $tIcon = 'fa-solid fa-plane-departure text-sky-400';
                            $tLabel = 'With Flight';
                            if ($tMode === 'train') { $tIcon = 'fa-solid fa-train text-amber-400'; $tLabel = 'By Train'; }
                            elseif ($tMode === 'bus') { $tIcon = 'fa-solid fa-bus text-emerald-400'; $tLabel = 'By Volvo'; }
                            elseif ($tMode === 'cab') { $tIcon = 'fa-solid fa-car text-sage-300'; $tLabel = 'By Cab'; }
                            elseif ($tMode === 'land_only') { $tIcon = 'fa-solid fa-hotel text-indigo-300'; $tLabel = 'Land Only'; }
                        ?>
                        <div class="bg-white border border-[#e5e4dc] flex flex-col justify-between group hover:border-sage-600 transition-colors">
                            <div>
                                <!-- Image & Badges Banner -->
                                <div class="relative h-44 bg-cream-100 overflow-hidden border-b border-[#e5e4dc]">
                                    <img src="<?php echo htmlspecialchars($coverImg); ?>" alt="<?php echo htmlspecialchars($pkg['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    
                                    <!-- Top Left Badge -->
                                    <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
                                        <span class="text-[10px] font-bold px-2 py-0.5 bg-sage-900/90 text-white border border-white/20">
                                            <?php echo htmlspecialchars($pkg['badge'] ?: 'Bestseller'); ?>
                                        </span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 bg-white/95 text-slate-900 border border-[#e5e4dc]">
                                            <?php echo htmlspecialchars($pkg['duration'] ?: ($pkg['duration_nights'] . 'N / ' . $pkg['duration_days'] . 'D')); ?>
                                        </span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-900/90 text-white border border-white/20 flex items-center gap-1">
                                            <i class="<?php echo $tIcon; ?> text-[9px]"></i>
                                            <span><?php echo $tLabel; ?></span>
                                        </span>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="absolute top-2.5 right-2.5">
                                        <?php if ($pkg['status'] === 'active'): ?>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-emerald-600 text-white">Active</span>
                                        <?php else: ?>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-slate-600 text-white">Draft</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Bottom Location Banner -->
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-950/85 via-slate-950/50 to-transparent p-2.5 pt-6 text-white text-[11px] font-medium flex items-center justify-between">
                                        <div class="truncate flex items-center gap-1.5">
                                            <i class="fa-solid fa-location-dot text-sage-400 text-[10px]"></i>
                                            <span class="truncate"><?php echo htmlspecialchars($pkg['location']); ?></span>
                                        </div>
                                        <span class="text-[10px] shrink-0 font-bold bg-white/20 px-1.5 py-0.5">
                                            ★ <?php echo number_format((float)$pkg['rating'], 1); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Content Body -->
                                <div class="p-4 space-y-3">
                                    <div>
                                        <div class="text-[10px] font-bold text-sage-700 uppercase tracking-wider mb-0.5 flex items-center justify-between">
                                            <span><?php echo htmlspecialchars($pkg['circuit_type'] ?: ucfirst($pkg['category'])); ?></span>
                                            <?php if (!empty($pkg['departure_city'])): ?>
                                                <span class="text-[9px] text-slate-400 font-normal"><?php echo htmlspecialchars($pkg['departure_city']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-900 leading-snug line-clamp-2" title="<?php echo htmlspecialchars($pkg['title']); ?>">
                                            <?php echo htmlspecialchars($pkg['title']); ?>
                                        </h3>
                                    </div>

                                    <!-- Quick Specs Matrix -->
                                    <div class="grid grid-cols-2 gap-2 text-[11px] py-2 border-y border-[#e5e4dc] text-slate-600 bg-cream-50/40 px-2">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-calendar-days text-sage-700 text-[10px]"></i>
                                            <span><?php echo $itCount > 0 ? "{$itCount} Days Plan" : "Custom Days"; ?></span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-hotel text-sage-700 text-[10px]"></i>
                                            <span><?php echo $htlCount > 0 ? "{$htlCount} Stays Linked" : "Verified Hotels"; ?></span>
                                        </div>
                                    </div>

                                    <!-- Pricing Strip -->
                                    <div class="flex items-end justify-between pt-1">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block line-through">₹<?php echo number_format((float)$pkg['original_price']); ?></span>
                                            <div class="text-base font-extrabold font-space text-slate-900">
                                                ₹<?php echo number_format((float)$pkg['price']); ?>
                                                <span class="text-[10px] font-normal text-slate-500">/ person</span>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-[9px] text-slate-500 block">Token Advance</span>
                                            <span class="text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5">
                                                ₹<?php echo number_format((float)$pkg['token_advance']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Action Buttons -->
                            <div class="p-3 bg-cream-50/70 border-t border-[#e5e4dc] flex items-center justify-between gap-2">
                                <a href="../package-details.php?id=PKG-DB-<?php echo $pkg['id']; ?>" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] flex items-center gap-1 transition-colors" title="Preview on Website">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-sage-700"></i>
                                    <span>Preview</span>
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <a href="package-edit.php?id=<?php echo $pkg['id']; ?>" class="px-3 py-1 text-[11px] font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 flex items-center gap-1 transition-colors">
                                        <i class="fa-solid fa-pen-to-square text-[9px]"></i>
                                        <span>Edit Tour</span>
                                    </a>
                                    <a href="packages.php?delete_id=<?php echo $pkg['id']; ?>" onclick="return confirm('Are you sure you want to delete this tour package?');" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Delete Package">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white border border-[#e5e4dc] p-12 text-center space-y-3">
                    <div class="w-12 h-12 bg-cream-100 border border-[#e5e4dc] flex items-center justify-center mx-auto text-slate-400">
                        <i class="fa-solid fa-map-location-dot text-xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No Tour Packages Found</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">No packages match the selected destination tab or search keyword. Create your first tour package now.</p>
                    <a href="package-edit.php" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Add New Package</span>
                    </a>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include 'components/footer.php'; ?>
