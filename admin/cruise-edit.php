<?php
/**
 * Admin Ocean Cruise Add & Edit Controller - Orion Advent
 * Comprehensive Form with Dynamic Cabin Categories, Day-by-Day Itinerary Builder,
 * Live Local Image Previews, Inclusions & Maritime Policies.
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

$cruiseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = ($cruiseId > 0);

// Default empty cruise structure
$cruise = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'subtitle' => '',
    'cruise_line' => 'Cordelia Cruises',
    'ship_name' => 'The Empress',
    'departure_port' => 'Mumbai',
    'destination_ports' => 'Mumbai, High Seas, Goa',
    'duration_nights' => 3,
    'duration_days' => 4,
    'duration_text' => '3 Nights / 4 Days',
    'starting_price' => 19999.00,
    'original_price' => 26999.00,
    'token_advance' => 3000.00,
    'badge' => 'Bestseller',
    'rating' => 4.9,
    'reviews_count' => 350,
    'featured_image' => '',
    'gallery' => '[]',
    'highlights' => '[]',
    'itinerary' => '[]',
    'cabins' => '[]',
    'inclusions' => '[]',
    'exclusions' => '[]',
    'policies' => '100% refund on cancellations requested 30 days prior to sailing.',
    'category' => 'domestic',
    'sailing_dates' => 'Every Friday & Monday departures',
    'status' => 'active'
];

if ($isEdit && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM `cruises` WHERE `id` = ?");
    $stmt->execute([$cruiseId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $cruise = array_merge($cruise, $existing);
    } else {
        header("Location: cruises.php");
        exit();
    }
}

// Upload directory for cruises
$uploadDir = __DIR__ . '/../uploads/cruises/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    try {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        $subtitle = trim($_POST['subtitle'] ?? '');
        $cruiseLine = trim($_POST['cruise_line'] ?? 'Cordelia Cruises');
        $shipName = trim($_POST['ship_name'] ?? 'The Empress');
        $departurePort = trim($_POST['departure_port'] ?? 'Mumbai');
        $destinationPorts = trim($_POST['destination_ports'] ?? '');
        $durationNights = (int)($_POST['duration_nights'] ?? 3);
        $durationDays = (int)($_POST['duration_days'] ?? 4);
        $durationText = trim($_POST['duration_text'] ?? "{$durationNights} Nights / {$durationDays} Days");
        $startingPrice = (float)($_POST['starting_price'] ?? 0);
        $originalPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : ($startingPrice * 1.3);
        $tokenAdvance = (float)($_POST['token_advance'] ?? 3000);
        $badge = trim($_POST['badge'] ?? 'Bestseller');
        $category = in_array($_POST['category'] ?? '', ['domestic', 'international']) ? $_POST['category'] : 'domestic';
        $sailingDates = trim($_POST['sailing_dates'] ?? '');
        $policies = trim($_POST['policies'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'draft']) ? $_POST['status'] : 'active';

        // Process Featured Image
        $featuredImage = trim($_POST['existing_featured_image'] ?? '');
        if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['featured_image_file']['tmp_name'];
            $fileName = time() . '_cruise_' . preg_replace('/[^A-Za-z0-9._-]/', '', $_FILES['featured_image_file']['name']);
            if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
                $featuredImage = 'uploads/cruises/' . $fileName;
            }
        } elseif (!empty($_POST['featured_image_url'])) {
            $featuredImage = trim($_POST['featured_image_url']);
        }

        // Process Gallery Images
        $galleryArr = [];
        if (isset($_POST['gallery_existing']) && is_array($_POST['gallery_existing'])) {
            foreach ($_POST['gallery_existing'] as $gUrl) {
                if (!empty(trim($gUrl))) $galleryArr[] = trim($gUrl);
            }
        }
        if (isset($_POST['gallery_urls']) && is_array($_POST['gallery_urls'])) {
            foreach ($_POST['gallery_urls'] as $gUrl) {
                if (!empty(trim($gUrl))) $galleryArr[] = trim($gUrl);
            }
        }
        if (isset($_FILES['gallery_files']) && !empty($_FILES['gallery_files']['name'][0])) {
            foreach ($_FILES['gallery_files']['name'] as $idx => $origName) {
                if ($_FILES['gallery_files']['error'][$idx] === UPLOAD_ERR_OK) {
                    $tmpF = $_FILES['gallery_files']['tmp_name'][$idx];
                    $cleanF = time() . '_gal_' . $idx . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $origName);
                    if (move_uploaded_file($tmpF, $uploadDir . $cleanF)) {
                        $galleryArr[] = 'uploads/cruises/' . $cleanF;
                    }
                }
            }
        }
        $galleryJson = json_encode(array_values(array_unique($galleryArr)), JSON_UNESCAPED_UNICODE);

        // Process Highlights
        $highlightsArr = [];
        if (isset($_POST['highlights']) && is_array($_POST['highlights'])) {
            foreach ($_POST['highlights'] as $hl) {
                $hlTitle = trim($hl['title'] ?? '');
                if (!empty($hlTitle)) {
                    $highlightsArr[] = [
                        'icon' => !empty($hl['icon']) ? trim($hl['icon']) : 'fa-solid fa-ship',
                        'title' => $hlTitle,
                        'desc' => trim($hl['desc'] ?? '')
                    ];
                }
            }
        }
        $highlightsJson = json_encode($highlightsArr, JSON_UNESCAPED_UNICODE);

        // Process Day-by-Day Itinerary
        $itineraryArr = [];
        if (isset($_POST['itinerary']) && is_array($_POST['itinerary'])) {
            foreach ($_POST['itinerary'] as $it) {
                $itTitle = trim($it['title'] ?? '');
                if (!empty($itTitle)) {
                    $itineraryArr[] = [
                        'day' => trim($it['day'] ?? 'Day'),
                        'port' => trim($it['port'] ?? 'At Sea'),
                        'arrive' => trim($it['arrive'] ?? 'Cruising'),
                        'depart' => trim($it['depart'] ?? 'All Day'),
                        'title' => $itTitle,
                        'desc' => trim($it['desc'] ?? '')
                    ];
                }
            }
        }
        $itineraryJson = json_encode($itineraryArr, JSON_UNESCAPED_UNICODE);

        // Process Cabin Categories
        $cabinsArr = [];
        if (isset($_POST['cabins']) && is_array($_POST['cabins'])) {
            foreach ($_POST['cabins'] as $cb) {
                $cbName = trim($cb['name'] ?? '');
                if (!empty($cbName)) {
                    $cabinsArr[] = [
                        'name' => $cbName,
                        'type' => trim($cb['type'] ?? 'Stateroom'),
                        'price' => (float)($cb['price'] ?? 0),
                        'original_price' => (float)($cb['original_price'] ?? 0),
                        'capacity' => trim($cb['capacity'] ?? '2-3 Guests'),
                        'size' => trim($cb['size'] ?? '150 sq.ft'),
                        'perks' => trim($cb['perks'] ?? ''),
                        'image' => trim($cb['image'] ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80')
                    ];
                }
            }
        }
        $cabinsJson = json_encode($cabinsArr, JSON_UNESCAPED_UNICODE);

        // Process Inclusions & Exclusions
        $inclusionsArr = array_filter(array_map('trim', explode("\n", $_POST['inclusions_text'] ?? '')));
        $inclusionsJson = json_encode(array_values($inclusionsArr), JSON_UNESCAPED_UNICODE);

        $exclusionsArr = array_filter(array_map('trim', explode("\n", $_POST['exclusions_text'] ?? '')));
        $exclusionsJson = json_encode(array_values($exclusionsArr), JSON_UNESCAPED_UNICODE);

        if (empty($title)) {
            throw new Exception("Cruise Title is required.");
        }
        if (empty($departurePort)) {
            throw new Exception("Departure Port is required.");
        }

        if ($isEdit) {
            $sql = "UPDATE `cruises` SET
                `title` = ?, `slug` = ?, `subtitle` = ?, `cruise_line` = ?, `ship_name` = ?,
                `departure_port` = ?, `destination_ports` = ?, `duration_nights` = ?, `duration_days` = ?,
                `duration_text` = ?, `starting_price` = ?, `original_price` = ?, `token_advance` = ?,
                `badge` = ?, `featured_image` = ?, `gallery` = ?, `highlights` = ?, `itinerary` = ?,
                `cabins` = ?, `inclusions` = ?, `exclusions` = ?, `policies` = ?, `category` = ?,
                `sailing_dates` = ?, `status` = ?
                WHERE `id` = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title, $slug, $subtitle, $cruiseLine, $shipName,
                $departurePort, $destinationPorts, $durationNights, $durationDays,
                $durationText, $startingPrice, $originalPrice, $tokenAdvance,
                $badge, $featuredImage, $galleryJson, $highlightsJson, $itineraryJson,
                $cabinsJson, $inclusionsJson, $exclusionsJson, $policies, $category,
                $sailingDates, $status, $cruiseId
            ]);
            $alertMessage = "Cruise '{$title}' updated successfully!";
        } else {
            $sql = "INSERT INTO `cruises` (
                `title`, `slug`, `subtitle`, `cruise_line`, `ship_name`,
                `departure_port`, `destination_ports`, `duration_nights`, `duration_days`,
                `duration_text`, `starting_price`, `original_price`, `token_advance`,
                `badge`, `featured_image`, `gallery`, `highlights`, `itinerary`,
                `cabins`, `inclusions`, `exclusions`, `policies`, `category`,
                `sailing_dates`, `status`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title, $slug, $subtitle, $cruiseLine, $shipName,
                $departurePort, $destinationPorts, $durationNights, $durationDays,
                $durationText, $startingPrice, $originalPrice, $tokenAdvance,
                $badge, $featuredImage, $galleryJson, $highlightsJson, $itineraryJson,
                $cabinsJson, $inclusionsJson, $exclusionsJson, $policies, $category,
                $sailingDates, $status
            ]);
            $newId = $pdo->lastInsertId();
            header("Location: cruise-edit.php?id={$newId}&saved=1");
            exit();
        }

        // Refresh current object
        $stmt = $pdo->prepare("SELECT * FROM `cruises` WHERE `id` = ?");
        $stmt->execute([$cruiseId]);
        $cruise = $stmt->fetch();
    } catch (Exception $e) {
        $alertMessage = "Error: " . $e->getMessage();
        $alertType = 'error';
    }
}

$galleryImages = json_decode($cruise['gallery'] ?? '[]', true) ?: [];
$highlights = json_decode($cruise['highlights'] ?? '[]', true) ?: [];
$itinerary = json_decode($cruise['itinerary'] ?? '[]', true) ?: [];
$cabins = json_decode($cruise['cabins'] ?? '[]', true) ?: [];
$inclusions = json_decode($cruise['inclusions'] ?? '[]', true) ?: [];
$exclusions = json_decode($cruise['exclusions'] ?? '[]', true) ?: [];

$pageTitle = $isEdit ? "Edit Cruise: " . $cruise['title'] : "Add New Ocean Cruise";
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

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full space-y-6">

            <!-- Top Breadcrumb & Header Action Strip -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#e5e4dc]">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        <a href="cruises.php" class="hover:text-sage-800 transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-ship text-[11px] text-sage-700"></i>
                            <span>Cruises Catalog</span>
                        </a>
                        <span>/</span>
                        <span class="text-slate-800 font-bold"><?php echo $isEdit ? 'Edit Cruise' : 'New Voyage'; ?></span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        <?php echo $isEdit ? htmlspecialchars($cruise['title']) : 'Add New Ocean Cruise Listing'; ?>
                    </h1>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="cruises.php" class="px-3.5 py-2 text-xs font-bold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Back to Cruises</span>
                    </a>
                    <?php if ($isEdit): ?>
                        <a href="../cruise-details.php?slug=<?php echo urlencode($cruise['slug']); ?>" target="_blank" class="px-3.5 py-2 text-xs font-bold bg-cream-50 hover:bg-cream-200 text-slate-800 border border-[#e5e4dc] transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-sage-700"></i>
                            <span>View Live</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-4 text-xs font-semibold flex items-center gap-2.5 <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                    <span><?php echo htmlspecialchars($alertMessage); ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Container -->
            <form method="POST" action="cruise-edit.php<?php echo $isEdit ? '?id=' . $cruise['id'] : ''; ?>" enctype="multipart/form-data" class="space-y-6">

                <!-- Section 1: Basic Identity & Ship Details -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">1. Basic Identity &amp; Ship Details</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Cruise Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" id="cruiseTitle" value="<?php echo htmlspecialchars($cruise['title']); ?>" required placeholder="e.g. Mumbai to Goa &amp; Diu Luxury Weekend Cruise" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold text-slate-900" onkeyup="autoSyncSlug(this.value)">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                URL Slug <span class="text-slate-400 font-normal">(auto-generated)</span>
                            </label>
                            <input type="text" name="slug" id="cruiseSlug" value="<?php echo htmlspecialchars($cruise['slug']); ?>" placeholder="mumbai-goa-diu-cruise" class="w-full text-xs p-2.5 bg-cream-50 border border-[#e5e4dc] focus:border-sage-700 outline-none font-mono text-slate-600">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Cruise Line Company <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="cruise_line" list="cruiseLinesList" value="<?php echo htmlspecialchars($cruise['cruise_line']); ?>" required placeholder="e.g. Cordelia Cruises" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                            <datalist id="cruiseLinesList">
                                <option value="Cordelia Cruises">
                                <option value="Royal Caribbean">
                                <option value="Costa Cruises">
                                <option value="Resorts World Cruises">
                                <option value="Genting Dream">
                                <option value="Norwegian Cruise Line">
                                <option value="MSC Cruises">
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Ship / Liner Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="ship_name" value="<?php echo htmlspecialchars($cruise['ship_name']); ?>" required placeholder="e.g. The Empress, Spectrum of the Seas" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Marketing Badge / Highlight Tag
                            </label>
                            <input type="text" name="badge" list="cruiseBadgesList" value="<?php echo htmlspecialchars($cruise['badge']); ?>" placeholder="e.g. Bestseller, Luxury Liner" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                            <datalist id="cruiseBadgesList">
                                <option value="Bestseller">
                                <option value="Weekend Special">
                                <option value="All-Inclusive">
                                <option value="Ultra Luxury">
                                <option value="Family Fav">
                                <option value="Visa Free">
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">Category</label>
                            <select name="category" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                <option value="domestic" <?php echo ($cruise['category'] === 'domestic') ? 'selected' : ''; ?>>Domestic Cruise (Indian Waters)</option>
                                <option value="international" <?php echo ($cruise['category'] === 'international') ? 'selected' : ''; ?>>International Cruise (Global Waters)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">Status</label>
                            <select name="status" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                <option value="active" <?php echo ($cruise['status'] === 'active') ? 'selected' : ''; ?>>Active (Visible on Website)</option>
                                <option value="draft" <?php echo ($cruise['status'] === 'draft') ? 'selected' : ''; ?>>Draft (Hidden / Unlisted)</option>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-slate-800 mb-1">Short Voyage Summary / Subtitle</label>
                            <textarea name="subtitle" rows="2" placeholder="Brief 1-2 line enticing overview of the sea voyage..." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium text-slate-700"><?php echo htmlspecialchars($cruise['subtitle']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Ports of Call & Sailing Duration -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">2. Ports of Call, Route &amp; Sailing Duration</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Departure Port <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="departure_port" value="<?php echo htmlspecialchars($cruise['departure_port']); ?>" required placeholder="e.g. Mumbai, Kochi, Chennai, Singapore" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold text-slate-800">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Destination Ports / Route Circuit <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="destination_ports" value="<?php echo htmlspecialchars($cruise['destination_ports']); ?>" required placeholder="e.g. Mumbai, High Seas, Mormugao (Goa)" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">Sailing Schedule</label>
                            <input type="text" name="sailing_dates" value="<?php echo htmlspecialchars($cruise['sailing_dates']); ?>" placeholder="e.g. Weekly Departures • Friday" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">Duration Nights</label>
                            <input type="number" name="duration_nights" id="durNights" value="<?php echo htmlspecialchars($cruise['duration_nights']); ?>" min="1" max="30" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold" onchange="syncDuration()">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">Duration Days</label>
                            <input type="number" name="duration_days" id="durDays" value="<?php echo htmlspecialchars($cruise['duration_days']); ?>" min="1" max="30" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold" onchange="syncDuration()">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-800 mb-1">Duration Display Text</label>
                            <input type="text" name="duration_text" id="durationText" value="<?php echo htmlspecialchars($cruise['duration_text']); ?>" placeholder="3 Nights / 4 Days" class="w-full text-xs p-2.5 bg-cream-50 border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold text-slate-700">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pricing & Token Advance -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">3. Starting Fares &amp; Booking Token Advance</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Starting Fare (Per Person) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input type="number" step="0.01" name="starting_price" value="<?php echo htmlspecialchars($cruise['starting_price']); ?>" required class="w-full text-xs pl-7 pr-2.5 py-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">Original Price (Strikeout)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input type="number" step="0.01" name="original_price" value="<?php echo htmlspecialchars($cruise['original_price']); ?>" class="w-full text-xs pl-7 pr-2.5 py-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium text-slate-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">Token Advance Required</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input type="number" step="0.01" name="token_advance" value="<?php echo htmlspecialchars($cruise['token_advance']); ?>" class="w-full text-xs pl-7 pr-2.5 py-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold text-emerald-700">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Visuals & Cover Photo (Live Previews) -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">4. Ocean Cruise Visuals &amp; Multi-Photo Gallery</h2>
                    </div>

                    <!-- Part A: Featured Cover Photo -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-800">
                            Featured Cruise Ship Cover Photo <span class="text-slate-400 font-normal">(Grand ship exterior or ocean deck photo)</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center bg-cream-50 p-3.5 border border-[#e5e4dc]">
                            <div class="md:col-span-2 space-y-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Cover Image URL</label>
                                    <input type="url" name="featured_image_url" id="cruiseCoverUrl" value="<?php echo htmlspecialchars($cruise['featured_image']); ?>" placeholder="https://images.unsplash.com/photo-..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium" oninput="updateCruiseCoverPreview(this.value)">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Or Upload Local Image File</label>
                                    <input type="file" name="featured_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:border file:border-[#e5e4dc] file:text-[11px] file:font-semibold file:bg-white hover:file:bg-cream-100 file:cursor-pointer" onchange="previewLocalImage(this, 'cruiseCoverPreview')">
                                    <input type="hidden" name="existing_featured_image" value="<?php echo htmlspecialchars($cruise['featured_image']); ?>">
                                </div>
                            </div>
                            <div class="h-28 bg-white border border-[#e5e4dc] flex items-center justify-center overflow-hidden relative group">
                                <img id="cruiseCoverPreview" src="<?php echo htmlspecialchars(getAdminImageUrl($cruise['featured_image'] ?? '', 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=600&q=80')); ?>" alt="Cover" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] px-1.5 py-0.5 font-bold">Cover Preview</span>
                            </div>
                        </div>
                    </div>

                    <!-- Part B: Multi-Photo Gallery -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-slate-800">
                                    Cruise Gallery Photos <span class="text-slate-400 font-normal">(Decks, Dining Halls, Theatres, Pools, Casino, Lounges)</span>
                                </label>
                            </div>
                            <button type="button" onclick="addCruiseGalleryUrlRow()" class="px-2.5 py-1 text-xs font-bold bg-cream-50 text-slate-800 border border-[#e5e4dc] hover:bg-cream-100 transition-colors flex items-center gap-1">
                                <i class="fa-solid fa-link text-[10px] text-sage-700"></i>
                                <span>Add Photo URL</span>
                            </button>
                        </div>

                        <!-- Multi-file Upload Box -->
                        <div class="p-3 bg-white border border-dashed border-sage-400 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-sage-50 text-sage-700 border border-sage-200 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">Upload Multiple Ship Gallery Photos</div>
                                    <div class="text-[10px] text-slate-400">Select multiple JPG, PNG, or WebP images from your computer</div>
                                </div>
                            </div>
                            <input type="file" name="gallery_files[]" multiple accept="image/*" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:border file:border-sage-300 file:text-xs file:font-bold file:bg-sage-50 file:text-sage-800 hover:file:bg-sage-100 file:cursor-pointer">
                        </div>

                        <!-- Gallery Photos Grid -->
                        <div id="cruiseGalleryContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                            <?php if (!empty($galleryImages)): ?>
                                <?php foreach ($galleryImages as $gi => $gUrl): ?>
                                    <div class="cr-gal-card bg-white border border-[#e5e4dc] p-2 space-y-2 relative group">
                                        <div class="h-28 bg-cream-50 border border-[#e5e4dc] overflow-hidden">
                                            <img src="<?php echo htmlspecialchars(getAdminImageUrl($gUrl)); ?>" alt="Gallery" class="w-full h-full object-cover">
                                        </div>
                                        <input type="hidden" name="gallery_existing[]" value="<?php echo htmlspecialchars($gUrl); ?>">
                                        <div class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($gUrl); ?></div>
                                        <button type="button" onclick="this.closest('.cr-gal-card').remove()" class="absolute top-3 right-3 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] shadow-sm transition-colors" title="Delete Photo">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Cabin Categories Builder -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#e5e4dc] gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">5. Cabin Categories &amp; Stateroom Fares</h2>
                        </div>
                        <button type="button" onclick="addNewCabinRow()" class="px-2.5 py-1 text-xs font-bold bg-sage-50 text-sage-800 border border-sage-300 hover:bg-sage-100 flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Add Cabin Category</span>
                        </button>
                    </div>

                    <div id="cabinsContainer" class="space-y-4">
                        <?php if (!empty($cabins)): ?>
                            <?php foreach ($cabins as $cIdx => $cab): ?>
                                <div class="cabin-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative group">
                                    <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                                        <span class="text-xs font-bold text-sage-800 uppercase tracking-wider">Cabin Category #<?php echo $cIdx + 1; ?></span>
                                        <button type="button" onclick="this.closest('.cabin-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                            <span>Remove</span>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Name <span class="text-rose-500">*</span></label>
                                            <input type="text" name="cabins[<?php echo $cIdx; ?>][name]" value="<?php echo htmlspecialchars($cab['name'] ?? ''); ?>" required placeholder="e.g. Interior Stateroom / Balcony Suite" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold focus:border-sage-700 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Type / Tag</label>
                                            <input type="text" name="cabins[<?php echo $cIdx; ?>][type]" value="<?php echo htmlspecialchars($cab['type'] ?? 'Standard'); ?>" placeholder="Standard / Sea View / Suite" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Capacity</label>
                                            <input type="text" name="cabins[<?php echo $cIdx; ?>][capacity]" value="<?php echo htmlspecialchars($cab['capacity'] ?? '2-3 Guests'); ?>" placeholder="2-4 Guests" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Fare (₹ per person) <span class="text-rose-500">*</span></label>
                                            <input type="number" step="0.01" name="cabins[<?php echo $cIdx; ?>][price]" value="<?php echo htmlspecialchars($cab['price'] ?? 0); ?>" required class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold text-slate-900">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Original Fare (₹ Strikeout)</label>
                                            <input type="number" step="0.01" name="cabins[<?php echo $cIdx; ?>][original_price]" value="<?php echo htmlspecialchars($cab['original_price'] ?? 0); ?>" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Size</label>
                                            <input type="text" name="cabins[<?php echo $cIdx; ?>][size]" value="<?php echo htmlspecialchars($cab['size'] ?? '160 sq.ft'); ?>" placeholder="160 sq.ft" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Inclusions &amp; Perks</label>
                                            <input type="text" name="cabins[<?php echo $cIdx; ?>][perks]" value="<?php echo htmlspecialchars($cab['perks'] ?? ''); ?>" placeholder="e.g. Queen Bed, Private Balcony, 24/7 Room Service, Interactive TV" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Photo URL</label>
                                            <input type="url" name="cabins[<?php echo $cIdx; ?>][image]" value="<?php echo htmlspecialchars($cab['image'] ?? ''); ?>" placeholder="https://..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 6: Day-by-Day Sailing Itinerary Builder -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#e5e4dc] gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">6. Day-by-Day Sailing Itinerary &amp; Ports Schedule</h2>
                        </div>
                        <button type="button" onclick="addNewItineraryDay()" class="px-2.5 py-1 text-xs font-bold bg-sage-50 text-sage-800 border border-sage-300 hover:bg-sage-100 flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Add Sailing Day</span>
                        </button>
                    </div>

                    <div id="itineraryContainer" class="space-y-4">
                        <?php if (!empty($itinerary)): ?>
                            <?php foreach ($itinerary as $itIdx => $day): ?>
                                <div class="itinerary-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative group">
                                    <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-sage-800 uppercase tracking-wider"><?php echo htmlspecialchars($day['day'] ?? 'Day ' . ($itIdx + 1)); ?></span>
                                            <input type="hidden" name="itinerary[<?php echo $itIdx; ?>][day]" value="<?php echo htmlspecialchars($day['day'] ?? 'Day ' . ($itIdx + 1)); ?>">
                                        </div>
                                        <button type="button" onclick="this.closest('.itinerary-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                            <span>Remove</span>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Port of Call / Sea Location <span class="text-rose-500">*</span></label>
                                            <input type="text" name="itinerary[<?php echo $itIdx; ?>][port]" value="<?php echo htmlspecialchars($day['port'] ?? ''); ?>" required placeholder="e.g. Mumbai Port / At Sea / Mormugao" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Arrival Timing</label>
                                            <input type="text" name="itinerary[<?php echo $itIdx; ?>][arrive]" value="<?php echo htmlspecialchars($day['arrive'] ?? 'Cruising'); ?>" placeholder="e.g. 08:00 AM or Cruising" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Departure Timing</label>
                                            <input type="text" name="itinerary[<?php echo $itIdx; ?>][depart]" value="<?php echo htmlspecialchars($day['depart'] ?? 'All Day'); ?>" placeholder="e.g. 05:00 PM or All Day" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Day Title / Theme <span class="text-rose-500">*</span></label>
                                            <input type="text" name="itinerary[<?php echo $itIdx; ?>][title]" value="<?php echo htmlspecialchars($day['title'] ?? ''); ?>" required placeholder="e.g. Embarkation at Mumbai Port &amp; Sunset Welcome Party" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold">
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Day Activities, Meals &amp; Shore Excursion Details</label>
                                            <textarea name="itinerary[<?php echo $itIdx; ?>][desc]" rows="2" placeholder="Describe the day schedule, entertainment, dining, activities..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] outline-none font-medium"><?php echo htmlspecialchars($day['desc'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 7: Inclusions, Exclusions & Cancellation Policy -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">7. Inclusions, Exclusions &amp; Policies</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Cruise Inclusions <span class="text-slate-400 font-normal">(One line per inclusion item)</span>
                            </label>
                            <textarea name="inclusions_text" rows="6" placeholder="Accommodations in selected cabin category&#10;All standard buffet meals across specialty restaurants&#10;Access to swimming pools and whirlpool jacuzzis&#10;Entry to nightly Broadway theatre shows and live musicals" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium text-slate-800"><?php echo htmlspecialchars(implode("\n", $inclusions)); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Cruise Exclusions <span class="text-slate-400 font-normal">(One line per exclusion item)</span>
                            </label>
                            <textarea name="exclusions_text" rows="6" placeholder="Casino gaming chips and VIP bar privileges&#10;Alcoholic and packaged canned beverages&#10;Spa and salon wellness therapies&#10;Optional Shore Excursions at ports of call&#10;18% Government GST" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium text-slate-800"><?php echo htmlspecialchars(implode("\n", $exclusions)); ?></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Cancellation &amp; Maritime Policies
                            </label>
                            <textarea name="policies" rows="3" placeholder="100% refund for cancellations up to 30 days before sailing date. Valid Passport required for international sailings." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium text-slate-800"><?php echo htmlspecialchars($cruise['policies']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e5e4dc]">
                    <a href="cruises.php" class="px-5 py-2.5 text-xs font-bold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-7 py-2.5 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span><?php echo $isEdit ? 'Update Ocean Cruise' : 'Publish Ocean Cruise'; ?></span>
                    </button>
                </div>

            </form>

        </main>

        <!-- Footer -->
        <?php include 'components/footer.php'; ?>
    </div>
</div>

<!-- Dynamic Script for Live Image Preview, Slug Sync, Cabins & Itinerary Rows -->
<script>
    let cabinIndex = <?php echo count($cabins); ?>;
    let dayIndex = <?php echo count($itinerary); ?>;

    function autoSyncSlug(title) {
        const slugInput = document.getElementById('cruiseSlug');
        if (slugInput && <?php echo $isEdit ? 'false' : 'true'; ?>) {
            slugInput.value = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }
    }

    function syncDuration() {
        const n = document.getElementById('durNights').value || 1;
        const d = document.getElementById('durDays').value || 2;
        const txt = document.getElementById('durationText');
        if (txt) {
            txt.value = `${n} Nights / ${d} Days`;
        }
    }

    function previewLocalImage(input, targetId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(targetId);
                if (img) img.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCruiseCoverPreview(url) {
        const prev = document.getElementById('cruiseCoverPreview');
        if (prev && url && url.trim().length > 5) {
            prev.src = url.trim();
        }
    }

    function addCruiseGalleryUrlRow() {
        const container = document.getElementById('cruiseGalleryContainer');
        const card = document.createElement('div');
        card.className = 'cr-gal-card bg-white border border-[#e5e4dc] p-2 space-y-2 relative group';
        card.innerHTML = `
            <div class="h-28 bg-cream-50 border border-[#e5e4dc] overflow-hidden flex items-center justify-center">
                <img src="" alt="Gallery Preview" class="w-full h-full object-cover cr-gal-img hidden">
                <i class="fa-solid fa-ship text-2xl text-slate-300 cr-gal-ph"></i>
            </div>
            <div>
                <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Image URL</label>
                <input type="url" name="gallery_urls[]" required placeholder="https://images.unsplash.com/..." class="w-full text-[11px] p-1.5 bg-white border border-[#e5e4dc]" oninput="const img = this.closest('.cr-gal-card').querySelector('.cr-gal-img'); const ph = this.closest('.cr-gal-card').querySelector('.cr-gal-ph'); if (this.value.trim()) { img.src = this.value; img.classList.remove('hidden'); ph.classList.add('hidden'); } else { img.classList.add('hidden'); ph.classList.remove('hidden'); }">
            </div>
            <button type="button" onclick="this.closest('.cr-gal-card').remove()" class="absolute top-3 right-3 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] shadow-sm transition-colors" title="Delete Photo">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(card);
    }

    function addNewCabinRow() {
        cabinIndex++;
        const container = document.getElementById('cabinsContainer');
        const div = document.createElement('div');
        div.className = 'cabin-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative group';
        div.innerHTML = `
            <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                <span class="text-xs font-bold text-sage-800 uppercase tracking-wider">Cabin Category #${cabinIndex}</span>
                <button type="button" onclick="this.closest('.cabin-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                    <span>Remove</span>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="cabins[${cabinIndex}][name]" required placeholder="e.g. Balcony Ocean Suite" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold focus:border-sage-700 outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Type / Tag</label>
                    <input type="text" name="cabins[${cabinIndex}][type]" value="Balcony" placeholder="Balcony / Suite" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Capacity</label>
                    <input type="text" name="cabins[${cabinIndex}][capacity]" value="2-3 Guests" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Fare (₹ per person) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="cabins[${cabinIndex}][price]" value="29999" required class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold text-slate-900">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Original Fare (₹ Strikeout)</label>
                    <input type="number" step="0.01" name="cabins[${cabinIndex}][original_price]" value="38000" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Size</label>
                    <input type="text" name="cabins[${cabinIndex}][size]" value="210 sq.ft" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Inclusions &amp; Perks</label>
                    <input type="text" name="cabins[${cabinIndex}][perks]" value="Private Sea Balcony, Ensuite Bath, Interactive TV" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Cabin Photo URL</label>
                    <input type="url" name="cabins[${cabinIndex}][image]" value="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function addNewItineraryDay() {
        dayIndex++;
        const container = document.getElementById('itineraryContainer');
        const div = document.createElement('div');
        div.className = 'itinerary-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative group';
        const dayLabel = 'Day ' + dayIndex;
        div.innerHTML = `
            <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-sage-800 uppercase tracking-wider">${dayLabel}</span>
                    <input type="hidden" name="itinerary[${dayIndex}][day]" value="${dayLabel}">
                </div>
                <button type="button" onclick="this.closest('.itinerary-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                    <span>Remove</span>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Port of Call / Sea Location <span class="text-rose-500">*</span></label>
                    <input type="text" name="itinerary[${dayIndex}][port]" required placeholder="e.g. Mormugao Port (Goa)" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Arrival Timing</label>
                    <input type="text" name="itinerary[${dayIndex}][arrive]" value="08:00 AM" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Departure Timing</label>
                    <input type="text" name="itinerary[${dayIndex}][depart]" value="05:00 PM" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Day Title / Theme <span class="text-rose-500">*</span></label>
                    <input type="text" name="itinerary[${dayIndex}][title]" required placeholder="e.g. Shore Excursion & Beachside Adventures" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Day Activities, Meals &amp; Details</label>
                    <textarea name="itinerary[${dayIndex}][desc]" rows="2" placeholder="Describe the day schedule, entertainment, dining, activities..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] outline-none font-medium"></textarea>
                </div>
            </div>
        `;
        container.appendChild(div);
    }
</script>
