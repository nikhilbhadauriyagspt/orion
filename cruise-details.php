<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ocean Cruise Details & Cabin Reservation Portal - Orion Advent
 * - Interactive Cabin Category Selection (Interior, Ocean View, Balcony Suite, Presidential)
 * - Dynamic Day-by-Day Port Sailing Itinerary Timeline
 * - 5-Photo Luxury Magazine Grid
 * - Sticky Token Advance Cruise Booking Sidebar & Guest Calculator
 * - 100% Flat, Border-First (Zero Shadows), Pure Font Awesome 6 Icons Only
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : (isset($_GET['id']) ? trim($_GET['id']) : '');

$pdo = getDBConnection();
$cruise = null;

if ($pdo && !empty($slug)) {
    try {
        if (is_numeric($slug)) {
            $stmt = $pdo->prepare("SELECT * FROM `cruises` WHERE `id` = ? LIMIT 1");
            $stmt->execute([(int)$slug]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `cruises` WHERE `slug` = ? LIMIT 1");
            $stmt->execute([$slug]);
        }
        $cruise = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $cruise = null;
    }
}

// Fallback if not found or empty
if (!$cruise && $pdo) {
    try {
        $cruise = $pdo->query("SELECT * FROM `cruises` WHERE `status` = 'active' ORDER BY `id` ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// Hardcoded fallback data if database is empty
if (!$cruise) {
    $cruise = [
        'id' => 1,
        'title' => 'Mumbai to Goa & Arabian High Seas Luxury Sailing',
        'slug' => 'mumbai-goa-arabian-sea-cordelia',
        'subtitle' => 'Experience the magic of sailing into the sunset with 5-star hospitality, infinity pools, live Broadway shows, and open-ocean stargazing.',
        'cruise_line' => 'Cordelia Cruises',
        'ship_name' => 'The Empress',
        'departure_port' => 'Mumbai (Green Gate Pier)',
        'destination_ports' => 'Mumbai • Arabian High Seas • Mormugao (Goa)',
        'duration_nights' => 3,
        'duration_days' => 4,
        'duration_text' => '3 Nights / 4 Days',
        'starting_price' => 19999,
        'original_price' => 26999,
        'token_advance' => 3000,
        'badge' => 'Bestseller 2026',
        'rating' => 4.9,
        'reviews_count' => 380,
        'category' => 'domestic',
        'sailing_dates' => 'Every Friday & Monday',
        'featured_image' => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=1200&q=80',
        'gallery' => json_encode([
            'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'
        ]),
        'highlights' => json_encode([
            ['icon' => 'fa-solid fa-water-ladder', 'title' => 'Top Deck Ocean Pools', 'desc' => 'Dual swimming pools and heated whirlpool jacuzzis with sea views'],
            ['icon' => 'fa-solid fa-masks-theater', 'title' => 'Broadway Theatre & Burlesque', 'desc' => 'Nightly live musicals, magic extravaganzas & standup comedy'],
            ['icon' => 'fa-solid fa-utensils', 'title' => 'All-Inclusive Ocean Dining', 'desc' => 'Lavish global buffets, Jain cuisine & Indian culinary stations'],
            ['icon' => 'fa-solid fa-dice', 'title' => 'Casino & Nightclub Lounges', 'desc' => 'Live DJs, rock bar, casino gaming tables & stargazing deck']
        ]),
        'cabins' => json_encode([
            ['name' => 'Interior Stateroom', 'type' => 'Standard Cozy', 'price' => 19999, 'original_price' => 24999, 'capacity' => '2-3 Guests', 'size' => '135 sq.ft', 'perks' => 'Queen/Twin Beds, Ensuite Bathroom, 24/7 Room Service, Interactive TV', 'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Ocean View Stateroom', 'type' => 'Window Panoramic', 'price' => 24999, 'original_price' => 31999, 'capacity' => '2-4 Guests', 'size' => '160 sq.ft', 'perks' => 'Picture Window Ocean Views, Sofa Bed, Mini Bar, Priority Dining', 'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Mini Suite with Private Balcony', 'type' => 'Private Verandah', 'price' => 34999, 'original_price' => 44999, 'capacity' => '2-4 Guests', 'size' => '240 sq.ft', 'perks' => 'Private Sea Balcony, Jacuzzi Bath Tub, Welcome Champagne, Butler Service', 'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Chairman\'s Presidential Suite', 'type' => 'Ultra Luxury', 'price' => 59999, 'original_price' => 79999, 'capacity' => '2-5 Guests', 'size' => '450 sq.ft', 'perks' => 'Expansive Sun Deck, Dedicated 24/7 Concierge, Free Premium Spirits, VIP Lounge Access', 'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=600&q=80']
        ]),
        'itinerary' => json_encode([
            ['day' => 'Day 1', 'port' => 'Mumbai Port', 'arrive' => '02:00 PM Check-in', 'depart' => '06:30 PM Sailing', 'title' => 'Embarkation at Mumbai Port & Sunset Welcome', 'desc' => 'Board The Empress at Green Gate pier. Settle into your stateroom, complete the safety drill, and join the sunset sail-away deck party with live DJ beats. Lavish dinner buffet follows.'],
            ['day' => 'Day 2', 'port' => 'Arabian High Seas', 'arrive' => 'Cruising', 'depart' => 'All Day', 'title' => 'Full Day Luxury Cruising on Arabian High Seas', 'desc' => 'Wake up to endless turquoise horizons. Enjoy rock climbing, pool games, luxury spa treatments, afternoon tea, and grand gala Broadway musical at Marquee Theatre.'],
            ['day' => 'Day 3', 'port' => 'Mormugao Port (Goa)', 'arrive' => '07:30 AM', 'depart' => '05:00 PM', 'title' => 'Port of Call: Goa Excursion & Beach Vibes', 'desc' => 'Dock at Mormugao Port. Disembark for South Goa beach clubs, heritage Portuguese churches, or spice plantations. Return on board before sunset for the Bollywood gala deck party.'],
            ['day' => 'Day 4', 'port' => 'Mumbai Return', 'arrive' => '08:00 AM', 'depart' => 'Disembark', 'title' => 'Morning Arrival & Fond Memories', 'desc' => 'Enjoy breakfast as the Mumbai skyline comes into view. Complete debarkation formalities with cherished sea voyage memories.']
        ]),
        'inclusions' => json_encode([
            'Accommodation in selected luxury stateroom/suite category',
            'All buffet meals (Breakfast, Lunch, Evening Snacks & Dinner) across multi-cuisine restaurants',
            'Complimentary access to swimming pools, jacuzzi, and ocean fitness centre',
            'Nightly Broadway theatre musicals, magic shows & live band performances',
            'Port handling charges, fuel surcharge & mandatory baggage transfer'
        ]),
        'exclusions' => json_encode([
            'Casino gaming chips & casino bar access',
            'Alcoholic drinks & packaged beverages (Beverage packages available)',
            'Spa therapies & salon treatments',
            'Optional Shore Excursions at port stops',
            '18% Government GST'
        ])
    ];
}

// Decode JSON fields
$gallery = !empty($cruise['gallery']) ? json_decode($cruise['gallery'], true) : [];
if (empty($gallery)) {
    $gallery = [
        $cruise['featured_image'] ?: 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'
    ];
}

$highlights = !empty($cruise['highlights']) ? json_decode($cruise['highlights'], true) : [];
$cabins = !empty($cruise['cabins']) ? json_decode($cruise['cabins'], true) : [];
$itinerary = !empty($cruise['itinerary']) ? json_decode($cruise['itinerary'], true) : [];
$inclusions = !empty($cruise['inclusions']) ? json_decode($cruise['inclusions'], true) : [];
$exclusions = !empty($cruise['exclusions']) ? json_decode($cruise['exclusions'], true) : [];

if (empty($cabins)) {
    $cabins = [
        ['name' => 'Interior Stateroom', 'type' => 'Standard Cozy', 'price' => (float)$cruise['starting_price'], 'original_price' => (float)$cruise['original_price'], 'capacity' => '2-3 Guests', 'size' => '135 sq.ft', 'perks' => 'Queen/Twin Beds, Ensuite Bathroom, 24/7 Room Service', 'image' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Ocean View Stateroom', 'type' => 'Window Panoramic', 'price' => (float)$cruise['starting_price'] + 5000, 'original_price' => (float)$cruise['original_price'] + 6000, 'capacity' => '2-4 Guests', 'size' => '160 sq.ft', 'perks' => 'Picture Window Ocean Views, Sofa Bed, Priority Dining', 'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80'],
        ['name' => 'Mini Suite with Balcony', 'type' => 'Private Verandah', 'price' => (float)$cruise['starting_price'] + 15000, 'original_price' => (float)$cruise['original_price'] + 18000, 'capacity' => '2-4 Guests', 'size' => '240 sq.ft', 'perks' => 'Private Sea Balcony, Jacuzzi Bath, Butler Service', 'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=600&q=80']
    ];
}

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

if ($userLoggedIn && (empty($userName) || empty($userPhone) || empty($userEmail)) && $pdo) {
    try {
        $uStmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ? LIMIT 1");
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
    } catch (Exception $e) {}
}

$siteName = getSetting('site_name', 'Orion Advent');
$pageTitle = htmlspecialchars($cruise['title']) . ' | Ocean Cruise Booking - ' . htmlspecialchars($siteName);
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ==========================================
     BREADCRUMB & HEADER CONTAINER
=========================================== -->
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-3 sm:px-8 xl:px-12 py-3 sm:py-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-1.5 sm:space-x-2 text-[11px] sm:text-xs text-slate-500 mb-2.5 sm:mb-3 overflow-x-auto no-scrollbar whitespace-nowrap">
            <a href="index.php" class="hover:text-brand-700 font-medium shrink-0 flex items-center space-x-1">
                <i class="fa-solid fa-house text-[10px] sm:text-[11px]"></i>
                <span class="hidden sm:inline">Home</span>
            </a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[10px] text-slate-300 shrink-0"></i>
            <a href="cruises.php" class="hover:text-brand-700 font-medium shrink-0">Ocean Cruises</a>
            <i class="fa-solid fa-chevron-right text-[8px] sm:text-[10px] text-slate-300 shrink-0"></i>
            <span class="text-slate-800 font-semibold truncate max-w-[150px] sm:max-w-xs md:max-w-md"><?= htmlspecialchars($cruise['title']) ?></span>
        </nav>

        <!-- Top Title Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
            <div class="space-y-1.5 sm:space-y-2">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-ship text-[10px] sm:text-[11px] text-brand-600"></i>
                        <?= htmlspecialchars($cruise['cruise_line'] . ' • ' . $cruise['ship_name']) ?>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-anchor text-brand-600 mr-1"></i>Ex-<?= htmlspecialchars($cruise['departure_port']) ?>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <?= htmlspecialchars($cruise['badge'] ?: 'Bestseller') ?>
                    </span>
                    <div class="flex items-center space-x-1 text-[11px] sm:text-xs text-slate-700 ml-0.5 font-semibold">
                        <i class="fa-solid fa-star text-amber-500 text-[10px] sm:text-xs"></i>
                        <span><?= number_format((float)($cruise['rating'] ?: 4.9), 1) ?></span>
                        <span class="text-slate-400 font-normal">(<?= (int)($cruise['reviews_count'] ?: 380) ?> reviews)</span>
                    </div>
                </div>

                <h1 class="text-xl sm:text-3xl md:text-4xl font-extrabold sm:font-black text-slate-900 tracking-tight leading-tight">
                    <?= htmlspecialchars($cruise['title']) ?>
                </h1>

                <p class="text-[11px] sm:text-sm text-slate-500 max-w-3xl leading-relaxed">
                    <?= htmlspecialchars($cruise['subtitle'] ?: 'Experience luxury ocean travel with all-inclusive dining, pools, and non-stop entertainment.') ?>
                </p>
            </div>

            <!-- Header Quick Price Strip (Mobile / Responsive) -->
            <div class="shrink-0 text-left lg:text-right p-3 bg-slate-50 rounded-xl sm:rounded-2xl border border-slate-200 lg:border-none lg:bg-transparent lg:p-0 flex lg:block items-center justify-between">
                <div>
                    <span class="text-[10px] sm:text-xs text-slate-400 block font-medium">Starting Cabin Fare</span>
                    <div class="flex items-baseline lg:justify-end gap-1.5 sm:gap-2">
                        <span class="text-xl sm:text-3xl font-black text-slate-900 font-space">₹<?= number_format((float)$cruise['starting_price']) ?></span>
                        <?php if (!empty($cruise['original_price']) && $cruise['original_price'] > $cruise['starting_price']): ?>
                            <span class="text-[11px] sm:text-sm text-slate-400 line-through">₹<?= number_format((float)$cruise['original_price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="text-[10px] sm:text-[11px] text-slate-500 block">per person &bull; twin stateroom</span>
                </div>
                <a href="#cabinSelectionSection" class="lg:hidden px-4 py-2 rounded-full bg-brand-600 text-white font-bold text-xs uppercase tracking-wider active:scale-95 transition">
                    Select Cabin
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     MAGAZINE PHOTO GALLERY & MOBILE CAROUSEL
=========================================== -->
<div class="bg-white py-3 sm:py-4 px-3 sm:px-8 xl:px-12 border-b border-slate-200">
    <div class="max-w-7xl mx-auto">
        <!-- Desktop Grid View -->
        <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-2.5 h-[340px] sm:h-[420px] md:h-[460px] rounded-3xl overflow-hidden bg-slate-100 border border-slate-200">
            <!-- Main Hero Image (Spans 2 cols, full height) -->
            <div class="md:col-span-2 h-full relative group overflow-hidden bg-slate-200">
                <img src="<?= htmlspecialchars($gallery[0] ?? $cruise['featured_image']) ?>" 
                     alt="Cruise Liner Main" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer">
                <div class="absolute bottom-3 left-3 bg-slate-900/80 text-white text-[11px] font-bold px-3 py-1 rounded-full backdrop-blur-xs flex items-center space-x-1.5 border border-white/20">
                    <i class="fa-solid fa-ship text-brand-300"></i>
                    <span><?= htmlspecialchars($cruise['ship_name']) ?> Ocean Liner</span>
                </div>
            </div>

            <!-- Right 4 Grid Photos -->
            <div class="hidden md:grid md:col-span-2 grid-cols-2 gap-2.5 h-full">
                <?php for ($i = 1; $i <= 4; $i++): 
                    $photoUrl = $gallery[$i] ?? ($gallery[0] ?? '');
                ?>
                    <div class="h-[225px] relative group overflow-hidden bg-slate-200">
                        <img src="<?= htmlspecialchars($photoUrl) ?>" 
                             alt="Cruise Gallery Photo <?= $i ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer">
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Mobile Auto-Scrolling Carousel with swipe & touch support -->
        <div class="block md:hidden relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-950 aspect-[16/10] max-h-[280px]">
            <div id="mobileCruiseCarousel" class="flex overflow-x-auto snap-x snap-mandatory scroll-smooth no-scrollbar w-full h-full">
                <?php foreach ($gallery as $gIdx => $gPhoto): ?>
                    <div class="w-full shrink-0 snap-center h-full relative" data-carousel-slide="<?= $gIdx ?>">
                        <img src="<?= htmlspecialchars($gPhoto) ?>" 
                             alt="<?= htmlspecialchars($cruise['title']) ?> Photo <?= $gIdx + 1 ?>" 
                             class="w-full h-full object-cover">
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Mobile Badge Overlay -->
            <span class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900/80 backdrop-blur-md text-white border border-white/20 shadow-xs">
                <i class="fa-solid fa-ship mr-1 text-teal-300"></i> <?= htmlspecialchars($cruise['ship_name']) ?>
            </span>

            <!-- Counter Pill -->
            <div class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900/80 backdrop-blur-md text-white border border-white/20 flex items-center space-x-1 pointer-events-none">
                <i class="fa-solid fa-images text-[9px] text-teal-300"></i>
                <span id="mobileCruiseGalleryCounter">1 / <?= count($gallery) ?></span>
            </div>

            <!-- Left & Right Arrow controls -->
            <?php if (count($gallery) > 1): ?>
                <button type="button" id="prevCruiseSlideBtn" aria-label="Previous image" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-slate-900/60 backdrop-blur-xs text-white text-xs flex items-center justify-center border border-white/20 active:scale-90 transition">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </button>
                <button type="button" id="nextCruiseSlideBtn" aria-label="Next image" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-slate-900/60 backdrop-blur-xs text-white text-xs flex items-center justify-center border border-white/20 active:scale-90 transition">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
                
                <!-- Dot indicators -->
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center space-x-1.5 pointer-events-none" id="mobileCruiseCarouselDots">
                    <?php foreach ($gallery as $dIdx => $dP): ?>
                        <span class="h-1.5 rounded-full transition-all duration-300 <?= $dIdx === 0 ? 'bg-white w-4' : 'bg-white/50 w-1.5' ?>" data-dot-index="<?= $dIdx ?>"></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==========================================
     MAIN CONTENT & STICKY BOOKING SIDEBAR
=========================================== -->
<main class="w-full py-6 sm:py-10 px-3 sm:px-8 xl:px-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-6 sm:gap-8 items-start">

            <!-- LEFT COLUMN: CRUISE SPECS, CABINS & ITINERARY (Width ~ 65%) -->
            <div class="flex-1 w-full space-y-5 sm:space-y-8">

                <!-- 1. Quick Voyage Highlights Strip -->
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-3 sm:mb-4 flex items-center space-x-2">
                        <i class="fa-solid fa-sparkles text-brand-600"></i>
                        <span>On-Board Voyage Highlights</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <?php if (!empty($highlights) && is_array($highlights)): ?>
                            <?php foreach ($highlights as $hl): ?>
                                <div class="flex items-start space-x-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 text-sm">
                                        <i class="<?= htmlspecialchars($hl['icon'] ?? 'fa-solid fa-circle-check') ?>"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900"><?= htmlspecialchars($hl['title'] ?? '') ?></h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed"><?= htmlspecialchars($hl['desc'] ?? '') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="flex items-start space-x-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 text-sm">
                                    <i class="fa-solid fa-water-ladder"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">Deck Swimming Pools & Jacuzzis</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Top-deck panoramic swimming pools and heated jacuzzis with sun lounges.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 text-sm">
                                    <i class="fa-solid fa-masks-theater"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">Broadway Shows & Marquee Live Theatre</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">World-class theatrical musicals, magic performances, and comedy acts nightly.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Interactive Cabin Categories Picker -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-5" id="cabinSelectionSection">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center space-x-2">
                                <i class="fa-solid fa-bed text-brand-600"></i>
                                <span>Choose Your Stateroom / Cabin Category</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Select a stateroom to lock in your live price and advance token calculation</p>
                        </div>
                    </div>

                    <div class="space-y-4" id="cabinsListContainer">
                        <?php foreach ($cabins as $index => $cb): 
                            $cbPrice = (float)($cb['price'] ?? $cruise['starting_price']);
                            $cbOrig = (float)($cb['original_price'] ?? ($cbPrice * 1.25));
                            $cbImg = !empty($cb['image']) ? $cb['image'] : 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80';
                            $isSelected = ($index === 0);
                        ?>
                            <div class="cabin-option-card rounded-2xl border <?= $isSelected ? 'border-brand-600 bg-brand-50/20 ring-1 ring-brand-500' : 'border-slate-200 bg-white' ?> p-4 transition-all hover:border-brand-500 flex flex-col md:flex-row gap-4 items-start md:items-center cursor-pointer"
                                 onclick="selectCabinCategory('<?= htmlspecialchars(addslashes($cb['name'])) ?>', <?= $cbPrice ?>, '<?= htmlspecialchars(addslashes($cb['type'] ?? 'Cabin')) ?>', this)">
                                
                                <!-- Cabin Thumbnail -->
                                <div class="w-full md:w-36 h-28 shrink-0 rounded-xl overflow-hidden bg-slate-100 relative">
                                    <img src="<?= htmlspecialchars($cbImg) ?>" alt="<?= htmlspecialchars($cb['name']) ?>" class="w-full h-full object-cover">
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900/80 text-white">
                                        <?= htmlspecialchars($cb['size'] ?? '150 sq.ft') ?>
                                    </span>
                                </div>

                                <!-- Cabin Details -->
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-slate-900"><?= htmlspecialchars($cb['name']) ?></h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            <?= htmlspecialchars($cb['type'] ?? 'Stateroom') ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500"><?= htmlspecialchars($cb['perks'] ?? 'Ensuite washroom, interactive TV, AC, room service') ?></p>
                                    <div class="text-[11px] text-slate-400 flex items-center gap-3 pt-1">
                                        <span><i class="fa-solid fa-users text-[10px] mr-1 text-slate-400"></i><?= htmlspecialchars($cb['capacity'] ?? '2-3 Guests') ?></span>
                                        <span><i class="fa-solid fa-check text-[10px] mr-1 text-emerald-600"></i>All Buffets Included</span>
                                    </div>
                                </div>

                                <!-- Pricing & Selection Radio -->
                                <div class="w-full md:w-auto shrink-0 flex md:flex-col items-center md:items-end justify-between border-t md:border-t-0 pt-2 md:pt-0 border-slate-100">
                                    <div class="text-left md:text-right">
                                        <span class="text-xs text-slate-400 line-through">₹<?= number_format($cbOrig) ?></span>
                                        <div class="text-base sm:text-lg font-black text-slate-900 font-space">
                                            ₹<?= number_format($cbPrice) ?>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block">per person</span>
                                    </div>

                                    <button type="button" 
                                            class="cabin-select-btn mt-2 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase transition <?= $isSelected ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-700' ?>">
                                        <?= $isSelected ? 'Selected' : 'Select Cabin' ?>
                                    </button>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Day-by-Day Sailing Itinerary Timeline -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 flex items-center space-x-2">
                                <i class="fa-solid fa-route text-brand-600"></i>
                                <span>Day-by-Day Sailing Itinerary</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Explore your sea voyage ports of call and schedule</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                            <?= htmlspecialchars($cruise['duration_text'] ?: ($cruise['duration_nights'] . 'N / ' . $cruise['duration_days'] . 'D')) ?>
                        </span>
                    </div>

                    <div class="relative pl-6 sm:pl-8 space-y-8 before:absolute before:left-2.5 sm:before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-brand-200">
                        <?php foreach ($itinerary as $stepIndex => $day): ?>
                            <div class="relative group">
                                <!-- Marker Icon -->
                                <div class="absolute -left-6 sm:-left-8 top-0.5 w-6 h-6 rounded-full bg-white border-2 border-brand-600 text-brand-600 flex items-center justify-center text-[10px] font-bold">
                                    <i class="fa-solid fa-anchor text-[9px]"></i>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-extrabold bg-brand-600 text-white">
                                            <?= htmlspecialchars($day['day'] ?? ('Day ' . ($stepIndex + 1))) ?>
                                        </span>
                                        <span class="text-xs font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200/60">
                                            <?= htmlspecialchars($day['port'] ?? 'Port of Call') ?>
                                        </span>
                                        <?php if (!empty($day['arrive']) || !empty($day['depart'])): ?>
                                            <span class="text-[11px] text-slate-400 font-medium">
                                                (Arr: <?= htmlspecialchars($day['arrive'] ?? '-') ?> &bull; Dep: <?= htmlspecialchars($day['depart'] ?? '-') ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h4 class="text-sm sm:text-base font-bold text-slate-900">
                                        <?= htmlspecialchars($day['title'] ?? '') ?>
                                    </h4>

                                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                                        <?= htmlspecialchars($day['desc'] ?? '') ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 4. Fare Inclusions & Exclusions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Inclusions -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4">
                        <div class="flex items-center space-x-2 text-emerald-700 font-bold text-sm">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Included in Your Cruise Fare</span>
                        </div>
                        <ul class="space-y-2.5">
                            <?php foreach ($inclusions as $inc): ?>
                                <li class="flex items-start space-x-2 text-xs text-slate-600 leading-relaxed">
                                    <i class="fa-solid fa-check text-emerald-600 text-xs shrink-0 mt-0.5"></i>
                                    <span><?= htmlspecialchars(is_string($inc) ? $inc : ($inc['label'] ?? '')) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Exclusions -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4">
                        <div class="flex items-center space-x-2 text-rose-700 font-bold text-sm">
                            <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                            <span>Excluded / Extra Charges</span>
                        </div>
                        <ul class="space-y-2.5">
                            <?php foreach ($exclusions as $exc): ?>
                                <li class="flex items-start space-x-2 text-xs text-slate-600 leading-relaxed">
                                    <i class="fa-solid fa-xmark text-rose-500 text-xs shrink-0 mt-0.5"></i>
                                    <span><?= htmlspecialchars(is_string($exc) ? $exc : ($exc['label'] ?? '')) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- 5. Sailing Policies & Health Guidelines -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-shield-halved text-brand-600"></i>
                        <span>Boarding Policies &amp; Important Information</span>
                    </h3>
                    <div class="text-xs text-slate-600 space-y-2 leading-relaxed">
                        <p>&bull; <strong>Mandatory Identification:</strong> Government-issued original Photo ID (Aadhaar Card / Passport / Voter ID) is mandatory for domestic Indian sailings. International sailings strictly require a Passport valid for at least 6 months.</p>
                        <p>&bull; <strong>Check-in &amp; Boarding:</strong> Web check-in opens 48 hours prior. Port boarding gates close strictly 2 hours prior to the scheduled sailing time.</p>
                        <p>&bull; <strong>Cancellation Policy:</strong> Free cancellation assistance up to 15 days before sailing. 50% refund between 7 to 14 days. Non-refundable within 7 days of sailing.</p>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: STICKY BOOKING CALCULATOR SIDEBAR (Width ~ 35%) -->
            <aside class="w-full lg:w-96 shrink-0 lg:sticky lg:top-28 space-y-5">
                
                <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-5">
                    
                    <!-- Top Fare Header -->
                    <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 font-medium block">Selected Stateroom</span>
                            <h4 id="calcCabinTitle" class="text-sm font-bold text-slate-900">
                                <?= htmlspecialchars($cabins[0]['name'] ?? 'Interior Stateroom') ?>
                            </h4>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                            Instant Confirmation
                        </span>
                    </div>

                    <!-- Booking Form -->
                    <form id="cruiseBookingForm" onsubmit="handleCruiseBookingSubmit(event)" class="space-y-4">
                        <input type="hidden" name="cruise_id" value="<?= htmlspecialchars($cruise['id']) ?>">
                        <input type="hidden" name="cruise_title" value="<?= htmlspecialchars($cruise['title']) ?>">
                        <input type="hidden" name="ship_name" value="<?= htmlspecialchars($cruise['ship_name']) ?>">
                        <input type="hidden" id="selectedCabinInput" name="cabin_name" value="<?= htmlspecialchars($cabins[0]['name'] ?? 'Interior Stateroom') ?>">
                        <input type="hidden" id="cabinPerPersonPrice" name="per_person_price" value="<?= (float)($cabins[0]['price'] ?? $cruise['starting_price']) ?>">
                        <input type="hidden" id="tokenAdvanceAmount" name="token_advance" value="<?= (float)($cruise['token_advance'] ?: 3000) ?>">

                        <!-- Sailing Date Picker -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                <i class="fa-regular fa-calendar-check text-brand-600 mr-1"></i>Departure Sailing Date
                            </label>
                            <input type="date" 
                                   required 
                                   id="sailingDateInput" 
                                   name="sailing_date" 
                                   min="<?= date('Y-m-d', strtotime('+2 days')) ?>" 
                                   value="<?= date('Y-m-d', strtotime('+10 days')) ?>"
                                   class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 font-semibold focus:bg-white focus:border-brand-500 outline-none transition">
                        </div>

                        <!-- Guests Selector Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Adults (12+ yrs)</label>
                                <select id="adultsCountSelect" name="adults_count" onchange="recalculateCruisePrice()" class="w-full px-3 py-2.5 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 font-semibold focus:border-brand-500 outline-none">
                                    <option value="1">1 Adult</option>
                                    <option value="2" selected>2 Adults (1 Cabin)</option>
                                    <option value="3">3 Adults (Triple)</option>
                                    <option value="4">4 Adults (2 Cabins)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kids (2-11 yrs)</label>
                                <select id="kidsCountSelect" name="kids_count" onchange="recalculateCruisePrice()" class="w-full px-3 py-2.5 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 font-semibold focus:border-brand-500 outline-none">
                                    <option value="0" selected>0 Children</option>
                                    <option value="1">1 Child</option>
                                    <option value="2">2 Children</option>
                                </select>
                            </div>
                        </div>

                        <!-- Traveler Contact Details -->
                        <div class="space-y-2.5 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Primary Traveler Name</label>
                                <input type="text" 
                                       required 
                                       name="lead_name" 
                                       placeholder="e.g. Vikram Sharma" 
                                       value="<?= htmlspecialchars($userName) ?>"
                                       class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 font-medium focus:bg-white focus:border-brand-500 outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Phone Number</label>
                                    <input type="tel" 
                                           required 
                                           name="lead_phone" 
                                           placeholder="e.g. 9876543210" 
                                           value="<?= htmlspecialchars($userPhone) ?>"
                                           class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 font-medium focus:bg-white focus:border-brand-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Email ID</label>
                                    <input type="email" 
                                           required 
                                           name="lead_email" 
                                           placeholder="e.g. vikram@gmail.com" 
                                           value="<?= htmlspecialchars($userEmail) ?>"
                                           class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 text-slate-800 font-medium focus:bg-white focus:border-brand-500 outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- Live Cost Breakdown -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span id="summaryRateLabel">Stateroom Fare (2 Adults):</span>
                                <span class="font-bold text-slate-800" id="summaryBasePrice">₹39,998</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Port Taxes &amp; Fuel Levies:</span>
                                <span class="font-bold text-slate-800" id="summaryTaxes">₹3,600</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                                <span class="font-bold text-slate-900">Total Sailing Amount:</span>
                                <span class="text-base font-black text-slate-900 font-space" id="summaryTotalAmount">₹43,598</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-brand-800 bg-brand-50 p-2.5 rounded-xl border border-brand-200/60">
                                <div>
                                    <span class="font-bold block text-[11px]">Pay Token Advance Today:</span>
                                    <span class="text-[10px] text-slate-500">Balance payable before sailing</span>
                                </div>
                                <span class="text-base font-black text-brand-700 font-space" id="summaryTokenAdvance">₹6,000</span>
                            </div>
                        </div>

                        <!-- Submit CTA Buttons -->
                        <div class="space-y-2 pt-2">
                            <button type="submit" 
                                    id="bookCruiseBtn" 
                                    class="w-full py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-black text-xs uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-lock text-xs"></i>
                                <span>Lock Cabin with Token Advance</span>
                            </button>

                            <button type="button" 
                                    onclick="openCruiseInquiryModal('<?= htmlspecialchars(addslashes($cruise['title'])) ?>', '<?= htmlspecialchars(addslashes($cruise['ship_name'])) ?>')"
                                    class="w-full py-2.5 rounded-full bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center space-x-1.5">
                                <i class="fa-regular fa-comment-dots text-xs"></i>
                                <span>Request Custom Cabin Advice</span>
                            </button>
                        </div>

                        <p class="text-[10px] text-center text-slate-400">
                            <i class="fa-solid fa-shield-halved mr-1 text-emerald-600"></i>
                            100% Flat Pricing &bull; Official Liner Inventory &bull; Instant Confirmation
                        </p>

                    </form>

                </div>

            </aside>

        </div>
    </div>
</main>

<!-- ==========================================
     INQUIRY MODAL REUSE
=========================================== -->
<div id="cruiseInquiryModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-6 space-y-4 animate-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-ship"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Cruise Concierge Inquiry</h3>
                    <p class="text-[10px] text-slate-400">Get customized cabin quote &amp; group discounts</p>
                </div>
            </div>
            <button type="button" onclick="closeCruiseInquiryModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="cruiseInquiryForm" onsubmit="submitCruiseInquiry(event)" class="space-y-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Cruise Sailing</label>
                <input type="text" id="modalCruiseTitle" readonly class="w-full px-3 py-2 rounded-xl text-xs bg-slate-100 border border-slate-200 text-slate-800 font-semibold" value="<?= htmlspecialchars($cruise['title'] . ' (' . $cruise['ship_name'] . ')') ?>">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Your Full Name</label>
                    <input type="text" required id="inqName" placeholder="e.g. John Doe" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Contact Phone</label>
                    <input type="tel" required id="inqPhone" placeholder="e.g. +91 9876543210" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 outline-none">
                </div>
            </div>

            <button type="submit" id="inqSubmitBtn" class="w-full py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95">
                Send Request to Cruise Concierge
            </button>
        </form>
    </div>
</div>

<!-- Razorpay Official Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<!-- Dynamic Live Price Calculator & Cabin Picker Script -->
<script>
const advanceType = <?= json_encode($advanceType) ?>;
const advancePercent = <?= (float)$advancePercent ?>;
const advanceFixed = <?= (float)$advanceFixed ?>;
const razorpayKeyId = <?= json_encode($razorpayKeyId) ?>;
const isUserLoggedIn = <?= $userLoggedIn ? 'true' : 'false' ?>;
const userEmail = <?= json_encode($userEmail) ?>;
const siteName = <?= json_encode($siteName) ?>;

let currentPerPersonPrice = <?= (float)($cabins[0]['price'] ?? $cruise['starting_price']) ?>;

function calcCruiseAdvance(total, adults) {
    if (advanceType === 'fixed') {
        return Math.min(total, advanceFixed * adults);
    } else {
        return Math.round(total * (advancePercent / 100));
    }
}

function selectCabinCategory(cabinName, price, type, el) {
    currentPerPersonPrice = parseFloat(price);
    document.getElementById('selectedCabinInput').value = cabinName;
    document.getElementById('cabinPerPersonPrice').value = currentPerPersonPrice;
    document.getElementById('calcCabinTitle').innerText = cabinName;

    // Highlight selected card
    document.querySelectorAll('.cabin-option-card').forEach(card => {
        card.classList.remove('border-brand-600', 'bg-brand-50/20', 'ring-1', 'ring-brand-500');
        card.classList.add('border-slate-200', 'bg-white');
        const btn = card.querySelector('.cabin-select-btn');
        if (btn) {
            btn.classList.remove('bg-brand-600', 'text-white');
            btn.classList.add('bg-slate-100', 'text-slate-700');
            btn.innerText = 'Select Cabin';
        }
    });

    if (el) {
        el.classList.remove('border-slate-200', 'bg-white');
        el.classList.add('border-brand-600', 'bg-brand-50/20', 'ring-1', 'ring-brand-500');
        const btn = el.querySelector('.cabin-select-btn');
        if (btn) {
            btn.classList.remove('bg-slate-100', 'text-slate-700');
            btn.classList.add('bg-brand-600', 'text-white');
            btn.innerText = 'Selected';
        }
    }

    recalculateCruisePrice();
}

function recalculateCruisePrice() {
    const adults = parseInt(document.getElementById('adultsCountSelect').value) || 2;
    const kids = parseInt(document.getElementById('kidsCountSelect').value) || 0;

    const baseFare = (adults * currentPerPersonPrice) + (kids * (currentPerPersonPrice * 0.5));
    const portTaxes = (adults + kids) * 1800;
    const totalAmount = baseFare + portTaxes;
    const tokenAdvance = calcCruiseAdvance(totalAmount, adults);

    document.getElementById('summaryRateLabel').innerText = `Stateroom Fare (${adults} Adult${adults > 1 ? 's' : ''}${kids > 0 ? ', ' + kids + ' Kid' : ''}):`;
    document.getElementById('summaryBasePrice').innerText = `₹${baseFare.toLocaleString('en-IN')}`;
    document.getElementById('summaryTaxes').innerText = `₹${portTaxes.toLocaleString('en-IN')}`;
    document.getElementById('summaryTotalAmount').innerText = `₹${totalAmount.toLocaleString('en-IN')}`;
    document.getElementById('summaryTokenAdvance').innerText = `₹${tokenAdvance.toLocaleString('en-IN')}`;
    document.getElementById('tokenAdvanceAmount').value = tokenAdvance;
}

function handleCruiseBookingSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('bookCruiseBtn');
    const formData = new FormData(form);
    const cruiseTitle = formData.get('cruise_title') || 'Cruise';
    const leadName = formData.get('lead_name') || '';
    const leadPhone = formData.get('lead_phone') || '';
    const tokenAmount = parseFloat(document.getElementById('tokenAdvanceAmount').value || 0);

    if (!leadName || !leadPhone) {
        alert('Please provide your full traveler name and contact phone number.');
        return;
    }

    if (!isUserLoggedIn) {
        showLoginModalForCruise(cruiseTitle);
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Securing Cabin...</span>';

    function executeFinalCruiseBooking(paymentId, gateway) {
        if (paymentId) formData.append('razorpay_payment_id', paymentId);
        if (gateway) formData.append('payment_gateway', gateway);

        fetch('api/book-cruise.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Lock Cabin with Token Advance</span>';

            if (data.login_required) {
                showLoginModalForCruise(cruiseTitle);
                return;
            }

            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else if (data.success) {
                showCruiseBookingSuccessModal(data.booking_code, formData.get('cruise_title'), formData.get('cabin_name'), formData.get('lead_name'), formData.get('sailing_date'), data.advance_amount, data.total_amount);
            } else {
                alert(data.message || 'Cruise reservation failed.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Lock Cabin with Token Advance</span>';
            alert('Reservation could not be placed. Please contact cruise concierge.');
        });
    }

    // Razorpay Gateway Check
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
            const options = {
                key: razorpayKeyId,
                amount: Math.round(tokenAmount * 100),
                currency: "INR",
                name: siteName,
                description: "Cruise Cabin Advance: " + cruiseTitle,
                image: "assets/images/logo/logo.webp",
                order_id: orderData.order_id || undefined,
                prefill: {
                    name: leadName,
                    contact: leadPhone,
                    email: userEmail
                },
                theme: {
                    color: "#0f766e"
                },
                handler: function (response) {
                    executeFinalCruiseBooking(response.razorpay_payment_id, 'razorpay');
                },
                modal: {
                    ondismiss: function () {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Lock Cabin with Token Advance</span>';
                    }
                }
            };
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (resp) {
                alert('Payment Failed: ' + (resp.error?.description || 'Transaction declined'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-lock text-xs"></i> <span>Lock Cabin with Token Advance</span>';
            });
            rzp.open();
        })
        .catch(err => {
            executeFinalCruiseBooking(null, 'razorpay');
        });
    } else {
        executeFinalCruiseBooking(null, 'razorpay');
    }
}

function showLoginModalForCruise(title) {
    const existing = document.getElementById('loginRequiredModalOverlay');
    if (existing) existing.remove();

    const currentUrl = window.location.href;
    const modalHtml = `
        <div id="loginRequiredModalOverlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-6 text-center space-y-4 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 border border-brand-200 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-ship"></i>
                </div>
                <div class="space-y-1">
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                        Traveler Account Required
                    </span>
                    <h3 class="text-xl font-black text-slate-900 font-space">Sign In to Lock Cruise Stateroom</h3>
                    <p class="text-xs text-slate-500">Please sign in to your <?php echo htmlspecialchars($siteName); ?> account to secure your cruise cabin and receive official boarding passes.</p>
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

function showCruiseBookingSuccessModal(code, title, cabin, name, date, token, total) {
    const modalHtml = `
        <div id="cruiseSuccessModalOverlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-6 text-center space-y-4 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-anchor"></i>
                </div>
                <div class="space-y-1">
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                        Ocean Stateroom Confirmed
                    </span>
                    <h3 class="text-xl font-black text-slate-900 font-space">Cabin Reserved!</h3>
                    <p class="text-xs text-slate-500">Your sea voyage stateroom has been secured with token advance.</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-left space-y-2 text-xs">
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-400">Booking Code:</span>
                        <span class="font-bold text-brand-600">${code}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Liner:</span>
                        <span class="font-semibold text-slate-800">${title}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Cabin Type:</span>
                        <span class="font-semibold text-slate-800">${cabin}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Sailing Date:</span>
                        <span class="font-semibold text-slate-800">${date}</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-slate-200">
                        <span class="text-slate-600 font-bold">Advance Token Paid:</span>
                        <span class="font-black text-emerald-600">₹${Number(token).toLocaleString('en-IN')}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="booking-confirmation.php?code=${encodeURIComponent(code)}" class="py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider text-center transition">
                        View Boarding Pass
                    </a>
                    <button type="button" onclick="document.getElementById('cruiseSuccessModalOverlay').remove()" class="py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider text-center transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function openCruiseInquiryModal(title, ship) {
    document.getElementById('modalCruiseTitle').value = `${title} (${ship})`;
    document.getElementById('cruiseInquiryModal').classList.remove('hidden');
}

function closeCruiseInquiryModal() {
    document.getElementById('cruiseInquiryModal').classList.add('hidden');
}

function submitCruiseInquiry(e) {
    e.preventDefault();
    const btn = document.getElementById('inqSubmitBtn');
    btn.disabled = true;
    btn.innerText = 'Sending Inquiry...';

    setTimeout(() => {
        alert('Thank you! Your cruise inquiry has been dispatched to our ocean concierge specialist. We will connect with you within 2 hours.');
        closeCruiseInquiryModal();
        btn.disabled = false;
        btn.innerText = 'Send Request to Cruise Concierge';
    }, 700);
}

// Initial calculation on load
document.addEventListener('DOMContentLoaded', () => {
    recalculateCruisePrice();

    // Mobile Cruise Photo Gallery Auto-Scroll
    const carousel = document.getElementById('mobileCruiseCarousel');
    if (carousel) {
        const slides = carousel.querySelectorAll('[data-carousel-slide]');
        const totalSlides = slides.length;
        if (totalSlides > 1) {
            const counter = document.getElementById('mobileCruiseGalleryCounter');
            const dots = document.querySelectorAll('#mobileCruiseCarouselDots [data-dot-index]');
            const prevBtn = document.getElementById('prevCruiseSlideBtn');
            const nextBtn = document.getElementById('nextCruiseSlideBtn');
            let currentIndex = 0;
            let autoScrollTimer = null;
            let userPauseTimeout = null;

            function goToSlide(idx) {
                currentIndex = (idx + totalSlides) % totalSlides;
                const width = carousel.clientWidth;
                carousel.scrollTo({
                    left: currentIndex * width,
                    behavior: 'smooth'
                });
                updateIndicators();
            }

            function updateIndicators() {
                if (counter) counter.textContent = `${currentIndex + 1} / ${totalSlides}`;
                dots.forEach((dot, dIdx) => {
                    if (dIdx === currentIndex) {
                        dot.className = 'h-1.5 rounded-full transition-all duration-300 bg-white w-4';
                    } else {
                        dot.className = 'h-1.5 rounded-full transition-all duration-300 bg-white/50 w-1.5';
                    }
                });
            }

            function startAutoScroll() {
                stopAutoScroll();
                autoScrollTimer = setInterval(() => {
                    goToSlide(currentIndex + 1);
                }, 3200);
            }

            function stopAutoScroll() {
                if (autoScrollTimer) {
                    clearInterval(autoScrollTimer);
                    autoScrollTimer = null;
                }
            }

            function handleUserInteraction() {
                stopAutoScroll();
                if (userPauseTimeout) clearTimeout(userPauseTimeout);
                userPauseTimeout = setTimeout(() => {
                    startAutoScroll();
                }, 4500);
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    handleUserInteraction();
                    goToSlide(currentIndex - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    handleUserInteraction();
                    goToSlide(currentIndex + 1);
                });
            }

            carousel.addEventListener('scroll', function() {
                const width = carousel.clientWidth;
                if (width > 0) {
                    const detectedIdx = Math.round(carousel.scrollLeft / width);
                    if (detectedIdx !== currentIndex && detectedIdx >= 0 && detectedIdx < totalSlides) {
                        currentIndex = detectedIdx;
                        updateIndicators();
                    }
                }
            }, { passive: true });

            carousel.addEventListener('touchstart', handleUserInteraction, { passive: true });
            carousel.addEventListener('mouseenter', stopAutoScroll);
            carousel.addEventListener('mouseleave', startAutoScroll);

            startAutoScroll();
        }
    }
});
</script>

<?php require_once 'components/footer.php'; ?>
