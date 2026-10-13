<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/db.php';

$siteName = getSetting('site_name', 'Orion Advent');
$siteWhatsapp = getSetting('site_whatsapp', '919876543210');
$sitePhone = getSetting('site_phone', '+91 98765 43210');
$globalSettings = getGlobalSettings();

$advanceType = $globalSettings['booking_advance_type'] ?? 'percentage';
$advancePercent = (float)($globalSettings['booking_advance_percent'] ?? 30);
$advanceFixed = (float)($globalSettings['booking_advance_fixed'] ?? 2500);
$razorpayKeyId = trim($globalSettings['razorpay_key_id'] ?? '');
$paymentGatewayProvider = trim($globalSettings['payment_gateway_provider'] ?? 'razorpay');

// Auto-fill logged-in user credentials
$userLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';

$pdoDetails = getDBConnection();

if ($userLoggedIn && (empty($userName) || empty($userPhone) || empty($userEmail)) && $pdoDetails) {
    try {
        $uStmt = $pdoDetails->prepare("SELECT name, email, phone FROM users WHERE id = ? LIMIT 1");
        $uStmt->execute([(int)$_SESSION['user_id']]);
        $uData = $uStmt->fetch();
        if ($uData) {
            if (empty($userName)) $userName = $uData['name'] ?? '';
            if (empty($userPhone)) $userPhone = $uData['phone'] ?? '';
            if (empty($userEmail)) $userEmail = $uData['email'] ?? '';
            $_SESSION['user_name'] = $userName;
            $_SESSION['user_phone'] = $userPhone;
            $_SESSION['user_email'] = $userEmail;
        }
    } catch (Exception $e) {
    }
}

// 100% Dynamic Hotel Resolution from Database
$rawHotelSearch = isset($_GET['id']) ? trim($_GET['id']) : (isset($_GET['slug']) ? trim($_GET['slug']) : (isset($_GET['hotel']) ? trim($_GET['hotel']) : ''));
$fetchedHotel = null;
$dbHotelId = null;

if ($pdoDetails) {
    if (!empty($rawHotelSearch)) {
        if (strpos($rawHotelSearch, 'HTL-DB-') === 0) {
            $dbHotelId = (int)str_replace('HTL-DB-', '', $rawHotelSearch);
        } elseif (is_numeric($rawHotelSearch) && (int)$rawHotelSearch > 0) {
            $dbHotelId = (int)$rawHotelSearch;
        }

        if ($dbHotelId) {
            $dbStmt = $pdoDetails->prepare("SELECT * FROM `hotels` WHERE `id` = ?");
            $dbStmt->execute([$dbHotelId]);
            $fetchedHotel = $dbStmt->fetch();
        }

        if (!$fetchedHotel) {
            $dbStmt = $pdoDetails->prepare("SELECT * FROM `hotels` WHERE `slug` = ? OR `name` LIKE ? LIMIT 1");
            $dbStmt->execute([$rawHotelSearch, '%' . $rawHotelSearch . '%']);
            $fetchedHotel = $dbStmt->fetch();
            if ($fetchedHotel) {
                $dbHotelId = (int)$fetchedHotel['id'];
            }
        }
    }

    // If no specific hotel requested or not found, pick the latest active hotel from Database
    if (!$fetchedHotel) {
        $dbStmt = $pdoDetails->query("SELECT * FROM `hotels` WHERE `status` = 'active' ORDER BY `id` DESC LIMIT 1");
        $fetchedHotel = $dbStmt->fetch();
        if ($fetchedHotel) {
            $dbHotelId = (int)$fetchedHotel['id'];
        }
    }
}

// If database has no hotels at all
if (!$fetchedHotel) {
    $pageTitle = 'Hotel Not Found | ' . htmlspecialchars($siteName);
    require_once 'components/header.php';
    require_once 'components/navbar.php';
?>
    <main class="w-full min-h-[60vh] flex items-center justify-center py-16 px-4 bg-slate-50">
        <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 p-8 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-hotel"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900">Hotel Not Found</h1>
            <p class="text-xs text-slate-500 leading-relaxed">The requested stay could not be located in our verified collection. Please explore our other destinations or contact our team for assistance.</p>
            <a href="search.php?type=hotel" class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition">
                <i class="fa-solid fa-compass text-xs"></i>
                <span>Explore Stays</span>
            </a>
        </div>
    </main>
<?php
    require_once 'components/footer.php';
    exit();
}

// 1. Dynamic Gallery Images Fetching
$galleryList = [];
if (!empty($fetchedHotel['featured_image'])) {
    $galleryList[] = $fetchedHotel['featured_image'];
}

$gStmt = $pdoDetails->prepare("SELECT `image_url` FROM `hotel_images` WHERE `hotel_id` = ? ORDER BY `sort_order` ASC, `id` ASC");
$gStmt->execute([$dbHotelId]);
$galleryRows = $gStmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($galleryRows as $gr) {
    if (!empty($gr) && !in_array($gr, $galleryList)) {
        $galleryList[] = $gr;
    }
}

if (empty($galleryList)) {
    $galleryList[] = 'assets/images/placeholder-hotel.jpg';
}

// 2. Dynamic Room Categories Fetching
$rStmt = $pdoDetails->prepare("SELECT * FROM `hotel_rooms` WHERE `hotel_id` = ? AND `status` = 'available' ORDER BY `price_per_night` ASC, `id` ASC");
$rStmt->execute([$dbHotelId]);
$fetchedRooms = $rStmt->fetchAll();

$formattedRooms = [];
foreach ($fetchedRooms as $fr) {
    $roomImage = !empty($fr['image_url']) ? $fr['image_url'] : (!empty($fetchedHotel['featured_image']) ? $fetchedHotel['featured_image'] : 'assets/images/placeholder-hotel.jpg');

    $inclusions = [];
    if (!empty($fr['meal_plan'])) {
        $inclusions[] = trim($fr['meal_plan']);
    }
    if (!empty($fr['amenities'])) {
        $customAms = array_filter(array_map('trim', explode(',', $fr['amenities'])));
        $inclusions = array_merge($inclusions, $customAms);
    }
    if (empty($inclusions)) {
        $inclusions = ['Free High-Speed Wi-Fi', 'Air Conditioning', 'Ensuite Bathroom'];
    }

    $roomPrice = (float)$fr['price_per_night'];
    $roomOrigPrice = (float)($fr['original_price'] ?: ($roomPrice * 1.25));
    $discountPercent = ($roomOrigPrice > $roomPrice) ? round((($roomOrigPrice - $roomPrice) / $roomOrigPrice) * 100) : 0;

    $formattedRooms[] = [
        'id' => 'room-db-' . $fr['id'],
        'db_id' => $fr['id'],
        'name' => $fr['room_name'],
        'type_badge' => $fr['room_type'] ?: 'Deluxe Room',
        'price' => $roomPrice,
        'original_price' => $roomOrigPrice,
        'discount_percent' => $discountPercent,
        'size' => $fr['room_size'] ?: 'Spacious Room',
        'bed' => $fr['bed_type'] ?: '1 Double Bed',
        'adults' => (int)($fr['max_adults'] ?: 2),
        'children' => (int)($fr['max_children'] ?: 0),
        'occupancy' => ($fr['max_adults'] ?: 2) . ' Adults' . (($fr['max_children'] > 0) ? ', ' . $fr['max_children'] . ' Child' : ''),
        'image' => $roomImage,
        'amenities' => $inclusions,
        'cancellation' => 'Flexible cancellation as per hotel policy'
    ];
}

// Default room fallback if admin hasn't added rooms yet
if (empty($formattedRooms)) {
    $basePrice = (float)$fetchedHotel['starting_price'];
    $origPrice = (float)($fetchedHotel['original_price'] ?: ($basePrice * 1.25));
    $formattedRooms[] = [
        'id' => 'room-db-def',
        'db_id' => 0,
        'name' => 'Standard Deluxe Stay',
        'type_badge' => 'Deluxe Room',
        'price' => $basePrice,
        'original_price' => $origPrice,
        'discount_percent' => round((($origPrice - $basePrice) / $origPrice) * 100),
        'size' => '380 sq ft',
        'bed' => '1 King Bed',
        'adults' => 2,
        'children' => 1,
        'occupancy' => '2 Adults, 1 Child',
        'image' => !empty($fetchedHotel['featured_image']) ? $fetchedHotel['featured_image'] : 'assets/images/placeholder-hotel.jpg',
        'amenities' => ['Free High-Speed Wi-Fi', 'Air Conditioning', 'Ensuite Bathroom'],
        'cancellation' => 'Flexible cancellation as per hotel policy'
    ];
}

// 3. Dynamic Amenities Parsing & Categorization (Single Consolidated Section)
$categorizedAmenities = [
    'Facilities & Services' => [],
    'Wellness & Recreation' => [],
    'Dining & Culinary' => [],
    'Room Comforts' => []
];

if (!empty($fetchedHotel['amenities'])) {
    $decAm = json_decode($fetchedHotel['amenities'], true);
    if (is_array($decAm)) {
        foreach ($decAm as $amItem) {
            $amName = '';
            $amIcon = 'fa-solid fa-circle-check';
            if (is_array($amItem)) {
                $amName = trim($amItem['name'] ?? '');
                $amIcon = !empty($amItem['icon']) ? $amItem['icon'] : 'fa-solid fa-circle-check';
            } elseif (is_string($amItem)) {
                $amName = trim($amItem);
            }

            if (!empty($amName)) {
                $lowName = strtolower($amName);
                if (str_contains($lowName, 'spa') || str_contains($lowName, 'gym') || str_contains($lowName, 'fitness') || str_contains($lowName, 'pool') || str_contains($lowName, 'swimming') || str_contains($lowName, 'jacuzzi') || str_contains($lowName, 'wellness') || str_contains($lowName, 'sauna') || str_contains($lowName, 'beach')) {
                    $categorizedAmenities['Wellness & Recreation'][] = ['icon' => $amIcon, 'name' => $amName];
                } elseif (str_contains($lowName, 'breakfast') || str_contains($lowName, 'restaurant') || str_contains($lowName, 'dining') || str_contains($lowName, 'bar') || str_contains($lowName, 'cafe') || str_contains($lowName, 'food') || str_contains($lowName, 'lounge')) {
                    $categorizedAmenities['Dining & Culinary'][] = ['icon' => $amIcon, 'name' => $amName];
                } elseif (str_contains($lowName, 'ac') || str_contains($lowName, 'conditioning') || str_contains($lowName, 'tv') || str_contains($lowName, 'kettle') || str_contains($lowName, 'bed') || str_contains($lowName, 'shower') || str_contains($lowName, 'bath') || str_contains($lowName, 'balcony')) {
                    $categorizedAmenities['Room Comforts'][] = ['icon' => $amIcon, 'name' => $amName];
                } else {
                    $categorizedAmenities['Facilities & Services'][] = ['icon' => $amIcon, 'name' => $amName];
                }
            }
        }
    }
}

// Remove empty categories
$categorizedAmenities = array_filter($categorizedAmenities, function ($group) {
    return !empty($group);
});

// 4. Dynamic Policies Parsing
$parsedHotelPolicy = [
    'summary' => 'Cancellation rules as per hotel booking schedule.',
    'rules' => [
        ['timeline' => '15+ Days Before Check-in', 'refund' => '100% Refund', 'deduction' => '0% Fee', 'badge_type' => 'full_refund'],
        ['timeline' => '7 to 14 Days Before Check-in', 'refund' => '70% Refund', 'deduction' => '30% Fee', 'badge_type' => 'partial_refund'],
        ['timeline' => '3 to 6 Days Before Check-in', 'refund' => '30% Refund', 'deduction' => '70% Fee', 'badge_type' => 'partial_refund'],
        ['timeline' => 'Within 48 Hours / No-Show', 'refund' => '0% (Non-Refundable)', 'deduction' => '100% Non-Refundable', 'badge_type' => 'no_refund']
    ],
    'child_policy' => 'Children up to 6 years stay complimentary in existing bedding.',
    'id_proof' => 'Government-issued photo identification (Aadhaar, Passport, Driving License) required for all adult guests checking in.'
];

if (!empty($fetchedHotel['policies'])) {
    $decPol = json_decode($fetchedHotel['policies'], true);
    if (is_array($decPol)) {
        if (!empty($decPol['summary'])) $parsedHotelPolicy['summary'] = $decPol['summary'];
        if (!empty($decPol['rules']) && is_array($decPol['rules'])) $parsedHotelPolicy['rules'] = $decPol['rules'];
        if (!empty($decPol['child_policy'])) $parsedHotelPolicy['child_policy'] = $decPol['child_policy'];
        if (!empty($decPol['id_proof'])) $parsedHotelPolicy['id_proof'] = $decPol['id_proof'];
    } else {
        $parsedHotelPolicy['summary'] = $fetchedHotel['policies'];
    }
}

// Construct Final Dynamic Hotel Object
$starVal = (float)($fetchedHotel['star_rating'] ?: 4.5);
$propType = $fetchedHotel['property_type'] ?: 'Luxury Resort';
$locationStr = $fetchedHotel['address'] ?: ($fetchedHotel['city'] . (!empty($fetchedHotel['country']) ? ', ' . $fetchedHotel['country'] : ''));

$baseHotelPrice = (float)$fetchedHotel['starting_price'];
$origHotelPrice = (float)($fetchedHotel['original_price'] ?: ($baseHotelPrice * 1.25));
$overallDiscount = ($origHotelPrice > $baseHotelPrice) ? round((($origHotelPrice - $baseHotelPrice) / $origHotelPrice) * 100) : 0;

$mapQuery = !empty($fetchedHotel['address']) ? ($fetchedHotel['name'] . ', ' . $fetchedHotel['address']) : ($fetchedHotel['name'] . ', ' . $fetchedHotel['city']);
$mapEmbedUrl = !empty($fetchedHotel['map_embed_url']) ? $fetchedHotel['map_embed_url'] : ('https://maps.google.com/maps?q=' . urlencode($mapQuery) . '&t=&z=14&ie=UTF8&iwloc=&output=embed');

$hotel = [
    'id' => 'HTL-DB-' . $fetchedHotel['id'],
    'db_id' => $fetchedHotel['id'],
    'name' => $fetchedHotel['name'],
    'description' => $fetchedHotel['description'] ?: 'Enjoy an extraordinary stay at ' . $fetchedHotel['name'] . ', featuring well-appointed rooms, modern hospitality, and prime destination access.',
    'location' => $locationStr,
    'city' => $fetchedHotel['city'] . (!empty($fetchedHotel['country']) ? ', ' . $fetchedHotel['country'] : ''),
    'address' => $fetchedHotel['address'] ?: $locationStr,
    'map_query' => $mapQuery,
    'map_embed_url' => $mapEmbedUrl,
    'star_rating_num' => $starVal,
    'property_type' => $propType,
    'badge' => $fetchedHotel['badge'] ?: 'Premier Stay',
    'base_price' => $baseHotelPrice,
    'original_price' => $origHotelPrice,
    'discount_percent' => $overallDiscount,
    'gallery' => $galleryList,
    'rooms' => $formattedRooms,
    'amenities_categories' => $categorizedAmenities,
    'policies' => [
        'check_in' => $fetchedHotel['checkin_time'] ?: '02:00 PM',
        'check_out' => $fetchedHotel['checkout_time'] ?: '11:00 AM',
        'cancellation' => $parsedHotelPolicy['summary'],
        'cancellation_rules' => $parsedHotelPolicy['rules'],
        'child_policy' => $parsedHotelPolicy['child_policy'],
        'id_proof' => $parsedHotelPolicy['id_proof']
    ]
];

$pageTitle = $hotel['name'] . ' - Book Rooms Online | ' . htmlspecialchars($siteName);
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<?php
$totalPhotos = count($hotel['gallery']);
$mainHeroPhoto = $hotel['gallery'][0];
$secondaryPhotos = array_slice($hotel['gallery'], 1, 5);
$hotelRoomCount = count($hotel['rooms']);
?>
<style>
    /* Orion Advent — visual-only hotel detail refresh, using existing brand utility classes */
    .hotel-detail-page {
        font-family: inherit;
        color: #0d203c
    }

    .hotel-detail-page .hotel-container {
        max-width: 1360px;
        margin-left: auto;
        margin-right: auto;
    }

    .hotel-detail-page .hotel-hero {
        position: relative;
        isolation: isolate;
        min-height: 315px;
        width: 100%;
        overflow: hidden;
        background: #e2edf7;
        border-radius: 0;
    }

    .hotel-detail-page .hotel-hero:before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(255, 251, 243, .97) 0%, rgba(255, 250, 243, .86) 29%, rgba(255, 251, 246, .27) 56%, rgba(2, 28, 60, .08) 100%);
        z-index: 1;
    }

    .hotel-detail-page .hotel-hero>img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 47%;
    }

    .hotel-detail-page .hotel-hero-content {
        position: relative;
        z-index: 2;
        min-height: 315px;
        width: 100%;
        max-width: 1360px;
        margin-left: auto;
        margin-right: auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .hotel-detail-page .hotel-hero-copy {
        max-width: 680px;
    }

    .hotel-detail-page .hotel-nav a {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        padding: 14px 4px;
        border-bottom: 2px solid transparent;
        white-space: nowrap;
        transition: color 0.15s, border-color 0.15s;
    }

    .hotel-detail-page .hotel-nav a:hover,
    .hotel-detail-page .hotel-nav a:focus,
    .hotel-detail-page .hotel-nav a.active {
        color: #068285;
        border-bottom-color: #068285;
    }

    .hotel-detail-page .hotel-sticky-nav {
        position: sticky;
        top: 86px;
        z-index: 40;
        transition: top 0.15s ease;
    }

    @media(min-width: 640px) {
        .hotel-detail-page .hotel-sticky-nav {
            top: 115px;
        }
    }

    @media(min-width: 768px) {
        .hotel-detail-page .hotel-sticky-nav {
            top: 132px;
        }
    }

    .hotel-detail-page .hotel-section {
        scroll-margin-top: 195px;
    }

    .hotel-detail-page .hotel-room-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 11px
    }

    .hotel-detail-page .room-option-card {
        overflow: hidden;
        min-width: 0;
        display: flex;
        flex-direction: column
    }

    .hotel-detail-page .room-option-card img {
        width: 100%;
        height: 135px;
        object-fit: cover
    }

    .hotel-detail-page .hotel-input {
        width: 100%;
        border: 1px solid #d8e1ed;
        border-radius: 7px;
        padding: 9px 10px;
        background: white;
        color: #243448;
        outline: none;
        font-size: 12px;
        min-width: 0
    }

    .hotel-detail-page .hotel-input:focus {
        border-color: #068285
    }

    .hotel-detail-page .hotel-gallery-thumb {
        height: 67px;
        min-width: 0;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        border-radius: 6px;
        border: 2px solid rgba(255, 255, 255, .93)
    }

    .hotel-detail-page .hotel-gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .25s
    }

    .hotel-detail-page .hotel-gallery-thumb:hover img {
        transform: scale(1.04)
    }

    .hotel-detail-page .hotel-gallery-thumb:focus-visible {
        outline: 3px solid #068285;
        outline-offset: 2px
    }

    .hotel-detail-page .hotel-blue-header {
        background: linear-gradient(135deg, #068285 0%, #0d9488 45%, #0a4648 100%);
    }

    .hotel-detail-page .hotel-amenity-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #f0fdfa;
        flex-shrink: 0
    }

    .hotel-detail-page .hotel-showcase {
        position: relative
    }

    .hotel-detail-page .hotel-hero-content .hotel-gallery-strip {
        position: absolute;
        z-index: 4;
        right: 28px;
        bottom: 24px;
        width: min(44%, 540px);
        padding: 0;
    }

    .hotel-detail-page .hotel-highlight-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 9px;
    }

    @media(min-width:1024px) {
        .hotel-detail-page .hotel-left {
            min-width: 0;
            flex: 1;
        }

        .hotel-detail-page .hotel-right {
            width: 350px;
            flex: 0 0 350px;
            position: sticky;
            top: 195px;
            z-index: 20;
        }
    }

    @media(max-width:1199px) {
        .hotel-detail-page .hotel-room-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hotel-detail-page .hotel-highlight-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width:767px) {
        .hotel-detail-page .hotel-hero {
            min-height: 400px;
            border-radius: 0;
        }

        .hotel-detail-page .hotel-hero-content {
            min-height: 400px;
            padding-bottom: 95px;
        }

        .hotel-detail-page .hotel-hero:before {
            background: linear-gradient(90deg, rgba(255, 251, 245, .95), rgba(255, 250, 243, .65) 72%, rgba(255, 255, 255, .16));
        }

        .hotel-detail-page .hotel-room-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hotel-detail-page .hotel-gallery-thumb {
            height: 58px;
        }

        .hotel-detail-page .hotel-hero-content .hotel-gallery-strip {
            width: calc(100% - 24px);
            left: 12px;
            right: 12px;
            bottom: 12px;
        }

        .hotel-detail-page .hotel-highlight-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr))
        }
    }

    @media(max-width:430px) {
        .hotel-detail-page .hotel-room-grid {
            grid-template-columns: 1fr
        }

        .hotel-detail-page .hotel-hero {
            min-height: 485px
        }

        .hotel-detail-page .hotel-hero-content {
            min-height: 485px
        }
    }
</style>
<div class="hotel-detail-page bg-[#f5f8fc] pb-20 lg:pb-0">
    <div class="bg-white border-b border-slate-200 px-3 sm:px-7 xl:px-10">
        <div class="hotel-container flex justify-between items-center gap-4 py-2.5 text-[11px] text-slate-500">
            <nav class="flex items-center gap-2 min-w-0 overflow-hidden whitespace-nowrap" aria-label="Breadcrumb">
                <a href="index.php" class="hover:text-brand-600">Home</a><i class="fa-solid fa-chevron-right text-[9px]"></i>
                <a href="search.php?type=hotel" class="hover:text-brand-600">Hotels</a><i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="truncate font-semibold text-slate-700"><?= htmlspecialchars($hotel['name']) ?></span>
            </nav>
            <a href="search.php?type=hotel" class="hidden sm:inline-flex items-center gap-2 font-semibold hover:text-brand-600"><i class="fa-solid fa-arrow-left"></i> Back to hotels</a>
        </div>
    </div>
    <section class="hotel-showcase bg-white border-b border-slate-200">
        <div class="hotel-hero">
            <img src="<?= htmlspecialchars($mainHeroPhoto) ?>" alt="<?= htmlspecialchars($hotel['name']) ?> hotel" fetchpriority="high">
            <div class="hotel-container hotel-hero-content flex flex-col justify-center px-3 sm:px-7 xl:px-10 py-6 sm:py-8 relative">
                <div class="hotel-hero-copy">
                    <div class="flex flex-wrap gap-2.5 items-center text-[11px] font-semibold mb-3">
                        <span class="bg-rose-500 text-white px-3 py-1 rounded-md"><?= htmlspecialchars($hotel['badge']) ?></span>
                        <span class="text-slate-700 font-bold"><?= htmlspecialchars($hotel['property_type']) ?></span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-700 font-bold"><i class="fa-solid fa-location-dot text-brand-600 mr-1"></i><?= htmlspecialchars($hotel['city']) ?></span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-800 font-bold"><i class="fa-solid fa-star text-amber-500 mr-1"></i><?= number_format($hotel['star_rating_num'], 1) ?> / 5</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-[40px] tracking-tight leading-[1.12] font-extrabold text-slate-950 mb-3"><?= htmlspecialchars($hotel['name']) ?></h1>
                    <p class="text-[13px] sm:text-[14px] leading-relaxed text-slate-700 max-w-2xl line-clamp-2 overflow-hidden text-ellipsis"><?= htmlspecialchars($hotel['description']) ?></p>
                </div>
                <?php if ($totalPhotos > 1): ?>
                    <div class="hotel-gallery-strip grid grid-cols-5 gap-1.5 sm:gap-2">
                        <?php foreach (array_slice($hotel['gallery'], 0, 5) as $i => $photo): ?>
                            <button type="button" onclick="openPhotoGalleryModal(<?= $i ?>)" class="hotel-gallery-thumb <?= $i === 0 ? 'ring-2 ring-brand-600' : '' ?>" aria-label="View hotel photo <?= $i + 1 ?>">
                                <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($hotel['name']) ?> hotel photo <?= $i + 1 ?>" loading="lazy">
                                <?php if ($i === 4 && $totalPhotos > 5): ?><span class="absolute inset-0 bg-slate-950/70 flex items-center justify-center text-white text-[10px] font-bold">+<?= $totalPhotos - 5 ?> Photos</span><?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <div id="hotelStickyNavBar" class="hotel-sticky-nav sticky z-40 bg-white/95 backdrop-blur-md border-y border-slate-200 px-3 sm:px-7 xl:px-10 shadow-xs">
        <div class="hotel-container flex items-center justify-between gap-5">
            <nav id="hotelSubNavMenu" class="hotel-nav flex items-center gap-5 sm:gap-8 overflow-x-auto no-scrollbar text-xs font-bold text-slate-600">
                <a href="#hotelOverviewSec" class="active"><i class="fa-solid fa-circle-info"></i>Overview</a>
                <a href="#roomSelectionSec"><i class="fa-solid fa-bed"></i>Rooms</a>
                <a href="#amenitiesSec"><i class="fa-solid fa-bell-concierge"></i>Amenities</a>
                <a href="#locationSec"><i class="fa-solid fa-location-dot"></i>Location</a>
                <a href="#policiesSec"><i class="fa-solid fa-file-lines"></i>Policies</a>
            </nav>
            <a href="#roomSelectionSec" class="hidden lg:flex bg-brand-600 text-white text-xs font-bold px-5 py-2.5 rounded-lg shrink-0 hover:bg-brand-700 transition">Choose Room <i class="fa-solid fa-arrow-right ml-2"></i></a>
        </div>
    </div>
    <main class="px-3 sm:px-7 xl:px-10 py-6">
        <div class="hotel-container flex flex-col lg:flex-row items-start gap-5">
            <div class="hotel-left w-full min-w-0 space-y-6">
                <section id="hotelOverviewSec" class="hotel-section bg-white border border-slate-200 rounded-xl p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Hotel Overview</h2>
                            <p class="text-xs text-slate-500">Property information, timings and location overview</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 bg-brand-50 text-brand-700 border border-brand-200 rounded-md">
                            <i class="fa-solid fa-hotel mr-1"></i><?= htmlspecialchars($hotel['property_type']) ?>
                        </span>
                    </div>
                    <p class="text-[13px] leading-7 text-slate-600">
                        <?= nl2br(htmlspecialchars($hotel['description'])) ?>
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <i class="fa-regular fa-clock text-brand-600 mb-1.5 block text-sm"></i>
                            <div class="text-[10px] text-slate-500 font-medium">Check-In</div>
                            <strong class="text-xs text-slate-900 font-bold"><?= htmlspecialchars($hotel['policies']['check_in']) ?></strong>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <i class="fa-solid fa-right-from-bracket text-brand-600 mb-1.5 block text-sm"></i>
                            <div class="text-[10px] text-slate-500 font-medium">Check-Out</div>
                            <strong class="text-xs text-slate-900 font-bold"><?= htmlspecialchars($hotel['policies']['check_out']) ?></strong>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <i class="fa-solid fa-location-dot text-brand-600 mb-1.5 block text-sm"></i>
                            <div class="text-[10px] text-slate-500 font-medium">Location</div>
                            <strong class="text-xs text-slate-900 font-bold truncate block" title="<?= htmlspecialchars($hotel['city']) ?>"><?= htmlspecialchars($hotel['city']) ?></strong>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <i class="fa-solid fa-star text-amber-500 mb-1.5 block text-sm"></i>
                            <div class="text-[10px] text-slate-500 font-medium">Guest Rating</div>
                            <strong class="text-xs text-slate-900 font-bold"><?= number_format($hotel['star_rating_num'], 1) ?> / 5</strong>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 col-span-2 sm:col-span-1">
                            <i class="fa-solid fa-bed text-brand-600 mb-1.5 block text-sm"></i>
                            <div class="text-[10px] text-slate-500 font-medium">Room Categories</div>
                            <strong class="text-xs text-slate-900 font-bold"><?= $hotelRoomCount ?> Available</strong>
                        </div>
                    </div>
                </section>
                <section id="roomSelectionSec" class="hotel-section">
                    <div class="flex flex-wrap justify-between items-end gap-3 mb-4">
                        <div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Choose Your Room</h2>
                            <p class="text-xs text-slate-500 mt-1">Compare room facilities and select the perfect stay</p>
                        </div><span class="text-xs text-slate-500"><?= $hotelRoomCount ?> room categories</span>
                    </div>
                    <div class="hotel-room-grid">
                        <?php foreach ($hotel['rooms'] as $idx => $room): ?>
                            <article class="room-option-card bg-white border rounded-xl <?= $idx === 0 ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-slate-200' ?>" data-room-id="<?= htmlspecialchars($room['id']) ?>" data-room-name="<?= htmlspecialchars($room['name']) ?>" data-room-price="<?= (float)$room['price'] ?>">
                                <div class="relative"><img src="<?= htmlspecialchars($room['image']) ?>" alt="<?= htmlspecialchars($room['name']) ?>" loading="lazy"><span class="absolute left-3 top-3 bg-white/95 text-slate-800 rounded-md px-2 py-1 text-[10px] font-bold"><?= htmlspecialchars($room['type_badge']) ?></span><?php if ($room['discount_percent'] > 0): ?><span class="absolute right-3 top-3 bg-rose-600 text-white px-2 py-1 rounded-md text-[10px] font-bold"><?= (int)$room['discount_percent'] ?>% OFF</span><?php endif; ?></div>
                                <div class="p-4 flex flex-col gap-3">
                                    <div>
                                        <h3 class="text-sm font-extrabold text-slate-900 leading-snug"><?= htmlspecialchars($room['name']) ?></h3>
                                        <div class="mt-1 flex flex-wrap gap-3 text-[11px] text-slate-500"><span><i class="fa-solid fa-users mr-1"></i><?= htmlspecialchars($room['occupancy']) ?></span><span><i class="fa-solid fa-expand mr-1"></i><?= htmlspecialchars($room['size']) ?></span></div>
                                    </div>
                                    <p class="text-[11px] text-slate-600"><i class="fa-solid fa-bed mr-1 text-brand-600"></i><?= htmlspecialchars($room['bed']) ?></p>
                                    <div class="min-h-[50px] flex flex-wrap content-start gap-x-3 gap-y-1 text-[11px] text-slate-600"><?php foreach (array_slice($room['amenities'], 0, 4) as $amenity): ?><span><i class="fa-solid fa-check text-emerald-600 mr-1"></i><?= htmlspecialchars($amenity) ?></span><?php endforeach; ?></div>
                                    <div class="border-t border-slate-100 pt-3 mt-auto">
                                        <div class="flex items-center gap-2"><strong class="text-xl font-extrabold text-brand-700">₹<?= number_format($room['price']) ?></strong><?php if ($room['original_price'] > $room['price']): ?><s class="text-xs text-slate-400">₹<?= number_format($room['original_price']) ?></s><?php endif; ?></div>
                                        <div class="text-[10px] text-slate-500 mb-3">per night • taxes extra</div>
                                        <button type="button" class="select-room-btn w-full rounded-lg border border-brand-600 font-bold text-xs py-2.5 transition <?= $idx === 0 ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-800 hover:bg-brand-600 hover:text-white' ?>" data-room-id="<?= htmlspecialchars($room['id']) ?>" data-room-name="<?= htmlspecialchars($room['name']) ?>" data-room-price="<?= (float)$room['price'] ?>"><?= $idx === 0 ? '✓ Selected' : 'Select Room' ?></button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php if (!empty($hotel['amenities_categories'])): ?>
                    <section id="amenitiesSec" class="hotel-section bg-white border border-slate-200 rounded-xl p-5 sm:p-6">
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-1">Amenities & Facilities</h2>
                        <p class="text-xs text-slate-500 mb-5">Facilities listed by the property</p>
                        <div class="grid sm:grid-cols-2 gap-5"><?php foreach ($hotel['amenities_categories'] as $catName => $items): ?><div class="border border-slate-200 rounded-lg p-4">
                                    <h3 class="text-[13px] font-bold text-slate-800 mb-3"><?= htmlspecialchars($catName) ?></h3>
                                    <div class="grid grid-cols-1 gap-3"><?php foreach ($items as $it): ?><div class="flex items-center gap-3 text-xs text-slate-600"><span class="hotel-amenity-icon text-brand-600"><i class="<?= htmlspecialchars($it['icon']) ?>"></i></span><?= htmlspecialchars($it['name']) ?></div><?php endforeach; ?></div>
                                </div><?php endforeach; ?></div>
                    </section><?php endif; ?>
                <section id="locationSec" class="hotel-section bg-white border border-slate-200 rounded-xl p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row items-stretch gap-4">
                        <!-- Left: Address & Quick Actions -->
                        <div class="flex-1 flex flex-col justify-between space-y-2.5">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="w-6 h-6 rounded-md bg-teal-50 text-brand-600 flex items-center justify-center text-xs shrink-0">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </span>
                                    <h2 class="text-sm font-extrabold text-slate-900">Hotel Location &amp; Map</h2>
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                                        Prime Locality
                                    </span>
                                </div>
                                <strong class="text-xs font-bold text-slate-900 block"><?= htmlspecialchars($hotel['name']) ?></strong>
                                <p class="text-xs text-slate-600 leading-relaxed mt-0.5"><?= htmlspecialchars($hotel['location']) ?></p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($hotel['map_query']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition shadow-2xs">
                                    <i class="fa-solid fa-diamond-turn-right text-[10px]"></i>
                                    <span>Get Directions</span>
                                </a>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($hotel['map_query']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold px-3 py-1.5 rounded-lg transition">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    <span>Open in Maps</span>
                                </a>
                            </div>
                        </div>

                        <!-- Right: Compact Mini Map -->
                        <div class="w-full sm:w-[260px] md:w-[290px] h-[135px] rounded-lg overflow-hidden border border-slate-200 relative shrink-0">
                            <iframe 
                                src="<?= htmlspecialchars($hotel['map_embed_url']) ?>" 
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade" 
                                title="<?= htmlspecialchars($hotel['name']) ?> Map"
                                class="w-full h-full">
                            </iframe>
                        </div>
                    </div>
                </section>
                <section id="policiesSec" class="hotel-section bg-white border border-slate-200 rounded-xl p-5 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Hotel Policies & House Rules</h2>
                    <p class="text-xs text-slate-600 mb-4"><?= htmlspecialchars($hotel['policies']['cancellation']) ?></p>
                    <div class="grid sm:grid-cols-2 gap-3 mb-5">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs"><span class="block text-slate-500 mb-1">Check-in time</span><strong><?= htmlspecialchars($hotel['policies']['check_in']) ?></strong></div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs"><span class="block text-slate-500 mb-1">Check-out time</span><strong><?= htmlspecialchars($hotel['policies']['check_out']) ?></strong></div>
                    </div>
                    <h3 class="font-bold text-sm mb-3">Cancellation schedule</h3>
                    <div class="grid sm:grid-cols-2 gap-3"><?php foreach ($hotel['policies']['cancellation_rules'] as $rule): ?><div class="p-3 rounded-lg border border-slate-200 bg-slate-50 text-xs"><strong class="block text-slate-900"><?= htmlspecialchars($rule['timeline'] ?? '') ?></strong><span class="text-brand-700 font-bold block mt-1"><?= htmlspecialchars($rule['refund'] ?? '') ?></span><span class="text-slate-500"><?= htmlspecialchars($rule['deduction'] ?? '') ?></span></div><?php endforeach; ?></div>
                    <div class="grid sm:grid-cols-2 gap-4 mt-5 pt-5 border-t border-slate-200 text-xs">
                        <div><strong class="block mb-2"><i class="fa-solid fa-child text-brand-600 mr-1"></i>Child & Extra Bedding</strong>
                            <p class="leading-6 text-slate-600"><?= htmlspecialchars($hotel['policies']['child_policy']) ?></p>
                        </div>
                        <div><strong class="block mb-2"><i class="fa-solid fa-id-card text-brand-600 mr-1"></i>Identification Requirements</strong>
                            <p class="leading-6 text-slate-600"><?= htmlspecialchars($hotel['policies']['id_proof']) ?></p>
                        </div>
                    </div>
                </section>
            </div>
            <aside id="hotelBookingSidebar" class="hotel-right w-full lg:sticky lg:top-[195px]">
                <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
                    <div class="hotel-blue-header px-5 py-4 text-white">
                        <div class="flex items-center justify-between text-[11px]"><span class="font-semibold">Starting from</span><span class="bg-white/20 rounded-full px-2 py-1"><i class="fa-solid fa-bolt mr-1"></i>Room Booking</span></div>
                        <div class="flex items-baseline gap-2 mt-1"><strong id="sidebarNightlyRate" class="text-[28px] font-extrabold">₹<?= number_format($hotel['rooms'][0]['price']) ?></strong><s class="text-xs text-white/70">₹<?= number_format($hotel['rooms'][0]['original_price']) ?></s></div>
                        <p class="text-[11px] text-white/85">per room / night • excluding taxes</p>
                    </div>
                    <form id="hotelReservationForm" onsubmit="event.preventDefault(); submitHotelReservation();" class="p-4 sm:p-5 space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <div class="text-[10px] text-slate-500">Selected Room</div><strong id="selectedRoomName" class="text-[13px] text-slate-900"><?= htmlspecialchars($hotel['rooms'][0]['name']) ?></strong>
                            </div><a href="#roomSelectionSec" class="text-xs text-brand-700 font-semibold underline">Change</a>
                        </div>
                        <div class="grid grid-cols-2 gap-2"><label class="text-[11px] font-bold text-slate-700">Check-in<input type="date" required id="checkInDate" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="hotel-input mt-1.5"></label><label class="text-[11px] font-bold text-slate-700">Check-out<input type="date" required id="checkOutDate" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" class="hotel-input mt-1.5"></label></div>
                        <div class="grid grid-cols-2 gap-2"><label class="text-[11px] font-bold text-slate-700">Rooms<select id="roomsCountSelect" class="hotel-input mt-1.5"><?php for ($i = 1; $i <= 4; $i++): ?><option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Room' : 'Rooms' ?></option><?php endfor; ?></select></label><label class="text-[11px] font-bold text-slate-700">Adults<select id="guestsCountSelect" class="hotel-input mt-1.5"><?php for ($i = 1; $i <= 8; $i++): ?><option value="<?= $i ?>" <?= $i === 2 ? 'selected' : '' ?>><?= $i ?> <?= $i === 1 ? 'Adult' : 'Adults' ?></option><?php endfor; ?></select></label></div>
                        <div class="border-t border-slate-100 pt-3">
                            <h3 class="text-xs font-extrabold mb-3">Traveler Details</h3>
                            <div class="space-y-2.5"><label class="text-[11px] text-slate-700 font-semibold block">Full Name<input id="guestName" required type="text" autocomplete="name" value="<?= htmlspecialchars($userName) ?>" placeholder="Enter guest name" class="hotel-input mt-1"></label><label class="text-[11px] text-slate-700 font-semibold block">Phone Number<input id="guestPhone" required type="tel" autocomplete="tel" value="<?= htmlspecialchars($userPhone) ?>" placeholder="Mobile number" class="hotel-input mt-1"></label></div>
                        </div>
                        <div class="bg-slate-50 rounded-lg border border-slate-200 p-3 space-y-2 text-[11px]">
                            <div class="font-extrabold text-[13px] mb-2">Fare Summary</div>
                            <div class="flex justify-between gap-3 text-slate-600"><span>Room Total (<span id="summaryNights">2</span> nights × <span id="summaryRooms">1</span> room)</span><strong id="subtotalDisplay" class="text-slate-800">₹0</strong></div>
                            <div class="flex justify-between text-slate-600"><span>Taxes & GST (12%)</span><strong id="taxesDisplay" class="text-slate-800">₹0</strong></div>
                            <div class="border-t border-slate-200 pt-2 flex justify-between text-slate-900 font-extrabold text-sm"><span>Grand Total</span><span id="grandTotalDisplay">₹0</span></div>
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 flex justify-between items-center gap-2">
                                <div><strong class="text-emerald-800 text-xs block">Pay Token Advance</strong><span class="text-[10px] text-slate-500">Balance at property: <b id="hotelBalanceDisplay">₹0</b></span></div><strong id="hotelTokenAdvanceDisplay" class="text-emerald-700 text-base">₹0</strong>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs py-3 rounded-lg"><i class="fa-solid fa-lock mr-2"></i>Book with Token Advance</button>
                        <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/', '', $siteWhatsapp)) ?>?text=<?= urlencode('Hi ' . $siteName . ', I want room availability for ' . $hotel['name']) ?>" target="_blank" rel="noopener noreferrer" class="w-full block text-center border border-brand-500 text-brand-700 font-bold text-xs py-3 rounded-lg hover:bg-brand-50"><i class="fa-brands fa-whatsapp mr-1"></i> Ask About This Hotel</a>
                        <p class="text-center text-[10px] text-slate-500"><i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i>Secure checkout • Balance payable at hotel</p>
                    </form>
                </div>
            </aside>
        </div>
    </main>
</div>

<!-- ==========================================
     MMT PHOTO LIGHTBOX / GALLERY MODAL
=========================================== -->
<div id="hotelPhotoLightboxModal" class="fixed inset-0 z-50 bg-black/95 hidden items-center justify-center flex-col p-4 backdrop-blur-md">
    <!-- Top Bar -->
    <div class="w-full max-w-5xl flex items-center justify-between text-white pb-3 border-b border-white/10 text-xs">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-sm tracking-tight"><?= htmlspecialchars($hotel['name']) ?></span>
            <span class="text-white/40">&bull;</span>
            <span id="lightboxCounter" class="text-white/70">1 / <?= $totalPhotos ?></span>
        </div>
        <button type="button" onclick="closePhotoGalleryModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>

    <!-- Main Active Photo Display -->
    <div class="w-full max-w-5xl flex-1 flex items-center justify-center relative py-4 overflow-hidden">
        <button type="button" onclick="changeLightboxPhoto(-1)" class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/15 hover:bg-white/30 text-white text-base flex items-center justify-center transition z-10">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <img id="lightboxMainImage" src="<?= htmlspecialchars($hotel['gallery'][0]) ?>" alt="Hotel Gallery Preview" class="max-h-[65vh] max-w-full object-contain rounded-xl shadow-2xl transition duration-300">

        <button type="button" onclick="changeLightboxPhoto(1)" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/15 hover:bg-white/30 text-white text-base flex items-center justify-center transition z-10">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>

    <!-- Bottom Thumbnails Strip -->
    <div class="w-full max-w-5xl flex items-center space-x-2 overflow-x-auto no-scrollbar py-2 border-t border-white/10">
        <?php foreach ($hotel['gallery'] as $tIdx => $tImg): ?>
            <button type="button" onclick="selectLightboxIndex(<?= $tIdx ?>)" class="w-16 h-12 rounded-lg overflow-hidden shrink-0 border-2 transition <?= $tIdx === 0 ? 'border-brand-500 scale-105' : 'border-transparent opacity-60 hover:opacity-100' ?>" data-thumb-index="<?= $tIdx ?>">
                <img src="<?= htmlspecialchars($tImg) ?>" alt="Thumb <?= $tIdx ?>" class="w-full h-full object-cover">
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- ==========================================
     MOBILE STICKY BOTTOM RESERVATION BAR
=========================================== -->
<div class="lg:hidden fixed bottom-[60px] left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-3 flex items-center justify-between shadow-lg">
    <div>
        <span class="text-[10px] text-slate-400 block line-through">₹<?= number_format($hotel['original_price']) ?></span>
        <div class="flex items-baseline space-x-1">
            <span class="text-base font-black text-slate-900 leading-none">₹<?= number_format($hotel['base_price']) ?></span>
            <span class="text-[10px] text-slate-500 font-medium">/ night</span>
        </div>
    </div>
    <a href="#hotelBookingSidebar" class="px-5 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center space-x-1.5 shrink-0">
        <span>Reserve Room</span>
        <i class="fa-solid fa-arrow-right text-[10px]"></i>
    </a>
</div>

<!-- Razorpay Official Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<!-- Interactive Price Calculator & Dynamic Booking Logic JS -->
<script>
    const allGalleryPhotos = <?= json_encode($hotel['gallery']) ?>;
    const advanceType = <?= json_encode($advanceType) ?>;
    const advancePercent = <?= (float)$advancePercent ?>;
    const advanceFixed = <?= (float)$advanceFixed ?>;
    const razorpayKeyId = <?= json_encode($razorpayKeyId) ?>;
    const isUserLoggedIn = <?= $userLoggedIn ? 'true' : 'false' ?>;
    const userEmail = <?= json_encode($userEmail) ?>;
    const siteName = <?= json_encode($siteName) ?>;

    function calcHotelAdvance(total, roomsCount) {
        if (advanceType === 'fixed') {
            return Math.min(total, advanceFixed * roomsCount);
        } else {
            return Math.round(total * (advancePercent / 100));
        }
    }

    let currentRoomPrice = <?= (float)$hotel['rooms'][0]['price'] ?>;
    let nights = 2;
    let rooms = 1;

    document.addEventListener('DOMContentLoaded', () => {
        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');
        const roomsSelect = document.getElementById('roomsCountSelect');
        const selectedRoomNameEl = document.getElementById('selectedRoomName');
        const sidebarNightlyRateEl = document.getElementById('sidebarNightlyRate');
        const summaryNightsEl = document.getElementById('summaryNights');
        const summaryRoomsEl = document.getElementById('summaryRooms');
        const subtotalDisplayEl = document.getElementById('subtotalDisplay');
        const taxesDisplayEl = document.getElementById('taxesDisplay');
        const grandTotalDisplayEl = document.getElementById('grandTotalDisplay');
        const hotelTokenAdvEl = document.getElementById('hotelTokenAdvanceDisplay');
        const hotelBalEl = document.getElementById('hotelBalanceDisplay');

        function calculateDates() {
            if (checkInInput && checkOutInput) {
                const inDate = new Date(checkInInput.value);
                const outDate = new Date(checkOutInput.value);
                const diffTime = outDate - inDate;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                nights = diffDays > 0 ? diffDays : 1;
            }
            recalculate();
        }

        function recalculate() {
            rooms = parseInt(roomsSelect?.value || '1', 10);
            const subtotal = currentRoomPrice * nights * rooms;
            const taxes = Math.round(subtotal * 0.12);
            const total = subtotal + taxes;
            const tokenAdv = calcHotelAdvance(total, rooms);
            const balance = Math.max(0, total - tokenAdv);

            if (summaryNightsEl) summaryNightsEl.textContent = nights;
            if (summaryRoomsEl) summaryRoomsEl.textContent = rooms;
            if (sidebarNightlyRateEl) sidebarNightlyRateEl.textContent = '₹' + currentRoomPrice.toLocaleString('en-IN');
            if (subtotalDisplayEl) subtotalDisplayEl.textContent = '₹' + subtotal.toLocaleString('en-IN');
            if (taxesDisplayEl) taxesDisplayEl.textContent = '₹' + taxes.toLocaleString('en-IN');
            if (grandTotalDisplayEl) grandTotalDisplayEl.textContent = '₹' + total.toLocaleString('en-IN');
            if (hotelTokenAdvEl) hotelTokenAdvEl.textContent = '₹' + tokenAdv.toLocaleString('en-IN');
            if (hotelBalEl) hotelBalEl.textContent = '₹' + balance.toLocaleString('en-IN');
        }

        if (checkInInput) checkInInput.addEventListener('change', calculateDates);
        if (checkOutInput) checkOutInput.addEventListener('change', calculateDates);
        if (roomsSelect) roomsSelect.addEventListener('change', recalculate);

        // Room Selection Click Handler
        document.querySelectorAll('.select-room-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const roomId = btn.getAttribute('data-room-id');
                const roomName = btn.getAttribute('data-room-name');
                const roomPrice = parseFloat(btn.getAttribute('data-room-price')) || currentRoomPrice;

                currentRoomPrice = roomPrice;
                if (selectedRoomNameEl) selectedRoomNameEl.textContent = roomName;

                // Update button styles
                document.querySelectorAll('.select-room-btn').forEach(b => {
                    b.textContent = 'Select Room';
                    b.classList.remove('bg-brand-600', 'text-white', 'shadow-brand-600/20');
                    b.classList.add('bg-slate-100', 'text-slate-800', 'hover:bg-brand-600', 'hover:text-white');
                });
                btn.textContent = '✓ Selected';
                btn.classList.remove('bg-slate-100', 'text-slate-800', 'hover:bg-brand-600', 'hover:text-white');
                btn.classList.add('bg-brand-600', 'text-white', 'shadow-brand-600/20');

                // Highlight card
                document.querySelectorAll('.room-option-card').forEach(c => {
                    c.classList.remove('border-brand-500', 'ring-2', 'ring-brand-500/20', 'shadow-sm');
                    c.classList.add('border-slate-200');
                });
                const parentCard = btn.closest('.room-option-card');
                if (parentCard) {
                    parentCard.classList.remove('border-slate-200');
                    parentCard.classList.add('border-brand-500', 'ring-2', 'ring-brand-500/20', 'shadow-sm');
                }

                recalculate();
            });
        });

        calculateDates();
    });

    // Sticky Sub-Navigation & Sidebar Dynamic Offset & Scroll Spy
    (function() {
        function updateStickyOffsets() {
            const mainHeader = document.querySelector('header');
            const subNav = document.getElementById('hotelStickyNavBar');
            const sidebar = document.getElementById('hotelBookingSidebar');
            const headerHeight = mainHeader ? mainHeader.offsetHeight : 130;
            if (subNav) {
                subNav.style.top = headerHeight + 'px';
            }
            if (sidebar && window.innerWidth >= 1024) {
                const subNavHeight = subNav ? subNav.offsetHeight : 46;
                sidebar.style.top = (headerHeight + subNavHeight + 16) + 'px';
            }
        }
        window.addEventListener('resize', updateStickyOffsets);
        window.addEventListener('load', updateStickyOffsets);
        document.addEventListener('DOMContentLoaded', updateStickyOffsets);
        updateStickyOffsets();

        // Active Tab Scroll Spy
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('.hotel-section');
            const navLinks = document.querySelectorAll('#hotelSubNavMenu a');
            if (!sections.length || !navLinks.length) return;
            
            const mainHeader = document.querySelector('header');
            const headerOffset = (mainHeader ? mainHeader.offsetHeight : 130) + 70;
            let currentId = '';

            sections.forEach(sec => {
                const rect = sec.getBoundingClientRect();
                if (rect.top <= headerOffset) {
                    currentId = sec.getAttribute('id');
                }
            });

            if (currentId) {
                navLinks.forEach(link => {
                    if (link.getAttribute('href') === '#' + currentId) {
                        link.classList.add('active', 'text-brand-700', 'border-brand-600');
                    } else {
                        link.classList.remove('active', 'text-brand-700', 'border-brand-600');
                    }
                });
            }
        }, { passive: true });
    })();

    // Gallery Lightbox JS
    let activeLightboxIndex = 0;

    function openPhotoGalleryModal(idx) {
        activeLightboxIndex = idx || 0;
        const modal = document.getElementById('hotelPhotoLightboxModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        updateLightboxView();
    }

    function closePhotoGalleryModal() {
        const modal = document.getElementById('hotelPhotoLightboxModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function changeLightboxPhoto(direction) {
        activeLightboxIndex = (activeLightboxIndex + direction + allGalleryPhotos.length) % allGalleryPhotos.length;
        updateLightboxView();
    }

    function selectLightboxIndex(idx) {
        activeLightboxIndex = idx;
        updateLightboxView();
    }

    function updateLightboxView() {
        const imgEl = document.getElementById('lightboxMainImage');
        const counterEl = document.getElementById('lightboxCounter');
        if (imgEl && allGalleryPhotos[activeLightboxIndex]) {
            imgEl.src = allGalleryPhotos[activeLightboxIndex];
        }
        if (counterEl) {
            counterEl.textContent = `${activeLightboxIndex + 1} / ${allGalleryPhotos.length}`;
        }

        document.querySelectorAll('#hotelPhotoLightboxModal [data-thumb-index]').forEach((th, i) => {
            if (i === activeLightboxIndex) {
                th.classList.add('border-brand-500', 'scale-105', 'opacity-100');
                th.classList.remove('border-transparent', 'opacity-60');
            } else {
                th.classList.remove('border-brand-500', 'scale-105', 'opacity-100');
                th.classList.add('border-transparent', 'opacity-60');
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('hotelPhotoLightboxModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'Escape') closePhotoGalleryModal();
            if (e.key === 'ArrowLeft') changeLightboxPhoto(-1);
            if (e.key === 'ArrowRight') changeLightboxPhoto(1);
        }
    });

    // Mobile Carousel Auto-Scroll JS
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('mobileHotelCarousel');
        if (!carousel) return;
        const slides = carousel.querySelectorAll('[data-carousel-slide]');
        const totalSlides = slides.length;
        if (totalSlides <= 1) return;

        const counter = document.getElementById('mobileGalleryCounter');
        const prevBtn = document.getElementById('prevSlideBtn');
        const nextBtn = document.getElementById('nextSlideBtn');
        let currentIndex = 0;

        function goToSlide(idx) {
            currentIndex = (idx + totalSlides) % totalSlides;
            const width = carousel.clientWidth;
            carousel.scrollTo({
                left: currentIndex * width,
                behavior: 'smooth'
            });
            if (counter) counter.textContent = `${currentIndex + 1} / ${totalSlides}`;
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                goToSlide(currentIndex - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                goToSlide(currentIndex + 1);
            });
        }

        carousel.addEventListener('scroll', function() {
            const width = carousel.clientWidth;
            if (width > 0) {
                const detectedIdx = Math.round(carousel.scrollLeft / width);
                if (detectedIdx !== currentIndex && detectedIdx >= 0 && detectedIdx < totalSlides) {
                    currentIndex = detectedIdx;
                    if (counter) counter.textContent = `${currentIndex + 1} / ${totalSlides}`;
                }
            }
        }, {
            passive: true
        });
    });

    // Reservation Booking Submission Logic
    function submitHotelReservation() {
        const name = document.getElementById('guestName')?.value.trim() || '';
        const phone = document.getElementById('guestPhone')?.value.trim() || '';
        const room = document.getElementById('selectedRoomName')?.textContent || 'Room';
        const hotelName = <?= json_encode($hotel['name']) ?>;
        const inDate = document.getElementById('checkInDate')?.value || '';
        const outDate = document.getElementById('checkOutDate')?.value || '';
        const rooms = parseInt(document.getElementById('roomsCountSelect')?.value || '1', 10);
        const guests = document.getElementById('guestsCountSelect')?.value || 2;
        const totalRaw = parseFloat(document.getElementById('grandTotalDisplay')?.textContent.replace(/[^0-9]/g, '') || 0);
        const hotelId = <?= json_encode($hotel['id'] ?? '') ?>;
        const tokenAmount = calcHotelAdvance(totalRaw, rooms);

        if (!name || !phone) {
            alert('Please provide your full guest name and contact phone number.');
            return;
        }

        if (!isUserLoggedIn) {
            showHotelLoginModal(hotelName + ' (' + room.trim() + ')');
            return;
        }

        const btn = document.querySelector('#hotelReservationForm button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-xs"></i> <span>Securing Reservation...</span>';
        }

        function executeFinalHotelBooking(paymentId, gateway) {
            const formData = new FormData();
            formData.append('hotel_id', hotelId);
            formData.append('guest_name', name);
            formData.append('guest_phone', phone);
            formData.append('guest_email', userEmail);
            formData.append('hotel_name', hotelName);
            formData.append('room_name', room.trim());
            formData.append('check_in', inDate);
            formData.append('check_out', outDate);
            formData.append('rooms_count', rooms);
            formData.append('guests_count', guests);
            formData.append('total_amount', totalRaw);
            formData.append('token_amount', tokenAmount);
            formData.append('payment_gateway', gateway || 'razorpay');
            if (paymentId) formData.append('razorpay_payment_id', paymentId);

            fetch('api/book-hotel.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Book with Token Advance</span>';
                    }
                    if (data.login_required) {
                        showHotelLoginModal(hotelName + ' (' + room.trim() + ')');
                        return;
                    }
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.success) {
                        showBookingSuccessModal(data.booking_code, hotelName, room.trim(), name, totalRaw);
                    } else {
                        alert(data.message || 'Booking failed. Please try again.');
                    }
                })
                .catch(err => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Book with Token Advance</span>';
                    }
                    alert('Reservation could not be placed. Please contact hotel concierge.');
                });
        }

        // Razorpay Gateway Verification
        const hasValidRzp = razorpayKeyId && !razorpayKeyId.includes('placeholder') && typeof Razorpay !== 'undefined';

        if (hasValidRzp) {
            const orderForm = new FormData();
            orderForm.append('amount', tokenAmount);

            fetch('api/create-razorpay-order.php', {
                    method: 'POST',
                    body: orderForm
                })
                .then(res => res.json())
                .then(orderData => {
                    if (!orderData.order_id) throw new Error('Payment order not created');
                    const options = {
                        key: razorpayKeyId,
                        amount: Math.round(tokenAmount * 100),
                        currency: "INR",
                        name: siteName,
                        description: "Hotel Token Advance: " + hotelName,
                        image: "assets/images/logo/logo.webp",
                        order_id: orderData.order_id || undefined,
                        prefill: {
                            name: name,
                            contact: phone,
                            email: userEmail
                        },
                        theme: {
                            color: "#0f766e"
                        },
                        handler: function(response) {
                            executeFinalHotelBooking(response.razorpay_payment_id, 'razorpay');
                        },
                        modal: {
                            ondismiss: function() {
                                if (btn) {
                                    btn.disabled = false;
                                    btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Book with Token Advance</span>';
                                }
                            }
                        }
                    };
                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', function(resp) {
                        alert('Payment Failed: ' + (resp.error?.description || 'Transaction declined'));
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Book with Token Advance</span>';
                        }
                    });
                    rzp.open();
                })
                .catch(err => {
                    alert('Unable to start secure payment. Please try again or contact us.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-lock"></i> Book with Token Advance';
                    }
                });
        } else {
            alert('Online payment is currently unavailable. Please contact our booking team.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-lock"></i> Book with Token Advance';
            }
        }
    }

    function showHotelLoginModal(title) {
        const existing = document.getElementById('loginRequiredModalOverlay');
        if (existing) existing.remove();

        const currentUrl = window.location.href;
        const modalHtml = `
        <div id="loginRequiredModalOverlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-center space-y-4 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 border border-brand-200 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="space-y-1">
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                        Traveler Account Required
                    </span>
                    <h3 class="text-xl font-black text-slate-900">Sign In to Reserve Room</h3>
                    <p class="text-xs text-slate-500">Please sign in to your <?php echo htmlspecialchars($siteName); ?> account to complete your hotel booking and receive instant check-in vouchers.</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 truncate">
                    <span>${title}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="login.php?redirect=${encodeURIComponent(currentUrl)}" class="py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider text-center transition">
                        Log In Now
                    </a>
                    <a href="signup.php?redirect=${encodeURIComponent(currentUrl)}" class="py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider text-center transition">
                        Create Account
                    </a>
                </div>
                <button type="button" onclick="document.getElementById('loginRequiredModalOverlay').remove()" class="text-xs text-slate-400 hover:text-slate-600 font-bold">
                    Cancel
                </button>
            </div>
        </div>
    `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    function showBookingSuccessModal(code, hotel, room, guest, amount) {
        const modalHtml = `
        <div id="bookingModalOverlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-center space-y-4 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="space-y-1">
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                        Booking Code: ${code}
                    </span>
                    <h3 class="text-xl font-black text-slate-900">Reservation Confirmed!</h3>
                    <p class="text-xs text-slate-500">Thank you <strong>${guest}</strong>, your stay has been reserved successfully.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-medium">Hotel:</span>
                        <strong class="text-slate-900 truncate max-w-[200px]">${hotel}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-medium">Room Category:</span>
                        <strong class="text-brand-600 truncate max-w-[200px]">${room}</strong>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-1.5 font-bold text-slate-900">
                        <span>Total Payable Amount:</span>
                        <span class="text-emerald-700 font-black">₹${parseInt(amount).toLocaleString('en-IN')}</span>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('bookingModalOverlay').remove(); window.location.href='index.php';" class="w-full py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition">
                    Done &bull; Back to Home
                </button>
            </div>
        </div>
    `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
</script>

<?php
require_once 'components/footer.php';
?>