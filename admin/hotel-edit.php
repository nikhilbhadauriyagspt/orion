<?php
/**
 * Admin Dedicated Add / Edit Hotel Controller & View
 * Clean full-page form with zero popup confusion, instant visual hints,
 * location autocomplete, dynamic room builder with room photos, and multi-photo resort gallery.
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

$uploadDir = __DIR__ . '/../uploads/hotels/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$hotelId = isset($_GET['id']) && (int)$_GET['id'] > 0 ? (int)$_GET['id'] : null;
$isEdit = !empty($hotelId);

if ($pdo) {
    try {
        $pdo->exec("ALTER TABLE `hotels` MODIFY COLUMN `star_rating` DECIMAL(3,1) DEFAULT 4.5");
    } catch (Exception $e) {}
}

// 1. HANDLE POST SAVE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hotel'])) {
    if ($pdo) {
        try {
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }
            $propertyType = trim($_POST['property_type'] ?? '');
            if ($propertyType === '__custom__' || empty($propertyType)) {
                $propertyType = trim($_POST['property_type_custom'] ?? '');
            }
            if (empty($propertyType)) {
                $propertyType = '5★ Luxury Resort';
            }
            $starRating = (float)($_POST['star_rating'] ?? 4.8);
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $country = trim($_POST['country'] ?? 'India');
            $address = trim($_POST['address'] ?? '');
            $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
            $startingPrice = (float)($_POST['starting_price'] ?? 0);
            $originalPrice = (float)($_POST['original_price'] ?? ($startingPrice * 1.25));
            $discountPercent = $originalPrice > $startingPrice ? round((($originalPrice - $startingPrice) / $originalPrice) * 100) : 0;
            $badge = trim($_POST['badge'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            $checkinTime = trim($_POST['checkin_time'] ?? '02:00 PM');
            $checkoutTime = trim($_POST['checkout_time'] ?? '11:00 AM');
            $description = trim($_POST['description'] ?? '');

            // Process Cancellation & Refund Policies Structure
            $policySummary = trim($_POST['policy_summary'] ?? ($_POST['policies'] ?? 'Free cancellation up to 48 hours before check-in.'));
            $childPolicy = trim($_POST['child_policy'] ?? 'Children up to 6 years stay complimentary in existing bedding.');
            $idProofPolicy = trim($_POST['id_proof_policy'] ?? 'Government-issued photo identification required for all adult guests.');

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
                    ['timeline' => '15+ Days Before Check-in', 'refund' => '100% Refund', 'deduction' => '0% Deduction (Full Refund)', 'badge_type' => 'full_refund'],
                    ['timeline' => '7 to 14 Days Before Check-in', 'refund' => '70% Refund', 'deduction' => '30% Cancellation Fee', 'badge_type' => 'partial_refund'],
                    ['timeline' => '3 to 6 Days Before Check-in', 'refund' => '30% Refund', 'deduction' => '70% Cancellation Fee', 'badge_type' => 'partial_refund'],
                    ['timeline' => 'Within 48 Hours / No-Show', 'refund' => '0% (Non-Refundable)', 'deduction' => '100% Non-Refundable', 'badge_type' => 'no_refund']
                ];
            }

            $policyData = [
                'summary' => $policySummary,
                'rules' => $cancellationTiers,
                'child_policy' => $childPolicy,
                'id_proof' => $idProofPolicy
            ];
            $policies = json_encode($policyData, JSON_UNESCAPED_UNICODE);

            // Process Amenities Array to JSON (Save only valid checked items)
            $amenitiesPost = [];
            if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
                foreach ($_POST['amenities'] as $amRow) {
                    $amName = trim($amRow['name'] ?? '');
                    if (!empty($amName)) {
                        $amenitiesPost[] = [
                            'name' => $amName,
                            'icon' => !empty($amRow['icon']) ? trim($amRow['icon']) : 'fa-solid fa-circle-check',
                            'desc' => trim($amRow['desc'] ?? 'Complimentary guest inclusion')
                        ];
                    }
                }
            }
            $amenitiesJson = json_encode($amenitiesPost, JSON_UNESCAPED_UNICODE);

            // Handle Featured Cover Image Upload or URL
            $featuredImage = trim($_POST['existing_featured_image'] ?? '');
            if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['featured_image_file']['tmp_name'];
                $fileName = time() . '_cover_' . preg_replace('/[^A-Za-z0-9._-]/', '', $_FILES['featured_image_file']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $featuredImage = 'uploads/hotels/' . $fileName;
                }
            } elseif (!empty($_POST['featured_image_url'])) {
                $featuredImage = trim($_POST['featured_image_url']);
            }

            if (empty($name)) {
                throw new Exception("Hotel Name is required.");
            }
            if (empty($city)) {
                throw new Exception("Destination City is required.");
            }

            if ($isEdit) {
                // Update Hotel
                $sql = "UPDATE `hotels` SET 
                    `name` = ?, `slug` = ?, `star_rating` = ?, `property_type` = ?,
                    `city` = ?, `state` = ?, `country` = ?, `address` = ?,
                    `latitude` = ?, `longitude` = ?, `featured_image` = ?,
                    `starting_price` = ?, `original_price` = ?, `discount_percent` = ?,
                    `amenities` = ?, `description` = ?, `policies` = ?,
                    `checkin_time` = ?, `checkout_time` = ?, `badge` = ?, `status` = ?
                    WHERE `id` = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $slug, $starRating, $propertyType,
                    $city, $state, $country, $address,
                    $latitude, $longitude, $featuredImage,
                    $startingPrice, $originalPrice, $discountPercent,
                    $amenitiesJson, $description, $policies,
                    $checkinTime, $checkoutTime, $badge, $status,
                    $hotelId
                ]);
                $alertMessage = "Hotel '{$name}' updated successfully!";
            } else {
                // Insert New Hotel
                $sql = "INSERT INTO `hotels` (
                    `name`, `slug`, `star_rating`, `property_type`,
                    `city`, `state`, `country`, `address`,
                    `latitude`, `longitude`, `featured_image`,
                    `starting_price`, `original_price`, `discount_percent`,
                    `amenities`, `description`, `policies`,
                    `checkin_time`, `checkout_time`, `badge`, `status`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $slug, $starRating, $propertyType,
                    $city, $state, $country, $address,
                    $latitude, $longitude, $featuredImage,
                    $startingPrice, $originalPrice, $discountPercent,
                    $amenitiesJson, $description, $policies,
                    $checkinTime, $checkoutTime, $badge, $status
                ]);
                $hotelId = (int)$pdo->lastInsertId();
                $isEdit = true;
                $alertMessage = "Hotel '{$name}' created successfully!";
            }

            // 2. HANDLE RESORT GALLERY IMAGES (hotel_images table)
            // A. Collect existing gallery images retained
            $finalGallery = [];
            if (isset($_POST['gallery_existing']) && is_array($_POST['gallery_existing'])) {
                foreach ($_POST['gallery_existing'] as $gRow) {
                    $url = trim($gRow['url'] ?? '');
                    $caption = trim($gRow['caption'] ?? '');
                    if (!empty($url)) {
                        $finalGallery[] = [
                            'url' => $url,
                            'caption' => $caption
                        ];
                    }
                }
            }

            // B. Collect new URL-based gallery images
            if (isset($_POST['gallery_urls']) && is_array($_POST['gallery_urls'])) {
                foreach ($_POST['gallery_urls'] as $gUrlRow) {
                    $url = trim($gUrlRow['url'] ?? '');
                    $caption = trim($gUrlRow['caption'] ?? '');
                    if (!empty($url)) {
                        $finalGallery[] = [
                            'url' => $url,
                            'caption' => $caption
                        ];
                    }
                }
            }

            // C. Handle multiple file uploads for resort gallery
            if (isset($_FILES['gallery_files']) && is_array($_FILES['gallery_files']['name'])) {
                $fileCount = count($_FILES['gallery_files']['name']);
                for ($fi = 0; $fi < $fileCount; $fi++) {
                    if ($_FILES['gallery_files']['error'][$fi] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['gallery_files']['tmp_name'][$fi];
                        $origName = $_FILES['gallery_files']['name'][$fi];
                        $cleanName = time() . '_gal_' . $fi . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $origName);
                        $targetPath = $uploadDir . $cleanName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $finalGallery[] = [
                                'url' => 'uploads/hotels/' . $cleanName,
                                'caption' => pathinfo($origName, PATHINFO_FILENAME)
                            ];
                        }
                    }
                }
            }

            // Save gallery images to database
            $pdo->prepare("DELETE FROM `hotel_images` WHERE `hotel_id` = ?")->execute([$hotelId]);
            if (!empty($finalGallery)) {
                $gInsert = $pdo->prepare("INSERT INTO `hotel_images` (`hotel_id`, `image_url`, `caption`, `sort_order`) VALUES (?, ?, ?, ?)");
                foreach ($finalGallery as $sIdx => $gItem) {
                    $gInsert->execute([$hotelId, $gItem['url'], $gItem['caption'] ?: 'Resort View', $sIdx]);
                }
            }

            // 3. HANDLE DYNAMIC ROOM CATEGORIES WITH ROOM IMAGES (hotel_rooms table)
            if (isset($_POST['rooms']) && is_array($_POST['rooms'])) {
                $pdo->prepare("DELETE FROM `hotel_rooms` WHERE `hotel_id` = ?")->execute([$hotelId]);
                $roomStmt = $pdo->prepare("INSERT INTO `hotel_rooms` (
                    `hotel_id`, `room_name`, `room_type`, `price_per_night`, `original_price`, 
                    `max_adults`, `max_children`, `bed_type`, `room_size`, `meal_plan`, `image_url`, `status`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')");

                foreach ($_POST['rooms'] as $rIdx => $room) {
                    $rName = trim($room['room_name'] ?? '');
                    if (!empty($rName)) {
                        $rType = trim($room['room_type'] ?? 'Deluxe Room');
                        $rPrice = (float)($room['price_per_night'] ?? $startingPrice);
                        $rOrig = (float)($room['original_price'] ?? ($rPrice * 1.25));
                        $rAdults = (int)($room['max_adults'] ?? 2);
                        $rChildren = (int)($room['max_children'] ?? 1);
                        $rBed = trim($room['bed_type'] ?? '1 King Bed');
                        $rSize = trim($room['room_size'] ?? '380 sq ft');
                        $rMeal = trim($room['meal_plan'] ?? 'Free Breakfast Included');

                        // Room Image Handling (File Upload or URL or Existing)
                        $rImage = trim($room['image_url'] ?? '');
                        if (empty($rImage)) {
                            $rImage = trim($room['existing_image'] ?? '');
                        }

                        // Check if file uploaded specifically for this room
                        if (isset($_FILES['room_files']) && isset($_FILES['room_files']['error'][$rIdx]) && $_FILES['room_files']['error'][$rIdx] === UPLOAD_ERR_OK) {
                            $rTmp = $_FILES['room_files']['tmp_name'][$rIdx];
                            $rOrigFile = $_FILES['room_files']['name'][$rIdx];
                            $rCleanName = time() . '_room_' . $rIdx . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $rOrigFile);
                            if (move_uploaded_file($rTmp, $uploadDir . $rCleanName)) {
                                $rImage = 'uploads/hotels/' . $rCleanName;
                            }
                        }

                        if (empty($rImage)) {
                            $rImage = $featuredImage ?: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80';
                        }

                        $roomStmt->execute([
                            $hotelId, $rName, $rType, $rPrice, $rOrig,
                            $rAdults, $rChildren, $rBed, $rSize, $rMeal, $rImage
                        ]);
                    }
                }
            }

            // Redirect back with success flag
            header("Location: hotels.php?saved=1");
            exit();

        } catch (Exception $e) {
            $alertMessage = "Error saving hotel: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// 2. FETCH HOTEL RECORD FOR EDITING
$hotel = [
    'name' => '', 'slug' => '', 'star_rating' => 5, 'property_type' => '5★ Luxury Resort',
    'city' => '', 'state' => '', 'country' => 'India', 'address' => '',
    'latitude' => '', 'longitude' => '', 'featured_image' => '',
    'starting_price' => 7500, 'original_price' => 9500, 'badge' => '5★ Luxury',
    'status' => 'active', 'checkin_time' => '02:00 PM', 'checkout_time' => '11:00 AM',
    'description' => '', 'policies' => 'Free Cancellation up to 48 hours before check-in. Kids under 6 stay free.',
    'amenities' => ''
];
$rooms = [];
$galleryImages = [];

if ($isEdit && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM `hotels` WHERE `id` = ?");
    $stmt->execute([$hotelId]);
    $fetched = $stmt->fetch();
    if ($fetched) {
        $hotel = $fetched;
        
        // Fetch Rooms
        $rStmt = $pdo->prepare("SELECT * FROM `hotel_rooms` WHERE `hotel_id` = ? ORDER BY `id` ASC");
        $rStmt->execute([$hotelId]);
        $rooms = $rStmt->fetchAll();

        // Fetch Gallery Images
        $gStmt = $pdo->prepare("SELECT * FROM `hotel_images` WHERE `hotel_id` = ? ORDER BY `sort_order` ASC, `id` ASC");
        $gStmt->execute([$hotelId]);
        $galleryImages = $gStmt->fetchAll();
    }
}

// Property Types Catalog: Standard list + Dynamic from DB
$standardPropertyTypes = [
    '5★ Luxury Resort',
    '5★ Beachfront Resort',
    '5★ Royal Heritage Palace',
    '5★ Backwater Sanctuary',
    '4★ Premium Boutique Hotel',
    'Alpine Snow Chalet',
    'Jungle & Safari Lodge',
    'Luxury Wellness Retreat',
    'Heritage Haveli',
    'Private Pool Villa',
    'Boutique Homestay'
];
$dbPropertyTypes = [];
if ($pdo) {
    try {
        $dbTypesStmt = $pdo->query("SELECT DISTINCT property_type FROM `hotels` WHERE property_type IS NOT NULL AND TRIM(property_type) != ''");
        $dbPropertyTypes = $dbTypesStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}
$allPropertyTypes = array_values(array_unique(array_filter(array_merge($standardPropertyTypes, $dbPropertyTypes))));

// Standard Available Amenities Catalog
$standardAmenities = [
    ['name' => 'Free Breakfast', 'icon' => 'fa-solid fa-mug-saucer', 'desc' => 'Daily buffet or continental breakfast'],
    ['name' => 'Swimming Pool', 'icon' => 'fa-solid fa-water-ladder', 'desc' => 'Infinity or outdoor pool access'],
    ['name' => 'Free High-Speed Wi-Fi', 'icon' => 'fa-solid fa-wifi', 'desc' => 'High speed internet in all rooms'],
    ['name' => 'Spa & Wellness', 'icon' => 'fa-solid fa-spa', 'desc' => 'Ayurvedic treatments & massage center'],
    ['name' => 'Beachfront / Ocean View', 'icon' => 'fa-solid fa-umbrella-beach', 'desc' => 'Direct walking access to beach'],
    ['name' => 'Mountain / Valley View', 'icon' => 'fa-solid fa-mountain-sun', 'desc' => 'Balcony with alpine snow/valley view'],
    ['name' => 'Fitness Center / Gym', 'icon' => 'fa-solid fa-dumbbell', 'desc' => 'Cardio equipment & weights room'],
    ['name' => 'Fine Dining Restaurant', 'icon' => 'fa-solid fa-utensils', 'desc' => 'Multi-cuisine in-house chef restaurant'],
    ['name' => 'Bar & Lounge', 'icon' => 'fa-solid fa-martini-glass-citrus', 'desc' => 'Sunset cocktail bar & lounge'],
    ['name' => 'Airport Shuttle Service', 'icon' => 'fa-solid fa-van-shuttle', 'desc' => 'Scheduled airport pickup and drop'],
    ['name' => 'Air Conditioning', 'icon' => 'fa-solid fa-snowflake', 'desc' => 'Individual climate control AC'],
    ['name' => '24x7 Room Service', 'icon' => 'fa-solid fa-bell-concierge', 'desc' => 'Around the clock food & butler support'],
    ['name' => 'Free Valet Parking', 'icon' => 'fa-solid fa-square-parking', 'desc' => 'Secure on-premise vehicle parking'],
    ['name' => 'Jacuzzi / Bathtub', 'icon' => 'fa-solid fa-hot-tub-person', 'desc' => 'Private in-room jacuzzi or bathtub']
];

// Parse selected and custom amenities
$selectedAmenities = [];
$customAmenities = [];
if (!empty($hotel['amenities'])) {
    $dec = json_decode($hotel['amenities'], true);
    if (is_array($dec)) {
        $stdNames = array_column($standardAmenities, 'name');
        foreach ($dec as $item) {
            $name = is_array($item) ? ($item['name'] ?? '') : (string)$item;
            $icon = is_array($item) ? ($item['icon'] ?? 'fa-solid fa-circle-check') : 'fa-solid fa-circle-check';
            $desc = is_array($item) ? ($item['desc'] ?? 'Complimentary guest inclusion') : 'Complimentary guest inclusion';
            if (!empty($name)) {
                $selectedAmenities[] = $name;
                if (!in_array($name, $stdNames)) {
                    $customAmenities[] = ['name' => $name, 'icon' => $icon, 'desc' => $desc];
                }
            }
        }
    }
}

// Parse structured cancellation and property policies
$parsedPolicy = [
    'summary' => 'Free cancellation up to 48 hours before check-in. Tiered refund structure applies thereafter.',
    'rules' => [
        ['timeline' => '15+ Days Before Check-in', 'refund' => '100% Refund', 'deduction' => '0% Deduction (Full Refund)', 'badge_type' => 'full_refund'],
        ['timeline' => '7 to 14 Days Before Check-in', 'refund' => '70% Refund', 'deduction' => '30% Cancellation Fee', 'badge_type' => 'partial_refund'],
        ['timeline' => '3 to 6 Days Before Check-in', 'refund' => '30% Refund', 'deduction' => '70% Cancellation Fee', 'badge_type' => 'partial_refund'],
        ['timeline' => 'Within 48 Hours / No-Show', 'refund' => '0% (Non-Refundable)', 'deduction' => '100% Non-Refundable', 'badge_type' => 'no_refund']
    ],
    'child_policy' => 'Children up to 6 years stay complimentary in existing bedding.',
    'id_proof' => 'Government-issued photo identification (Aadhar/Passport) required for all adult guests.'
];

if (!empty($hotel['policies'])) {
    $decPol = json_decode($hotel['policies'], true);
    if (is_array($decPol)) {
        if (!empty($decPol['summary'])) $parsedPolicy['summary'] = $decPol['summary'];
        if (!empty($decPol['rules']) && is_array($decPol['rules'])) $parsedPolicy['rules'] = $decPol['rules'];
        if (!empty($decPol['child_policy'])) $parsedPolicy['child_policy'] = $decPol['child_policy'];
        if (!empty($decPol['id_proof'])) $parsedPolicy['id_proof'] = $decPol['id_proof'];
    } else {
        $parsedPolicy['summary'] = $hotel['policies'];
    }
}

$pageTitle = $isEdit ? "Edit Hotel: " . htmlspecialchars($hotel['name']) : "Add New Hotel";
include 'components/head.php';
?>
<style>
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

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full space-y-6">

            <!-- Page Header / Breadcrumb -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-[#e5e4dc] p-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                        <a href="hotels.php" class="text-sage-700 hover:underline font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Back to Hotels List</span>
                        </a>
                        <span>/</span>
                        <span><?php echo $isEdit ? 'Edit Property' : 'New Property'; ?></span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        <?php echo $isEdit ? 'Edit Hotel, Gallery &amp; Room Rates' : 'Add New Hotel, Gallery &amp; Room Rates'; ?>
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    <a href="hotels.php" class="px-3.5 py-1.5 text-xs font-semibold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors">
                        Cancel
                    </a>
                    <button type="submit" form="hotelForm" class="px-4 py-1.5 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                        <span>Save Hotel</span>
                    </button>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 text-xs font-semibold flex items-center gap-2.5 <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                    <span><?php echo htmlspecialchars($alertMessage); ?></span>
                </div>
            <?php endif; ?>

            <!-- Full Page Form -->
            <form id="hotelForm" method="POST" action="hotel-edit.php<?php echo $isEdit ? '?id=' . $hotelId : ''; ?>" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="save_hotel" value="1">
                <input type="hidden" name="hotel_id" value="<?php echo htmlspecialchars((string)($hotelId ?? '')); ?>">

                <!-- Section 1: Basic Information & Property Classification -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">1. Basic Property Details</h2>
                        </div>
                        <span class="text-[10px] text-slate-500 font-medium">Customer Facing Information</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Hotel Full Name -->
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Hotel / Resort Full Name</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Appears as the primary headline on homepage cards, search listings, and at the top of the hotel details page.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="name" id="hotelName" value="<?php echo htmlspecialchars($hotel['name']); ?>" required placeholder="e.g. Taj Exotica Resort & Spa Goa" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold text-slate-900">
                        </div>

                        <!-- Hotel Overview / About Property (Description) -->
                        <div class="md:col-span-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Property Overview &bull; About Hotel (Description)</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Frontend details page par "Property Overview" aur "About [Hotel Name]" section ka main text yahi hota hai.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <textarea name="description" rows="4" placeholder="Hotel ke bare me detail likhein - jaise atmosphere, beachfront/mountain view, unique guest experience, proximity to attractions..." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium leading-relaxed"><?php echo htmlspecialchars($hotel['description']); ?></textarea>
                            <p class="text-[10px] text-slate-500 mt-1 font-medium">Yahan likha hua text Frontend Hotel Details page par "Property Overview" aur "About <?= htmlspecialchars($hotel['name']) ?>" heading ke niche display hota hai.</p>
                        </div>

                        <!-- Property Type (Select or Add New) -->
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Property Type</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Displays as a category pill under the hotel name (e.g. 5★ Luxury Resort, Private Pool Villa).
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <div class="space-y-1.5">
                                <?php 
                                $currentType = !empty($hotel['property_type']) ? $hotel['property_type'] : '5★ Luxury Resort';
                                $isCustomType = !in_array($currentType, $allPropertyTypes);
                                ?>
                                <select id="propertyTypeSelect" onchange="handlePropertyTypeSelect(this.value)" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold text-slate-800">
                                    <?php foreach ($allPropertyTypes as $pt): ?>
                                        <option value="<?php echo htmlspecialchars($pt); ?>" <?php echo (!$isCustomType && $currentType === $pt) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($pt); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__custom__" <?php echo $isCustomType ? 'selected' : ''; ?>>+ Type Custom Property Type...</option>
                                </select>

                                <!-- Custom Property Type Input Field -->
                                <div id="customPropertyTypeContainer" class="<?php echo $isCustomType ? '' : 'hidden'; ?> p-2 bg-cream-50 border border-sage-300 space-y-1">
                                    <label class="block text-[10px] font-bold text-sage-900">Enter Custom Property Type:</label>
                                    <input type="text" id="customPropertyTypeInput" name="property_type_custom" value="<?php echo $isCustomType ? htmlspecialchars($currentType) : ''; ?>" placeholder="e.g. Desert Luxury Camp / Treehouse Eco Villa" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                                    <p class="text-[9px] text-slate-500">Saved custom types will automatically appear in this dropdown for future hotels.</p>
                                </div>
                                <input type="hidden" name="property_type" id="finalPropertyType" value="<?php echo htmlspecialchars($currentType); ?>">
                            </div>
                        </div>

                        <!-- Star Rating (Decimal Input + Preset Buttons) -->
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Star Rating / Guest Score</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Displays golden stars (★★★★★) and numerical score (e.g. 4.8 / 5) across cards and reviews.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="relative w-32 shrink-0">
                                    <input type="number" step="0.1" min="1.0" max="5.0" name="star_rating" id="starRatingInput" 
                                           value="<?php echo htmlspecialchars((string)($hotel['star_rating'] ?: '4.8')); ?>" 
                                           required placeholder="4.8" 
                                           class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold text-amber-600 pl-7">
                                    <span class="absolute left-2.5 top-2.5 text-amber-500 text-xs">★</span>
                                </div>
                                <!-- Quick Rating Select Buttons -->
                                <div class="flex flex-wrap items-center gap-1 text-[11px]">
                                    <button type="button" onclick="setStarRating(5.0)" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-[10px] rounded transition">5.0 ★</button>
                                    <button type="button" onclick="setStarRating(4.9)" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-[10px] rounded transition">4.9 ★</button>
                                    <button type="button" onclick="setStarRating(4.8)" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-[10px] rounded transition">4.8 ★</button>
                                    <button type="button" onclick="setStarRating(4.7)" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-[10px] rounded transition">4.7 ★</button>
                                    <button type="button" onclick="setStarRating(4.5)" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-[10px] rounded transition">4.5 ★</button>
                                </div>
                            </div>
                        </div>

                        <!-- Status Field -->
                        <div class="md:col-span-2 pt-2 border-t border-[#e5e4dc]">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Publishing Status</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-60 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Active makes it instantly visible live on the website. Inactive saves it as a private draft.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <select name="status" class="w-full sm:w-72 text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold text-slate-800">
                                <option value="active" <?php echo ($hotel['status'] === 'active') ? 'selected' : ''; ?>>Active (Visible on Website)</option>
                                <option value="inactive" <?php echo ($hotel['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive (Hidden Draft)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Location & Destination Mapping -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">2. Destination &amp; Location Tabs</h2>
                        </div>
                        <span class="text-[10px] text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5 font-bold">
                            <i class="fa-solid fa-wand-magic-sparkles mr-1 text-sage-600"></i> Auto-Fill Supported
                        </span>
                    </div>

                    <!-- Live Location Search & Auto-Fill Field -->
                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-800 mb-1">
                            Search Location to Auto-Fill Details <span class="text-slate-400 font-normal">(Type city or area e.g. "Baga Beach Goa", "Old Manali", "Dubai Marina")</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="locationSearchInput" autocomplete="off" placeholder="Type city or place to search & auto-fill below fields..." class="w-full pl-8 pr-10 py-2.5 text-xs bg-cream-50/80 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-medium">
                            <div id="locationSearchSpinner" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                <i class="fa-solid fa-circle-notch fa-spin text-xs text-sage-700"></i>
                            </div>
                        </div>
                        <!-- Suggestions Dropdown -->
                        <div id="locationSuggestions" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-[#e5e4dc] shadow-lg max-h-56 overflow-y-auto z-50 divide-y divide-[#e5e4dc]"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Destination City</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-60 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Connects to destination tab filters on homepage, category pills, and search listings.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="city" id="hotelCity" value="<?php echo htmlspecialchars($hotel['city']); ?>" required placeholder="e.g. Goa, Manali, Jaipur, Dubai" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>State / Province</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Appears next to city name in location breadcrumb and search meta.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="state" id="hotelState" value="<?php echo htmlspecialchars($hotel['state']); ?>" placeholder="e.g. Goa, Himachal Pradesh" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Country</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-52 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        International destination grouping &amp; country badge.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="country" id="hotelCountry" value="<?php echo htmlspecialchars($hotel['country']); ?>" placeholder="India" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>

                        <div class="md:col-span-3">
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Complete Address</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Full location line shown directly under the hotel headline and on customer confirmation vouchers.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="address" id="hotelAddress" value="<?php echo htmlspecialchars($hotel['address']); ?>" placeholder="e.g. Benaulim Beach, South Goa, Goa 403716" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pricing & Check-in Timings -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">3. Starting Price &amp; Check-In Timings</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Starting Price (Per Night)</span>
                                <span class="text-rose-500">*</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-60 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        The prominent price tag ("From ₹X / night") shown on hotel cards and search results.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input type="number" name="starting_price" value="<?php echo htmlspecialchars($hotel['starting_price']); ?>" required class="w-full text-xs pl-7 pr-2.5 py-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Original Price (Strikeout)</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-60 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Strikethrough rate that calculates and displays the discount pill (e.g. "25% OFF").
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input type="number" name="original_price" value="<?php echo htmlspecialchars($hotel['original_price']); ?>" class="w-full text-xs pl-7 pr-2.5 py-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium text-slate-500">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Check-In Time</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-52 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Standard check-in time shown in House Rules section.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="checkin_time" value="<?php echo htmlspecialchars($hotel['checkin_time']); ?>" placeholder="02:00 PM" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Check-Out Time</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-52 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Standard check-out time shown in House Rules section.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="checkout_time" value="<?php echo htmlspecialchars($hotel['checkout_time']); ?>" placeholder="11:00 AM" class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Main Cover Photo & Multi-Photo Resort Gallery -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">4. Cover Photo &amp; Resort Photo Gallery</h2>
                        </div>
                        <span class="text-[10px] text-slate-500 font-medium">Customer Facing Media</span>
                    </div>

                    <!-- Part A: Featured Cover Photo -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                            <span>Resort Featured Cover Photo</span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-teal-800 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded">
                                <i class="fa-solid fa-expand text-teal-600"></i> Recommended: 1920 &times; 800 px (16:9 or 21:9 Landscape)
                            </span>
                            <span class="text-rose-500">*</span>
                            <span class="admin-tooltip">
                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                    Primary hero photo shown on homepage cards, search results, and at the top of the hotel details page.
                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                </span>
                            </span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center bg-cream-50 p-3.5 border border-[#e5e4dc]">
                            <div class="md:col-span-2 space-y-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Cover Image URL</label>
                                    <input type="url" name="featured_image_url" id="coverUrlInput" value="<?php echo htmlspecialchars($hotel['featured_image']); ?>" placeholder="https://images.unsplash.com/photo-..." class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium" oninput="updateCoverPreview(this.value)">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Or Upload Local Image File</label>
                                    <input type="file" name="featured_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:border file:border-[#e5e4dc] file:text-[11px] file:font-semibold file:bg-white hover:file:bg-cream-100 file:cursor-pointer" onchange="previewLocalImage(this, 'coverPreviewImg')">
                                    <input type="hidden" name="existing_featured_image" value="<?php echo htmlspecialchars($hotel['featured_image']); ?>">
                                    <p class="text-[10px] text-slate-500 pt-1"><i class="fa-regular fa-lightbulb text-amber-500 mr-1"></i><strong>Best Fit:</strong> Landscape orientation (1920 &times; 800 px, min width 1200px). Avoid vertical or cropped low-res photos.</p>
                                </div>
                            </div>
                            <div class="h-28 bg-white border border-[#e5e4dc] flex items-center justify-center overflow-hidden relative group">
                                <img id="coverPreviewImg" src="<?php echo htmlspecialchars(getAdminImageUrl($hotel['featured_image'] ?? '')); ?>" alt="Cover" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] px-1.5 py-0.5 font-bold">Cover Preview</span>
                            </div>
                        </div>
                    </div>

                    <!-- Part B: Multi-Photo Resort Gallery Manager -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                    <span>Resort Gallery Photos</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-teal-800 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded">
                                        <i class="fa-solid fa-images text-teal-600"></i> Recommended: 1200 &times; 800 px (Landscape)
                                    </span>
                                    <span class="admin-tooltip">
                                        <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                        <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                            <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                            Appears in the 5-photo magazine grid and the interactive full-screen photo gallery lightbox on the hotel details page.
                                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                        </span>
                                    </span>
                                </label>
                                <p class="text-[10px] text-slate-500">Add multiple photos for guest browsing (<strong>Recommended: 1200 &times; 800 px</strong>). Appears in the 5-photo preview strip &amp; full-screen lightbox modal.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="addGalleryUrlRow()" class="px-2.5 py-1 text-xs font-bold bg-cream-50 text-slate-800 border border-[#e5e4dc] hover:bg-cream-100 transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-link text-[10px] text-sage-700"></i>
                                    <span>Add Photo URL</span>
                                </button>
                            </div>
                        </div>

                        <!-- Multi-file Upload Box -->
                        <div class="p-3 bg-white border border-dashed border-sage-400 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-sage-50 text-sage-700 border border-sage-200 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">Upload Multiple Resort Gallery Photos</div>
                                    <div class="text-[10px] text-slate-400">Select multiple JPG, PNG, or WebP images from your computer</div>
                                </div>
                            </div>
                            <input type="file" name="gallery_files[]" multiple accept="image/*" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:border file:border-sage-300 file:text-xs file:font-bold file:bg-sage-50 file:text-sage-800 hover:file:bg-sage-100 file:cursor-pointer">
                        </div>

                        <!-- Existing and Dynamic Gallery Items Grid -->
                        <div id="galleryContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-2">
                            <?php if (!empty($galleryImages)): ?>
                                <?php foreach ($galleryImages as $gi => $gImg): ?>
                                    <div class="gallery-card bg-white border border-[#e5e4dc] p-2 space-y-2 relative group">
                                        <div class="h-28 bg-cream-50 border border-[#e5e4dc] overflow-hidden">
                                            <img src="<?php echo htmlspecialchars(getAdminImageUrl($gImg['image_url'])); ?>" alt="Gallery" class="w-full h-full object-cover">
                                        </div>
                                        <input type="hidden" name="gallery_existing[<?php echo $gi; ?>][url]" value="<?php echo htmlspecialchars($gImg['image_url']); ?>">
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Photo Caption / Tag</label>
                                            <input type="text" name="gallery_existing[<?php echo $gi; ?>][caption]" value="<?php echo htmlspecialchars($gImg['caption'] ?? 'Resort View'); ?>" placeholder="e.g. Sunset Infinity Pool" class="w-full text-[11px] p-1.5 bg-cream-50 border border-[#e5e4dc]">
                                        </div>
                                        <button type="button" onclick="this.closest('.gallery-card').remove()" class="absolute top-3 right-3 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] shadow-sm transition-colors" title="Delete Photo">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Included Amenities Checklist & Custom Amenity Builder -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#e5e4dc] gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">5. Included Stay Amenities</h2>
                            <span class="admin-tooltip">
                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                    Checked amenities are displayed with icons under the "Property Highlights &amp; Amenities" grid on the hotel details page.
                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                </span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="showAddAmenityInline()" class="px-2.5 py-1 text-xs font-bold bg-sage-50 text-sage-800 border border-sage-300 hover:bg-sage-100 flex items-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Add Custom Amenity</span>
                            </button>
                        </div>
                    </div>

                    <!-- Inline Custom Amenity Creation Box -->
                    <div id="addAmenityBox" class="hidden p-3.5 bg-cream-50 border border-sage-300 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-sage-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-sparkles text-sage-700"></i>
                                <span>Add New Property Amenity</span>
                            </span>
                            <button type="button" onclick="hideAddAmenityInline()" class="text-slate-400 hover:text-slate-700 text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Amenity Name <span class="text-rose-500">*</span></label>
                                <input type="text" id="newAmenityName" placeholder="e.g. Private Heated Jacuzzi" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Icon Class</label>
                                <input type="text" id="newAmenityIcon" list="amenityIconPresets" value="fa-solid fa-circle-check" placeholder="fa-solid fa-hot-tub-person" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none">
                                <datalist id="amenityIconPresets">
                                    <option value="fa-solid fa-hot-tub-person">
                                    <option value="fa-solid fa-water-ladder">
                                    <option value="fa-solid fa-water">
                                    <option value="fa-solid fa-spa">
                                    <option value="fa-solid fa-utensils">
                                    <option value="fa-solid fa-mug-saucer">
                                    <option value="fa-solid fa-martini-glass">
                                    <option value="fa-solid fa-van-shuttle">
                                    <option value="fa-solid fa-helicopter">
                                    <option value="fa-solid fa-fire">
                                    <option value="fa-solid fa-tree">
                                    <option value="fa-solid fa-paw">
                                    <option value="fa-solid fa-shield-halved">
                                    <option value="fa-solid fa-circle-check">
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Short Description</label>
                                <input type="text" id="newAmenityDesc" placeholder="e.g. 24/7 temperature controlled in-room" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" onclick="hideAddAmenityInline()" class="px-3 py-1 text-xs font-semibold bg-white border border-[#e5e4dc] hover:bg-cream-100">Cancel</button>
                            <button type="button" onclick="confirmAddAmenity()" class="px-3.5 py-1 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white">Add Amenity</button>
                        </div>
                    </div>

                    <div id="amenitiesGridContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                        <?php 
                        $curIndex = 0;
                        foreach ($standardAmenities as $am): 
                            $isChecked = in_array($am['name'], $selectedAmenities);
                        ?>
                            <label class="flex items-start gap-2.5 p-2.5 border border-[#e5e4dc] hover:bg-cream-50 cursor-pointer transition-colors select-none">
                                <input type="checkbox" name="amenities[<?php echo $curIndex; ?>][name]" value="<?php echo htmlspecialchars($am['name']); ?>" <?php echo $isChecked ? 'checked' : ''; ?> class="mt-0.5 accent-sage-700">
                                <input type="hidden" name="amenities[<?php echo $curIndex; ?>][icon]" value="<?php echo htmlspecialchars($am['icon']); ?>">
                                <input type="hidden" name="amenities[<?php echo $curIndex; ?>][desc]" value="<?php echo htmlspecialchars($am['desc']); ?>">
                                <div class="text-xs">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <i class="<?php echo htmlspecialchars($am['icon']); ?> text-sage-700 text-[11px]"></i>
                                        <span><?php echo htmlspecialchars($am['name']); ?></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400"><?php echo htmlspecialchars($am['desc']); ?></div>
                                </div>
                            </label>
                        <?php 
                            $curIndex++;
                        endforeach; 
                        ?>

                        <?php if (!empty($customAmenities)): ?>
                            <?php foreach ($customAmenities as $cam): ?>
                                <label class="flex items-start gap-2.5 p-2.5 border border-sage-300 bg-sage-50/40 hover:bg-cream-50 cursor-pointer transition-colors select-none">
                                    <input type="checkbox" name="amenities[<?php echo $curIndex; ?>][name]" value="<?php echo htmlspecialchars($cam['name']); ?>" checked class="mt-0.5 accent-sage-700">
                                    <input type="hidden" name="amenities[<?php echo $curIndex; ?>][icon]" value="<?php echo htmlspecialchars($cam['icon']); ?>">
                                    <input type="hidden" name="amenities[<?php echo $curIndex; ?>][desc]" value="<?php echo htmlspecialchars($cam['desc']); ?>">
                                    <div class="text-xs">
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                            <i class="<?php echo htmlspecialchars($cam['icon']); ?> text-sage-700 text-[11px]"></i>
                                            <span><?php echo htmlspecialchars($cam['name']); ?></span>
                                            <span class="text-[9px] text-sage-800 bg-sage-100 border border-sage-300 px-1 py-0.2 rounded font-bold">Custom</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400"><?php echo htmlspecialchars($cam['desc']); ?></div>
                                    </div>
                                </label>
                            <?php 
                                $curIndex++;
                            endforeach; 
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 6: Dynamic Room Categories & Room Photo Builder -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">6. Room Categories, Pricing &amp; Room Photos</h2>
                            <span class="admin-tooltip">
                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                    All room options defined here appear in the "Choose Your Room" section with pricing, guest capacity, and book now buttons.
                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                </span>
                            </span>
                        </div>
                        <button type="button" onclick="addNewRoomRow()" class="px-3 py-1.5 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white transition-colors flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Add Room Category</span>
                        </button>
                    </div>

                    <div id="roomsContainer" class="space-y-4">
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $ri => $room): ?>
                                <div class="room-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative">
                                    <div class="flex items-center justify-between pb-2.5 border-b border-[#e5e4dc]">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 bg-sage-800 text-white text-[10px] font-bold uppercase tracking-wider">Room #<?php echo $ri + 1; ?></span>
                                            <span class="text-[11px] font-semibold text-slate-600 truncate"><?php echo htmlspecialchars($room['room_name'] ?: 'New Category'); ?></span>
                                        </div>
                                        <button type="button" onclick="this.closest('.room-row').remove()" class="text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-2 py-1 text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Delete Category">
                                            <i class="fa-solid fa-trash-can text-[11px]"></i>
                                            <span>Delete Category</span>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="md:col-span-2">
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Room Category Title</span>
                                                <span class="text-rose-500">*</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        The primary title shown in the "Choose Your Room" section on the hotel details page.
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="text" name="rooms[<?php echo $ri; ?>][room_name]" value="<?php echo htmlspecialchars($room['room_name']); ?>" required placeholder="e.g. Deluxe Sea Facing Suite with Balcony" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold text-slate-900 focus:border-sage-700 outline-none">
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Room Type / View Badge</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        Small badge tag shown beside room title (e.g. "Deluxe Suite", "Ocean View", "Pool Villa").
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="text" name="rooms[<?php echo $ri; ?>][room_type]" value="<?php echo htmlspecialchars($room['room_type'] ?? 'Deluxe Room'); ?>" placeholder="Deluxe Room / Suite / Villa" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Price / Night (₹)</span>
                                                <span class="text-rose-500">*</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        The booking price per night shown in bold green font.
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                                <input type="number" name="rooms[<?php echo $ri; ?>][price_per_night]" value="<?php echo htmlspecialchars($room['price_per_night']); ?>" required class="w-full text-xs pl-6 pr-2 py-2 bg-white border border-[#e5e4dc] font-bold text-sage-900 focus:border-sage-700 outline-none">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Original Price (₹)</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        Cut-through strikethrough rate showing discount savings.
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                                <input type="number" name="rooms[<?php echo $ri; ?>][original_price]" value="<?php echo htmlspecialchars($room['original_price'] ?? ($room['price_per_night'] * 1.25)); ?>" class="w-full text-xs pl-6 pr-2 py-2 bg-white border border-[#e5e4dc] text-slate-500 line-through focus:border-sage-700 outline-none font-medium">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Max Adults</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        Shown as guest capacity (e.g. "2 Adults") on the room card.
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="number" min="1" max="10" name="rooms[<?php echo $ri; ?>][max_adults]" value="<?php echo htmlspecialchars($room['max_adults'] ?? 2); ?>" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold text-slate-800 focus:border-sage-700 outline-none">
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Max Children</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        Shown as child capacity (e.g. "1 Child") on the room card.
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="number" min="0" max="6" name="rooms[<?php echo $ri; ?>][max_children]" value="<?php echo htmlspecialchars($room['max_children'] ?? 1); ?>" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold text-slate-800 focus:border-sage-700 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Bed Configuration</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        Bed specifications badge (e.g. "1 King Bed", "2 Twin Beds").
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="text" name="rooms[<?php echo $ri; ?>][bed_type]" value="<?php echo htmlspecialchars($room['bed_type'] ?? '1 King Bed'); ?>" placeholder="1 King Bed" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Room Size (Area)</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        Floor area badge shown under room title (e.g. "450 sq ft").
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="text" name="rooms[<?php echo $ri; ?>][room_size]" value="<?php echo htmlspecialchars($room['room_size'] ?? '420 sq ft'); ?>" placeholder="420 sq ft" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                                <span>Meal Plan &amp; Included Perks</span>
                                                <span class="admin-tooltip ml-1">
                                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                        Perk bullets highlighted under the room price (e.g. "Free Breakfast Included").
                                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                    </span>
                                                </span>
                                            </label>
                                            <input type="text" name="rooms[<?php echo $ri; ?>][meal_plan]" value="<?php echo htmlspecialchars($room['meal_plan'] ?? 'Free Breakfast Included'); ?>" placeholder="Free Breakfast Included" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                        </div>
                                    </div>

                                    <div class="pt-1">
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Room Photo (URL or File Upload)</span>
                                            <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-teal-800 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded">
                                                Recommended: 800 &times; 500 px (Landscape)
                                            </span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Thumbnail photo displayed on the room selection card on the hotel page.
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <div class="flex flex-col sm:flex-row items-center gap-2">
                                            <input type="url" name="rooms[<?php echo $ri; ?>][image_url]" value="<?php echo htmlspecialchars($room['image_url'] ?? ''); ?>" placeholder="https://images.unsplash.com/photo-..." class="w-full sm:flex-1 text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                            <input type="file" name="room_files[<?php echo $ri; ?>]" accept="image/*" class="w-full sm:w-auto text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:border file:border-[#e5e4dc] file:text-[11px] file:bg-white file:font-semibold hover:file:bg-cream-100 file:cursor-pointer">
                                            <input type="hidden" name="rooms[<?php echo $ri; ?>][existing_image]" value="<?php echo htmlspecialchars($room['image_url'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default First Room Row -->
                            <div class="room-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative">
                                <div class="flex items-center justify-between pb-2.5 border-b border-[#e5e4dc]">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 bg-sage-800 text-white text-[10px] font-bold uppercase tracking-wider">Room #1</span>
                                        <span class="text-[11px] font-semibold text-slate-600">Deluxe Ocean View Room</span>
                                    </div>
                                    <button type="button" onclick="this.closest('.room-row').remove()" class="text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-2 py-1 text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Delete Category">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        <span>Delete Category</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Room Category Title</span>
                                            <span class="text-rose-500">*</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    The primary title shown in the "Choose Your Room" section on the hotel details page.
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <input type="text" name="rooms[0][room_name]" value="Deluxe Ocean View Room" required placeholder="e.g. Deluxe Sea Facing Suite with Balcony" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold text-slate-900 focus:border-sage-700 outline-none">
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Room Type / View Badge</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Small badge tag shown beside room title (e.g. "Deluxe Suite", "Ocean View", "Pool Villa").
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <input type="text" name="rooms[0][room_type]" value="Deluxe Room" placeholder="Deluxe Room / Suite / Villa" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Price / Night (₹)</span>
                                            <span class="text-rose-500">*</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    The booking price per night shown in bold green font.
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                            <input type="number" name="rooms[0][price_per_night]" value="<?php echo htmlspecialchars($hotel['starting_price']); ?>" required class="w-full text-xs pl-6 pr-2 py-2 bg-white border border-[#e5e4dc] font-bold text-sage-900 focus:border-sage-700 outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Original Price (₹)</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Cut-through strikethrough rate showing discount savings.
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                            <input type="number" name="rooms[0][original_price]" value="<?php echo htmlspecialchars($hotel['original_price']); ?>" class="w-full text-xs pl-6 pr-2 py-2 bg-white border border-[#e5e4dc] text-slate-500 line-through focus:border-sage-700 outline-none font-medium">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Max Adults</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Shown as guest capacity (e.g. "2 Adults") on the room card.
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <input type="number" min="1" max="10" name="rooms[0][max_adults]" value="2" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold text-slate-800 focus:border-sage-700 outline-none">
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Max Children</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Shown as child capacity (e.g. "1 Child") on the room card.
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <input type="number" min="0" max="6" name="rooms[0][max_children]" value="1" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold text-slate-800 focus:border-sage-700 outline-none">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Bed Configuration</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Bed specifications badge (e.g. "1 King Bed", "2 Twin Beds").
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <input type="text" name="rooms[0][bed_type]" value="1 King Bed" placeholder="1 King Bed" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Room Size (Area)</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Floor area badge shown under room title (e.g. "450 sq ft").
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <input type="text" name="rooms[0][room_size]" value="420 sq ft" placeholder="420 sq ft" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                            <span>Meal Plan &amp; Included Perks</span>
                                            <span class="admin-tooltip ml-1">
                                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                                <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                    Perk bullets highlighted under the room price (e.g. "Free Breakfast Included").
                                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                                </span>
                                            </span>
                                        </label>
                                        <input type="text" name="rooms[0][meal_plan]" value="Free Breakfast Included" placeholder="Free Breakfast Included" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                    </div>
                                </div>

                                <div class="pt-1">
                                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                                        <span>Room Photo (URL or File Upload)</span>
                                        <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-teal-800 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded">
                                            Recommended: 800 &times; 500 px (Landscape)
                                        </span>
                                        <span class="admin-tooltip ml-1">
                                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                            <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                                Thumbnail photo displayed on the room selection card on the hotel page.
                                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                            </span>
                                        </span>
                                    </label>
                                    <div class="flex flex-col sm:flex-row items-center gap-2">
                                        <input type="url" name="rooms[0][image_url]" value="" placeholder="https://images.unsplash.com/... (or leave blank to use hotel cover)" class="w-full sm:flex-1 text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                        <input type="file" name="room_files[0]" accept="image/*" class="w-full sm:w-auto text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:border file:border-[#e5e4dc] file:text-[11px] file:bg-white file:font-semibold hover:file:bg-cream-100 file:cursor-pointer">
                                        <input type="hidden" name="rooms[0][existing_image]" value="">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section 7: Overview, House Rules & Tiered Cancellation Policy -->
                <div class="bg-white border border-[#e5e4dc] p-5 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">7. House Rules, Check-In Requirements &amp; Tiered Cancellation Policy</h2>
                            <span class="admin-tooltip">
                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                    Controls the "About Property" description, house rules, check-in requirements, and cancellation schedule modal.
                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                </span>
                            </span>
                        </div>
                    </div>


                    <!-- Policy Summary Headline -->
                    <div>
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                            <span>Cancellation Policy Headline / Short Summary</span>
                            <span class="admin-tooltip">
                                <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                    <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                    Green pill badge displayed above the booking table (e.g. "Free cancellation up to 48 hours before check-in").
                                    <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                </span>
                            </span>
                        </label>
                        <input type="text" name="policy_summary" value="<?php echo htmlspecialchars($parsedPolicy['summary']); ?>" placeholder="e.g. Free cancellation up to 48 hours before check-in." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-semibold">
                    </div>

                    <!-- Tiered Refund & Cancellation Schedule Table -->
                    <div class="space-y-3 pt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-clock-rotate-left text-sage-700"></i>
                                    <span>Cancellation &amp; Refund Schedule (Days vs Refund %)</span>
                                    <span class="admin-tooltip">
                                        <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                        <span class="tooltip-box w-64 p-2.5 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                            <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                            Detailed schedule shown in the cancellation policy modal when customers click "Cancellation Policy".
                                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                        </span>
                                    </span>
                                </h3>
                                <p class="text-[10px] text-slate-400">Specify exact refund percentage and deduction fees for each cancellation window.</p>
                            </div>
                            <button type="button" onclick="addPolicyTierRow()" class="px-2.5 py-1 text-xs font-bold bg-sage-50 text-sage-800 border border-sage-300 hover:bg-sage-100 flex items-center gap-1.5 transition-colors self-start sm:self-auto">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Add Policy Tier</span>
                            </button>
                        </div>

                        <div id="policyTiersContainer" class="space-y-2.5">
                            <?php foreach ($parsedPolicy['rules'] as $ti => $tier): ?>
                                <div class="policy-tier-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group">
                                    <div class="sm:col-span-4">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Cancellation Window</label>
                                        <input type="text" name="cancellation_tiers[<?php echo $ti; ?>][timeline]" value="<?php echo htmlspecialchars($tier['timeline']); ?>" required placeholder="e.g. 15+ Days Before Check-in" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold focus:border-sage-700 outline-none">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Refund Return</label>
                                        <input type="text" name="cancellation_tiers[<?php echo $ti; ?>][refund]" value="<?php echo htmlspecialchars($tier['refund']); ?>" required placeholder="e.g. 100% Refund" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-bold text-sage-900 focus:border-sage-700 outline-none">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Deduction / Fee</label>
                                        <input type="text" name="cancellation_tiers[<?php echo $ti; ?>][deduction]" value="<?php echo htmlspecialchars($tier['deduction']); ?>" placeholder="e.g. 0% (Zero Fee)" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] text-slate-600 focus:border-sage-700 outline-none">
                                    </div>
                                    <div class="sm:col-span-2 flex items-center justify-between gap-1.5 pt-3 sm:pt-0">
                                        <select name="cancellation_tiers[<?php echo $ti; ?>][badge_type]" class="w-full text-[11px] p-1.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                                            <option value="full_refund" <?php echo (($tier['badge_type'] ?? '') === 'full_refund') ? 'selected' : ''; ?>>Full Refund</option>
                                            <option value="partial_refund" <?php echo (($tier['badge_type'] ?? '') === 'partial_refund') ? 'selected' : ''; ?>>Partial Refund</option>
                                            <option value="no_refund" <?php echo (($tier['badge_type'] ?? '') === 'no_refund') ? 'selected' : ''; ?>>Non-Refundable</option>
                                        </select>
                                        <button type="button" onclick="this.closest('.policy-tier-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs shrink-0 transition-colors" title="Delete Tier">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Additional House Rules -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-[#e5e4dc]">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Child &amp; Extra Bedding Policy</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Listed under the "House Rules" tab on the hotel details page.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="child_policy" value="<?php echo htmlspecialchars($parsedPolicy['child_policy']); ?>" placeholder="e.g. Children up to 6 years stay complimentary in existing bedding." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-bold text-slate-800 mb-1">
                                <span>Government ID Requirements</span>
                                <span class="admin-tooltip">
                                    <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                                    <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                        <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                        Listed under "Check-in Requirements" on the hotel details page.
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                                    </span>
                                </span>
                            </label>
                            <input type="text" name="id_proof_policy" value="<?php echo htmlspecialchars($parsedPolicy['id_proof']); ?>" placeholder="e.g. Government-issued photo identification required for all adult guests." class="w-full text-xs p-2.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bottom Save Button Bar -->
                <div class="flex items-center justify-between p-4 bg-white border border-[#e5e4dc]">
                    <a href="hotels.php" class="px-4 py-2 text-xs font-semibold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors">
                        Cancel &amp; Return
                    </a>
                    <button type="submit" class="px-6 py-2.5 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span><?php echo $isEdit ? 'Save Changes' : 'Publish Hotel Stay'; ?></span>
                    </button>
                </div>

            </form>

        </main>
    </div>
</div>

<script>
    let roomIndex = <?php echo max(count($rooms), 1); ?>;
    let galleryIndex = <?php echo max(count($galleryImages), 1); ?>;
    let amenityIndex = <?php echo max($curIndex ?? 14, 14); ?>;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function handlePropertyTypeSelect(val) {
        const customContainer = document.getElementById('customPropertyTypeContainer');
        const customInput = document.getElementById('customPropertyTypeInput');
        const finalInput = document.getElementById('finalPropertyType');
        if (val === '__custom__') {
            if (customContainer) customContainer.classList.remove('hidden');
            if (customInput) {
                customInput.focus();
                if (finalInput) finalInput.value = customInput.value.trim();
            }
        } else {
            if (customContainer) customContainer.classList.add('hidden');
            if (finalInput) finalInput.value = val;
        }
    }

    function setStarRating(val) {
        const inp = document.getElementById('starRatingInput');
        if (inp) {
            inp.value = parseFloat(val).toFixed(1);
        }
    }

    // Connect custom property type input to final hidden input
    document.addEventListener('DOMContentLoaded', function() {
        const customInput = document.getElementById('customPropertyTypeInput');
        const finalInput = document.getElementById('finalPropertyType');
        const select = document.getElementById('propertyTypeSelect');
        if (customInput && finalInput && select) {
            customInput.addEventListener('input', function() {
                if (select.value === '__custom__') {
                    finalInput.value = this.value.trim();
                }
            });
        }
    });

    function showAddAmenityInline() {
        const box = document.getElementById('addAmenityBox');
        if (box) {
            box.classList.remove('hidden');
            document.getElementById('newAmenityName')?.focus();
        }
    }

    function hideAddAmenityInline() {
        const box = document.getElementById('addAmenityBox');
        if (box) box.classList.add('hidden');
    }

    function confirmAddAmenity() {
        const nameInput = document.getElementById('newAmenityName');
        const iconInput = document.getElementById('newAmenityIcon');
        const descInput = document.getElementById('newAmenityDesc');
        const container = document.getElementById('amenitiesGridContainer');

        const name = nameInput ? nameInput.value.trim() : '';
        const icon = (iconInput && iconInput.value.trim()) ? iconInput.value.trim() : 'fa-solid fa-circle-check';
        const desc = (descInput && descInput.value.trim()) ? descInput.value.trim() : 'Custom property amenity';

        if (!name) {
            alert('Please enter amenity title/name.');
            nameInput?.focus();
            return;
        }

        amenityIndex++;
        const label = document.createElement('label');
        label.className = 'flex items-start gap-2.5 p-2.5 border border-sage-300 bg-sage-50/50 hover:bg-cream-50 cursor-pointer transition-colors select-none';
        label.innerHTML = `
            <input type="checkbox" name="amenities[${amenityIndex}][name]" value="${escapeHtml(name)}" checked class="mt-0.5 accent-sage-700">
            <input type="hidden" name="amenities[${amenityIndex}][icon]" value="${escapeHtml(icon)}">
            <input type="hidden" name="amenities[${amenityIndex}][desc]" value="${escapeHtml(desc)}">
            <div class="text-xs">
                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i class="${escapeHtml(icon)} text-sage-700 text-[11px]"></i>
                    <span>${escapeHtml(name)}</span>
                    <span class="text-[9px] text-sage-800 bg-sage-100 border border-sage-300 px-1 py-0.2 rounded font-bold">New</span>
                </div>
                <div class="text-[10px] text-slate-400">${escapeHtml(desc)}</div>
            </div>
        `;

        if (container) {
            container.appendChild(label);
        }

        if (nameInput) nameInput.value = '';
        if (descInput) descInput.value = '';
        hideAddAmenityInline();
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

    function updateCoverPreview(url) {
        const preview = document.getElementById('coverPreviewImg');
        if (preview && url && url.trim().length > 5) {
            preview.src = url.trim();
        }
    }

    function addGalleryUrlRow() {
        galleryIndex++;
        const container = document.getElementById('galleryContainer');
        const card = document.createElement('div');
        card.className = 'gallery-card bg-white border border-[#e5e4dc] p-2 space-y-2 relative group';
        card.innerHTML = `
            <div class="h-28 bg-cream-50 border border-[#e5e4dc] overflow-hidden flex items-center justify-center">
                <img src="" alt="Gallery Preview" class="w-full h-full object-cover gal-prev-img hidden">
                <i class="fa-solid fa-image text-2xl text-slate-300 gal-prev-placeholder"></i>
            </div>
            <div>
                <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Image URL</label>
                <input type="url" name="gallery_urls[${galleryIndex}][url]" required placeholder="https://images.unsplash.com/..." class="w-full text-[11px] p-1.5 bg-white border border-[#e5e4dc]" oninput="const img = this.closest('.gallery-card').querySelector('.gal-prev-img'); const ph = this.closest('.gallery-card').querySelector('.gal-prev-placeholder'); if (this.value.trim()) { img.src = this.value; img.classList.remove('hidden'); ph.classList.add('hidden'); } else { img.classList.add('hidden'); ph.classList.remove('hidden'); }">
            </div>
            <div>
                <label class="block text-[9px] font-bold text-slate-500 mb-0.5">Caption</label>
                <input type="text" name="gallery_urls[${galleryIndex}][caption]" placeholder="e.g. Resort Pool / Beach Deck" class="w-full text-[11px] p-1.5 bg-cream-50 border border-[#e5e4dc]">
            </div>
            <button type="button" onclick="this.closest('.gallery-card').remove()" class="absolute top-3 right-3 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] shadow-sm transition-colors" title="Delete Photo">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(card);
    }

    function addNewRoomRow() {
        roomIndex++;
        const container = document.getElementById('roomsContainer');
        const div = document.createElement('div');
        div.className = 'room-row bg-cream-50 border border-[#e5e4dc] p-4 space-y-3 relative';
        div.innerHTML = `
            <div class="flex items-center justify-between pb-2.5 border-b border-[#e5e4dc]">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-sage-800 text-white text-[10px] font-bold uppercase tracking-wider">Room #${roomIndex}</span>
                    <span class="text-[11px] font-semibold text-slate-600">New Category</span>
                </div>
                <button type="button" onclick="this.closest('.room-row').remove()" class="text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-2 py-1 text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Delete Category">
                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                    <span>Delete Category</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Room Category Title</span>
                        <span class="text-rose-500">*</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                The primary title shown in the "Choose Your Room" section on the hotel details page.
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <input type="text" name="rooms[${roomIndex}][room_name]" required placeholder="e.g. Deluxe Sea Facing Suite with Balcony" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-bold text-slate-900 focus:border-sage-700 outline-none">
                </div>
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Room Type / View Badge</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                Small badge tag shown beside room title (e.g. "Deluxe Suite", "Ocean View", "Pool Villa").
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <input type="text" name="rooms[${roomIndex}][room_type]" value="Deluxe Suite" placeholder="Deluxe Room / Suite / Villa" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Price / Night (₹)</span>
                        <span class="text-rose-500">*</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                The booking price per night shown in bold green font.
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                        <input type="number" name="rooms[${roomIndex}][price_per_night]" value="8500" required class="w-full text-xs pl-6 pr-2 py-2 bg-white border border-[#e5e4dc] font-bold text-sage-900 focus:border-sage-700 outline-none">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Original Price (₹)</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                Cut-through strikethrough rate showing discount savings.
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                        <input type="number" name="rooms[${roomIndex}][original_price]" value="11000" class="w-full text-xs pl-6 pr-2 py-2 bg-white border border-[#e5e4dc] text-slate-500 line-through focus:border-sage-700 outline-none font-medium">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Max Adults</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                Shown as guest capacity (e.g. "2 Adults") on the room card.
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <input type="number" min="1" max="10" name="rooms[${roomIndex}][max_adults]" value="2" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold text-slate-800 focus:border-sage-700 outline-none">
                </div>
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Max Children</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                Shown as child capacity (e.g. "1 Child") on the room card.
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <input type="number" min="0" max="6" name="rooms[${roomIndex}][max_children]" value="1" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] font-semibold text-slate-800 focus:border-sage-700 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Bed Configuration</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                Bed specifications badge (e.g. "1 King Bed", "2 Twin Beds").
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <input type="text" name="rooms[${roomIndex}][bed_type]" value="1 King Bed" placeholder="1 King Bed" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                </div>
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Room Size (Area)</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-56 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                Floor area badge shown under room title (e.g. "450 sq ft").
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <input type="text" name="rooms[${roomIndex}][room_size]" value="420 sq ft" placeholder="420 sq ft" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                </div>
                <div>
                    <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                        <span>Meal Plan &amp; Included Perks</span>
                        <span class="admin-tooltip ml-1">
                            <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                            <span class="tooltip-box w-60 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                                <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                                Perk bullets highlighted under the room price (e.g. "Free Breakfast Included").
                                <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                            </span>
                        </span>
                    </label>
                    <input type="text" name="rooms[${roomIndex}][meal_plan]" value="Free Breakfast Included" placeholder="Free Breakfast Included" class="w-full text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                </div>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-1 text-[11px] font-bold text-slate-800 mb-1">
                    <span>Room Photo (URL or File Upload)</span>
                    <span class="inline-flex items-center gap-1 text-[9px] font-semibold text-teal-800 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded">
                        Recommended: 800 &times; 500 px (Landscape)
                    </span>
                    <span class="admin-tooltip ml-1">
                        <i class="fa-solid fa-circle-info text-slate-400 group-hover:text-sage-700 text-[11px] transition-colors"></i>
                        <span class="tooltip-box w-64 p-2 bg-slate-900 text-white text-[11px] font-normal normal-case rounded shadow-xl border border-slate-700 pointer-events-none leading-snug">
                            <strong class="text-amber-400 block mb-0.5">Frontend Display:</strong>
                            Thumbnail photo displayed on the room selection card on the hotel page.
                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></span>
                        </span>
                    </span>
                </label>
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <input type="url" name="rooms[${roomIndex}][image_url]" placeholder="https://images.unsplash.com/photo-..." class="w-full sm:flex-1 text-xs p-2 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                    <input type="file" name="room_files[${roomIndex}]" accept="image/*" class="w-full sm:w-auto text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:border file:border-[#e5e4dc] file:text-[11px] file:bg-white file:font-semibold hover:file:bg-cream-100 file:cursor-pointer">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    let policyTierIndex = <?php echo max(count($parsedPolicy['rules']), 1); ?>;

    function addPolicyTierRow() {
        policyTierIndex++;
        const container = document.getElementById('policyTiersContainer');
        const div = document.createElement('div');
        div.className = 'policy-tier-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 p-3 bg-cream-50 border border-[#e5e4dc] items-center relative group';
        div.innerHTML = `
            <div class="sm:col-span-4">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Cancellation Window</label>
                <input type="text" name="cancellation_tiers[${policyTierIndex}][timeline]" required placeholder="e.g. 10 to 14 Days Before Check-in" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-semibold focus:border-sage-700 outline-none">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Refund Return</label>
                <input type="text" name="cancellation_tiers[${policyTierIndex}][refund]" required placeholder="e.g. 80% Refund" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] font-bold text-sage-900 focus:border-sage-700 outline-none">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[9px] font-bold text-slate-600 mb-0.5">Deduction / Fee</label>
                <input type="text" name="cancellation_tiers[${policyTierIndex}][deduction]" placeholder="e.g. 20% Fee" class="w-full text-xs p-1.5 bg-white border border-[#e5e4dc] text-slate-600 focus:border-sage-700 outline-none">
            </div>
            <div class="sm:col-span-2 flex items-center justify-between gap-1.5 pt-3 sm:pt-0">
                <select name="cancellation_tiers[${policyTierIndex}][badge_type]" class="w-full text-[11px] p-1.5 bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none font-medium">
                    <option value="full_refund">Full Refund</option>
                    <option value="partial_refund" selected>Partial Refund</option>
                    <option value="no_refund">Non-Refundable</option>
                </select>
                <button type="button" onclick="this.closest('.policy-tier-row').remove()" class="w-7 h-7 bg-white hover:bg-rose-50 text-rose-600 border border-[#e5e4dc] hover:border-rose-300 flex items-center justify-center text-xs shrink-0 transition-colors" title="Delete Tier">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
        if (container) {
            container.appendChild(div);
        }
    }

    // Live Location Autocomplete & Auto-fill System
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('locationSearchInput');
        const suggestionsBox = document.getElementById('locationSuggestions');
        const spinner = document.getElementById('locationSearchSpinner');
        const cityField = document.getElementById('hotelCity');
        const stateField = document.getElementById('hotelState');
        const countryField = document.getElementById('hotelCountry');
        const addressField = document.getElementById('hotelAddress');
        const nameField = document.getElementById('hotelName');

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
                fetch(`../api/places.php?q=${encodeURIComponent(val)}&type=hotel`)
                    .then(res => res.json())
                    .then(data => {
                        if (spinner) spinner.classList.add('hidden');
                        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                            renderSuggestions(data.data);
                        } else {
                            suggestionsBox.innerHTML = '<div class="p-3 text-xs text-slate-400 text-center">No matching locations found. You can enter manually below.</div>';
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
                
                const hasDbPill = item.has_hotels ? `<span class="text-[9px] font-bold bg-sage-50 text-sage-800 border border-sage-200 px-1.5 py-0.5">In Database</span>` : '';
                
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
                    // Auto-fill form fields
                    if (cityField && item.city) cityField.value = item.city;
                    if (stateField && item.state) stateField.value = item.state;
                    if (countryField && item.country) countryField.value = item.country;
                    if (addressField && item.full_address) addressField.value = item.full_address;

                    // If name is blank, suggest a clean name prefix
                    if (nameField && !nameField.value.trim()) {
                        nameField.value = item.name ? `${item.name} Luxury Stay` : `${item.city} Resort & Spa`;
                    }

                    input.value = item.full_address || item.name;
                    suggestionsBox.classList.add('hidden');

                    // Flash feedback highlight
                    [cityField, stateField, countryField, addressField].forEach(f => {
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

        // Close on outside click
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
