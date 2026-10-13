<?php
/**
 * Admin Hotels & Resorts Management Hub - Orion Advent
 * Location-Wise Dynamic Filter Tabs, Modern Visual Card View Grid,
 * Status Switcher, Room Counters, and Dedicated Add/Edit Routing.
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

if (isset($_GET['saved'])) {
    $alertMessage = "Hotel information saved successfully!";
}

// 1. HANDLE QUICK ACTIONS (Delete / Toggle Status)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($pdo && $targetId > 0) {
        try {
            if ($action === 'delete') {
                $pdo->prepare("DELETE FROM `hotel_images` WHERE `hotel_id` = ?")->execute([$targetId]);
                $pdo->prepare("DELETE FROM `hotel_rooms` WHERE `hotel_id` = ?")->execute([$targetId]);
                $pdo->prepare("DELETE FROM `hotels` WHERE `id` = ?")->execute([$targetId]);
                $alertMessage = "Hotel record and its room categories deleted.";
            } elseif ($action === 'toggle_status') {
                $curr = $pdo->prepare("SELECT `status` FROM `hotels` WHERE `id` = ?");
                $curr->execute([$targetId]);
                $st = $curr->fetchColumn();
                $newSt = ($st === 'active') ? 'inactive' : 'active';
                $pdo->prepare("UPDATE `hotels` SET `status` = ? WHERE `id` = ?")->execute([$newSt, $targetId]);
                $alertMessage = "Hotel visibility changed to " . strtoupper($newSt) . ".";
            }
        } catch (Exception $e) {
            $alertMessage = "Action failed: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// 2. FETCH ALL UNIQUE DESTINATIONS / CITIES WITH HOTEL COUNTS
$locationTabs = [];
$totalHotelsCount = 0;
$activeHotelsCount = 0;

if ($pdo) {
    try {
        $totalHotelsCount = (int)$pdo->query("SELECT COUNT(*) FROM `hotels`")->fetchColumn();
        $activeHotelsCount = (int)$pdo->query("SELECT COUNT(*) FROM `hotels` WHERE `status` = 'active'")->fetchColumn();

        // Distinct location counts
        $locStmt = $pdo->query("SELECT `city`, COUNT(*) as count FROM `hotels` WHERE `city` IS NOT NULL AND `city` != '' GROUP BY `city` ORDER BY count DESC, `city` ASC");
        $locationTabs = $locStmt->fetchAll();
    } catch (Exception $e) {
        $locationTabs = [];
    }
}

// 3. FILTER HOTELS BY SEARCH AND CITY TAB
$activeCity = isset($_GET['city']) ? trim($_GET['city']) : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$hotels = [];

if ($pdo) {
    try {
        $sql = "SELECT h.*, (SELECT COUNT(*) FROM `hotel_rooms` hr WHERE hr.hotel_id = h.id) as room_count FROM `hotels` h WHERE 1=1";
        $params = [];

        if (!empty($activeCity)) {
            $sql .= " AND LOWER(h.city) = LOWER(?)";
            $params[] = $activeCity;
        }

        if (!empty($searchQuery)) {
            $sql .= " AND (h.name LIKE ? OR h.city LIKE ? OR h.property_type LIKE ? OR h.address LIKE ?)";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }

        $sql .= " ORDER BY h.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $hotels = $stmt->fetchAll();
    } catch (Exception $e) {
        $hotels = [];
    }
}

$pageTitle = "Hotels & Resorts Management";
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

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full space-y-5">

            <!-- 1. Header Banner with Action Buttons -->
            <div class="bg-white border border-[#e5e4dc] p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 bg-sage-700"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5">Stay Catalog</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        Hotels &amp; Luxury Resorts
                    </h1>
                    <p class="text-xs text-slate-500">
                        Manage properties, location tags, live room categories, amenities, and starting rates.
                    </p>
                </div>

                <!-- Create Hotel Link (Direct full page, no popup) -->
                <a href="hotel-edit.php" class="px-4 py-2 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center justify-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Add New Hotel</span>
                </a>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 text-xs font-semibold flex items-center gap-2.5 <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                    <span><?php echo htmlspecialchars($alertMessage); ?></span>
                </div>
            <?php endif; ?>

            <!-- 2. Dynamic Location-Wise Tabs & Search Filter -->
            <div class="bg-white border border-[#e5e4dc] p-3 space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    
                    <!-- Dynamic City Tabs Track -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <!-- All Locations Tab -->
                        <a href="hotels.php<?php echo !empty($searchQuery) ? '?q=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-1.5 text-xs font-bold border transition-colors shrink-0 flex items-center gap-1.5 <?php echo empty($activeCity) ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 hover:bg-cream-100 text-slate-700 border-[#e5e4dc]'; ?>">
                            <i class="fa-solid fa-globe text-[10px]"></i>
                            <span>All Stays</span>
                            <span class="text-[9px] px-1.5 py-0.2 bg-black/15 font-mono"><?php echo $totalHotelsCount; ?></span>
                        </a>

                        <!-- Dynamic Database City Tabs -->
                        <?php foreach ($locationTabs as $tab): ?>
                            <?php $isTabActive = (strtolower($activeCity) === strtolower($tab['city'])); ?>
                            <a href="hotels.php?city=<?php echo urlencode($tab['city']); ?><?php echo !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-1.5 text-xs font-semibold border transition-colors shrink-0 flex items-center gap-1.5 <?php echo $isTabActive ? 'bg-sage-700 text-white border-sage-800 font-bold' : 'bg-cream-50 hover:bg-cream-100 text-slate-700 border-[#e5e4dc]'; ?>">
                                <i class="fa-solid fa-location-dot text-[10px] <?php echo $isTabActive ? 'text-white' : 'text-sage-700'; ?>"></i>
                                <span><?php echo htmlspecialchars($tab['city']); ?></span>
                                <span class="text-[9px] px-1.5 py-0.2 border <?php echo $isTabActive ? 'bg-white/20 border-white/30 text-white' : 'bg-white border-[#e5e4dc] text-slate-600'; ?> font-mono">
                                    <?php echo $tab['count']; ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Search Input -->
                    <form method="GET" action="hotels.php" class="flex items-center gap-2 shrink-0">
                        <?php if (!empty($activeCity)): ?>
                            <input type="hidden" name="city" value="<?php echo htmlspecialchars($activeCity); ?>">
                        <?php endif; ?>
                        <div class="relative w-full sm:w-64">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search hotel name, location..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none">
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3. Dynamic Hotel Cards Grid View -->
            <?php if (!empty($hotels)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <?php foreach ($hotels as $h): ?>
                        <?php
                        $isActive = ($h['status'] === 'active');
                        $amenitiesList = [];
                        if (!empty($h['amenities'])) {
                            $dec = json_decode($h['amenities'], true);
                            if (is_array($dec)) {
                                foreach ($dec as $am) {
                                    $amenitiesList[] = is_string($am) ? $am : ($am['name'] ?? '');
                                    if (count($amenitiesList) >= 3) break;
                                }
                            }
                        }
                        $img = getAdminImageUrl($h['featured_image'] ?? '');
                        ?>
                        <div class="bg-white border border-[#e5e4dc] hover:border-slate-400 transition-all flex flex-col justify-between group">
                            <div>
                                <!-- Card Image & Top Floating Badges -->
                                <div class="relative h-48 bg-slate-100 overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($h['name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>

                                    <!-- Top Left: Location City & Property Badge -->
                                    <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white/95 text-slate-800 border border-white/60">
                                            <i class="fa-solid fa-location-dot text-sage-700 mr-0.5"></i> <?php echo htmlspecialchars($h['city']); ?>
                                        </span>
                                        <?php if (!empty($h['badge'])): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-sage-800/90 text-white border border-sage-600/50">
                                                <?php echo htmlspecialchars($h['badge']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Top Right: Active / Inactive Status Toggle -->
                                    <div class="absolute top-2.5 right-2.5">
                                        <a href="hotels.php?action=toggle_status&id=<?php echo $h['id']; ?><?php echo !empty($activeCity) ? '&city=' . urlencode($activeCity) : ''; ?>" title="Click to toggle visibility" class="px-2 py-0.5 text-[10px] font-bold border transition-colors <?php echo $isActive ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-slate-800/90 text-slate-300 border-slate-700'; ?>">
                                            <?php echo $isActive ? 'Active' : 'Draft'; ?>
                                        </a>
                                    </div>

                                    <!-- Bottom Overlay Inside Photo: Price & Star Rating -->
                                    <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-end justify-between text-white">
                                        <div>
                                            <div class="text-[11px] text-amber-300 font-bold">
                                                <?php echo str_repeat('★', (int)($h['star_rating'] ?: 5)); ?>
                                            </div>
                                            <div class="text-[11px] text-slate-200 truncate max-w-[180px]"><?php echo htmlspecialchars($h['property_type']); ?></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[10px] text-slate-300 uppercase tracking-wider">Starts From</div>
                                            <div class="text-base font-bold font-space text-white">₹<?php echo number_format($h['starting_price']); ?><span class="text-[10px] font-normal text-slate-300">/nt</span></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Content Body -->
                                <div class="p-4 space-y-3">
                                    <div>
                                        <h2 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-sage-800 transition-colors line-clamp-1">
                                            <?php echo htmlspecialchars($h['name']); ?>
                                        </h2>
                                        <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                            <i class="fa-solid fa-map-pin text-[9px] mr-1 text-slate-400"></i>
                                            <?php echo htmlspecialchars($h['address'] ?: ($h['city'] . ', ' . $h['country'])); ?>
                                        </p>
                                    </div>

                                    <!-- Key Amenities Pill Strip -->
                                    <?php if (!empty($amenitiesList)): ?>
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            <?php foreach ($amenitiesList as $am): ?>
                                                <span class="text-[10px] font-medium bg-cream-100 text-slate-600 px-2 py-0.5 border border-[#e5e4dc]">
                                                    <?php echo htmlspecialchars($am); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Rooms & Timings Strip -->
                                    <div class="pt-2 border-t border-[#f0eee6] flex items-center justify-between text-[11px] text-slate-500">
                                        <span>
                                            <i class="fa-solid fa-bed text-sage-700 mr-1"></i>
                                            <strong><?php echo (int)($h['room_count'] ?? 1); ?></strong> Room Categories
                                        </span>
                                        <span>
                                            <i class="fa-regular fa-clock text-slate-400 mr-1"></i>
                                            In: <?php echo htmlspecialchars($h['checkin_time'] ?: '02 PM'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Bottom Action Bar -->
                            <div class="p-3 bg-cream-50/70 border-t border-[#e5e4dc] flex items-center justify-between gap-2">
                                <a href="../hotel-details.php?id=<?php echo $h['id']; ?>" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold bg-white hover:bg-cream-100 text-slate-600 border border-[#e5e4dc] transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    <span>Preview</span>
                                </a>

                                <div class="flex items-center gap-1.5">
                                    <a href="hotel-edit.php?id=<?php echo $h['id']; ?>" class="px-3 py-1 text-[11px] font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                        <span>Edit Hotel</span>
                                    </a>
                                    <a href="hotels.php?action=delete&id=<?php echo $h['id']; ?><?php echo !empty($activeCity) ? '&city=' . urlencode($activeCity) : ''; ?>" onclick="return confirm('Are you sure you want to delete this hotel?');" title="Delete Hotel" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white border border-[#e5e4dc] p-12 text-center space-y-3">
                    <div class="w-12 h-12 bg-cream-100 text-slate-400 mx-auto flex items-center justify-center border border-[#e5e4dc] text-lg">
                        <i class="fa-solid fa-hotel"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-800">No Hotels Found</h3>
                        <p class="text-xs text-slate-400">There are no hotel properties matching this location or search filter.</p>
                    </div>
                    <div class="pt-2">
                        <a href="hotel-edit.php" class="inline-flex items-center gap-1.5 px-4 py-2 bg-sage-700 hover:bg-sage-800 text-white text-xs font-bold border border-sage-800 transition-colors">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Add First Hotel in this Location</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include 'components/footer.php'; ?>
