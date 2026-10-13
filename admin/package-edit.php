<?php

/**
 * Admin Dedicated Tour Package Add / Edit Controller & View
 * Full-page builder with Orion Advent Sage & Warm Cream aesthetic (strictly zero shadow, flat border-first):
 * - Interactive Info Tooltips on all form fields matching hotel-edit.php
 * - 100% Professional English UI (Zero Hindi script or words)
 * - Custom Categories & Custom Themes support
 * - Dynamic Route / Stops configuration
 * - Full Tour Overview & Experience description
 * - Key Highlights Builder (Icon, Title, Description)
 * - Day-by-Day Dynamic Itinerary Builder (with Day Location / Route Stop)
 * - Multi-Photo Tour Gallery & Cover Photo (1200x800 recommended badges)
 * - Linked Hotels + Custom Hotel Stays builder
 * - Inclusions & Exclusions Checklist
 * - Tiered Cancellation & Reschedule Policies
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

$uploadDir = __DIR__ . '/../uploads/packages/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$pkgId = isset($_GET['id']) && (int)$_GET['id'] > 0 ? (int)$_GET['id'] : null;
$isEdit = !empty($pkgId);

// 1. HANDLE POST SAVE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_package'])) {
    if ($pdo) {
        try {
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            }
            $subtitle = trim($_POST['subtitle'] ?? '');
            $overviewHeading = trim($_POST['overview_heading'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $stateCountry = trim($_POST['state_country'] ?? '');

            // Category: if custom category entered, use that; otherwise use dropdown selection
            $categorySelect = trim($_POST['category_select'] ?? 'domestic');
            $categoryCustom = trim($_POST['category_custom'] ?? '');
            $category = !empty($categoryCustom) ? $categoryCustom : $categorySelect;

            // Circuit Type / Theme: if custom entered, use that
            $themeSelect = trim($_POST['circuit_type_select'] ?? '');
            $themeCustom = trim($_POST['circuit_type_custom'] ?? '');
            $circuitType = !empty($themeCustom) ? $themeCustom : (!empty($themeSelect) ? $themeSelect : trim($_POST['circuit_type'] ?? 'Holiday Special'));

            $travelMode = trim($_POST['travel_mode'] ?? 'flight');
            $departureCity = trim($_POST['departure_city'] ?? 'All Major Cities');
            $durationNights = (int)($_POST['duration_nights'] ?? 4);
            $durationDays = (int)($_POST['duration_days'] ?? 5);
            $durationText = trim($_POST['duration_text'] ?? "{$durationNights} Nights / {$durationDays} Days");
            $price = (float)($_POST['price'] ?? 0);
            $originalPrice = (float)($_POST['original_price'] ?? ($price * 1.25));
            $tokenAdvance = (float)($_POST['token_advance'] ?? 2500);
            $badge = trim($_POST['badge'] ?? 'Bestseller 2026');
            $rating = (float)($_POST['rating'] ?? 4.9);
            $reviewsCount = (int)($_POST['reviews_count'] ?? 350);
            $status = trim($_POST['status'] ?? 'active');

            // Process Cancellation & Refund Policies Structure
            $policySummary = trim($_POST['policy_summary'] ?? ($_POST['policies'] ?? 'Free cancellation and date reschedule options available before departure.'));
            $reschedulePolicy = trim($_POST['reschedule_policy'] ?? 'Flexible date reschedule permitted up to 7 days before tour departure without penalty.');
            $medicalPolicy = trim($_POST['medical_policy'] ?? '100% token refund on medical emergencies with verified certificate.');

            $cancellationTiers = [];
            if (isset($_POST['cancellation_tiers']) && is_array($_POST['cancellation_tiers'])) {
                foreach ($_POST['cancellation_tiers'] as $tier) {
                    $timeline = trim($tier['timeline'] ?? '');
                    $refund = trim($tier['refund'] ?? '');
                    $deduction = trim($tier['deduction'] ?? '');
                    $badgeType = trim($tier['badge_type'] ?? 'partial_refund');
                    if (!empty($timeline) && !empty($refund)) {
                        $cancellationTiers[] = [
                            'timeline' => $timeline,
                            'refund' => $refund,
                            'deduction' => $deduction,
                            'badge_type' => $badgeType
                        ];
                    }
                }
            }

            if (empty($cancellationTiers)) {
                $cancellationTiers = [
                    ['timeline' => '15+ Days Before Tour Departure', 'refund' => '100% Refund', 'deduction' => '0% Deduction (Full Token Refund)', 'badge_type' => 'full_refund'],
                    ['timeline' => '7 to 14 Days Before Departure', 'refund' => '80% Refund', 'deduction' => '20% Cancellation / Reschedule Fee', 'badge_type' => 'partial_refund'],
                    ['timeline' => '3 to 6 Days Before Departure', 'refund' => '50% Refund', 'deduction' => '50% Cancellation Fee', 'badge_type' => 'partial_refund'],
                    ['timeline' => 'Within 48 Hours / No-Show', 'refund' => '0% (Non-Refundable)', 'deduction' => '100% Non-Refundable Cutoff', 'badge_type' => 'no_refund']
                ];
            }

            $policyData = [
                'summary' => $policySummary,
                'rules' => $cancellationTiers,
                'reschedule_policy' => $reschedulePolicy,
                'medical_policy' => $medicalPolicy
            ];
            $policies = json_encode($policyData, JSON_UNESCAPED_UNICODE);

            if (empty($title)) {
                throw new Exception("Tour Package Title is required.");
            }
            if (empty($location)) {
                throw new Exception("Tour Route / Destination is required.");
            }

            // Handle Featured Cover Image
            $featuredImage = trim($_POST['existing_featured_image'] ?? '');
            if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['featured_image_file']['tmp_name'];
                $fileName = time() . '_pkg_cover_' . preg_replace('/[^A-Za-z0-9._-]/', '', $_FILES['featured_image_file']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $featuredImage = 'uploads/packages/' . $fileName;
                }
            } elseif (!empty($_POST['featured_image_url'])) {
                $featuredImage = trim($_POST['featured_image_url']);
            }

            // Handle Tour Gallery Photos
            $galleryPhotos = [];
            if (isset($_POST['gallery_existing']) && is_array($_POST['gallery_existing'])) {
                foreach ($_POST['gallery_existing'] as $gUrl) {
                    $u = trim($gUrl);
                    if (!empty($u)) $galleryPhotos[] = $u;
                }
            }
            if (isset($_POST['gallery_urls']) && is_array($_POST['gallery_urls'])) {
                foreach ($_POST['gallery_urls'] as $gUrl) {
                    $u = trim($gUrl);
                    if (!empty($u)) $galleryPhotos[] = $u;
                }
            }
            if (isset($_FILES['gallery_files']) && is_array($_FILES['gallery_files']['name'])) {
                $fCount = count($_FILES['gallery_files']['name']);
                for ($fi = 0; $fi < $fCount; $fi++) {
                    if ($_FILES['gallery_files']['error'][$fi] === UPLOAD_ERR_OK) {
                        $tmp = $_FILES['gallery_files']['tmp_name'][$fi];
                        $cleanN = time() . '_pkg_gal_' . $fi . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $_FILES['gallery_files']['name'][$fi]);
                        if (move_uploaded_file($tmp, $uploadDir . $cleanN)) {
                            $galleryPhotos[] = 'uploads/packages/' . $cleanN;
                        }
                    }
                }
            }
            $galleryJson = json_encode(array_values(array_unique($galleryPhotos)), JSON_UNESCAPED_UNICODE);

            // Handle Itinerary (Day-by-Day with Day Location / Route)
            $itineraryList = [];
            if (isset($_POST['itinerary']) && is_array($_POST['itinerary'])) {
                foreach ($_POST['itinerary'] as $day) {
                    if (!empty($day['title']) || !empty($day['desc']) || !empty($day['location'])) {
                        $itineraryList[] = [
                            'day' => trim($day['day'] ?? 'Day'),
                            'location' => trim($day['location'] ?? ''),
                            'title' => trim($day['title'] ?? ''),
                            'desc' => trim($day['desc'] ?? ''),
                            'meals' => trim($day['meals'] ?? 'Breakfast & Dinner Included'),
                            'stay' => trim($day['stay'] ?? '4★ Hotel Stay')
                        ];
                    }
                }
            }
            $itineraryJson = json_encode($itineraryList, JSON_UNESCAPED_UNICODE);

            // Handle Key Highlights
            $highlightsList = [];
            if (isset($_POST['highlights']) && is_array($_POST['highlights'])) {
                foreach ($_POST['highlights'] as $hl) {
                    if (!empty($hl['title'])) {
                        $highlightsList[] = [
                            'icon' => trim($hl['icon'] ?? 'fa-solid fa-star'),
                            'title' => trim($hl['title'] ?? ''),
                            'desc' => trim($hl['desc'] ?? '')
                        ];
                    }
                }
            }
            $highlightsJson = json_encode($highlightsList, JSON_UNESCAPED_UNICODE);

            // Inclusions & Exclusions
            $inclusionsRaw = explode("\n", str_replace("\r", "", $_POST['inclusions_text'] ?? ''));
            $inclusionsList = array_values(array_filter(array_map('trim', $inclusionsRaw)));
            $inclusionsJson = json_encode($inclusionsList, JSON_UNESCAPED_UNICODE);

            $exclusionsRaw = explode("\n", str_replace("\r", "", $_POST['exclusions_text'] ?? ''));
            $exclusionsList = array_values(array_filter(array_map('trim', $exclusionsRaw)));
            $exclusionsJson = json_encode($exclusionsList, JSON_UNESCAPED_UNICODE);

            // Linked Hotels from database
            $hotelIds = isset($_POST['hotel_ids']) && is_array($_POST['hotel_ids']) ? array_values($_POST['hotel_ids']) : [];
            $hotelIdsJson = json_encode($hotelIds, JSON_UNESCAPED_UNICODE);

            // Custom Stays (Direct Entry)
            $customStaysList = [];
            if (isset($_POST['custom_stays']) && is_array($_POST['custom_stays'])) {
                foreach ($_POST['custom_stays'] as $cs) {
                    if (!empty($cs['name'])) {
                        $customStaysList[] = [
                            'name' => trim($cs['name']),
                            'location' => trim($cs['location'] ?? ''),
                            'nights' => trim($cs['nights'] ?? 'Included Stay'),
                            'rating' => (float)($cs['rating'] ?? 4.8),
                            'image' => trim($cs['image'] ?? '')
                        ];
                    }
                }
            }
            $customStaysJson = json_encode($customStaysList, JSON_UNESCAPED_UNICODE);

            if ($isEdit) {
                // Update Package
                $sql = "UPDATE `packages` SET
                    `title` = ?, `slug` = ?, `subtitle` = ?, `overview_heading` = ?, `description` = ?,
                    `location` = ?, `state_country` = ?, `circuit_type` = ?, `travel_mode` = ?, `departure_city` = ?,
                    `duration` = ?, `duration_nights` = ?, `duration_days` = ?, `price` = ?, `original_price` = ?, 
                    `token_advance` = ?, `badge` = ?, `rating` = ?, `reviews_count` = ?, 
                    `featured_image` = ?, `gallery` = ?, `highlights` = ?, `itinerary` = ?, 
                    `inclusions` = ?, `exclusions` = ?, `hotel_ids` = ?, `custom_stays` = ?, `policies` = ?, 
                    `category` = ?, `status` = ?
                    WHERE `id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $title,
                    $slug,
                    $subtitle,
                    $overviewHeading,
                    $description,
                    $location,
                    $stateCountry,
                    $circuitType,
                    $travelMode,
                    $departureCity,
                    $durationText,
                    $durationNights,
                    $durationDays,
                    $price,
                    $originalPrice,
                    $tokenAdvance,
                    $badge,
                    $rating,
                    $reviewsCount,
                    $featuredImage,
                    $galleryJson,
                    $highlightsJson,
                    $itineraryJson,
                    $inclusionsJson,
                    $exclusionsJson,
                    $hotelIdsJson,
                    $customStaysJson,
                    $policies,
                    $category,
                    $status,
                    $pkgId
                ]);
                $alertMessage = "Tour Package '{$title}' updated successfully!";
            } else {
                // Insert New Package
                $sql = "INSERT INTO `packages` (
                    `title`, `slug`, `subtitle`, `overview_heading`, `description`,
                    `location`, `state_country`, `circuit_type`, `travel_mode`, `departure_city`,
                    `duration`, `duration_nights`, `duration_days`, `price`, `original_price`, 
                    `token_advance`, `badge`, `rating`, `reviews_count`, 
                    `featured_image`, `gallery`, `highlights`, `itinerary`, 
                    `inclusions`, `exclusions`, `hotel_ids`, `custom_stays`, `policies`, 
                    `category`, `status`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $title,
                    $slug,
                    $subtitle,
                    $overviewHeading,
                    $description,
                    $location,
                    $stateCountry,
                    $circuitType,
                    $travelMode,
                    $departureCity,
                    $durationText,
                    $durationNights,
                    $durationDays,
                    $price,
                    $originalPrice,
                    $tokenAdvance,
                    $badge,
                    $rating,
                    $reviewsCount,
                    $featuredImage,
                    $galleryJson,
                    $highlightsJson,
                    $itineraryJson,
                    $inclusionsJson,
                    $exclusionsJson,
                    $hotelIdsJson,
                    $customStaysJson,
                    $policies,
                    $category,
                    $status
                ]);
                $pkgId = (int)$pdo->lastInsertId();
                $isEdit = true;
                $alertMessage = "Tour Package '{$title}' created successfully!";
            }

            header("Location: packages.php?saved=1");
            exit();
        } catch (Exception $e) {
            $alertMessage = "Error saving package: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// 2. FETCH RECORD FOR EDITING
$pkg = [
    'title' => '',
    'slug' => '',
    'subtitle' => '',
    'overview_heading' => '',
    'description' => '',
    'location' => '',
    'state_country' => '',
    'circuit_type' => 'Holiday Special',
    'travel_mode' => 'flight',
    'departure_city' => 'All Major Cities',
    'category' => 'domestic',
    'duration_nights' => 5,
    'duration_days' => 6,
    'duration' => '5 Nights / 6 Days',
    'price' => 17999,
    'original_price' => 22999,
    'token_advance' => 2500,
    'badge' => 'Bestseller 2026',
    'rating' => 4.9,
    'reviews_count' => 350,
    'featured_image' => '',
    'gallery' => '[]',
    'highlights' => '[]',
    'itinerary' => '[]',
    'inclusions' => '[]',
    'exclusions' => '[]',
    'hotel_ids' => '[]',
    'custom_stays' => '[]',
    'policies' => 'Free Cancellation up to 7 days before tour departure. 100% token refund on emergency medical cancellations.',
    'status' => 'active'
];

$itinerary = [];
$highlights = [];
$inclusions = [];
$exclusions = [];
$gallery = [];
$selectedHotelIds = [];
$customStays = [];

if ($isEdit && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM `packages` WHERE `id` = ?");
    $stmt->execute([$pkgId]);
    $fetched = $stmt->fetch();
    if ($fetched) {
        $pkg = $fetched;
        if (empty($pkg['travel_mode'])) {
            $pkg['travel_mode'] = ($pkg['category'] === 'international') ? 'flight' : 'cab';
        }
        $itinerary = json_decode($pkg['itinerary'] ?? '[]', true) ?: [];
        $highlights = json_decode($pkg['highlights'] ?? '[]', true) ?: [];
        $inclusions = json_decode($pkg['inclusions'] ?? '[]', true) ?: [];
        $exclusions = json_decode($pkg['exclusions'] ?? '[]', true) ?: [];
        $gallery = json_decode($pkg['gallery'] ?? '[]', true) ?: [];
        $selectedHotelIds = json_decode($pkg['hotel_ids'] ?? '[]', true) ?: [];

        $decCustom = json_decode($pkg['custom_stays'] ?? '[]', true);
        if (is_array($decCustom)) {
            $customStays = $decCustom;
        } elseif (!empty($pkg['custom_stays']) && is_string($pkg['custom_stays'])) {
            $parts = preg_split('/[;+]/', $pkg['custom_stays']);
            foreach ($parts as $p) {
                if (trim($p)) {
                    $customStays[] = ['name' => trim($p), 'location' => $pkg['location'] ?? '', 'nights' => 'Included Stay', 'rating' => 4.8, 'image' => ''];
                }
            }
        }
    }
}

// Fetch all available hotels from database for linking
$availableHotels = [];
if ($pdo) {
    try {
        $availableHotels = $pdo->query("SELECT id, name, city, star_rating, starting_price FROM `hotels` WHERE `status` = 'active' ORDER BY city ASC, name ASC")->fetchAll();
    } catch (Exception $e) {
        $availableHotels = [];
    }
}

// Default standard inclusions checklist if empty
$inclusionsText = !empty($inclusions) ? implode("\n", $inclusions) : "Pick-up and drop from Airport / Station in private dedicated AC vehicle\nLuxury hotel stays in verified 4-Star / 5-Star properties\nDaily buffet breakfast and chef-curated dinner included\nAll destination sightseeing excursions as per itinerary\nAll interstate toll taxes, state parking charges, fuel, and driver night allowances\n24/7 on-ground tour manager support";

// Default exclusions checklist if empty
$exclusionsText = !empty($exclusions) ? implode("\n", $exclusions) : "Personal expenses, laundry, tips, and portage charges\nOptional adventure activities, safari tickets, cable cars not mentioned in itinerary\nEntry fees to monuments, camera/drone permissions\nAny meals not specified in the inclusions";

// Parse structured tour cancellation and reschedule policies
$parsedPolicy = [
    'summary' => 'Free cancellation and flexible date change available before departure. Tiered refund structure applies thereafter.',
    'rules' => [
        ['timeline' => '15+ Days Before Tour Departure', 'refund' => '100% Refund', 'deduction' => '0% Deduction (Full Token Refund)', 'badge_type' => 'full_refund'],
        ['timeline' => '7 to 14 Days Before Departure', 'refund' => '80% Refund', 'deduction' => '20% Cancellation / Reschedule Fee', 'badge_type' => 'partial_refund'],
        ['timeline' => '3 to 6 Days Before Departure', 'refund' => '50% Refund', 'deduction' => '50% Cancellation Fee', 'badge_type' => 'partial_refund'],
        ['timeline' => 'Within 48 Hours / No-Show', 'refund' => '0% (Non-Refundable)', 'deduction' => '100% Non-Refundable Cutoff', 'badge_type' => 'no_refund']
    ],
    'reschedule_policy' => 'Flexible date reschedule permitted up to 7 days before tour departure without penalty.',
    'medical_policy' => '100% token refund on medical emergencies with verified certificate.'
];

if (!empty($pkg['policies'])) {
    $decPol = json_decode($pkg['policies'], true);
    if (is_array($decPol)) {
        if (!empty($decPol['summary'])) $parsedPolicy['summary'] = $decPol['summary'];
        if (!empty($decPol['rules']) && is_array($decPol['rules'])) $parsedPolicy['rules'] = $decPol['rules'];
        if (!empty($decPol['reschedule_policy'])) $parsedPolicy['reschedule_policy'] = $decPol['reschedule_policy'];
        if (!empty($decPol['medical_policy'])) $parsedPolicy['medical_policy'] = $decPol['medical_policy'];
    } else {
        $parsedPolicy['summary'] = $pkg['policies'];
    }
}

$pageTitle = $isEdit ? "Edit Tour Package: " . htmlspecialchars($pkg['title']) : "Add New Tour Package";
include 'components/head.php';
?>

<style>
    /* Admin Interactive Info Tooltip System matching hotel-edit.php */
    .admin-tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: help;
        vertical-align: middle;
    }
    .admin-tooltip .tooltip-box {
        display: none;
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 7px;
        z-index: 9999;
        pointer-events: none;
        white-space: normal;
        text-align: left;
    }
    .admin-tooltip:hover .tooltip-box {
        display: block;
    }
    .admin-tooltip:hover > i {
        color: #3a5c3d !important;
    }
</style>

<!-- App Wrapper -->
<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        <!-- Header -->
        <?php include 'components/header.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full max-w-5xl space-y-6">

            <!-- Breadcrumb Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-[#e5e4dc] p-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                        <a href="packages.php" class="text-sage-700 hover:underline font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Back to Tour Packages</span>
                        </a>
                        <span>/</span>
                        <span><?php echo $isEdit ? 'Edit Tour' : 'New Tour'; ?></span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        <?php echo $isEdit ? 'Edit Tour Package &amp; Itinerary' : 'Create New Holiday &amp; Tour Package'; ?>
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <a href="packages.php" class="px-3.5 py-1.5 text-xs font-semibold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors">
                        Cancel
                    </a>
                    <button type="submit" form="packageForm" class="px-4 py-1.5 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                        <span>Save Tour Package</span>
                    </button>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 text-xs font-semibold flex items-center gap-2.5 <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                    <span><?php echo htmlspecialchars($alertMessage); ?></span>
                </div>
            <?php endif; ?>

            <!-- Main Tour Package Form -->
            <form id="packageForm" method="POST" action="package-edit.php<?php echo $isEdit ? '?id=' . $pkgId : ''; ?>" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="save_package" value="1">
                <input type="hidden" name="package_id" value="<?php echo htmlspecialchars((string)($pkgId ?? '')); ?>">

                <!-- Section 1: Basic Tour Information & Classification -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">1. Basic Tour Details &amp; Classification</h2>
                        </div>
                        <span class="text-[10px] text-slate-500 font-medium">Customer Facing Information</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Tour Package Title</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Primary headline on tour cards, search results, and top of the details page.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="title" id="packageTitle" value="<?php echo htmlspecialchars($pkg['title']); ?>" required placeholder="e.g. Kashmir Paradise: Shikara, Snow Peaks & Dal Houseboat" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                        </div>

                        <!-- Category Selection + Custom Option -->
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Package Category</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Filters in search pages and categorized tabs across the site. Choose or enter custom.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <?php
                            $knownCategories = ['domestic', 'international', 'honeymoon', 'family', 'pilgrimage', 'adventure', 'luxury'];
                            $currentCat = strtolower($pkg['category'] ?? 'domestic');
                            $isCustomCat = !in_array($currentCat, $knownCategories) && !empty($pkg['category']);
                            ?>
                            <select name="category_select" id="pkgCategorySelect" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium" onchange="handleCategoryChange(this.value)">
                                <option value="domestic" <?php echo ($currentCat === 'domestic' && !$isCustomCat) ? 'selected' : ''; ?>>Domestic Holiday Circuit</option>
                                <option value="international" <?php echo ($currentCat === 'international' && !$isCustomCat) ? 'selected' : ''; ?>>International Holiday Circuit</option>
                                <option value="honeymoon" <?php echo ($currentCat === 'honeymoon' && !$isCustomCat) ? 'selected' : ''; ?>>Honeymoon &amp; Couples</option>
                                <option value="family" <?php echo ($currentCat === 'family' && !$isCustomCat) ? 'selected' : ''; ?>>Family Vacation</option>
                                <option value="pilgrimage" <?php echo ($currentCat === 'pilgrimage' && !$isCustomCat) ? 'selected' : ''; ?>>Pilgrimage &amp; Spiritual</option>
                                <option value="adventure" <?php echo ($currentCat === 'adventure' && !$isCustomCat) ? 'selected' : ''; ?>>Adventure &amp; Trekking</option>
                                <option value="luxury" <?php echo ($currentCat === 'luxury' && !$isCustomCat) ? 'selected' : ''; ?>>Luxury &amp; Royal Stays</option>
                                <option value="custom" <?php echo $isCustomCat ? 'selected' : ''; ?>>+ Other / Type Custom Category...</option>
                            </select>

                            <div id="customCategoryBox" class="<?php echo $isCustomCat ? '' : 'hidden'; ?> mt-2">
                                <label class="block text-[10px] font-bold text-sage-800 mb-0.5">Enter Custom Category Name:</label>
                                <input type="text" name="category_custom" id="categoryCustomInput" value="<?php echo $isCustomCat ? htmlspecialchars($pkg['category']) : ''; ?>" placeholder="e.g. Wildlife Safari, Solo Trips, Weekend Getaway" class="w-full text-xs p-2 bg-sage-50 border border-sage-300 focus:border-sage-700 outline-none font-semibold">
                            </div>
                        </div>

                        <!-- Circuit Type / Theme Selection + Custom Option -->
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Tour Theme / Circuit Type</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Theme tag rendered on the top of the card (e.g. Snow & Alpine, Beach & Islands, Royal Heritage).
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="circuit_type" list="themeSuggestions" value="<?php echo htmlspecialchars($pkg['circuit_type']); ?>" placeholder="e.g. Snow &amp; Alpine Trail, Beach &amp; Islands, Royal Heritage" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                            <datalist id="themeSuggestions">
                                <option value="Beach &amp; Party Circuit">
                                <option value="Alpine Snow Trail">
                                <option value="Royal Heritage &amp; Forts">
                                <option value="Honeymoon &amp; Romance">
                                <option value="Nature &amp; Hill Station">
                                <option value="Wildlife Safari">
                                <option value="Spiritual &amp; Pilgrimage">
                                <option value="Desert &amp; Dune Safari">
                                <option value="Futuristic City Explorer">
                            </datalist>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Marketing Badge Pill</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Colored pill on the top left of images (e.g. Bestseller 2026, Trending, Summer Special).
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="badge" value="<?php echo htmlspecialchars($pkg['badge']); ?>" placeholder="e.g. Bestseller 2026, Trending, Special Offer" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Publication Status</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Visibility:</strong>
                                        Active shows the tour publicly. Inactive hides it from searches and listings.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <select name="status" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                <option value="active" <?php echo ($pkg['status'] === 'active') ? 'selected' : ''; ?>>Active (Visible on Website)</option>
                                <option value="inactive" <?php echo ($pkg['status'] === 'inactive') ? 'selected' : ''; ?>>Draft / Inactive (Hidden)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Tour Overview & Detailed Description -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">2. Tour Overview &amp; Description</h2>
                        </div>
                        <span class="text-[10px] text-slate-400">Rendered in the Tour Overview section</span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Short Summary / Subtitle</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Short hook shown on search result cards and under the hero title.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="subtitle" value="<?php echo htmlspecialchars($pkg['subtitle']); ?>" placeholder="e.g. Explore the Switzerland of India with private chauffeured transfers, gondola assistance, and houseboat hospitality." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Overview Section Heading (Optional)</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Headline above the overview paragraph. If empty, automatically displays 'Discover the Wonders of [Destination]'.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="overview_heading" value="<?php echo htmlspecialchars($pkg['overview_heading'] ?? ''); ?>" placeholder="e.g. Experience the Wonders of Dubai / Discover Paradise in Kashmir" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Full Tour Detailed Overview</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Full text block rendered inside the Tour Overview card on the tour details page.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <textarea name="description" rows="5" placeholder="Write full details about the tour experience, sightseeing highlights, best time to visit, and special memories travelers will make..." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium leading-relaxed"><?php echo htmlspecialchars($pkg['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Route, Destination & Travel Mode -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">3. Tour Route, Destination &amp; Travel Mode</h2>
                        </div>
                        <span class="text-[10px] text-slate-400">Route, State &amp; Transfer details</span>
                    </div>

                    <!-- Autocomplete Search Helper -->
                    <div class="relative bg-cream-50 p-3 border border-[#e5e4dc]">
                        <label class="block text-[11px] font-bold text-slate-800 mb-1">Search Real City to Auto-Fill Destination &amp; State:</label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="pkgLocSearchInput" autocomplete="off" placeholder="Type city or place e.g. Srinagar, Manali, Goa, Dubai, Bali, Switzerland..." class="w-full pl-8 pr-10 py-2 text-xs bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                            <div id="pkgLocSearchSpinner" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                <i class="fa-solid fa-circle-notch fa-spin text-xs text-sage-700"></i>
                            </div>
                        </div>
                        <div id="pkgLocSuggestions" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-[#e5e4dc] shadow-lg max-h-60 overflow-y-auto z-50 divide-y divide-[#e5e4dc]"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Tour Route / Key Stops</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Rendered beside the route icon (Route: ...) on cards and in the tour details header.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="location" id="pkgLocation" value="<?php echo htmlspecialchars($pkg['location']); ?>" required placeholder="e.g. Srinagar • Gulmarg • Pahalgam • Sonmarg OR Delhi → Agra → Jaipur" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>State &amp; Country</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Location pin pill displayed next to rating on cards and header.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="state_country" id="pkgStateCountry" value="<?php echo htmlspecialchars($pkg['state_country']); ?>" placeholder="e.g. Jammu &amp; Kashmir, India OR Dubai, UAE" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                    </div>

                    <div class="space-y-3 pt-2 border-t border-[#e5e4dc]">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                <span>Travel &amp; Transportation Mode</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Determines the transfer badge pill on cards and the 3rd guarantee pill in overview.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <span class="text-[10px] text-slate-400">Search filter &amp; Guarantee pill will auto-reflect</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                            <label class="flex flex-col items-center justify-center p-3 border border-[#e5e4dc] hover:bg-cream-50 cursor-pointer text-center select-none travel-mode-card transition-colors">
                                <input type="radio" name="travel_mode" value="flight" <?php echo ($pkg['travel_mode'] === 'flight') ? 'checked' : ''; ?> class="accent-sage-700 mb-2">
                                <i class="fa-solid fa-plane-departure text-sky-600 text-lg mb-1"></i>
                                <span class="text-xs font-bold text-slate-800">By Flight</span>
                                <span class="text-[9px] text-slate-400 mt-0.5">Airfare Included</span>
                            </label>

                            <label class="flex flex-col items-center justify-center p-3 border border-[#e5e4dc] hover:bg-cream-50 cursor-pointer text-center select-none travel-mode-card transition-colors">
                                <input type="radio" name="travel_mode" value="train" <?php echo ($pkg['travel_mode'] === 'train') ? 'checked' : ''; ?> class="accent-sage-700 mb-2">
                                <i class="fa-solid fa-train text-amber-600 text-lg mb-1"></i>
                                <span class="text-xs font-bold text-slate-800">By Train</span>
                                <span class="text-[9px] text-slate-400 mt-0.5">Rail Circuit</span>
                            </label>

                            <label class="flex flex-col items-center justify-center p-3 border border-[#e5e4dc] hover:bg-cream-50 cursor-pointer text-center select-none travel-mode-card transition-colors">
                                <input type="radio" name="travel_mode" value="bus" <?php echo ($pkg['travel_mode'] === 'bus') ? 'checked' : ''; ?> class="accent-sage-700 mb-2">
                                <i class="fa-solid fa-bus text-emerald-600 text-lg mb-1"></i>
                                <span class="text-xs font-bold text-slate-800">By Volvo Bus</span>
                                <span class="text-[9px] text-slate-400 mt-0.5">Luxury Coach</span>
                            </label>

                            <label class="flex flex-col items-center justify-center p-3 border border-[#e5e4dc] hover:bg-cream-50 cursor-pointer text-center select-none travel-mode-card transition-colors">
                                <input type="radio" name="travel_mode" value="cab" <?php echo ($pkg['travel_mode'] === 'cab') ? 'checked' : ''; ?> class="accent-sage-700 mb-2">
                                <i class="fa-solid fa-car text-sage-700 text-lg mb-1"></i>
                                <span class="text-xs font-bold text-slate-800">By Private Cab</span>
                                <span class="text-[9px] text-slate-400 mt-0.5">Dedicated Chauffeur</span>
                            </label>

                            <label class="flex flex-col items-center justify-center p-3 border border-[#e5e4dc] hover:bg-cream-50 cursor-pointer text-center select-none travel-mode-card transition-colors col-span-2 sm:col-span-1">
                                <input type="radio" name="travel_mode" value="land_only" <?php echo ($pkg['travel_mode'] === 'land_only') ? 'checked' : ''; ?> class="accent-sage-700 mb-2">
                                <i class="fa-solid fa-hotel text-indigo-600 text-lg mb-1"></i>
                                <span class="text-xs font-bold text-slate-800">Land Only</span>
                                <span class="text-[9px] text-slate-400 mt-0.5">Stays &amp; Sightseeing</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Departure / Boarding City</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Indicates the primary departure point (e.g. Ex-Delhi, Ex-Mumbai, or All Major Cities).
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="departure_city" value="<?php echo htmlspecialchars($pkg['departure_city'] ?? 'All Major Cities'); ?>" placeholder="e.g. Ex-Delhi / Ex-Mumbai / All Major Cities" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Duration, Pricing & Token Advance -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">4. Duration, Pricing &amp; Token Advance</h2>
                        </div>
                        <span class="text-[10px] text-slate-400">Pricing &amp; Booking Lock Policy</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Nights</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Total night stays.
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="duration_nights" id="durNights" value="<?php echo htmlspecialchars($pkg['duration_nights']); ?>" min="1" max="30" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] font-bold text-center" oninput="syncDuration()">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Days</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Total tour days.
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="duration_days" id="durDays" value="<?php echo htmlspecialchars($pkg['duration_days']); ?>" min="1" max="31" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] font-bold text-center" oninput="syncDuration()">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Price / Person (₹)</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Base price per adult on twin sharing.
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($pkg['price']); ?>" required class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] font-bold text-sage-900">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Original Price (₹)</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Strike-through price to display discounts.
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="original_price" value="<?php echo htmlspecialchars($pkg['original_price']); ?>" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] text-slate-500">
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Token Advance (₹)</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Exact token advance required per adult to lock reservation.
                                    </span>
                                </span>
                            </label>
                            <input type="number" name="token_advance" value="<?php echo htmlspecialchars($pkg['token_advance']); ?>" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] font-bold text-amber-800 bg-amber-50/50">
                        </div>

                        <input type="hidden" name="duration_text" id="durationText" value="<?php echo htmlspecialchars($pkg['duration']); ?>">
                    </div>
                </div>

                <!-- Section 5: Photos & Tour Gallery -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">5. Cover Photo &amp; Multi-Photo Tour Gallery</h2>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-sage-50 text-sage-800 border border-sage-300 text-[10px] font-bold">
                            <i class="fa-solid fa-image"></i> Recommended: 1200 × 800 px
                        </span>
                    </div>

                    <!-- Part A: Main Cover Photo -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center bg-cream-50 p-3.5 border border-[#e5e4dc]">
                        <div class="md:col-span-2 space-y-2">
                            <div>
                                <label class="flex items-center gap-1.5 text-[10px] font-bold text-slate-700 mb-0.5">
                                    <span>Tour Cover Image URL</span>
                                    <span class="admin-tooltip">
                                        <i class="fa-solid fa-circle-info text-slate-400 text-[10px]"></i>
                                        <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                            Main image for cards and featured magazine slot.
                                        </span>
                                    </span>
                                </label>
                                <input type="url" name="featured_image_url" id="pkgCoverUrl" value="<?php echo htmlspecialchars($pkg['featured_image']); ?>" placeholder="https://images.unsplash.com/..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium" oninput="updatePkgCoverPreview(this.value)">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Or Upload Local Image File (1200 × 800 px)</label>
                                <input type="file" name="featured_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:border file:border-[#e5e4dc] file:text-[11px] file:font-semibold file:bg-white hover:file:bg-cream-100 file:cursor-pointer" onchange="previewLocalImage(this, 'pkgCoverPreview')">
                                <input type="hidden" name="existing_featured_image" value="<?php echo htmlspecialchars($pkg['featured_image']); ?>">
                            </div>
                        </div>
                        <div class="h-28 bg-white border border-[#e5e4dc] flex items-center justify-center overflow-hidden relative">
                            <img id="pkgCoverPreview" src="<?php echo htmlspecialchars(getAdminImageUrl($pkg['featured_image'] ?? '', 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=600&q=80')); ?>" alt="Cover" class="w-full h-full object-cover">
                            <span class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] px-1.5 py-0.5 font-bold">Cover Preview</span>
                        </div>
                    </div>

                    <!-- Part B: Tour Gallery Photos -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                    <span>Tour Gallery Photos</span>
                                    <span class="admin-tooltip">
                                        <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                        <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none">
                                            <strong class="text-amber-400 block mb-0.5">Recommended Count:</strong>
                                            At least 5 photos recommended to complete the 5-photo luxury magazine grid.
                                        </span>
                                    </span>
                                </label>
                                <span class="text-[10px] text-slate-400">Scenic attractions, viewpoints, accommodations, and activities</span>
                            </div>
                            <button type="button" onclick="addPkgGalleryUrlRow()" class="px-2.5 py-1 text-xs font-bold bg-cream-50 text-slate-800 border border-[#e5e4dc] hover:bg-cream-100 transition-colors flex items-center gap-1">
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
                                    <div class="text-xs font-bold text-slate-800">Upload Multiple Tour Photos</div>
                                    <div class="text-[10px] text-slate-400">Select multiple JPG, PNG, or WebP images from your computer</div>
                                </div>
                            </div>
                            <input type="file" name="gallery_files[]" multiple accept="image/*" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:border file:border-sage-300 file:text-xs file:font-bold file:bg-sage-50 file:text-sage-800 hover:file:bg-sage-100 file:cursor-pointer">
                        </div>

                        <!-- Gallery Photos Grid -->
                        <div id="pkgGalleryContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                            <?php if (!empty($gallery)): ?>
                                <?php foreach ($gallery as $gi => $gUrl): ?>
                                    <div class="pkg-gal-card bg-white border border-[#e5e4dc] p-2 space-y-2 relative group">
                                        <div class="h-28 bg-cream-50 border border-[#e5e4dc] overflow-hidden">
                                            <img src="<?php echo htmlspecialchars(getAdminImageUrl($gUrl)); ?>" alt="Gallery" class="w-full h-full object-cover">
                                        </div>
                                        <input type="hidden" name="gallery_existing[]" value="<?php echo htmlspecialchars($gUrl); ?>">
                                        <div class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($gUrl); ?></div>
                                        <button type="button" onclick="this.closest('.pkg-gal-card').remove()" class="absolute top-3 right-3 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] shadow-sm transition-colors" title="Delete Photo">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Key Tour Highlights Builder -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">6. Key Tour Highlights &amp; Features</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="autofillStandardHighlights()" class="px-2.5 py-1 text-xs font-semibold bg-cream-50 text-slate-700 border border-[#e5e4dc] hover:bg-cream-100 transition-colors">
                                <i class="fa-solid fa-wand-magic-sparkles mr-1 text-sage-600"></i> Autofill Standard Highlights
                            </button>
                            <button type="button" onclick="addHighlightRow()" class="px-3 py-1 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white transition-colors flex items-center gap-1">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Add Highlight</span>
                            </button>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500">Rendered in the 'What Makes This Circuit Extraordinary' grid on the package details page.</p>

                    <div id="highlightsContainer" class="space-y-3">
                        <?php if (!empty($highlights)): ?>
                            <?php foreach ($highlights as $hi => $hl): ?>
                                <div class="highlight-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group">
                                    <div class="sm:col-span-3">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Icon</label>
                                        <select name="highlights[<?php echo $hi; ?>][icon]" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
                                            <option value="fa-solid fa-star" <?php echo ($hl['icon'] === 'fa-solid fa-star') ? 'selected' : ''; ?>>Star / Special</option>
                                            <option value="fa-solid fa-sailboat" <?php echo ($hl['icon'] === 'fa-solid fa-sailboat') ? 'selected' : ''; ?>>Sailboat / Houseboat</option>
                                            <option value="fa-solid fa-car" <?php echo ($hl['icon'] === 'fa-solid fa-car') ? 'selected' : ''; ?>>Private Cab / Car</option>
                                            <option value="fa-solid fa-hotel" <?php echo ($hl['icon'] === 'fa-solid fa-hotel') ? 'selected' : ''; ?>>Luxury Hotel / Resort</option>
                                            <option value="fa-solid fa-utensils" <?php echo ($hl['icon'] === 'fa-solid fa-utensils') ? 'selected' : ''; ?>>Meals / Dining</option>
                                            <option value="fa-solid fa-mountain" <?php echo ($hl['icon'] === 'fa-solid fa-mountain') ? 'selected' : ''; ?>>Mountain / Snow</option>
                                            <option value="fa-solid fa-umbrella-beach" <?php echo ($hl['icon'] === 'fa-solid fa-umbrella-beach') ? 'selected' : ''; ?>>Beach / Island</option>
                                            <option value="fa-solid fa-cable-car" <?php echo ($hl['icon'] === 'fa-solid fa-cable-car') ? 'selected' : ''; ?>>Cable Car / Gondola</option>
                                            <option value="fa-solid fa-camera" <?php echo ($hl['icon'] === 'fa-solid fa-camera') ? 'selected' : ''; ?>>Sightseeing / Landmarks</option>
                                            <option value="fa-solid fa-shield-halved" <?php echo ($hl['icon'] === 'fa-solid fa-shield-halved') ? 'selected' : ''; ?>>Verified Protection</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Highlight Title *</label>
                                        <input type="text" name="highlights[<?php echo $hi; ?>][title]" value="<?php echo htmlspecialchars($hl['title']); ?>" required placeholder="e.g. Luxury Houseboat Stay" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold">
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Short Description</label>
                                        <input type="text" name="highlights[<?php echo $hi; ?>][desc]" value="<?php echo htmlspecialchars($hl['desc']); ?>" placeholder="e.g. 1 Night in handcrafted cedar wood luxury houseboat" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
                                    </div>
                                    <div class="sm:col-span-1 flex items-center justify-end pt-2 sm:pt-4">
                                        <button type="button" onclick="this.closest('.highlight-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs transition-colors" title="Delete Highlight">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 7: Day-by-Day Itinerary Builder -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">7. Day-by-Day Detailed Itinerary</h2>
                        </div>
                        <button type="button" onclick="addNewItineraryDay()" class="px-3 py-1.5 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Add Next Day</span>
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-500">Specify destination stop / route transfer for each day, along with activities, meals, and overnight accommodation.</p>

                    <div id="itineraryContainer" class="space-y-4">
                        <?php if (!empty($itinerary)): ?>
                            <?php foreach ($itinerary as $di => $day): ?>
                                <div class="itinerary-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative group">
                                    <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-sage-800 uppercase tracking-wider"><?php echo htmlspecialchars($day['day'] ?? 'Day ' . ($di + 1)); ?></span>
                                            <input type="hidden" name="itinerary[<?php echo $di; ?>][day]" value="<?php echo htmlspecialchars($day['day'] ?? 'Day ' . ($di + 1)); ?>">
                                        </div>
                                        <button type="button" onclick="this.closest('.itinerary-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1" title="Remove Day">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                            <span>Delete Day</span>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="flex items-center gap-1 text-[10px] font-bold text-slate-700 mb-0.5">
                                                <span>Day Route / Destination Stop</span>
                                                <span class="admin-tooltip">
                                                    <i class="fa-solid fa-circle-info text-slate-400 text-[10px]"></i>
                                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                                        Location or transfer route shown in the day badge (e.g. 'Arrival at Srinagar' or 'Srinagar to Gulmarg').
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="text" name="itinerary[<?php echo $di; ?>][location]" value="<?php echo htmlspecialchars($day['location'] ?? ''); ?>" placeholder="e.g. Arrival at Srinagar OR Srinagar → Gulmarg (52 km)" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold focus:border-sage-700 outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">
                                                Day Title / Headline <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" name="itinerary[<?php echo $di; ?>][title]" value="<?php echo htmlspecialchars($day['title']); ?>" required placeholder="e.g. Romantic Sunset Shikara Ride &amp; Houseboat Check-in" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold focus:border-sage-700 outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Activities &amp; Sightseeing Schedule</label>
                                        <textarea name="itinerary[<?php echo $di; ?>][desc]" rows="3" placeholder="Describe the day's sightseeing, scenic drives, landmarks, and highlights..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium"><?php echo htmlspecialchars($day['desc']); ?></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Meals Included</label>
                                            <input type="text" name="itinerary[<?php echo $di; ?>][meals]" value="<?php echo htmlspecialchars($day['meals'] ?? 'Breakfast & Dinner Included'); ?>" placeholder="Breakfast &amp; Dinner Included" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Overnight Stay</label>
                                            <input type="text" name="itinerary[<?php echo $di; ?>][stay]" value="<?php echo htmlspecialchars($day['stay'] ?? '4★ Hotel Stay'); ?>" placeholder="4★ Hotel in Srinagar" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default Initial Day 1 -->
                            <div class="itinerary-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative group">
                                <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                                    <span class="text-xs font-bold text-sage-800 uppercase tracking-wider">Day 01</span>
                                    <input type="hidden" name="itinerary[0][day]" value="Day 01">
                                    <button type="button" onclick="this.closest('.itinerary-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1" title="Remove Day">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        <span>Delete Day</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Day Route / Destination Stop</label>
                                        <input type="text" name="itinerary[0][location]" value="Arrival at Destination" placeholder="e.g. Arrival at Destination" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold focus:border-sage-700 outline-none">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Day Title / Headline <span class="text-rose-500">*</span></label>
                                        <input type="text" name="itinerary[0][title]" value="Arrival &amp; Welcome Reception" required placeholder="e.g. Arrival at Destination &amp; Evening Leisure" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold focus:border-sage-700 outline-none">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Activities &amp; Sightseeing Schedule</label>
                                    <textarea name="itinerary[0][desc]" rows="3" placeholder="Describe arrival transfer, hotel check-in, sunset views, or evening stroll..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">Pick-up from Airport/Railway station by dedicated private AC cab. Transfer to your pre-booked luxury stay, check in, and enjoy evening leisure with welcome tea.</textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Meals Included</label>
                                        <input type="text" name="itinerary[0][meals]" value="Dinner Included" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Overnight Stay</label>
                                        <input type="text" name="itinerary[0][stay]" value="4★ Luxury Resort" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 8: Accommodations & Hotel Stays -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">8. Accommodations &amp; Hotels</h2>
                        </div>
                        <span class="text-[10px] text-slate-400">Database Hotel Linking &amp; Direct Custom Stays</span>
                    </div>

                    <!-- Part A: Link from database -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="flex items-center gap-1.5 text-xs font-bold text-slate-800 uppercase tracking-wider">
                                <span>A) Link Properties from Website Hotel Database:</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Select existing verified hotels from your database. Their photos and details will be linked automatically.
                                    </span>
                                </span>
                            </h3>
                            <span class="text-[10px] text-slate-400">Optional</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto p-1.5 border border-[#e5e4dc] bg-cream-50/30">
                            <?php if (!empty($availableHotels)): ?>
                                <?php foreach ($availableHotels as $ht): ?>
                                    <?php $isLinked = in_array($ht['id'], $selectedHotelIds); ?>
                                    <label class="flex items-start gap-2.5 p-2 bg-white border border-[#e5e4dc] hover:bg-cream-100 cursor-pointer select-none transition-colors">
                                        <input type="checkbox" name="hotel_ids[]" value="<?php echo $ht['id']; ?>" <?php echo $isLinked ? 'checked' : ''; ?> class="mt-0.5 accent-sage-700">
                                        <div class="text-xs min-w-0">
                                            <div class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($ht['name']); ?></div>
                                            <div class="text-[10px] text-slate-500"><?php echo htmlspecialchars($ht['city']); ?> • <?php echo $ht['star_rating']; ?>★</div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-span-3 text-center text-xs text-slate-400 p-3">No active hotels found in database. You can add custom hotels below.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Part B: Custom Hotel Stays Direct Entry -->
                    <div class="space-y-3 pt-3 border-t border-[#e5e4dc]">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="flex items-center gap-1.5 text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    <span>B) Add Custom Stays Directly for This Tour:</span>
                                    <span class="admin-tooltip">
                                        <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                        <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none">
                                            Add property name, location, photo, and nights directly without needing to create a separate entry in the hotels manager.
                                        </span>
                                    </span>
                                </h3>
                                <p class="text-[10px] text-slate-400">Add property name, location, and photo URL directly.</p>
                            </div>
                            <button type="button" onclick="addCustomStayRow()" class="px-2.5 py-1 text-xs font-bold bg-sage-50 text-sage-800 border border-sage-300 hover:bg-sage-100 flex items-center gap-1">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Add Custom Hotel</span>
                            </button>
                        </div>

                        <div id="customStaysContainer" class="space-y-2.5">
                            <?php if (!empty($customStays)): ?>
                                <?php foreach ($customStays as $csi => $cStay): ?>
                                    <div class="custom-stay-row grid grid-cols-1 sm:grid-cols-12 gap-2 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group">
                                        <div class="sm:col-span-3">
                                            <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Hotel Name *</label>
                                            <input type="text" name="custom_stays[<?php echo $csi; ?>][name]" value="<?php echo htmlspecialchars($cStay['name']); ?>" required placeholder="e.g. Pine View Resort" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Location / City</label>
                                            <input type="text" name="custom_stays[<?php echo $csi; ?>][location]" value="<?php echo htmlspecialchars($cStay['location'] ?? ''); ?>" placeholder="e.g. Pahalgam / Dal Lake" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Nights Count</label>
                                            <input type="text" name="custom_stays[<?php echo $csi; ?>][nights]" value="<?php echo htmlspecialchars($cStay['nights'] ?? '2 Nights'); ?>" placeholder="e.g. 2 Nights" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Photo URL</label>
                                            <input type="url" name="custom_stays[<?php echo $csi; ?>][image]" value="<?php echo htmlspecialchars($cStay['image'] ?? ''); ?>" placeholder="https://..." class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
                                        </div>
                                        <div class="sm:col-span-1 flex items-center justify-end pt-2 sm:pt-4">
                                            <button type="button" onclick="this.closest('.custom-stay-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs transition-colors" title="Delete Hotel">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 9: Tour Inclusions & Exclusions -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">9. Tour Inclusions &amp; Exclusions</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Inclusions (One item per line)</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Each line displays with a green checkmark under What's Included.
                                    </span>
                                </span>
                            </label>
                            <textarea name="inclusions_text" rows="6" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium leading-relaxed"><?php echo htmlspecialchars($inclusionsText); ?></textarea>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Exclusions (One item per line)</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Each line displays with a red cross under What's Excluded.
                                    </span>
                                </span>
                            </label>
                            <textarea name="exclusions_text" rows="6" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium leading-relaxed"><?php echo htmlspecialchars($exclusionsText); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 10: Cancellation & Date Reschedule Policy -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">10. Cancellation &amp; Date Reschedule Policy</h2>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                            <span>Cancellation Headline / Short Summary</span>
                            <span class="admin-tooltip">
                                <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                    Short assurance headline above refund timeline cards.
                                </span>
                            </span>
                        </label>
                        <input type="text" name="policy_summary" value="<?php echo htmlspecialchars($parsedPolicy['summary']); ?>" placeholder="e.g. Free cancellation and date reschedule options available before departure." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                    </div>

                    <!-- Tiered Refund & Cancellation Schedule Table -->
                    <div class="space-y-3 pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-clock-rotate-left text-sage-700"></i>
                                    <span>Tour Cancellation &amp; Refund Schedule (Days vs Refund %)</span>
                                </h3>
                                <p class="text-[10px] text-slate-400">Specify exact refund percentage and deduction fees for each cancellation window.</p>
                            </div>
                            <button type="button" onclick="addPkgPolicyTierRow()" class="px-2.5 py-1 text-xs font-bold bg-sage-50 text-sage-800 border border-sage-300 hover:bg-sage-100 flex items-center gap-1.5 transition-colors self-start sm:self-auto">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Add Policy Tier</span>
                            </button>
                        </div>

                        <div id="pkgPolicyTiersContainer" class="space-y-2.5">
                            <?php foreach ($parsedPolicy['rules'] as $ti => $tier): ?>
                                <div class="pkg-policy-tier-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group">
                                    <div class="sm:col-span-4">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Cancellation Window</label>
                                        <input type="text" name="cancellation_tiers[<?php echo $ti; ?>][timeline]" value="<?php echo htmlspecialchars($tier['timeline']); ?>" required placeholder="e.g. 15+ Days Before Tour Departure" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold focus:border-sage-700 outline-none">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Refund Return</label>
                                        <input type="text" name="cancellation_tiers[<?php echo $ti; ?>][refund]" value="<?php echo htmlspecialchars($tier['refund']); ?>" required placeholder="e.g. 100% Refund" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-bold text-sage-900 focus:border-sage-700 outline-none">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Deduction / Fee</label>
                                        <input type="text" name="cancellation_tiers[<?php echo $ti; ?>][deduction]" value="<?php echo htmlspecialchars($tier['deduction']); ?>" placeholder="e.g. 0% Deduction" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] text-slate-600 focus:border-sage-700 outline-none">
                                    </div>
                                    <div class="sm:col-span-2 flex items-center justify-between gap-1.5 pt-3 sm:pt-0">
                                        <select name="cancellation_tiers[<?php echo $ti; ?>][badge_type]" class="w-full text-[11px] p-1.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                            <option value="full_refund" <?php echo (($tier['badge_type'] ?? '') === 'full_refund') ? 'selected' : ''; ?>>Full Refund</option>
                                            <option value="partial_refund" <?php echo (($tier['badge_type'] ?? '') === 'partial_refund') ? 'selected' : ''; ?>>Partial Refund</option>
                                            <option value="no_refund" <?php echo (($tier['badge_type'] ?? '') === 'no_refund') ? 'selected' : ''; ?>>Non-Refundable</option>
                                        </select>
                                        <button type="button" onclick="this.closest('.pkg-policy-tier-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs shrink-0 transition-colors" title="Delete Tier">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Additional Terms: Reschedule & Emergency Medical Policies -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-[#e5e4dc]">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Date Reschedule / Change Terms</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Terms for travelers wishing to change departure dates.
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="reschedule_policy" value="<?php echo htmlspecialchars($parsedPolicy['reschedule_policy']); ?>" placeholder="e.g. Flexible date reschedule permitted up to 7 days before tour departure without penalty." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Emergency Medical Protection</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 text-[11px]"></i>
                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[10px] rounded shadow-xl border border-slate-700 pointer-events-none">
                                        Protection and refund rules for certified medical emergencies.
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="medical_policy" value="<?php echo htmlspecialchars($parsedPolicy['medical_policy']); ?>" placeholder="e.g. 100% token refund on medical emergencies with verified certificate." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bottom Save Bar -->
                <div class="flex items-center justify-between p-4 bg-white border border-[#e5e4dc]">
                    <a href="packages.php" class="px-4 py-2 text-xs font-semibold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors">
                        Cancel &amp; Return
                    </a>
                    <button type="submit" class="px-6 py-2.5 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span><?php echo $isEdit ? 'Save Tour Changes' : 'Publish Tour Package'; ?></span>
                    </button>
                </div>

            </form>

        </main>
    </div>
</div>

<script>
    let dayIndex = <?php echo max(count($itinerary), 1); ?>;
    let galIndex = <?php echo max(count($gallery), 1); ?>;
    let hlIndex = <?php echo max(count($highlights), 1); ?>;
    let stayIndex = <?php echo max(count($customStays), 1); ?>;
    let pkgPolicyTierIndex = <?php echo max(count($parsedPolicy['rules']), 1); ?>;

    function handleCategoryChange(val) {
        const box = document.getElementById('customCategoryBox');
        const input = document.getElementById('categoryCustomInput');
        if (val === 'custom') {
            box.classList.remove('hidden');
            if (input) input.focus();
        } else {
            box.classList.add('hidden');
            if (input) input.value = '';
        }

        if (val === 'international') {
            const flightRadio = document.querySelector('input[name="travel_mode"][value="flight"]');
            if (flightRadio) flightRadio.checked = true;
        }
    }

    function addHighlightRow(icon = 'fa-solid fa-star', title = '', desc = '') {
        hlIndex++;
        const container = document.getElementById('highlightsContainer');
        const div = document.createElement('div');
        div.className = 'highlight-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group';
        div.innerHTML = `
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Icon</label>
                <select name="highlights[${hlIndex}][icon]" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
                    <option value="fa-solid fa-star" ${icon === 'fa-solid fa-star' ? 'selected' : ''}>Star / Special</option>
                    <option value="fa-solid fa-sailboat" ${icon === 'fa-solid fa-sailboat' ? 'selected' : ''}>Sailboat / Houseboat</option>
                    <option value="fa-solid fa-car" ${icon === 'fa-solid fa-car' ? 'selected' : ''}>Private Cab / Car</option>
                    <option value="fa-solid fa-hotel" ${icon === 'fa-solid fa-hotel' ? 'selected' : ''}>Luxury Hotel / Resort</option>
                    <option value="fa-solid fa-utensils" ${icon === 'fa-solid fa-utensils' ? 'selected' : ''}>Meals / Dining</option>
                    <option value="fa-solid fa-mountain" ${icon === 'fa-solid fa-mountain' ? 'selected' : ''}>Mountain / Snow</option>
                    <option value="fa-solid fa-umbrella-beach" ${icon === 'fa-solid fa-umbrella-beach' ? 'selected' : ''}>Beach / Island</option>
                    <option value="fa-solid fa-cable-car" ${icon === 'fa-solid fa-cable-car' ? 'selected' : ''}>Cable Car / Gondola</option>
                    <option value="fa-solid fa-camera" ${icon === 'fa-solid fa-camera' ? 'selected' : ''}>Sightseeing / Landmarks</option>
                    <option value="fa-solid fa-shield-halved" ${icon === 'fa-solid fa-shield-halved' ? 'selected' : ''}>Verified Protection</option>
                </select>
            </div>
            <div class="sm:col-span-4">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Highlight Title *</label>
                <input type="text" name="highlights[${hlIndex}][title]" value="${title}" required placeholder="e.g. Luxury Stay Experience" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Short Description</label>
                <input type="text" name="highlights[${hlIndex}][desc]" value="${desc}" placeholder="e.g. Handcrafted verified accommodation with scenic views" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
            </div>
            <div class="sm:col-span-1 flex items-center justify-end pt-2 sm:pt-4">
                <button type="button" onclick="this.closest('.highlight-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs transition-colors" title="Delete Highlight">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    }

    function autofillStandardHighlights() {
        const container = document.getElementById('highlightsContainer');
        container.innerHTML = '';
        addHighlightRow('fa-solid fa-hotel', 'Handpicked Verified Stays', 'Certified 4-Star & 5-Star premium accommodations throughout');
        addHighlightRow('fa-solid fa-car', 'Dedicated AC Vehicle', 'Private chauffeured transfers for all sightseeing & airport pickups');
        addHighlightRow('fa-solid fa-utensils', 'Daily Breakfast & Dinner', 'Freshly prepared buffet meals included throughout tour');
        addHighlightRow('fa-solid fa-shield-halved', 'Guaranteed Token Advance', 'Lock your holiday dates with an easy initial token amount');
    }

    function addCustomStayRow() {
        stayIndex++;
        const container = document.getElementById('customStaysContainer');
        const div = document.createElement('div');
        div.className = 'custom-stay-row grid grid-cols-1 sm:grid-cols-12 gap-2 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group';
        div.innerHTML = `
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Hotel Name *</label>
                <input type="text" name="custom_stays[${stayIndex}][name]" required placeholder="e.g. Grand Mountain Resort" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Location / City</label>
                <input type="text" name="custom_stays[${stayIndex}][location]" placeholder="e.g. City Center / Beachfront" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Nights Count</label>
                <input type="text" name="custom_stays[${stayIndex}][nights]" value="2 Nights" placeholder="e.g. 2 Nights" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Photo URL</label>
                <input type="url" name="custom_stays[${stayIndex}][image]" placeholder="https://..." class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc]">
            </div>
            <div class="sm:col-span-1 flex items-center justify-end pt-2 sm:pt-4">
                <button type="button" onclick="this.closest('.custom-stay-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs transition-colors" title="Delete Hotel">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    }

    function addPkgPolicyTierRow() {
        pkgPolicyTierIndex++;
        const container = document.getElementById('pkgPolicyTiersContainer');
        const div = document.createElement('div');
        div.className = 'pkg-policy-tier-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group';
        div.innerHTML = `
            <div class="sm:col-span-4">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Cancellation Window</label>
                <input type="text" name="cancellation_tiers[${pkgPolicyTierIndex}][timeline]" required placeholder="e.g. 10 to 14 Days Before Departure" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold focus:border-sage-700 outline-none">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Refund Return</label>
                <input type="text" name="cancellation_tiers[${pkgPolicyTierIndex}][refund]" required placeholder="e.g. 80% Refund" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-bold text-sage-900 focus:border-sage-700 outline-none">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Deduction / Fee</label>
                <input type="text" name="cancellation_tiers[${pkgPolicyTierIndex}][deduction]" placeholder="e.g. 20% Fee" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] text-slate-600 focus:border-sage-700 outline-none">
            </div>
            <div class="sm:col-span-2 flex items-center justify-between gap-1.5 pt-3 sm:pt-0">
                <select name="cancellation_tiers[${pkgPolicyTierIndex}][badge_type]" class="w-full text-[11px] p-1.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                    <option value="full_refund">Full Refund</option>
                    <option value="partial_refund" selected>Partial Refund</option>
                    <option value="no_refund">Non-Refundable</option>
                </select>
                <button type="button" onclick="this.closest('.pkg-policy-tier-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs shrink-0 transition-colors" title="Delete Tier">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
        if (container) {
            container.appendChild(div);
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

    function updatePkgCoverPreview(url) {
        const prev = document.getElementById('pkgCoverPreview');
        if (prev && url && url.trim().length > 5) {
            prev.src = url.trim();
        }
    }

    function addPkgGalleryUrlRow() {
        galIndex++;
        const container = document.getElementById('pkgGalleryContainer');
        const card = document.createElement('div');
        card.className = 'pkg-gal-card bg-white border border-[#e5e4dc] p-2 space-y-2 relative group';
        card.innerHTML = `
            <div class="h-28 bg-cream-50 border border-[#e5e4dc] overflow-hidden flex items-center justify-center">
                <img src="https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=600&q=80" alt="Gallery Preview" class="w-full h-full object-cover pkg-gal-img">
            </div>
            <div>
                <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Image URL</label>
                <input type="url" name="gallery_urls[]" required placeholder="https://images.unsplash.com/..." class="w-full text-[11px] p-1.5 bg-white border border-[#e5e4dc]" oninput="this.closest('.pkg-gal-card').querySelector('.pkg-gal-img').src = this.value">
            </div>
            <button type="button" onclick="this.closest('.pkg-gal-card').remove()" class="absolute top-3 right-3 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] shadow-sm transition-colors" title="Delete Photo">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(card);
    }

    function addNewItineraryDay() {
        dayIndex++;
        const container = document.getElementById('itineraryContainer');
        const div = document.createElement('div');
        div.className = 'itinerary-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative group';
        const dayLabel = 'Day ' + (dayIndex < 10 ? '0' + dayIndex : dayIndex);
        div.innerHTML = `
            <div class="flex items-center justify-between pb-2 border-b border-[#e5e4dc]">
                <span class="text-xs font-bold text-sage-800 uppercase tracking-wider">${dayLabel}</span>
                <input type="hidden" name="itinerary[${dayIndex}][day]" value="${dayLabel}">
                <button type="button" onclick="this.closest('.itinerary-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1" title="Remove Day">
                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                    <span>Delete Day</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">
                        Day Route / Destination Stop
                    </label>
                    <input type="text" name="itinerary[${dayIndex}][location]" placeholder="e.g. Next Destination Sightseeing" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold focus:border-sage-700 outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">
                        Day Title / Headline <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="itinerary[${dayIndex}][title]" required placeholder="e.g. Full Day Sightseeing &amp; Excursions" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold focus:border-sage-700 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Activities &amp; Sightseeing Schedule</label>
                <textarea name="itinerary[${dayIndex}][desc]" rows="3" placeholder="Describe the day's sightseeing, scenic drives, landmarks, and highlights..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Meals Included</label>
                    <input type="text" name="itinerary[${dayIndex}][meals]" value="Breakfast &amp; Dinner Included" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Overnight Stay</label>
                    <input type="text" name="itinerary[${dayIndex}][stay]" value="4★ Hotel Stay" class="w-full text-xs p-2 bg-white border border-[#e5e4dc]">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    // Live Real-Time Places Geocoding Autocomplete
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('pkgLocSearchInput');
        const suggestionsBox = document.getElementById('pkgLocSuggestions');
        const spinner = document.getElementById('pkgLocSearchSpinner');
        const locField = document.getElementById('pkgLocation');
        const stateCountryField = document.getElementById('pkgStateCountry');
        const titleField = document.getElementById('packageTitle');

        let debounceTimer = null;
        if (!input || !suggestionsBox) return;

        input.addEventListener('input', function() {
            const val = this.value.trim();
            clearTimeout(debounceTimer);

            if (val.length < 2) {
                suggestionsBox.classList.add('hidden');
                suggestionsBox.innerHTML = '';
                return;
            }

            if (spinner) spinner.classList.remove('hidden');

            debounceTimer = setTimeout(() => {
                fetch(`../api/places.php?q=${encodeURIComponent(val)}&type=package`)
                    .then(res => res.json())
                    .then(data => {
                        if (spinner) spinner.classList.add('hidden');
                        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                            renderSuggestions(data.data);
                        } else {
                            suggestionsBox.innerHTML = '<div class="p-3 text-xs text-slate-400 text-center">No matching places found. You can enter manually below.</div>';
                            suggestionsBox.classList.remove('hidden');
                        }
                    })
                    .catch(err => {
                        if (spinner) spinner.classList.add('hidden');
                        suggestionsBox.classList.add('hidden');
                    });
            }, 250);
        });

        function renderSuggestions(items) {
            suggestionsBox.innerHTML = '';
            items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'p-3 hover:bg-cream-100 cursor-pointer flex items-center justify-between gap-3 transition-colors';

                const hasDbPill = item.has_packages ? `<span class="text-[9px] font-bold bg-sage-50 text-sage-800 border border-sage-200 px-1.5 py-0.5">Tour in DB</span>` : '';

                div.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-6 h-6 bg-sage-50 text-sage-700 border border-sage-200 flex items-center justify-center shrink-0 text-xs">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 truncate">${escapeHtml(item.name || item.city)}</div>
                            <div class="text-[10px] text-slate-400 truncate">${escapeHtml(item.full_address || '')}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        ${hasDbPill}
                        <span class="text-[10px] text-sage-700 font-bold hover:underline flex items-center gap-1">
                            <span>Auto-Fill</span>
                            <i class="fa-solid fa-arrow-right text-[9px]"></i>
                        </span>
                    </div>
                `;

                div.addEventListener('click', function() {
                    if (locField) locField.value = item.name || item.city;
                    if (stateCountryField) {
                        const scParts = [];
                        if (item.state) scParts.push(item.state);
                        if (item.country) scParts.push(item.country);
                        stateCountryField.value = scParts.join(', ') || 'India';
                    }
                    if (titleField && !titleField.value.trim()) {
                        titleField.value = `${item.name || item.city} Luxury Holiday Circuit`;
                    }
                    input.value = item.full_address || item.name;
                    suggestionsBox.classList.add('hidden');

                    [locField, stateCountryField].forEach(f => {
                        if (f) {
                            f.classList.add('bg-sage-50', 'border-sage-600');
                            setTimeout(() => f.classList.remove('bg-sage-50', 'border-sage-600'), 1200);
                        }
                    });
                });
                suggestionsBox.appendChild(div);
            });
            suggestionsBox.classList.remove('hidden');
        }

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('hidden');
            }
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    });
</script>

<?php include 'components/footer.php'; ?>