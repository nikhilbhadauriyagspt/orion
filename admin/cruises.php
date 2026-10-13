<?php
/**
 * Admin Ocean Cruises Management Hub - Orion Advent
 * Dynamic Departure Port Tabs, Visual Card Grid, Status Switcher, and Edit Routing.
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
    $alertMessage = "Cruise information saved successfully!";
}

// 1. Handle Quick Actions (Delete / Toggle Status)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($pdo && $targetId > 0) {
        try {
            if ($action === 'delete') {
                $pdo->prepare("DELETE FROM `cruises` WHERE `id` = ?")->execute([$targetId]);
                $alertMessage = "Cruise record deleted successfully.";
            } elseif ($action === 'toggle_status') {
                $curr = $pdo->prepare("SELECT `status` FROM `cruises` WHERE `id` = ?");
                $curr->execute([$targetId]);
                $st = $curr->fetchColumn();
                $newSt = ($st === 'active') ? 'draft' : 'active';
                $pdo->prepare("UPDATE `cruises` SET `status` = ? WHERE `id` = ?")->execute([$newSt, $targetId]);
                $alertMessage = "Cruise status changed to " . strtoupper($newSt) . ".";
            }
        } catch (Exception $e) {
            $alertMessage = "Action failed: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// 2. Fetch Departure Ports & Counts
$portTabs = [];
$totalCruisesCount = 0;
$activeCruisesCount = 0;
$domesticCount = 0;
$internationalCount = 0;

if ($pdo) {
    try {
        $totalCruisesCount = (int)$pdo->query("SELECT COUNT(*) FROM `cruises`")->fetchColumn();
        $activeCruisesCount = (int)$pdo->query("SELECT COUNT(*) FROM `cruises` WHERE `status` = 'active'")->fetchColumn();
        $domesticCount = (int)$pdo->query("SELECT COUNT(*) FROM `cruises` WHERE `category` = 'domestic'")->fetchColumn();
        $internationalCount = (int)$pdo->query("SELECT COUNT(*) FROM `cruises` WHERE `category` = 'international'")->fetchColumn();

        $portStmt = $pdo->query("SELECT `departure_port`, COUNT(*) as count FROM `cruises` WHERE `departure_port` IS NOT NULL AND `departure_port` != '' GROUP BY `departure_port` ORDER BY count DESC, `departure_port` ASC");
        $portTabs = $portStmt->fetchAll();
    } catch (Exception $e) {
        $portTabs = [];
    }
}

// 3. Filter Cruises by Port, Category & Search
$activePort = isset($_GET['port']) ? trim($_GET['port']) : '';
$activeCat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$cruises = [];

if ($pdo) {
    try {
        $sql = "SELECT * FROM `cruises` WHERE 1=1";
        $params = [];

        if (!empty($activePort)) {
            $sql .= " AND LOWER(departure_port) = LOWER(?)";
            $params[] = $activePort;
        }

        if (!empty($activeCat)) {
            $sql .= " AND `category` = ?";
            $params[] = $activeCat;
        }

        if (!empty($searchQuery)) {
            $sql .= " AND (`title` LIKE ? OR `ship_name` LIKE ? OR `cruise_line` LIKE ? OR `departure_port` LIKE ? OR `destination_ports` LIKE ?)";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $cruises = $stmt->fetchAll();
    } catch (Exception $e) {
        $cruises = [];
    }
}

$pageTitle = "Ocean Cruises Management";
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
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5">Maritime Catalog</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        Ocean Cruises &amp; Liner Voyages
                    </h1>
                    <p class="text-xs text-slate-500">
                        Manage luxury ocean liners, ports of call, day-by-day sailing itineraries, cabin categories, and fares.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="cruise-edit.php" class="px-4 py-2 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center justify-center gap-2 shrink-0">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Add New Cruise</span>
                    </a>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white border border-[#e5e4dc] p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Total Cruises</div>
                        <div class="text-lg font-bold font-space text-slate-900"><?php echo $totalCruisesCount; ?></div>
                    </div>
                    <div class="w-8 h-8 bg-sage-50 text-sage-700 border border-sage-200 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-ship"></i>
                    </div>
                </div>

                <div class="bg-white border border-[#e5e4dc] p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Active Live</div>
                        <div class="text-lg font-bold font-space text-emerald-700"><?php echo $activeCruisesCount; ?></div>
                    </div>
                    <div class="w-8 h-8 bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <div class="bg-white border border-[#e5e4dc] p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Domestic Sailings</div>
                        <div class="text-lg font-bold font-space text-sky-700"><?php echo $domesticCount; ?></div>
                    </div>
                    <div class="w-8 h-8 bg-sky-50 text-sky-700 border border-sky-200 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-anchor"></i>
                    </div>
                </div>

                <div class="bg-white border border-[#e5e4dc] p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">International</div>
                        <div class="text-lg font-bold font-space text-indigo-700"><?php echo $internationalCount; ?></div>
                    </div>
                    <div class="w-8 h-8 bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-globe"></i>
                    </div>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 text-xs font-semibold flex items-center gap-2.5 <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                    <span><?php echo htmlspecialchars($alertMessage); ?></span>
                </div>
            <?php endif; ?>

            <!-- 2. Dynamic Departure Port Tabs & Search Filter -->
            <div class="bg-white border border-[#e5e4dc] p-3 space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    
                    <!-- Dynamic Port Tabs Track -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <!-- All Ports Tab -->
                        <a href="cruises.php<?php echo !empty($searchQuery) ? '?q=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-1.5 text-xs font-bold border transition-colors shrink-0 flex items-center gap-1.5 <?php echo empty($activePort) && empty($activeCat) ? 'bg-sage-700 text-white border-sage-800' : 'bg-cream-50 hover:bg-cream-100 text-slate-700 border-[#e5e4dc]'; ?>">
                            <i class="fa-solid fa-compass text-[10px]"></i>
                            <span>All Cruises</span>
                            <span class="text-[9px] px-1.5 py-0.2 bg-black/15 font-mono"><?php echo $totalCruisesCount; ?></span>
                        </a>

                        <!-- Category Quick Tabs -->
                        <a href="cruises.php?cat=domestic<?php echo !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-1.5 text-xs font-semibold border transition-colors shrink-0 flex items-center gap-1.5 <?php echo ($activeCat === 'domestic') ? 'bg-sage-700 text-white border-sage-800 font-bold' : 'bg-cream-50 hover:bg-cream-100 text-slate-700 border-[#e5e4dc]'; ?>">
                            <i class="fa-solid fa-flag text-[10px]"></i>
                            <span>Domestic</span>
                            <span class="text-[9px] px-1.5 py-0.2 border bg-white border-[#e5e4dc] text-slate-600 font-mono"><?php echo $domesticCount; ?></span>
                        </a>

                        <a href="cruises.php?cat=international<?php echo !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-1.5 text-xs font-semibold border transition-colors shrink-0 flex items-center gap-1.5 <?php echo ($activeCat === 'international') ? 'bg-sage-700 text-white border-sage-800 font-bold' : 'bg-cream-50 hover:bg-cream-100 text-slate-700 border-[#e5e4dc]'; ?>">
                            <i class="fa-solid fa-passport text-[10px]"></i>
                            <span>International</span>
                            <span class="text-[9px] px-1.5 py-0.2 border bg-white border-[#e5e4dc] text-slate-600 font-mono"><?php echo $internationalCount; ?></span>
                        </a>

                        <!-- Dynamic Database Port Tabs -->
                        <?php foreach ($portTabs as $pt): ?>
                            <?php $isPortActive = (strtolower($activePort) === strtolower($pt['departure_port'])); ?>
                            <a href="cruises.php?port=<?php echo urlencode($pt['departure_port']); ?><?php echo !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : ''; ?>" class="px-3 py-1.5 text-xs font-semibold border transition-colors shrink-0 flex items-center gap-1.5 <?php echo $isPortActive ? 'bg-sage-700 text-white border-sage-800 font-bold' : 'bg-cream-50 hover:bg-cream-100 text-slate-700 border-[#e5e4dc]'; ?>">
                                <i class="fa-solid fa-anchor text-[10px] <?php echo $isPortActive ? 'text-white' : 'text-sage-700'; ?>"></i>
                                <span><?php echo htmlspecialchars($pt['departure_port']); ?></span>
                                <span class="text-[9px] px-1.5 py-0.2 border <?php echo $isPortActive ? 'bg-white/20 border-white/30 text-white' : 'bg-white border-[#e5e4dc] text-slate-600'; ?> font-mono">
                                    <?php echo $pt['count']; ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Search Input -->
                    <form method="GET" action="cruises.php" class="flex items-center gap-2 shrink-0">
                        <?php if (!empty($activePort)): ?>
                            <input type="hidden" name="port" value="<?php echo htmlspecialchars($activePort); ?>">
                        <?php endif; ?>
                        <?php if (!empty($activeCat)): ?>
                            <input type="hidden" name="cat" value="<?php echo htmlspecialchars($activeCat); ?>">
                        <?php endif; ?>
                        <div class="relative w-full sm:w-64">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search cruise, ship, port..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none">
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3. Dynamic Cruise Cards Grid View -->
            <?php if (!empty($cruises)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <?php foreach ($cruises as $c): ?>
                        <?php
                        $isActive = ($c['status'] === 'active');
                        $cabinsList = json_decode($c['cabins'] ?? '[]', true) ?: [];
                        $cabinsCount = count($cabinsList);
                        $itineraryList = json_decode($c['itinerary'] ?? '[]', true) ?: [];
                        $daysCount = count($itineraryList);
                        $img = getAdminImageUrl($c['featured_image'] ?? '', 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=600&q=80');
                        ?>
                        <div class="bg-white border border-[#e5e4dc] hover:border-slate-400 transition-all flex flex-col justify-between group">
                            <div>
                                <!-- Card Image & Top Floating Badges -->
                                <div class="relative h-48 bg-slate-100 overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($c['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                                    <!-- Top Left: Port & Ship Badge -->
                                    <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-white/95 text-slate-800 border border-white/60 flex items-center gap-1">
                                            <i class="fa-solid fa-anchor text-sage-700 text-[9px]"></i>
                                            <span>From <?php echo htmlspecialchars($c['departure_port']); ?></span>
                                        </span>
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-sage-800/90 text-white border border-sage-600/50">
                                            <?php echo htmlspecialchars($c['duration_text'] ?: ($c['duration_nights'] . 'N / ' . $c['duration_days'] . 'D')); ?>
                                        </span>
                                        <?php if (!empty($c['badge'])): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-600 text-white">
                                                <?php echo htmlspecialchars($c['badge']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Top Right: Active / Draft Status Toggle -->
                                    <div class="absolute top-2.5 right-2.5">
                                        <a href="cruises.php?action=toggle_status&id=<?php echo $c['id']; ?><?php echo !empty($activePort) ? '&port=' . urlencode($activePort) : ''; ?>" title="Click to toggle visibility" class="px-2 py-0.5 text-[10px] font-bold border transition-colors <?php echo $isActive ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-slate-800/90 text-slate-300 border-slate-700'; ?>">
                                            <?php echo $isActive ? 'Active' : 'Draft'; ?>
                                        </a>
                                    </div>

                                    <!-- Bottom Overlay Inside Photo: Cruise Line, Ship & Price -->
                                    <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-end justify-between text-white">
                                        <div>
                                            <div class="text-[11px] text-amber-300 font-bold flex items-center gap-1">
                                                <i class="fa-solid fa-star text-[10px]"></i>
                                                <span><?php echo $c['rating']; ?></span>
                                                <span class="text-[9px] text-slate-300 font-normal">(<?php echo $c['reviews_count']; ?> reviews)</span>
                                            </div>
                                            <div class="text-[11px] text-slate-200 truncate max-w-[200px]">
                                                <i class="fa-solid fa-ship text-[10px] mr-1 text-sage-400"></i>
                                                <?php echo htmlspecialchars($c['cruise_line'] . ' • ' . $c['ship_name']); ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[10px] text-slate-300 uppercase tracking-wider">Starts From</div>
                                            <div class="text-base font-bold font-space text-white">₹<?php echo number_format($c['starting_price']); ?><span class="text-[10px] font-normal text-slate-300">/pp</span></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Content Body -->
                                <div class="p-4 space-y-3">
                                    <div>
                                        <h2 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-sage-800 transition-colors line-clamp-2">
                                            <?php echo htmlspecialchars($c['title']); ?>
                                        </h2>
                                        <p class="text-[11px] text-slate-500 truncate mt-1">
                                            <i class="fa-solid fa-route text-[10px] mr-1 text-slate-400"></i>
                                            <span>Ports: <?php echo htmlspecialchars($c['destination_ports']); ?></span>
                                        </p>
                                    </div>

                                    <!-- Quick Tags Strip -->
                                    <div class="flex flex-wrap gap-1.5 pt-1 border-t border-[#f0eee6]">
                                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-cream-50 text-slate-700 border border-[#e5e4dc]">
                                            <i class="fa-solid fa-bed text-[9px] text-sage-700 mr-1"></i>
                                            <?php echo $cabinsCount; ?> Cabin Types
                                        </span>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-cream-50 text-slate-700 border border-[#e5e4dc]">
                                            <i class="fa-regular fa-calendar-days text-[9px] text-sage-700 mr-1"></i>
                                            <?php echo $daysCount; ?> Days Itinerary
                                        </span>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-cream-50 text-slate-700 border border-[#e5e4dc]">
                                            <i class="fa-solid fa-shield-halved text-[9px] text-sage-700 mr-1"></i>
                                            ₹<?php echo number_format($c['token_advance']); ?> Advance
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Action Bar -->
                            <div class="p-3 bg-cream-50/70 border-t border-[#e5e4dc] flex items-center justify-between text-xs font-bold">
                                <a href="../cruise-details.php?slug=<?php echo urlencode($c['slug']); ?>" target="_blank" class="text-slate-600 hover:text-slate-900 flex items-center gap-1 text-[11px] font-medium" title="Preview on website">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    <span>Preview</span>
                                </a>

                                <div class="flex items-center gap-2">
                                    <a href="cruise-edit.php?id=<?php echo $c['id']; ?>" class="px-3 py-1 bg-white text-slate-800 border border-[#e5e4dc] hover:border-sage-700 hover:bg-sage-50 transition-colors flex items-center gap-1.5 text-xs">
                                        <i class="fa-solid fa-pen-to-square text-[10px] text-sage-700"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a href="cruises.php?action=delete&id=<?php echo $c['id']; ?><?php echo !empty($activePort) ? '&port=' . urlencode($activePort) : ''; ?>" onclick="return confirm('Are you sure you want to permanently delete this cruise listing?');" class="px-2 py-1 bg-white text-rose-600 border border-[#e5e4dc] hover:border-rose-300 hover:bg-rose-50 transition-colors" title="Delete Cruise">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white border border-[#e5e4dc] p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-none bg-cream-100 text-slate-400 mx-auto flex items-center justify-center text-lg border border-[#e5e4dc]">
                        <i class="fa-solid fa-ship"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">No Cruises Found</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            No cruise itineraries match your current port or search query. Click below to add a new ocean liner cruise.
                        </p>
                    </div>
                    <a href="cruise-edit.php" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold bg-sage-700 text-white hover:bg-sage-800 transition-colors border border-sage-800">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Add First Cruise</span>
                    </a>
                </div>
            <?php endif; ?>

        </main>

        <!-- Footer -->
        <?php include 'components/footer.php'; ?>
    </div>
</div>
