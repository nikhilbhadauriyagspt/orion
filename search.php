<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Search Results & Listing Portal - Orion Advent
 * - Universal Search across Tour Packages, Flights & Hotels
 * - 100% Flat, Border-First Design (Strictly ZERO shadows)
 * - Zero Emojis (Pure Font Awesome 6 Vector Icons)
 * - Lightweight, Clean, Non-Heavy Minimal Card Architecture
 * - Instant Live Client-Side Filtering, Sorting & URL Synchronization
 * - Integrated Quick Concierge Booking/Inquiry Modal
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/flights_service.php';

$siteName = getSetting('site_name', 'Orion Advent');
$siteWhatsapp = getSetting('site_whatsapp', '919876543210');
$pageTitle = 'Search Holiday Packages, Flights & Hotels | ' . htmlspecialchars($siteName);

// Initial Server-Side Query Fallbacks (refined via JS client-side instantly)
$initialType = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'all';
$initialCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';

if ($initialType === 'domestic' || $initialCategory === 'domestic') {
    $initialType = 'domestic';
} elseif ($initialType === 'international' || $initialCategory === 'international') {
    $initialType = 'international';
} elseif ($initialType === 'flight' || $initialType === 'flights') {
    $initialType = 'flight';
} elseif ($initialType === 'hotel' || $initialType === 'hotels') {
    $initialType = 'hotel';
} elseif ($initialType === 'cruise' || $initialType === 'cruises' || $initialCategory === 'cruise' || $initialCategory === 'cruises') {
    $initialType = 'cruise';
} elseif ($initialType === 'package' || $initialType === 'packages') {
    $initialType = 'domestic';
} else {
    $initialType = 'all';
}

$initialQuery = '';
if (!empty($_GET['query'])) {
    $initialQuery = htmlspecialchars(trim($_GET['query']));
} elseif (!empty($_GET['destination'])) {
    $initialQuery = htmlspecialchars(trim($_GET['destination']));
} elseif (!empty($_GET['city'])) {
    $initialQuery = htmlspecialchars(trim($_GET['city']));
} elseif (!empty($_GET['to'])) {
    $initialQuery = htmlspecialchars(trim($_GET['to']));
}

$initialTheme = 'all';
if (!empty($_GET['theme'])) {
    $tRaw = strtolower(trim($_GET['theme']));
    if (str_contains($tRaw, 'honeymoon') || str_contains($tRaw, 'romantic')) $initialTheme = 'honeymoon';
    elseif (str_contains($tRaw, 'family')) $initialTheme = 'family';
    elseif (str_contains($tRaw, 'adventure') || str_contains($tRaw, 'trek')) $initialTheme = 'adventure';
    elseif (str_contains($tRaw, 'beach')) $initialTheme = 'beach';
    elseif (str_contains($tRaw, 'spiritual') || str_contains($tRaw, 'pilgrimage')) $initialTheme = 'spiritual';
    elseif (str_contains($tRaw, 'luxury')) $initialTheme = 'luxury';
}

$pdoSearch = getDBConnection();
$searchItems = [];

// 1. Fetch Dynamic Packages from MySQL Database
if ($pdoSearch) {
    try {
        $dbPackages = $pdoSearch->query("SELECT * FROM `packages` WHERE `status` = 'active' ORDER BY `id` DESC")->fetchAll();
        foreach ($dbPackages as $p) {
            $inclusions = [];
            if (!empty($p['inclusions'])) {
                $decInc = json_decode($p['inclusions'], true);
                if (is_array($decInc)) {
                    foreach ($decInc as $item) {
                        $itemStr = is_string($item) ? $item : ($item['label'] ?? '');
                        $icon = 'fa-solid fa-circle-check';
                        $lower = strtolower($itemStr);
                        if (str_contains($lower, 'hotel') || str_contains($lower, 'stay')) $icon = 'fa-solid fa-hotel';
                        elseif (str_contains($lower, 'breakfast') || str_contains($lower, 'meal') || str_contains($lower, 'dinner')) $icon = 'fa-solid fa-utensils';
                        elseif (str_contains($lower, 'cab') || str_contains($lower, 'car') || str_contains($lower, 'transfer')) $icon = 'fa-solid fa-car';
                        elseif (str_contains($lower, 'boat') || str_contains($lower, 'cruise') || str_contains($lower, 'houseboat')) $icon = 'fa-solid fa-sailboat';
                        elseif (str_contains($lower, 'spa')) $icon = 'fa-solid fa-spa';
                        elseif (str_contains($lower, 'sightseeing')) $icon = 'fa-solid fa-camera';
                        $inclusions[] = ['icon' => $icon, 'label' => mb_strimwidth($itemStr, 0, 18, '..')];
                        if (count($inclusions) >= 4) break;
                    }
                }
            }
            if (empty($inclusions)) {
                $inclusions = [
                    ['icon' => 'fa-solid fa-hotel', 'label' => '4★ Verified Hotel'],
                    ['icon' => 'fa-solid fa-utensils', 'label' => 'Breakfast Included'],
                    ['icon' => 'fa-solid fa-car', 'label' => 'Private AC Cab'],
                    ['icon' => 'fa-solid fa-camera', 'label' => 'Sightseeing Pass']
                ];
            }

            $img = !empty($p['featured_image']) ? $p['featured_image'] : 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=800&q=80';
            $destStr = $p['location'] . (!empty($p['state_country']) ? ' • ' . $p['state_country'] : '');

            $themeSlug = 'general';
            $lowerText = strtolower($p['title'] . ' ' . ($p['circuit_type'] ?? '') . ' ' . $p['location'] . ' ' . ($p['subtitle'] ?? ''));
            if (str_contains($lowerText, 'honeymoon') || str_contains($lowerText, 'romantic') || str_contains($lowerText, 'couple') || str_contains($lowerText, 'kashmir')) $themeSlug = 'honeymoon';
            elseif (str_contains($lowerText, 'family') || str_contains($lowerText, 'kid') || str_contains($lowerText, 'leisure')) $themeSlug = 'family';
            elseif (str_contains($lowerText, 'trek') || str_contains($lowerText, 'adventure') || str_contains($lowerText, 'mountain') || str_contains($lowerText, 'snow') || str_contains($lowerText, 'safari') || str_contains($lowerText, 'rafting') || str_contains($lowerText, 'himalaya') || str_contains($lowerText, 'manali')) $themeSlug = 'adventure';
            elseif (str_contains($lowerText, 'temple') || str_contains($lowerText, 'pilgrimage') || str_contains($lowerText, 'spiritual') || str_contains($lowerText, 'darshan') || str_contains($lowerText, 'ghat')) $themeSlug = 'spiritual';
            elseif (str_contains($lowerText, 'beach') || str_contains($lowerText, 'coastal') || str_contains($lowerText, 'island') || str_contains($lowerText, 'watersport') || str_contains($lowerText, 'sea') || str_contains($lowerText, 'goa') || str_contains($lowerText, 'bali')) $themeSlug = 'beach';
            elseif (str_contains($lowerText, 'luxury') || str_contains($lowerText, 'resort') || str_contains($lowerText, 'villa') || str_contains($lowerText, 'palace') || str_contains($lowerText, 'houseboat') || str_contains($lowerText, 'dubai')) $themeSlug = 'luxury';

            $travelMode = !empty($p['travel_mode']) ? $p['travel_mode'] : (($p['category'] === 'international') ? 'flight' : 'cab');
            $tagsStr = strtolower($p['title'] . ' ' . $p['location'] . ' ' . $p['state_country'] . ' ' . $p['category'] . ' ' . $p['circuit_type'] . ' ' . $travelMode . ' ' . $themeSlug . ' package tour flight train bus volvo cab car cancellation breakfast sightseeing');

            $searchItems[] = [
                'id' => 'PKG-DB-' . $p['id'],
                'type' => 'package',
                'category' => $p['category'] ?: 'domestic',
                'travel_mode' => $travelMode,
                'departure_city' => $p['departure_city'] ?? 'All Major Cities',
                'theme' => $themeSlug,
                'sub_type' => ucfirst($p['category'] ?: 'Domestic') . ' Tour',
                'title' => $p['title'],
                'destination' => $destStr,
                'route' => $p['location'],
                'duration' => $p['duration_text'] ?: ($p['duration_nights'] . 'N / ' . $p['duration_days'] . 'D'),
                'badge' => $p['badge'] ?: 'Bestseller',
                'badge_class' => ($p['category'] === 'international') ? 'bg-amber-600 text-white' : 'bg-brand-600 text-white',
                'price' => (float)$p['price'],
                'original_price' => (float)($p['original_price'] ?: ($p['price'] * 1.25)),
                'rating' => (float)($p['rating'] ?: 4.9),
                'reviews' => ($p['reviews_count'] ?: 250) . ' reviews',
                'image' => $img,
                'inclusions' => $inclusions,
                'perks' => '100% Customizable • Verified 4★ Partner Hotels • Instant Confirmation',
                'tags' => $tagsStr,
                'slug' => $p['slug'] ?? ''
            ];
        }

        // 2. Fetch Dynamic Hotels from MySQL Database
        $dbHotels = $pdoSearch->query("SELECT * FROM `hotels` WHERE `status` = 'active' ORDER BY `id` DESC")->fetchAll();
        foreach ($dbHotels as $h) {
            $amenities = [];
            if (!empty($h['amenities'])) {
                $decAm = json_decode($h['amenities'], true);
                if (is_array($decAm)) {
                    foreach ($decAm as $da) {
                        $name = is_string($da) ? $da : ($da['name'] ?? '');
                        $icon = is_array($da) ? ($da['icon'] ?? 'fa-solid fa-circle-check') : 'fa-solid fa-circle-check';
                        $amenities[] = ['icon' => $icon, 'label' => mb_strimwidth($name, 0, 18, '..')];
                        if (count($amenities) >= 4) break;
                    }
                }
            }
            if (empty($amenities)) {
                $amenities = [
                    ['icon' => 'fa-solid fa-utensils', 'label' => 'Free Breakfast'],
                    ['icon' => 'fa-solid fa-wifi', 'label' => 'Free Wi-Fi'],
                    ['icon' => 'fa-solid fa-water-ladder', 'label' => 'Swimming Pool'],
                    ['icon' => 'fa-solid fa-spa', 'label' => 'Spa & Wellness']
                ];
            }

            $hotelCountry = strtolower($h['country'] ?? '');
            $isDomHotel = empty($hotelCountry) || str_contains($hotelCountry, 'india') || str_contains($hotelCountry, 'in');
            $hotelCategory = $isDomHotel ? 'domestic' : 'international';

            $img = !empty($h['featured_image']) ? $h['featured_image'] : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80';
            $destStr = (!empty($h['city']) ? $h['city'] : '') . (!empty($h['country']) ? ' • ' . $h['country'] : '');
            $tagsStr = strtolower($h['name'] . ' ' . $h['city'] . ' ' . $h['state'] . ' ' . $h['country'] . ' ' . $hotelCategory . ' hotel resort stay ' . $h['star_rating'] . 'star pool breakfast wifi');

            $hotelPerks = 'Free Cancellation • Pay at Hotel • Instant Confirmation';
            if (!empty($h['policies'])) {
                $decPol = json_decode($h['policies'], true);
                if (is_array($decPol) && !empty($decPol['summary'])) {
                    $hotelPerks = $decPol['summary'];
                } elseif (!str_starts_with(trim($h['policies']), '{')) {
                    $hotelPerks = $h['policies'];
                } elseif (preg_match('/"summary"\s*:\s*"([^"]+)"/', $h['policies'], $m)) {
                    $hotelPerks = stripslashes($m[1]);
                }
            }

            $searchItems[] = [
                'id' => 'HTL-DB-' . $h['id'],
                'type' => 'hotel',
                'category' => $hotelCategory,
                'sub_type' => ($h['star_rating'] ?: 4) . '-Star ' . ($h['property_type'] ?: 'Hotel'),
                'title' => $h['name'],
                'destination' => $destStr,
                'room_type' => 'Deluxe Room with Breakfast',
                'location' => $h['address'] ?: ($h['city'] . ', ' . $h['country']),
                'badge' => $h['badge'] ?: ($h['star_rating'] . '★ Verified'),
                'badge_class' => 'bg-amber-600 text-white',
                'price' => (float)$h['starting_price'],
                'original_price' => (float)($h['original_price'] ?: ($h['starting_price'] * 1.25)),
                'price_unit' => '/ night',
                'rating' => (float)($h['star_rating'] ?: 4.8),
                'reviews' => '150+ reviews',
                'image' => $img,
                'amenities' => $amenities,
                'perks' => $hotelPerks,
                'tags' => $tagsStr,
                'slug' => $h['slug'] ?? ''
            ];
        }
    } catch (Exception $e) {
    }
}

// 2.5 Fetch Dynamic Ocean Cruises from MySQL Database
if ($pdoSearch) {
    try {
        $dbCruises = $pdoSearch->query("SELECT * FROM `cruises` WHERE `status` = 'active' ORDER BY `id` DESC")->fetchAll();
        foreach ($dbCruises as $c) {
            $img = !empty($c['featured_image']) ? $c['featured_image'] : 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=800&q=80';
            $destStr = ($c['departure_port'] ?: 'Mumbai') . ' • ' . ($c['destination_ports'] ?: 'High Seas');
            $tagsStr = strtolower($c['title'] . ' ' . $c['ship_name'] . ' ' . $c['cruise_line'] . ' ' . $c['departure_port'] . ' ' . $c['destination_ports'] . ' cruise ocean sailing cordelia empress liner sea');

            $searchItems[] = [
                'id' => 'CRU-DB-' . $c['id'],
                'type' => 'cruise',
                'category' => $c['category'] ?: 'domestic',
                'departure_city' => $c['departure_port'] ?: 'Mumbai',
                'sub_type' => ($c['cruise_line'] ?: 'Cordelia') . ' • ' . ($c['ship_name'] ?: 'The Empress'),
                'title' => $c['title'],
                'destination' => $destStr,
                'route' => $c['destination_ports'] ?: 'Ocean Cruising',
                'duration' => $c['duration_text'] ?: ($c['duration_nights'] . 'N / ' . $c['duration_days'] . 'D'),
                'badge' => $c['badge'] ?: 'Premier Cruise',
                'badge_class' => 'bg-brand-600 text-white',
                'price' => (float)$c['starting_price'],
                'original_price' => (float)($c['original_price'] ?: ($c['starting_price'] * 1.3)),
                'price_unit' => '/ person',
                'rating' => (float)($c['rating'] ?: 4.9),
                'reviews' => ((int)($c['reviews_count'] ?: 250)) . '+ reviews',
                'image' => $img,
                'inclusions' => [
                    ['icon' => 'fa-solid fa-utensils', 'label' => 'All Buffet Meals'],
                    ['icon' => 'fa-solid fa-masks-theater', 'label' => 'Broadway Shows'],
                    ['icon' => 'fa-solid fa-water-ladder', 'label' => 'Infinity Pools'],
                    ['icon' => 'fa-solid fa-dice', 'label' => 'Casino & Lounges']
                ],
                'perks' => 'All Meals Included • Infinity Pools & Shows • Instant Confirmation',
                'tags' => $tagsStr,
                'slug' => $c['slug'] ?: $c['id']
            ];
        }
    } catch (Exception $e) {
    }
}

// 3. Dynamic Flights Engine & Aggregator (ONLY triggered if explicitly searching flights)
$showFlights = ($initialType === 'flight') || (!empty($_GET['from']) && !empty($_GET['to']));
if ($showFlights) {
    $flightOrigin = !empty($_GET['from']) ? strtoupper(trim($_GET['from'])) : 'DEL';
    $flightDest = !empty($_GET['to']) ? strtoupper(trim($_GET['to'])) : 'BOM';
    if (preg_match('/\b([A-Z]{3})\b/', $flightOrigin, $m)) $flightOrigin = $m[1];
    if (preg_match('/\b([A-Z]{3})\b/', $flightDest, $m)) $flightDest = $m[1];
    if (!empty($initialQuery) && preg_match('/\b([A-Z]{3})\b/', $initialQuery, $m)) {
        $flightDest = $m[1];
    }
    $dynamicFlights = getFlightsForSearchPortal($flightOrigin, $flightDest);
    foreach ($dynamicFlights as $fl) {
        $searchItems[] = $fl;
    }
}

// Calculate accurate category counts directly from database items
$countDomestic = count(array_filter($searchItems, fn($i) => $i['type'] === 'package' && ($i['category'] ?? '') === 'domestic'));
$countInternational = count(array_filter($searchItems, fn($i) => $i['type'] === 'package' && ($i['category'] ?? '') === 'international'));
$countPackages = count(array_filter($searchItems, fn($i) => $i['type'] === 'package'));
$countHotels = count(array_filter($searchItems, fn($i) => $i['type'] === 'hotel'));
$countCruises = count(array_filter($searchItems, fn($i) => $i['type'] === 'cruise'));
$countHolidays = $countPackages + $countHotels + $countCruises; // Combined tour packages, hotels & cruises (no flights!)
$countFlights = $showFlights ? count(array_filter($searchItems, fn($i) => $i['type'] === 'flight')) : 0;
$countAll = $countHolidays;

$initialHeading = 'Tours & Hotels';
$initialSubtext = 'Showing verified tour packages, boutique resorts, and 5-star handpicked stays';
$initialCount = $countHolidays;

if ($initialType === 'domestic') {
    $initialHeading = 'Domestic Tours';
    $initialSubtext = 'Showing verified Indian holiday packages';
    $initialCount = $countDomestic;
} elseif ($initialType === 'international') {
    $initialHeading = 'International Tours';
    $initialSubtext = 'Showing world tour packages, luxury getaways & international resorts';
    $initialCount = $countInternational;
} elseif ($initialType === 'hotel') {
    $initialHeading = 'Hotels & Resorts';
    $initialSubtext = 'Showing verified partner stays, luxury villas and resorts';
    $initialCount = $countHotels;
} elseif ($initialType === 'cruise') {
    $initialHeading = 'Ocean Cruises';
    $initialSubtext = 'Showing verified luxury ocean liners, high seas sailings & island cruises';
    $initialCount = $countCruises;
} elseif ($initialType === 'flight') {
    $initialHeading = 'Flight Deals';
    $initialSubtext = 'Showing non-stop flights and verified airline schedules';
    $initialCount = $countFlights;
}

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<style>
    /* Sleek custom scrollbar for fixed filter sidebar */
    .filter-sidebar-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .filter-sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .filter-sidebar-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }

    .filter-sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .filter-sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
</style>

<!-- ==========================================
     TOP REFINEMENT SEARCH BANNER (100% Flat, No Shadows)
=========================================== -->
<section class="w-full bg-slate-900 text-white border-b border-slate-800 relative py-4 sm:py-8 px-3 sm:px-8 xl:px-12">
    <div class="max-w-7xl mx-auto">

        <!-- Breadcrumb & Tagline -->
        <div class="flex items-center justify-between gap-3 mb-3 sm:mb-5">
            <div class="flex items-center space-x-1.5 sm:space-x-2 text-[11px] sm:text-xs text-slate-400">
                <a href="index.php" class="hover:text-white transition flex items-center space-x-1">
                    <i class="fa-solid fa-house text-[10px] sm:text-[11px]"></i>
                    <span>Home</span>
                </a>
                <i class="fa-solid fa-chevron-right text-[8px] sm:text-[9px] text-slate-600"></i>
                <span class="text-teal-400 font-semibold">Search Portal</span>
            </div>

            <div class="hidden sm:flex items-center space-x-2 text-xs text-slate-400">
                <i class="fa-solid fa-shield-halved text-brand-400 text-xs"></i>
                <span>100% Flat Pricing &bull; Zero Hidden Markups &bull; Instant Confirmation</span>
            </div>
        </div>

        <!-- Universal Search Input Bar -->
        <div class="bg-white rounded-2xl sm:rounded-full p-1.5 sm:p-2 border border-slate-700 flex flex-col sm:flex-row items-center gap-1.5 sm:gap-2">
            <div class="w-full flex-1 flex items-center px-3 sm:px-4 py-1 sm:py-1">
                <i class="fa-solid fa-magnifying-glass text-brand-600 text-sm sm:text-base mr-2.5 sm:mr-3 shrink-0"></i>
                <input type="text"
                    id="liveSearchInput"
                    value="<?= htmlspecialchars($initialQuery) ?>"
                    placeholder="Search destination, hotel, cruise, airline..."
                    class="w-full text-slate-900 placeholder-slate-400 text-xs sm:text-sm font-semibold bg-transparent focus:outline-none"
                    autocomplete="off">
                <button type="button" id="clearSearchBtn" class="text-slate-400 hover:text-slate-600 px-2 <?= empty($initialQuery) ? 'hidden' : '' ?>" title="Clear input">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Instant Search Button -->
            <button type="button" id="searchActionBtn" class="w-full sm:w-auto px-5 sm:px-8 py-2 sm:py-3 rounded-xl sm:rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm uppercase tracking-wider transition active:scale-95 shrink-0 flex items-center justify-center space-x-1.5 sm:space-x-2">
                <span>Filter Results</span>
                <i class="fa-solid fa-arrow-right text-[11px] sm:text-xs"></i>
            </button>
        </div>

        <!-- Trending Quick Keywords Pills -->
        <div class="flex items-center space-x-1.5 sm:space-x-2 mt-2.5 sm:mt-4 text-[11px] sm:text-xs overflow-x-auto no-scrollbar whitespace-nowrap py-0.5">
            <span class="text-slate-400 font-medium mr-0.5 shrink-0">Trending:</span>
            <button type="button" class="quick-term-pill shrink-0 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 transition" data-term="Kashmir">Kashmir</button>
            <button type="button" class="quick-term-pill shrink-0 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 transition" data-term="Goa">Goa</button>
            <button type="button" class="quick-term-pill shrink-0 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 transition" data-term="Dubai">Dubai</button>
            <button type="button" class="quick-term-pill shrink-0 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 transition" data-term="Himachal">Himachal</button>
            <?php if ($showFlights): ?>
                <button type="button" class="quick-term-pill shrink-0 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 transition" data-term="IndiGo">IndiGo</button>
            <?php endif; ?>
            <button type="button" class="quick-term-pill shrink-0 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 transition" data-term="Bali">Bali</button>
            <button type="button" class="quick-term-pill shrink-0 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 transition" data-term="Kerala">Kerala</button>
        </div>

    </div>
</section>

<!-- ==========================================
     CATEGORY SWITCHER STRIP (Sticky & Minimal)
=========================================== -->
<div class="w-full bg-white border-b border-slate-200 sticky top-14 md:top-24 z-30">
    <div class="max-w-7xl mx-auto px-3 sm:px-8 xl:px-12">
        <div class="flex items-center justify-between overflow-x-auto no-scrollbar py-2 sm:py-3 gap-2 sm:gap-3">

            <!-- Category Navigation Tabs -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 shrink-0" id="searchCategoryTabs">
                <!-- All Holidays & Stays (Tour Packages + Hotels) -->
                <button type="button"
                    class="cat-tab-btn px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[11px] sm:text-xs font-bold transition flex items-center space-x-1.5 sm:space-x-2 <?= ($initialType === 'all' || empty($initialType)) ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                    data-type="all">
                    <i class="fa-solid fa-layer-group text-[10px] sm:text-xs"></i>
                    <span>Tours &amp; Hotels</span>
                    <span class="rounded-full bg-white/25 px-1.5 py-0.2 text-[9px] sm:text-[10px] cat-count-all"><?= $countHolidays ?></span>
                </button>

                <!-- Domestic Tours & Stays -->
                <button type="button"
                    class="cat-tab-btn px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[11px] sm:text-xs font-bold transition flex items-center space-x-1.5 sm:space-x-2 <?= ($initialType === 'domestic') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                    data-type="domestic">
                    <i class="fa-solid fa-map-location-dot text-[10px] sm:text-xs"></i>
                    <span>Domestic Tours</span>
                    <span class="rounded-full bg-white/25 px-1.5 py-0.2 text-[9px] sm:text-[10px] cat-count-domestic"><?= $countDomestic ?></span>
                </button>

                <!-- International Tours & Stays -->
                <button type="button"
                    class="cat-tab-btn px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[11px] sm:text-xs font-bold transition flex items-center space-x-1.5 sm:space-x-2 <?= ($initialType === 'international') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                    data-type="international">
                    <i class="fa-solid fa-globe text-[10px] sm:text-xs"></i>
                    <span>International Tours</span>
                    <span class="rounded-full bg-white/25 px-1.5 py-0.2 text-[9px] sm:text-[10px] cat-count-international"><?= $countInternational ?></span>
                </button>

                <!-- Hotels & Resorts Only -->
                <button type="button"
                    class="cat-tab-btn px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[11px] sm:text-xs font-bold transition flex items-center space-x-1.5 sm:space-x-2 <?= ($initialType === 'hotel') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                    data-type="hotel">
                    <i class="fa-solid fa-hotel text-[10px] sm:text-xs"></i>
                    <span>Hotels Only</span>
                    <span class="rounded-full bg-white/25 px-1.5 py-0.2 text-[9px] sm:text-[10px] cat-count-hotel"><?= $countHotels ?></span>
                </button>

                <!-- Ocean Cruises Only -->
                <button type="button"
                    class="cat-tab-btn px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[11px] sm:text-xs font-bold transition flex items-center space-x-1.5 sm:space-x-2 <?= ($initialType === 'cruise') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                    data-type="cruise">
                    <i class="fa-solid fa-ship text-[10px] sm:text-xs"></i>
                    <span>Ocean Cruises</span>
                    <span class="rounded-full bg-white/25 px-1.5 py-0.2 text-[9px] sm:text-[10px] cat-count-cruise"><?= $countCruises ?></span>
                </button>

                <?php if ($showFlights): ?>
                    <!-- Flights (Separate - Only when clicked/searched) -->
                    <button type="button"
                        class="cat-tab-btn px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[11px] sm:text-xs font-bold transition flex items-center space-x-1.5 sm:space-x-2 <?= ($initialType === 'flight') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>"
                        data-type="flight">
                        <i class="fa-solid fa-plane-departure text-[10px] sm:text-xs"></i>
                        <span>Flights</span>
                        <span class="rounded-full bg-white/25 px-1.5 py-0.2 text-[9px] sm:text-[10px] cat-count-flight"><?= $countFlights ?></span>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Sort By Select Box -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 shrink-0 ml-auto">
                <span class="text-xs text-slate-500 font-semibold hidden md:inline">Sort:</span>
                <div class="relative">
                    <select id="sortBySelect" class="appearance-none bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] sm:text-xs font-bold py-1.5 sm:py-2 pl-2.5 sm:pl-3 pr-7 sm:pr-8 rounded-full border border-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer transition">
                        <option value="recommended">Best Recommended</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Top Rated (5★ first)</option>
                    </select>
                    <i class="fa-solid fa-chevron-down text-[9px] sm:text-[10px] text-slate-500 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>

                <!-- Mobile Filter Drawer Toggle Button -->
                <button type="button" id="mobileFilterToggleBtn" class="lg:hidden px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-full bg-brand-50 text-brand-700 border border-brand-200 text-[11px] sm:text-xs font-bold flex items-center space-x-1">
                    <i class="fa-solid fa-sliders text-[10px] sm:text-xs"></i>
                    <span>Filters</span>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ==========================================
     MAIN SEARCH RESULTS & FILTER LAYOUT
=========================================== -->
<main class="w-full py-5 sm:py-10 px-3 sm:px-8 xl:px-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <!-- Top Header Status Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-slate-200">
            <div>
                <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-1.5 sm:space-x-2">
                    <span id="resultsTypeTitle"><?= htmlspecialchars($initialHeading) ?></span>
                    <span class="text-slate-400 font-normal text-xs sm:text-base">(&bull; <span id="resultsCount"><?= $initialCount ?></span> available)</span>
                </h1>
                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5" id="resultsSubtext">
                    <?= htmlspecialchars($initialSubtext) ?>
                </p>
            </div>

            <!-- Active Filter Chips Container -->
            <div id="activeFilterChips" class="flex flex-wrap items-center gap-1.5">
                <!-- Dynamically populated via JS -->
            </div>
        </div>

        <!-- 2-Column Responsive Grid -->
        <div class="flex flex-col lg:flex-row gap-8 items-start">

            <!-- ==========================================
                 LEFT COLUMN: FILTER SIDEBAR (Fixed / Sticky with Internal Scroll)
            =========================================== -->
            <aside id="filterSidebar" class="hidden lg:block w-full lg:w-72 shrink-0 space-y-5 lg:sticky lg:top-36 xl:top-40 lg:max-h-[calc(100vh-10rem)] lg:overflow-y-auto overscroll-contain pr-1.5 filter-sidebar-scroll pb-6">

                <!-- Filter Box 1: Category Radios -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-list-check text-brand-600"></i>
                            <span>Travel Category</span>
                        </h3>
                        <button type="button" class="reset-filter-group text-[11px] text-brand-600 hover:underline font-semibold" data-group="category">Reset</button>
                    </div>

                    <div class="space-y-2.5">
                        <label class="flex items-center justify-between cursor-pointer group p-1.5 rounded-xl hover:bg-slate-50 transition">
                            <span class="flex items-center space-x-2.5 text-xs font-semibold text-slate-700 group-hover:text-brand-700">
                                <input type="radio" name="sideCategory" value="all" class="text-brand-600 focus:ring-brand-500 accent-teal-600" <?= ($initialType === 'all' || empty($initialType)) ? 'checked' : '' ?>>
                                <span>Tours &amp; Hotels</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-400"><?= $countHolidays ?></span>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer group p-1.5 rounded-xl hover:bg-slate-50 transition">
                            <span class="flex items-center space-x-2.5 text-xs font-semibold text-slate-700 group-hover:text-brand-700">
                                <input type="radio" name="sideCategory" value="domestic" class="text-brand-600 focus:ring-brand-500 accent-teal-600" <?= ($initialType === 'domestic') ? 'checked' : '' ?>>
                                <span>Domestic Tours</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-400"><?= $countDomestic ?></span>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer group p-1.5 rounded-xl hover:bg-slate-50 transition">
                            <span class="flex items-center space-x-2.5 text-xs font-semibold text-slate-700 group-hover:text-brand-700">
                                <input type="radio" name="sideCategory" value="international" class="text-brand-600 focus:ring-brand-500 accent-teal-600" <?= ($initialType === 'international') ? 'checked' : '' ?>>
                                <span>International Tours</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-400"><?= $countInternational ?></span>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer group p-1.5 rounded-xl hover:bg-slate-50 transition">
                            <span class="flex items-center space-x-2.5 text-xs font-semibold text-slate-700 group-hover:text-brand-700">
                                <input type="radio" name="sideCategory" value="hotel" class="text-brand-600 focus:ring-brand-500 accent-teal-600" <?= ($initialType === 'hotel') ? 'checked' : '' ?>>
                                <span>Hotels Only</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-400"><?= $countHotels ?></span>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer group p-1.5 rounded-xl hover:bg-slate-50 transition">
                            <span class="flex items-center space-x-2.5 text-xs font-semibold text-slate-700 group-hover:text-brand-700">
                                <input type="radio" name="sideCategory" value="cruise" class="text-brand-600 focus:ring-brand-500 accent-teal-600" <?= ($initialType === 'cruise') ? 'checked' : '' ?>>
                                <span>Ocean Cruises</span>
                            </span>
                            <span class="text-[11px] font-mono text-slate-400"><?= $countCruises ?></span>
                        </label>

                        <?php if ($showFlights): ?>
                            <label class="flex items-center justify-between cursor-pointer group p-1.5 rounded-xl hover:bg-slate-50 transition">
                                <span class="flex items-center space-x-2.5 text-xs font-semibold text-slate-700 group-hover:text-brand-700">
                                    <input type="radio" name="sideCategory" value="flight" class="text-brand-600 focus:ring-brand-500 accent-teal-600" <?= ($initialType === 'flight') ? 'checked' : '' ?>>
                                    <span>Flights</span>
                                </span>
                                <span class="text-[11px] font-mono text-slate-400"><?= $countFlights ?></span>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filter Box: Experience Theme / Style -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-brand-600"></i>
                            <span>Theme &amp; Style</span>
                        </h3>
                        <button type="button" class="reset-filter-group text-[11px] text-brand-600 hover:underline font-semibold" data-group="theme">Reset</button>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="themeFilter" value="all" <?= ($initialTheme === 'all' || empty($initialTheme)) ? 'checked' : '' ?> class="accent-teal-600">
                            <span>All Themes</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="themeFilter" value="honeymoon" <?= ($initialTheme === 'honeymoon') ? 'checked' : '' ?> class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-champagne-glasses text-rose-500 text-xs"></i>
                                <span>Honeymoon &amp; Romantic</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="themeFilter" value="family" <?= ($initialTheme === 'family') ? 'checked' : '' ?> class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-people-roof text-teal-600 text-xs"></i>
                                <span>Family &amp; Leisure</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="themeFilter" value="adventure" <?= ($initialTheme === 'adventure') ? 'checked' : '' ?> class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-person-hiking text-amber-500 text-xs"></i>
                                <span>Adventure &amp; Treks</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="themeFilter" value="beach" <?= ($initialTheme === 'beach') ? 'checked' : '' ?> class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-umbrella-beach text-sky-500 text-xs"></i>
                                <span>Beach &amp; Coastal</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="themeFilter" value="spiritual" <?= ($initialTheme === 'spiritual') ? 'checked' : '' ?> class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-om text-indigo-500 text-xs"></i>
                                <span>Pilgrimage &amp; Spiritual</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="themeFilter" value="luxury" <?= ($initialTheme === 'luxury') ? 'checked' : '' ?> class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-crown text-amber-500 text-xs"></i>
                                <span>Luxury Escapes</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Filter Box: Travel & Transportation Mode -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-route text-brand-600"></i>
                            <span>Travel Mode</span>
                        </h3>
                        <button type="button" class="reset-filter-group text-[11px] text-brand-600 hover:underline font-semibold" data-group="travelMode">Reset</button>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="travelModeFilter" value="all" checked class="accent-teal-600">
                            <span>All Travel Modes</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="travelModeFilter" value="flight" class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-plane-departure text-sky-500 text-xs"></i>
                                <span>With Flights (Airfare Included)</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="travelModeFilter" value="train" class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-train text-amber-500 text-xs"></i>
                                <span>By Train (Rail Tours)</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="travelModeFilter" value="bus" class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-bus text-emerald-500 text-xs"></i>
                                <span>By Volvo / Luxury Bus</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="travelModeFilter" value="cab" class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-car text-teal-600 text-xs"></i>
                                <span>By Private Cab / Road</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="travelModeFilter" value="land_only" class="accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-hotel text-indigo-500 text-xs"></i>
                                <span>Land Only (Stays &amp; Sightseeing)</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Filter Box 3: Price Budget Ranges -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-indian-rupee-sign text-brand-600"></i>
                            <span>Price Budget</span>
                        </h3>
                        <button type="button" class="reset-filter-group text-[11px] text-brand-600 hover:underline font-semibold" data-group="price">Reset</button>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="priceRange" value="all" checked class="accent-teal-600">
                            <span>Any Budget</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="priceRange" value="under-10k" class="accent-teal-600">
                            <span>Under ₹10,000</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="priceRange" value="10k-25k" class="accent-teal-600">
                            <span>₹10,000 – ₹25,000</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="priceRange" value="25k-50k" class="accent-teal-600">
                            <span>₹25,000 – ₹50,000</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="priceRange" value="above-50k" class="accent-teal-600">
                            <span>₹50,000 &amp; Above</span>
                        </label>
                    </div>
                </div>

                <!-- Filter Box 3: Guest Rating -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-star text-amber-500"></i>
                            <span>Guest Rating</span>
                        </h3>
                        <button type="button" class="reset-filter-group text-[11px] text-brand-600 hover:underline font-semibold" data-group="rating">Reset</button>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="ratingFilter" value="all" checked class="accent-teal-600">
                            <span>All Ratings</span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="ratingFilter" value="4.8" class="accent-teal-600">
                            <span class="flex items-center space-x-1">
                                <span>4.8★ &amp; above</span>
                                <span class="text-[10px] text-emerald-600 font-bold">(Exceptional)</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="ratingFilter" value="4.5" class="accent-teal-600">
                            <span class="flex items-center space-x-1">
                                <span>4.5★ &amp; above</span>
                                <span class="text-[10px] text-slate-400 font-medium">(Superb)</span>
                            </span>
                        </label>
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1.5 rounded-xl hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="radio" name="ratingFilter" value="4.0" class="accent-teal-600">
                            <span>4.0★ &amp; above</span>
                        </label>
                    </div>
                </div>

                <!-- Filter Box 4: Inclusions & Key Perks -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-circle-check text-brand-600"></i>
                            <span>Key Inclusions</span>
                        </h3>
                        <button type="button" class="reset-filter-group text-[11px] text-brand-600 hover:underline font-semibold" data-group="perks">Reset</button>
                    </div>

                    <div class="space-y-2.5">
                        <label class="flex items-center space-x-2.5 cursor-pointer p-1 rounded-lg hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="checkbox" value="breakfast" class="perk-checkbox rounded text-brand-600 focus:ring-brand-500 accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-utensils text-slate-400 text-xs"></i>
                                <span>Free Breakfast</span>
                            </span>
                        </label>

                        <label class="flex items-center space-x-2.5 cursor-pointer p-1 rounded-lg hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="checkbox" value="cancellation" class="perk-checkbox rounded text-brand-600 focus:ring-brand-500 accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-clock-rotate-left text-slate-400 text-xs"></i>
                                <span>Free Cancellation</span>
                            </span>
                        </label>

                        <?php if ($showFlights): ?>
                            <label class="flex items-center space-x-2.5 cursor-pointer p-1 rounded-lg hover:bg-slate-50 text-xs font-semibold text-slate-700">
                                <input type="checkbox" value="nonstop" class="perk-checkbox rounded text-brand-600 focus:ring-brand-500 accent-teal-600">
                                <span class="flex items-center space-x-1.5">
                                    <i class="fa-solid fa-plane-circle-check text-slate-400 text-xs"></i>
                                    <span>Non-Stop Flights</span>
                                </span>
                            </label>
                        <?php endif; ?>

                        <label class="flex items-center space-x-2.5 cursor-pointer p-1 rounded-lg hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="checkbox" value="pool" class="perk-checkbox rounded text-brand-600 focus:ring-brand-500 accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-person-swimming text-slate-400 text-xs"></i>
                                <span>Swimming Pool / Beach</span>
                            </span>
                        </label>

                        <label class="flex items-center space-x-2.5 cursor-pointer p-1 rounded-lg hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="checkbox" value="sightseeing" class="perk-checkbox rounded text-brand-600 focus:ring-brand-500 accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-camera text-slate-400 text-xs"></i>
                                <span>Sightseeing Included</span>
                            </span>
                        </label>

                        <label class="flex items-center space-x-2.5 cursor-pointer p-1 rounded-lg hover:bg-slate-50 text-xs font-semibold text-slate-700">
                            <input type="checkbox" value="wifi" class="perk-checkbox rounded text-brand-600 focus:ring-brand-500 accent-teal-600">
                            <span class="flex items-center space-x-1.5">
                                <i class="fa-solid fa-wifi text-slate-400 text-xs"></i>
                                <span>Free High-Speed Wi-Fi</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Reset All Filters Button -->
                <button type="button" id="resetAllFiltersBtn" class="w-full py-2.5 rounded-full bg-slate-200/80 hover:bg-slate-300 text-slate-700 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    <span>Reset All Filters</span>
                </button>

                <!-- Orion Trust Assurance Capsule -->
                <div class="p-4 rounded-2xl bg-teal-50/60 border border-brand-200/70 text-brand-900 space-y-2">
                    <div class="flex items-center space-x-2 text-xs font-bold text-brand-800">
                        <i class="fa-solid fa-badge-check text-brand-600"></i>
                        <span><?php echo htmlspecialchars($siteName); ?> Price Promise</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Found a lower comparable fare elsewhere? We'll match it and credit ₹1,000 straight to your <?php echo htmlspecialchars($siteName); ?> travel wallet.
                    </p>
                </div>

            </aside>

            <!-- ==========================================
                 RIGHT COLUMN: RESULTS STREAM
            =========================================== -->
            <div class="flex-1 w-full space-y-4" id="resultsFeedContainer">

                <?php foreach ($searchItems as $item): ?>

                    <?php if ($item['type'] === 'package'): ?>
                        <!-- ==========================================
                             TOUR PACKAGE CARD (Lightweight Minimal)
                        =========================================== -->
                        <article class="search-result-card bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition flex flex-col md:flex-row overflow-hidden"
                            data-id="<?= htmlspecialchars($item['id']) ?>"
                            data-type="package"
                            data-package-category="<?= htmlspecialchars($item['category'] ?? 'domestic') ?>"
                            data-travel-mode="<?= htmlspecialchars($item['travel_mode'] ?? 'flight') ?>"
                            data-price="<?= $item['price'] ?>"
                            data-rating="<?= $item['rating'] ?>"
                            data-tags="<?= htmlspecialchars($item['tags']) ?>"
                            data-theme="<?= htmlspecialchars($item['theme'] ?? 'general') ?>"
                            data-title="<?= htmlspecialchars($item['title']) ?>">

                            <!-- Thumbnail with Duration Pill -->
                            <div class="w-full md:w-64 h-40 sm:h-48 md:h-auto shrink-0 relative overflow-hidden bg-slate-100">
                                <img src="<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">

                                <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold <?= $item['badge_class'] ?>">
                                    <?= htmlspecialchars($item['badge']) ?>
                                </span>

                                <span class="absolute bottom-2.5 left-2.5 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold bg-slate-900/80 backdrop-blur-sm text-white">
                                    <i class="fa-regular fa-clock text-[9px] mr-1"></i>
                                    <?= htmlspecialchars($item['duration']) ?>
                                </span>
                            </div>

                            <!-- Content Body -->
                            <div class="flex-1 p-3.5 sm:p-5 flex flex-col justify-between">
                                <div>
                                    <!-- Destination Track & Rating -->
                                    <div class="flex flex-wrap items-center justify-between gap-1.5 sm:gap-2 mb-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-bold text-brand-700 bg-brand-50 px-2 sm:px-2.5 py-0.5 rounded-full">
                                                <i class="fa-solid fa-map-pin text-[9px]"></i>
                                                <span><?= htmlspecialchars($item['sub_type']) ?></span>
                                            </span>

                                            <?php
                                            $cMode = $item['travel_mode'] ?? 'flight';
                                            $cModeIcon = 'fa-solid fa-plane-departure text-sky-600';
                                            $cModeLabel = 'With Flights';
                                            if ($cMode === 'train') {
                                                $cModeIcon = 'fa-solid fa-train text-amber-600';
                                                $cModeLabel = 'By Train';
                                            } elseif ($cMode === 'bus') {
                                                $cModeIcon = 'fa-solid fa-bus text-emerald-600';
                                                $cModeLabel = 'By Volvo Bus';
                                            } elseif ($cMode === 'cab') {
                                                $cModeIcon = 'fa-solid fa-car text-teal-600';
                                                $cModeLabel = 'By Private Cab';
                                            } elseif ($cMode === 'land_only') {
                                                $cModeIcon = 'fa-solid fa-hotel text-indigo-600';
                                                $cModeLabel = 'Land Only';
                                            }
                                            ?>
                                            <span class="inline-flex items-center space-x-1 text-[9px] sm:text-[10px] font-bold text-slate-700 bg-slate-100 border border-slate-200 px-1.5 sm:px-2 py-0.5 rounded-full">
                                                <i class="<?= $cModeIcon ?> text-[8px] sm:text-[9px]"></i>
                                                <span><?= $cModeLabel ?></span>
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-1 text-[11px]">
                                            <span class="px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 font-bold border border-amber-200 text-[10px] sm:text-[11px] flex items-center space-x-0.5">
                                                <i class="fa-solid fa-star text-[9px] text-amber-500"></i>
                                                <span><?= $item['rating'] ?></span>
                                            </span>
                                            <span class="text-[10px] text-slate-400 hidden sm:inline">(<?= $item['reviews'] ?>)</span>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="text-sm sm:text-lg font-bold text-slate-900 leading-snug hover:text-brand-700 transition">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h3>

                                    <!-- Route Circuit -->
                                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1 flex items-center space-x-1 font-medium">
                                        <i class="fa-solid fa-route text-slate-400 text-[10px] sm:text-xs"></i>
                                        <span class="truncate"><?= htmlspecialchars($item['route']) ?></span>
                                    </p>

                                    <!-- Inclusions Pill Strip -->
                                    <div class="flex flex-wrap items-center gap-1 sm:gap-1.5 mt-2 sm:mt-3">
                                        <?php foreach ($item['inclusions'] as $inc): ?>
                                            <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-semibold bg-slate-100 text-slate-700 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full border border-slate-200/60">
                                                <i class="<?= $inc['icon'] ?> text-brand-600 text-[8px] sm:text-[10px]"></i>
                                                <span><?= htmlspecialchars($inc['label']) ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Bottom Perks -->
                                <div class="mt-2.5 sm:mt-4 pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px] text-slate-500">
                                    <span class="flex items-center space-x-1 text-emerald-600 font-semibold truncate">
                                        <i class="fa-solid fa-check text-[9px] sm:text-[10px]"></i>
                                        <span><?= htmlspecialchars($item['perks']) ?></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Right CTA & Price Strip -->
                            <div class="md:w-56 shrink-0 p-3.5 sm:p-5 bg-slate-50/70 border-t md:border-t-0 md:border-l border-slate-200 flex md:flex-col justify-between items-center md:items-end text-left md:text-right">
                                <div>
                                    <span class="text-[10px] sm:text-[11px] text-slate-400 block line-through">₹<?= number_format($item['original_price']) ?></span>
                                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-none">
                                        ₹<?= number_format($item['price']) ?>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] text-slate-500 block mt-0.5">per person &bull; incl. taxes</span>
                                </div>

                                <div class="flex md:flex-col items-center gap-1.5 shrink-0 md:w-full text-right">
                                    <a href="package-details.php?id=<?= urlencode($item['id']) ?>"
                                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-1.5">
                                        <span>Details</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                    <button type="button"
                                        class="open-inquire-btn hidden sm:flex w-full px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition items-center justify-center space-x-1"
                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                        data-category="Tour Package"
                                        data-price="₹<?= number_format($item['price']) ?>"
                                        data-duration="<?= htmlspecialchars($item['duration']) ?>">
                                        <i class="fa-solid fa-paper-plane text-[9px] text-brand-600"></i>
                                        <span>Inquiry</span>
                                    </button>
                                </div>
                            </div>

                        </article>

                    <?php elseif ($item['type'] === 'flight'): ?>
                        <!-- ==========================================
                             FLIGHT CARD (Lightweight Minimal)
                        =========================================== -->
                        <article class="search-result-card bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition flex flex-col md:flex-row overflow-hidden"
                            data-id="<?= htmlspecialchars($item['id']) ?>"
                            data-type="flight"
                            data-price="<?= $item['price'] ?>"
                            data-rating="<?= $item['rating'] ?>"
                            data-tags="<?= htmlspecialchars($item['tags']) ?>"
                            data-title="<?= htmlspecialchars($item['title']) ?>">

                            <!-- Airline Identity Column -->
                            <div class="w-full md:w-48 p-3.5 sm:p-5 shrink-0 bg-slate-50/50 border-b md:border-b-0 md:border-r border-slate-200 flex md:flex-col items-center justify-between md:justify-center text-center">
                                <div class="flex md:flex-col items-center space-x-2.5 md:space-x-0 md:space-y-2">
                                    <?php if (!empty($item['airline_logo'])): ?>
                                        <div class="h-8 sm:h-10 w-20 sm:w-24 bg-white border border-slate-200 rounded-lg sm:rounded-xl p-1 flex items-center justify-center shadow-xs">
                                            <img src="<?= htmlspecialchars($item['airline_logo']) ?>" alt="<?= htmlspecialchars($item['airline']) ?>" class="max-h-full max-w-full object-contain">
                                        </div>
                                    <?php else: ?>
                                        <span class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-brand-600 text-white flex items-center justify-center font-black text-xs sm:text-sm">
                                            <i class="fa-solid fa-plane-departure text-xs sm:text-sm"></i>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-xs sm:text-sm"><?= htmlspecialchars($item['airline']) ?></h4>
                                        <span class="text-[10px] sm:text-[11px] font-mono text-slate-500 block"><?= htmlspecialchars($item['airline_code']) ?></span>
                                    </div>
                                </div>

                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $item['badge_class'] ?> mt-0 md:mt-3">
                                    <?= htmlspecialchars($item['badge']) ?>
                                </span>
                            </div>

                            <!-- Flight Timeline & Details -->
                            <div class="flex-1 p-3.5 sm:p-5 flex flex-col justify-between">
                                <div>
                                    <!-- Route & Times Strip -->
                                    <div class="flex items-center justify-between gap-3 sm:gap-4 py-1 sm:py-2">
                                        <!-- Departure -->
                                        <div class="text-left">
                                            <span class="text-lg sm:text-2xl font-black text-slate-900 block"><?= htmlspecialchars($item['from_time']) ?></span>
                                            <span class="text-[11px] sm:text-xs font-bold text-brand-700"><?= htmlspecialchars($item['from_code']) ?></span>
                                            <span class="text-[10px] sm:text-[11px] text-slate-500 block"><?= htmlspecialchars($item['from_city']) ?></span>
                                        </div>

                                        <!-- Duration & Direct Bar -->
                                        <div class="flex-1 max-w-xs flex flex-col items-center px-2 sm:px-4">
                                            <span class="text-[10px] sm:text-[11px] font-bold text-slate-500 mb-0.5 sm:mb-1"><?= htmlspecialchars($item['duration']) ?></span>
                                            <div class="w-full flex items-center">
                                                <div class="h-0.5 bg-slate-300 flex-1"></div>
                                                <i class="fa-solid fa-plane text-brand-600 text-[10px] sm:text-xs mx-1.5 sm:mx-2"></i>
                                                <div class="h-0.5 bg-slate-300 flex-1"></div>
                                            </div>
                                            <span class="text-[9px] sm:text-[10px] font-semibold text-emerald-600 mt-0.5 sm:mt-1"><?= htmlspecialchars($item['stops']) ?></span>
                                        </div>

                                        <!-- Arrival -->
                                        <div class="text-right">
                                            <span class="text-lg sm:text-2xl font-black text-slate-900 block"><?= htmlspecialchars($item['to_time']) ?></span>
                                            <span class="text-[11px] sm:text-xs font-bold text-brand-700"><?= htmlspecialchars($item['to_code']) ?></span>
                                            <span class="text-[10px] sm:text-[11px] text-slate-500 block"><?= htmlspecialchars($item['to_city']) ?></span>
                                        </div>
                                    </div>

                                    <!-- Flight Baggage & Amenities Strip -->
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-2.5 sm:mt-4 pt-2 sm:pt-3 border-t border-slate-100">
                                        <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full">
                                            <i class="fa-solid fa-suitcase-rolling text-slate-500 text-[9px] sm:text-[10px]"></i>
                                            <span><?= htmlspecialchars($item['baggage']) ?></span>
                                        </span>
                                        <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full">
                                            <i class="fa-solid fa-couch text-slate-500 text-[9px] sm:text-[10px]"></i>
                                            <span><?= htmlspecialchars($item['airline_class'] ?? 'Economy Standard') ?></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Perks Note -->
                                <div class="mt-2.5 sm:mt-3 flex items-center justify-between text-[10px] sm:text-[11px] text-slate-500">
                                    <span class="text-emerald-600 font-semibold flex items-center space-x-1 truncate">
                                        <i class="fa-solid fa-shield-check text-[9px] sm:text-[10px]"></i>
                                        <span><?= htmlspecialchars($item['perks']) ?></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Right CTA & Price Strip -->
                            <div class="md:w-56 shrink-0 p-3.5 sm:p-5 bg-slate-50/70 border-t md:border-t-0 md:border-l border-slate-200 flex md:flex-col justify-between items-center md:items-end text-left md:text-right">
                                <div>
                                    <span class="text-[10px] sm:text-[11px] text-slate-400 block line-through">₹<?= number_format($item['original_price']) ?></span>
                                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-none">
                                        ₹<?= number_format($item['price']) ?>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] text-slate-500 block mt-0.5">per traveler &bull; all fees incl.</span>
                                </div>

                                <div class="shrink-0 md:w-full text-right">
                                    <button type="button"
                                        class="open-inquire-btn px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-1.5"
                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                        data-category="Flight Ticket"
                                        data-price="₹<?= number_format($item['price']) ?>"
                                        data-duration="<?= htmlspecialchars($item['duration']) ?>">
                                        <span>Book Flight</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </button>
                                </div>
                            </div>

                        </article>

                    <?php elseif ($item['type'] === 'hotel'): ?>
                        <!-- ==========================================
                             HOTEL & RESORT CARD (Lightweight Minimal)
                        =========================================== -->
                        <article class="search-result-card bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition flex flex-col md:flex-row overflow-hidden"
                            data-id="<?= htmlspecialchars($item['id']) ?>"
                            data-type="hotel"
                            data-package-category="<?= htmlspecialchars($item['category'] ?? 'domestic') ?>"
                            data-city="<?= htmlspecialchars($item['destination'] ?? '') ?>"
                            data-price="<?= $item['price'] ?>"
                            data-rating="<?= $item['rating'] ?>"
                            data-tags="<?= htmlspecialchars($item['tags']) ?>"
                            data-title="<?= htmlspecialchars($item['title']) ?>">

                            <!-- Resort Photo with Star Pill -->
                            <div class="w-full md:w-64 h-40 sm:h-48 md:h-auto shrink-0 relative overflow-hidden bg-slate-100">
                                <img src="<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">

                                <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold <?= $item['badge_class'] ?>">
                                    <?= htmlspecialchars($item['badge']) ?>
                                </span>
                            </div>

                            <!-- Resort Content Body -->
                            <div class="flex-1 p-3.5 sm:p-5 flex flex-col justify-between">
                                <div>
                                    <!-- Location & Rating -->
                                    <div class="flex items-center justify-between gap-1.5 sm:gap-2 mb-1">
                                        <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-bold text-brand-700 bg-brand-50 px-2 sm:px-2.5 py-0.5 rounded-full">
                                            <i class="fa-solid fa-location-dot text-[9px]"></i>
                                            <span><?= htmlspecialchars($item['location']) ?></span>
                                        </span>

                                        <div class="flex items-center space-x-1 text-[11px]">
                                            <span class="px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 font-bold border border-amber-200 text-[10px] sm:text-[11px] flex items-center space-x-0.5">
                                                <i class="fa-solid fa-star text-[9px] text-amber-500"></i>
                                                <span><?= $item['rating'] ?></span>
                                            </span>
                                            <span class="text-[10px] text-slate-400 hidden sm:inline">(<?= $item['reviews'] ?>)</span>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="text-sm sm:text-lg font-bold text-slate-900 leading-snug hover:text-brand-700 transition">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h3>

                                    <!-- Room Type -->
                                    <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1 flex items-center space-x-1 font-medium">
                                        <i class="fa-solid fa-bed text-slate-400 text-[10px] sm:text-xs"></i>
                                        <span class="truncate"><?= htmlspecialchars($item['room_type']) ?></span>
                                    </p>

                                    <!-- Amenities Pills -->
                                    <div class="flex flex-wrap items-center gap-1 sm:gap-1.5 mt-2 sm:mt-3">
                                        <?php foreach ($item['amenities'] as $am): ?>
                                            <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-semibold bg-slate-100 text-slate-700 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full border border-slate-200/60">
                                                <i class="<?= $am['icon'] ?> text-brand-600 text-[8px] sm:text-[10px]"></i>
                                                <span><?= htmlspecialchars($am['label']) ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Perks Note -->
                                <div class="mt-2.5 sm:mt-4 pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px] text-slate-500">
                                    <span class="text-emerald-600 font-semibold flex items-center space-x-1 truncate">
                                        <i class="fa-solid fa-check text-[9px] sm:text-[10px]"></i>
                                        <span><?= htmlspecialchars($item['perks']) ?></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Right CTA & Price Strip -->
                            <div class="md:w-56 shrink-0 p-3.5 sm:p-5 bg-slate-50/70 border-t md:border-t-0 md:border-l border-slate-200 flex md:flex-col justify-between items-center md:items-end text-left md:text-right">
                                <div>
                                    <span class="text-[10px] sm:text-[11px] text-slate-400 block line-through">₹<?= number_format($item['original_price']) ?></span>
                                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-none">
                                        ₹<?= number_format($item['price']) ?>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] text-slate-500 block mt-0.5"><?= $item['price_unit'] ?> &bull; plus taxes</span>
                                </div>

                                <div class="flex md:flex-col items-center gap-1.5 shrink-0 md:w-full text-right">
                                    <a href="hotel-details.php?id=<?= urlencode($item['id']) ?>"
                                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-1.5">
                                        <span>Rooms</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                    <button type="button"
                                        class="open-inquire-btn hidden sm:flex w-full px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition items-center justify-center space-x-1"
                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                        data-category="Hotel & Resort"
                                        data-price="₹<?= number_format($item['price']) ?>"
                                        data-duration="<?= htmlspecialchars($item['room_type']) ?>">
                                        <i class="fa-solid fa-paper-plane text-[9px] text-brand-600"></i>
                                        <span>Inquiry</span>
                                    </button>
                                </div>
                            </div>

                        </article>

                    <?php elseif ($item['type'] === 'cruise'): ?>
                        <!-- ==========================================
                             OCEAN CRUISE CARD (Lightweight Minimal)
                        =========================================== -->
                        <article class="search-result-card bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition flex flex-col md:flex-row overflow-hidden"
                            data-id="<?= htmlspecialchars($item['id']) ?>"
                            data-type="cruise"
                            data-package-category="<?= htmlspecialchars($item['category'] ?? 'domestic') ?>"
                            data-city="<?= htmlspecialchars($item['departure_city'] ?? '') ?>"
                            data-price="<?= $item['price'] ?>"
                            data-rating="<?= $item['rating'] ?>"
                            data-tags="<?= htmlspecialchars($item['tags']) ?>"
                            data-title="<?= htmlspecialchars($item['title']) ?>">

                            <!-- Cruise Photo with Badge & Duration -->
                            <div class="w-full md:w-64 h-40 sm:h-48 md:h-auto shrink-0 relative overflow-hidden bg-slate-100">
                                <img src="<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['title']) ?>"
                                    loading="lazy"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">

                                <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold <?= $item['badge_class'] ?>">
                                    <?= htmlspecialchars($item['badge']) ?>
                                </span>

                                <span class="absolute bottom-2.5 left-2.5 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-white/95 text-slate-800 border border-slate-200">
                                    <i class="fa-solid fa-anchor text-brand-600 mr-1"></i>Ex-<?= htmlspecialchars($item['departure_city']) ?>
                                </span>
                            </div>

                            <!-- Cruise Content Body -->
                            <div class="flex-1 p-3.5 sm:p-5 flex flex-col justify-between">
                                <div>
                                    <!-- Ship & Rating -->
                                    <div class="flex items-center justify-between gap-1.5 sm:gap-2 mb-1">
                                        <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-bold text-brand-700 bg-brand-50 px-2 sm:px-2.5 py-0.5 rounded-full border border-brand-200/60">
                                            <i class="fa-solid fa-ship text-[9px]"></i>
                                            <span><?= htmlspecialchars($item['sub_type']) ?></span>
                                        </span>

                                        <div class="flex items-center space-x-1 text-[11px]">
                                            <span class="px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 font-bold border border-amber-200 text-[10px] sm:text-[11px] flex items-center space-x-0.5">
                                                <i class="fa-solid fa-star text-[9px] text-amber-500"></i>
                                                <span><?= $item['rating'] ?></span>
                                            </span>
                                            <span class="text-[10px] text-slate-400 hidden sm:inline">(<?= $item['reviews'] ?>)</span>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="text-sm sm:text-lg font-bold text-slate-900 leading-snug hover:text-brand-600 transition">
                                        <a href="cruise-details.php?slug=<?= urlencode($item['slug']) ?>">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </a>
                                    </h3>

                                    <!-- Route & Duration -->
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-[11px] sm:text-xs text-slate-500 mt-0.5 sm:mt-1 font-medium">
                                        <span class="flex items-center space-x-1 text-brand-700">
                                            <i class="fa-solid fa-route text-brand-600 text-[10px] sm:text-xs"></i>
                                            <span class="truncate"><?= htmlspecialchars($item['route']) ?></span>
                                        </span>
                                        <span class="flex items-center space-x-1">
                                            <i class="fa-regular fa-clock text-slate-400 text-[10px] sm:text-xs"></i>
                                            <span><?= htmlspecialchars($item['duration']) ?></span>
                                        </span>
                                    </div>

                                    <!-- Inclusions Pills -->
                                    <div class="flex flex-wrap items-center gap-1 sm:gap-1.5 mt-2 sm:mt-3">
                                        <?php foreach ($item['inclusions'] as $inc): ?>
                                            <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-semibold bg-slate-100 text-slate-700 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full border border-slate-200/60">
                                                <i class="<?= $inc['icon'] ?> text-brand-600 text-[8px] sm:text-[10px]"></i>
                                                <span><?= htmlspecialchars($inc['label']) ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Perks Note -->
                                <div class="mt-2.5 sm:mt-4 pt-2 sm:pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px] text-slate-500">
                                    <span class="text-emerald-600 font-semibold flex items-center space-x-1 truncate">
                                        <i class="fa-solid fa-check text-[9px] sm:text-[10px]"></i>
                                        <span><?= htmlspecialchars($item['perks']) ?></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Right CTA & Price Strip -->
                            <div class="md:w-56 shrink-0 p-3.5 sm:p-5 bg-slate-50/70 border-t md:border-t-0 md:border-l border-slate-200 flex md:flex-col justify-between items-center md:items-end text-left md:text-right">
                                <div>
                                    <span class="text-[10px] sm:text-[11px] text-slate-400 block line-through">₹<?= number_format($item['original_price']) ?></span>
                                    <div class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight leading-none font-space">
                                        ₹<?= number_format($item['price']) ?>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] text-slate-500 block mt-0.5"><?= $item['price_unit'] ?> &bull; twin stateroom</span>
                                </div>

                                <div class="flex md:flex-col items-center gap-1.5 shrink-0 md:w-full text-right">
                                    <a href="cruise-details.php?slug=<?= urlencode($item['slug']) ?>"
                                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-1.5">
                                        <span>Staterooms</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                    <button type="button"
                                        class="open-inquire-btn hidden sm:flex w-full px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition items-center justify-center space-x-1"
                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                        data-category="Ocean Cruise"
                                        data-price="₹<?= number_format($item['price']) ?>"
                                        data-duration="<?= htmlspecialchars($item['duration']) ?>">
                                        <i class="fa-solid fa-paper-plane text-[9px] text-brand-600"></i>
                                        <span>Inquiry</span>
                                    </button>
                                </div>
                            </div>

                        </article>

                    <?php endif; ?>

                <?php endforeach; ?>

                <!-- ==========================================
                     EMPTY STATE (When no results match filters)
                =========================================== -->
                <div id="emptyResultsState" class="hidden bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-brand-50 border border-brand-200 text-brand-600 flex items-center justify-center mx-auto text-2xl">
                        <i class="fa-solid fa-magnifying-glass-location"></i>
                    </div>

                    <div class="max-w-md mx-auto space-y-1">
                        <h3 class="text-lg font-black text-slate-900">No Travel Listings Found</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            We couldn't find any packages, flights, or hotels matching your current keywords or filter criteria.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="button" id="emptyStateResetBtn" class="px-6 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition">
                            Reset Filters &amp; View All Packages
                        </button>
                        <button type="button" id="emptyStateInquireBtn" class="px-6 py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider transition inline-flex items-center space-x-1.5">
                            <i class="fa-solid fa-paper-plane text-[10px] text-brand-600"></i>
                            <span>Request Custom Itinerary</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>
</main>

<!-- ==========================================
     QUICK INQUIRY / BOOKING MODAL (100% Flat)
=========================================== -->
<div id="quickInquireModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 sm:p-8 space-y-5 animate-in fade-in zoom-in duration-200">

        <!-- Modal Close Button -->
        <button type="button" id="closeInquireModalBtn" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition focus:outline-none">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>

        <!-- Header -->
        <div class="space-y-1.5 pr-8">
            <span id="modalCategoryBadge" class="inline-flex items-center space-x-1.5 text-[11px] font-bold text-brand-700 bg-brand-50 px-3 py-0.5 rounded-full border border-brand-200">
                <i class="fa-solid fa-tag text-[10px]"></i>
                <span id="modalCategoryText">Tour Package</span>
            </span>
            <h3 id="modalTitle" class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-snug">
                Travel Package Inquiry
            </h3>
            <p id="modalDuration" class="text-xs text-slate-500 font-medium">
                Best Guaranteed Fare &bull; Fast-Track Concierge Booking
            </p>
        </div>

        <!-- Price Pill Strip -->
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-600">Starting Price</span>
            <span id="modalPrice" class="text-xl font-black text-brand-700 font-mono">₹17,999</span>
        </div>

        <!-- Booking & Inquiry Form -->
        <form id="inquiryForm" class="space-y-3.5" onsubmit="event.preventDefault(); submitInquiryForm();">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Your Full Name</label>
                <div class="relative">
                    <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="inquireName" required placeholder="e.g. Rahul Sharma" class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Phone / WhatsApp Number</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="tel" id="inquirePhone" required placeholder="+91 98765 43210" class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Travel Date</label>
                    <div class="relative">
                        <i class="fa-regular fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="date" id="inquireDate" class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-medium text-slate-700">
                    </div>
                </div>
            </div>

            <!-- Submit CTA Buttons -->
            <div class="pt-2 space-y-2">
                <button type="submit" class="w-full py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Confirm Booking Inquiry</span>
                </button>

                <a id="whatsappConciergeLink" href="https://wa.me/919876543210" target="_blank" class="w-full py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition flex items-center justify-center space-x-2">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Chat Directly on WhatsApp</span>
                </a>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================
     MOBILE FILTER DRAWER (Collapsible on Mobile)
=========================================== -->
<div id="mobileFilterDrawer" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex justify-end">
    <div class="w-full max-w-xs bg-white h-full overflow-y-auto p-6 space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <h3 class="text-sm font-black text-slate-900 uppercase">Filters</h3>
            <button type="button" id="closeMobileFilterBtn" class="p-2 text-slate-500 hover:text-slate-900">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div id="mobileFilterContentContainer" class="space-y-4">
            <!-- Filter items mirrored from sidebar via JS or clone -->
            <p class="text-xs text-slate-500">Refine by price, stars, and inclusions.</p>
        </div>

        <button type="button" id="applyMobileFiltersBtn" class="w-full py-3 rounded-full bg-brand-600 text-white font-bold text-xs uppercase tracking-wider">
            Apply Filters
        </button>
    </div>
</div>

<!-- ==========================================
     CLIENT-SIDE REALTIME FILTERING ENGINE
=========================================== -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const activeCategoryFilter = "";
        // Elements
        const searchInput = document.getElementById('liveSearchInput');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const searchActionBtn = document.getElementById('searchActionBtn');
        const categoryTabs = document.querySelectorAll('.cat-tab-btn');
        const sideCategoryRadios = document.querySelectorAll('input[name="sideCategory"]');
        const themeRadios = document.querySelectorAll('input[name="themeFilter"]');
        const travelModeRadios = document.querySelectorAll('input[name="travelModeFilter"]');
        const priceRadios = document.querySelectorAll('input[name="priceRange"]');
        const ratingRadios = document.querySelectorAll('input[name="ratingFilter"]');
        const perkCheckboxes = document.querySelectorAll('.perk-checkbox');
        const sortBySelect = document.getElementById('sortBySelect');
        const cards = document.querySelectorAll('.search-result-card');
        const emptyState = document.getElementById('emptyResultsState');
        const resultsCountEl = document.getElementById('resultsCount');
        const resultsTypeTitleEl = document.getElementById('resultsTypeTitle');
        const resultsSubtextEl = document.getElementById('resultsSubtext');
        const resetAllBtn = document.getElementById('resetAllFiltersBtn');
        const emptyStateResetBtn = document.getElementById('emptyStateResetBtn');
        const activeFilterChipsContainer = document.getElementById('activeFilterChips');

        // Quick trending pills
        document.querySelectorAll('.quick-term-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                const term = pill.getAttribute('data-term');
                if (searchInput) {
                    searchInput.value = term;
                    if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
                    runFilters();
                }
            });
        });

        // Clear search button
        if (clearSearchBtn && searchInput) {
            clearSearchBtn.addEventListener('click', () => {
                searchInput.value = '';
                clearSearchBtn.classList.add('hidden');
                runFilters();
                searchInput.focus();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                if (clearSearchBtn) {
                    if (searchInput.value.trim().length > 0) {
                        clearSearchBtn.classList.remove('hidden');
                    } else {
                        clearSearchBtn.classList.add('hidden');
                    }
                }
                runFilters();
            });
        }

        if (searchActionBtn) {
            searchActionBtn.addEventListener('click', runFilters);
        }

        // Top Category Navigation Tabs Click
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const type = tab.getAttribute('data-type');
                setCategory(type);
            });
        });

        // Sidebar Category Radio Change
        sideCategoryRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                setCategory(radio.value);
            });
        });

        function setCategory(type) {
            // Normalize
            if (type === 'packages') type = 'domestic';
            if (type === 'flights') type = 'flight';
            if (type === 'hotels') type = 'hotel';
            if (type === 'cruises') type = 'cruise';

            // Update tabs styling
            categoryTabs.forEach(t => {
                const tType = t.getAttribute('data-type');
                if (tType === type) {
                    t.classList.remove('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
                    t.classList.add('bg-brand-600', 'text-white');
                } else {
                    t.classList.remove('bg-brand-600', 'text-white');
                    t.classList.add('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
                }
            });

            // Update sidebar radio
            sideCategoryRadios.forEach(r => {
                r.checked = (r.value === type);
            });

            runFilters();
        }

        // Filter Listeners
        themeRadios.forEach(r => r.addEventListener('change', runFilters));
        travelModeRadios.forEach(r => r.addEventListener('change', runFilters));
        priceRadios.forEach(r => r.addEventListener('change', runFilters));
        ratingRadios.forEach(r => r.addEventListener('change', runFilters));
        perkCheckboxes.forEach(cb => cb.addEventListener('change', runFilters));
        if (sortBySelect) sortBySelect.addEventListener('change', runFilters);

        // Group Resets
        document.querySelectorAll('.reset-filter-group').forEach(btn => {
            btn.addEventListener('click', () => {
                const group = btn.getAttribute('data-group');
                if (group === 'category') {
                    setCategory('all');
                } else if (group === 'theme') {
                    const defaultTheme = document.querySelector('input[name="themeFilter"][value="all"]');
                    if (defaultTheme) defaultTheme.checked = true;
                    runFilters();
                } else if (group === 'travelMode') {
                    const defaultMode = document.querySelector('input[name="travelModeFilter"][value="all"]');
                    if (defaultMode) defaultMode.checked = true;
                    runFilters();
                } else if (group === 'price') {
                    const defaultPrice = document.querySelector('input[name="priceRange"][value="all"]');
                    if (defaultPrice) defaultPrice.checked = true;
                    runFilters();
                } else if (group === 'rating') {
                    const defaultRating = document.querySelector('input[name="ratingFilter"][value="all"]');
                    if (defaultRating) defaultRating.checked = true;
                    runFilters();
                } else if (group === 'perks') {
                    perkCheckboxes.forEach(cb => cb.checked = false);
                    runFilters();
                }
            });
        });

        // Reset All Buttons
        function resetAllFilters() {
            if (searchInput) searchInput.value = '';
            if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
            setCategory('all');
            const defaultTheme = document.querySelector('input[name="themeFilter"][value="all"]');
            if (defaultTheme) defaultTheme.checked = true;
            const defaultMode = document.querySelector('input[name="travelModeFilter"][value="all"]');
            if (defaultMode) defaultMode.checked = true;
            const defaultPrice = document.querySelector('input[name="priceRange"][value="all"]');
            if (defaultPrice) defaultPrice.checked = true;
            const defaultRating = document.querySelector('input[name="ratingFilter"][value="all"]');
            if (defaultRating) defaultRating.checked = true;
            perkCheckboxes.forEach(cb => cb.checked = false);
            if (sortBySelect) sortBySelect.value = 'recommended';
            initialPackageCategory = '';
            runFilters();
        }

        if (resetAllBtn) resetAllBtn.addEventListener('click', resetAllFilters);
        if (emptyStateResetBtn) emptyStateResetBtn.addEventListener('click', resetAllFilters);

        let initialPackageCategory = '<?= $initialCategory ?>';

        // Master Filter & Sort Execution Engine
        function runFilters() {
            const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
            const activeCatRadio = document.querySelector('input[name="sideCategory"]:checked');
            const cat = activeCatRadio ? activeCatRadio.value : 'all';
            const activeThemeRadio = document.querySelector('input[name="themeFilter"]:checked');
            const selectedTheme = activeThemeRadio ? activeThemeRadio.value : 'all';
            const activeModeRadio = document.querySelector('input[name="travelModeFilter"]:checked');
            const selectedMode = activeModeRadio ? activeModeRadio.value : 'all';
            const activePriceRadio = document.querySelector('input[name="priceRange"]:checked');
            const priceRange = activePriceRadio ? activePriceRadio.value : 'all';
            const activeRatingRadio = document.querySelector('input[name="ratingFilter"]:checked');
            const minRating = activeRatingRadio ? parseFloat(activeRatingRadio.value) || 0 : 0;

            const checkedPerks = [];
            perkCheckboxes.forEach(cb => {
                if (cb.checked) checkedPerks.push(cb.value.toLowerCase());
            });

            let visibleCount = 0;
            const visibleCardsArray = [];

            cards.forEach(card => {
                const cardType = card.getAttribute('data-type');
                const cardPrice = parseFloat(card.getAttribute('data-price')) || 0;
                const cardRating = parseFloat(card.getAttribute('data-rating')) || 0;
                const cardTags = (card.getAttribute('data-tags') || '').toLowerCase();
                const cardTitle = (card.getAttribute('data-title') || '').toLowerCase();

                // 1. Category Check
                // Flights tabhi dikhenge jab flight search/select ho. Baki tour packages and hotels ek sath dikhenge.
                const cardPkgCat = (card.getAttribute('data-package-category') || '').toLowerCase();
                let matchCat = false;
                if (cat === 'all') {
                    // Tour packages, hotels and cruises are shown together!
                    // Flights are strictly excluded unless searching flights explicitly!
                    matchCat = (cardType === 'package' || cardType === 'hotel' || cardType === 'cruise');
                } else if (cat === 'domestic') {
                    // Domestic tour packages only!
                    matchCat = (cardType === 'package' && cardPkgCat === 'domestic');
                } else if (cat === 'international') {
                    // International tour packages only!
                    matchCat = (cardType === 'package' && cardPkgCat === 'international');
                } else if (cat === 'package') {
                    matchCat = (cardType === 'package');
                } else if (cat === 'hotel') {
                    matchCat = (cardType === 'hotel');
                } else if (cat === 'cruise') {
                    matchCat = (cardType === 'cruise');
                } else if (cat === 'flight') {
                    // Flights only!
                    matchCat = (cardType === 'flight');
                }

                // 2. Travel Mode Check (By Flight, By Train, By Bus, By Cab, Land Only)
                let matchTravelMode = true;
                if (selectedMode !== 'all') {
                    if (cardType === 'package') {
                        const cardTravelMode = (card.getAttribute('data-travel-mode') || 'flight').toLowerCase();
                        matchTravelMode = (cardTravelMode === selectedMode);
                    } else {
                        matchTravelMode = false;
                    }
                }

                // 3. Query Text Check
                let matchQuery = true;
                if (query.length > 0) {
                    if (cardTitle.includes(query) || cardTags.includes(query)) {
                        matchQuery = true;
                    } else {
                        const stopWords = new Set(['and', 'the', 'for', 'with', 'from', 'near', 'tour', 'package', 'hotel', 'trip', 'holiday', 'city', 'state']);
                        const tokens = query.split(/[\s,–—\/-]+/).map(t => t.trim()).filter(t => t.length >= 3 && !stopWords.has(t));
                        if (tokens.length > 0) {
                            matchQuery = tokens.some(t => cardTags.includes(t) || cardTitle.includes(t));
                        } else {
                            matchQuery = false;
                        }
                    }
                }

                // 4. Price Budget Check
                let matchPrice = true;
                if (priceRange === 'under-10k') {
                    matchPrice = cardPrice < 10000;
                } else if (priceRange === '10k-25k') {
                    matchPrice = cardPrice >= 10000 && cardPrice <= 25000;
                } else if (priceRange === '25k-50k') {
                    matchPrice = cardPrice > 25000 && cardPrice <= 50000;
                } else if (priceRange === 'above-50k') {
                    matchPrice = cardPrice > 50000;
                }

                // 5. Rating Check
                let matchRating = true;
                if (minRating > 0) {
                    matchRating = cardRating >= minRating;
                }

                // 6. Inclusions Check
                let matchPerks = true;
                if (checkedPerks.length > 0) {
                    for (let perk of checkedPerks) {
                        if (!cardTags.includes(perk)) {
                            matchPerks = false;
                            break;
                        }
                    }
                }

                // 7. Theme Check
                let matchTheme = true;
                if (selectedTheme !== 'all') {
                    if (cardType === 'package') {
                        const cardTheme = (card.getAttribute('data-theme') || '').toLowerCase();
                        matchTheme = (cardTheme === selectedTheme || cardTags.includes(selectedTheme));
                    } else {
                        matchTheme = false;
                    }
                }

                // Final Decision
                if (matchCat && matchTheme && matchTravelMode && matchQuery && matchPrice && matchRating && matchPerks) {
                    card.style.display = '';
                    visibleCount++;
                    visibleCardsArray.push(card);
                } else {
                    card.style.display = 'none';
                }
            });

            // Sorting
            const sortVal = sortBySelect ? sortBySelect.value : 'recommended';
            if (sortVal !== 'recommended') {
                const container = document.getElementById('resultsFeedContainer');
                visibleCardsArray.sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price')) || 0;
                    const priceB = parseFloat(b.getAttribute('data-price')) || 0;
                    const ratingA = parseFloat(a.getAttribute('data-rating')) || 0;
                    const ratingB = parseFloat(b.getAttribute('data-rating')) || 0;

                    if (sortVal === 'price-low') return priceA - priceB;
                    if (sortVal === 'price-high') return priceB - priceA;
                    if (sortVal === 'rating') return ratingB - ratingA;
                    return 0;
                });

                visibleCardsArray.forEach(c => container.appendChild(c));
            }

            // Update Results Count & Header Text
            if (resultsCountEl) resultsCountEl.textContent = visibleCount;
            if (emptyState) {
                if (visibleCount === 0) {
                    emptyState.classList.remove('hidden');
                    const emptyTitle = emptyState.querySelector('h3');
                    const emptyDesc = emptyState.querySelector('p');
                    if (query.length > 0) {
                        if (emptyTitle) emptyTitle.textContent = `No Packages Found for "${query}"`;
                        if (emptyDesc) emptyDesc.innerHTML = `We currently do not have departures or packages for <strong class="text-slate-800">"${escapeHtml(query)}"</strong> in our database. Our travel concierge can craft a custom itinerary for you, or you can browse other active holiday packages.`;
                    } else {
                        if (emptyTitle) emptyTitle.textContent = 'No Travel Listings Found';
                        if (emptyDesc) emptyDesc.textContent = "We couldn't find any packages, flights, or hotels matching your filter criteria.";
                    }
                } else {
                    emptyState.classList.add('hidden');
                }
            }

            // Update Title according to active category
            if (resultsTypeTitleEl) {
                if (cat === 'domestic') resultsTypeTitleEl.textContent = 'Domestic Tours';
                else if (cat === 'international') resultsTypeTitleEl.textContent = 'International Tours';
                else if (cat === 'hotel') resultsTypeTitleEl.textContent = 'Hotels & Resorts';
                else if (cat === 'cruise') resultsTypeTitleEl.textContent = 'Ocean Cruises';
                else if (cat === 'flight') resultsTypeTitleEl.textContent = 'Flight Deals';
                else resultsTypeTitleEl.textContent = 'Tours & Hotels';
            }

            // Update Subtext
            if (resultsSubtextEl) {
                if (query.length > 0) {
                    resultsSubtextEl.innerHTML = `Showing filtered results for keyword <strong class="text-brand-700">"${escapeHtml(query)}"</strong>`;
                } else {
                    if (cat === 'domestic') resultsSubtextEl.textContent = 'Showing verified Indian holiday packages';
                    else if (cat === 'international') resultsSubtextEl.textContent = 'Showing world tour packages, luxury getaways & international resorts';
                    else if (cat === 'hotel') resultsSubtextEl.textContent = 'Showing verified partner stays, luxury villas and resorts';
                    else if (cat === 'cruise') resultsSubtextEl.textContent = 'Showing verified luxury ocean liners, high seas sailings & island cruises';
                    else if (cat === 'flight') resultsSubtextEl.textContent = 'Showing non-stop flights and verified airline schedules';
                    else resultsSubtextEl.textContent = 'Showing verified tour packages, boutique resorts, and 5-star handpicked stays';
                }
            }

            // Render Active Filter Chips
            renderActiveChips(query, cat, selectedTheme, priceRange, minRating, checkedPerks);

            // Synchronize with Browser URL (without reloading)
            const newUrl = new URL(window.location);
            if (cat !== 'all') newUrl.searchParams.set('type', cat);
            else newUrl.searchParams.delete('type');

            if (selectedTheme !== 'all') newUrl.searchParams.set('theme', selectedTheme);
            else newUrl.searchParams.delete('theme');

            if (query.length > 0) newUrl.searchParams.set('query', query);
            else newUrl.searchParams.delete('query');

            window.history.replaceState({}, '', newUrl);
        }

        function renderActiveChips(query, cat, selectedTheme, priceRange, minRating, checkedPerks) {
            if (!activeFilterChipsContainer) return;
            activeFilterChipsContainer.innerHTML = '';

            if (query.length > 0) {
                createChip(`Query: ${query}`, () => {
                    if (searchInput) searchInput.value = '';
                    if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
                    runFilters();
                });
            }

            if (cat !== 'all') {
                const catLabels = {
                    domestic: 'Domestic Tours',
                    international: 'International Tours',
                    hotel: 'Hotels Only',
                    package: 'Tour Packages',
                    flight: 'Flights'
                };
                createChip(`Category: ${catLabels[cat] || cat}`, () => setCategory('all'));
            }

            if (selectedTheme !== 'all') {
                const themeLabels = {
                    'honeymoon': 'Honeymoon & Romantic',
                    'family': 'Family & Leisure',
                    'adventure': 'Adventure & Treks',
                    'beach': 'Beach & Coastal',
                    'spiritual': 'Pilgrimage & Spiritual',
                    'luxury': 'Luxury Escapes'
                };
                createChip(`Theme: ${themeLabels[selectedTheme] || selectedTheme}`, () => {
                    const defaultTheme = document.querySelector('input[name="themeFilter"][value="all"]');
                    if (defaultTheme) defaultTheme.checked = true;
                    runFilters();
                });
            }

            if (priceRange !== 'all') {
                const priceLabels = {
                    'under-10k': '< ₹10,000',
                    '10k-25k': '₹10k–₹25k',
                    '25k-50k': '₹25k–₹50k',
                    'above-50k': '> ₹50,000'
                };
                createChip(`Budget: ${priceLabels[priceRange] || priceRange}`, () => {
                    const defaultPrice = document.querySelector('input[name="priceRange"][value="all"]');
                    if (defaultPrice) defaultPrice.checked = true;
                    runFilters();
                });
            }

            if (minRating > 0) {
                createChip(`Rating: ${minRating}★+`, () => {
                    const defaultRating = document.querySelector('input[name="ratingFilter"][value="all"]');
                    if (defaultRating) defaultRating.checked = true;
                    runFilters();
                });
            }

            checkedPerks.forEach(perk => {
                createChip(`Perk: ${perk}`, () => {
                    const cb = document.querySelector(`.perk-checkbox[value="${perk}"]`);
                    if (cb) cb.checked = false;
                    runFilters();
                });
            });
        }

        function createChip(label, onRemove) {
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-brand-50 text-brand-800 border border-brand-200';
            chip.innerHTML = `<span>${escapeHtml(label)}</span><i class="fa-solid fa-xmark text-[10px] cursor-pointer hover:text-brand-900 ml-1"></i>`;
            chip.querySelector('i').addEventListener('click', onRemove);
            activeFilterChipsContainer.appendChild(chip);
        }

        function escapeHtml(text) {
            return text.replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[m]);
        }

        // ==========================================
        // Quick Inquiry Modal Trigger
        // ==========================================
        const inquireModal = document.getElementById('quickInquireModal');
        const closeInquireBtn = document.getElementById('closeInquireModalBtn');
        const modalCategoryText = document.getElementById('modalCategoryText');
        const modalTitle = document.getElementById('modalTitle');
        const modalDuration = document.getElementById('modalDuration');
        const modalPrice = document.getElementById('modalPrice');
        const whatsappLink = document.getElementById('whatsappConciergeLink');

        document.querySelectorAll('.open-inquire-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const title = btn.getAttribute('data-title') || 'Travel Itinerary';
                const cat = btn.getAttribute('data-category') || 'Package';
                const price = btn.getAttribute('data-price') || 'Contact for price';
                const duration = btn.getAttribute('data-duration') || 'Flexible departure';

                if (modalTitle) modalTitle.textContent = title;
                if (modalCategoryText) modalCategoryText.textContent = cat;
                if (modalPrice) modalPrice.textContent = price;
                if (modalDuration) modalDuration.textContent = `${duration} • Verified Partner Fare`;

                // Prepare WhatsApp direct link
                if (whatsappLink) {
                    const message = encodeURIComponent(`Hi <?= addslashes($siteName) ?> Concierge, I am interested in booking: "${title}" (${price}). Please share the full itinerary and booking availability.`);
                    whatsappLink.href = `https://wa.me/<?= htmlspecialchars($siteWhatsapp) ?>?text=${message}`;
                }

                if (inquireModal) {
                    inquireModal.classList.remove('hidden');
                }
            });
        });

        const emptyStateInquireBtn = document.getElementById('emptyStateInquireBtn');
        if (emptyStateInquireBtn) {
            emptyStateInquireBtn.addEventListener('click', () => {
                const queryVal = (searchInput ? searchInput.value : '').trim() || 'Custom Destination';
                if (modalTitle) modalTitle.textContent = `Custom Itinerary: ${queryVal}`;
                if (modalCategoryText) modalCategoryText.textContent = 'Custom Tour Package';
                if (modalPrice) modalPrice.textContent = 'Contact for Custom Quote';
                if (modalDuration) modalDuration.textContent = 'Customized Days & Nights • Tailored by Experts';
                if (inquireModal) inquireModal.classList.remove('hidden');
            });
        }

        if (closeInquireBtn && inquireModal) {
            closeInquireBtn.addEventListener('click', () => {
                inquireModal.classList.add('hidden');
            });
        }

        if (inquireModal) {
            inquireModal.addEventListener('click', (e) => {
                if (e.target === inquireModal) {
                    inquireModal.classList.add('hidden');
                }
            });
        }

        // Mobile Filter Drawer Toggle
        const mobileFilterToggleBtn = document.getElementById('mobileFilterToggleBtn');
        const mobileFilterDrawer = document.getElementById('mobileFilterDrawer');
        const closeMobileFilterBtn = document.getElementById('closeMobileFilterBtn');
        const applyMobileFiltersBtn = document.getElementById('applyMobileFiltersBtn');

        if (mobileFilterToggleBtn && mobileFilterDrawer) {
            mobileFilterToggleBtn.addEventListener('click', () => {
                mobileFilterDrawer.classList.remove('hidden');
            });
        }

        if (closeMobileFilterBtn && mobileFilterDrawer) {
            closeMobileFilterBtn.addEventListener('click', () => {
                mobileFilterDrawer.classList.add('hidden');
            });
        }

        if (applyMobileFiltersBtn && mobileFilterDrawer) {
            applyMobileFiltersBtn.addEventListener('click', () => {
                mobileFilterDrawer.classList.add('hidden');
                runFilters();
            });
        }

        // Initial Filter Execution on Page Load
        runFilters();
    });

    function submitInquiryForm() {
        const name = document.getElementById('inquireName')?.value || 'Guest';
        const phone = document.getElementById('inquirePhone')?.value || '';
        const date = document.getElementById('inquireDate')?.value || 'Flexible';
        const title = document.getElementById('modalTitle')?.textContent || 'Travel Service';

        alert(`Thank you ${name}! Your booking inquiry for "${title}" has been received. Our senior travel concierge will contact you at ${phone} within 15 minutes.`);

        const modal = document.getElementById('quickInquireModal');
        if (modal) modal.classList.add('hidden');
    }
</script>

<?php
require_once 'components/footer.php';
?>