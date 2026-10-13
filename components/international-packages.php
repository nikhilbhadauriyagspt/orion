<?php
/**
 * International Tour Packages Component - Orion Advent
 * - Ultra-Modern Full-Bleed Poster Lookbook Card UI (Lightweight, Exciting & Sleek)
 * - Heroic Destination Photography with Smooth Hover Zoom
 * - Floating Top Country & Visa Assurance Badges
 * - Sleek Bottom Floating Capsule with Duration, Highlights & Quick Action
 * - 100% Flat, Border-First, Zero Shadows
 * - Font Awesome 6 Icons Only (Zero Emojis)
 */

$internationalPackages = [
    [
        'id' => 'INTL-DXB-01',
        'slug' => 'dubai-burj-khalifa-desert-safari',
        'title' => 'Dubai Future City & Desert Dunes',
        'country' => 'Dubai, UAE',
        'duration' => '5N / 6D',
        'category' => 'all dubai',
        'visa_badge' => 'Visa on Arrival',
        'visa_class' => 'bg-emerald-600 text-white',
        'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Dubai Burj Khalifa Skyline',
        'rating' => '4.9',
        'reviews' => '284',
        'highlights' => ['Burj Khalifa', 'Desert Safari', 'Marina Yacht'],
    ],
    [
        'id' => 'INTL-BALI-02',
        'slug' => 'bali-tropical-jungle-villa-nusa-penida',
        'title' => 'Bali Jungle Swings & Nusa Penida',
        'country' => 'Bali, Indonesia',
        'duration' => '6N / 7D',
        'category' => 'all bali',
        'visa_badge' => 'Visa Free Entry',
        'visa_class' => 'bg-emerald-600 text-white',
        'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Bali Jungle & Temple',
        'rating' => '4.9',
        'reviews' => '312',
        'highlights' => ['Ubud Swings', 'Nusa Penida', 'Pool Villa'],
    ],
    [
        'id' => 'INTL-THAI-03',
        'slug' => 'thailand-phuket-krabi-island-getaway',
        'title' => 'Thailand Phi Phi & Krabi Sunset',
        'country' => 'Phuket, Thailand',
        'duration' => '5N / 6D',
        'category' => 'all thailand',
        'visa_badge' => 'Visa Free For Indians',
        'visa_class' => 'bg-emerald-600 text-white',
        'image' => 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Thailand Phi Phi Islands',
        'rating' => '4.8',
        'reviews' => '245',
        'highlights' => ['Phi Phi Speedboat', 'Maya Bay', 'Coral Reefs'],
    ],
    [
        'id' => 'INTL-SIN-04',
        'slug' => 'singapore-sentosa-universal-studios',
        'title' => 'Singapore & Universal Studios',
        'country' => 'Singapore City',
        'duration' => '4N / 5D',
        'category' => 'all singapore',
        'visa_badge' => 'Fast 48h E-Visa',
        'visa_class' => 'bg-sky-600 text-white',
        'image' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Singapore Marina Bay Sands',
        'rating' => '4.9',
        'reviews' => '198',
        'highlights' => ['Universal Studios', 'Marina Bay', 'Gardens Bay'],
    ],
    [
        'id' => 'INTL-MLD-05',
        'slug' => 'maldives-luxury-overwater-villa',
        'title' => 'Maldives Luxury Overwater Villa',
        'country' => 'Maldives Atolls',
        'duration' => '4N / 5D',
        'category' => 'all maldives',
        'visa_badge' => 'Free 30-Day Visa',
        'visa_class' => 'bg-emerald-600 text-white',
        'image' => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Maldives Overwater Bungalow',
        'rating' => '4.9',
        'reviews' => '220',
        'highlights' => ['Overwater Villa', 'Speedboat Transfer', 'All Meals'],
    ],
    [
        'id' => 'INTL-VTN-06',
        'slug' => 'vietnam-halong-bay-golden-bridge',
        'title' => 'Vietnam: Halong Bay & Golden Hands',
        'country' => 'Hanoi, Vietnam',
        'duration' => '6N / 7D',
        'category' => 'all vietnam',
        'visa_badge' => 'Instant E-Visa (24h)',
        'visa_class' => 'bg-emerald-600 text-white',
        'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=80',
        'alt' => 'Vietnam Halong Bay & Golden Bridge',
        'rating' => '4.8',
        'reviews' => '174',
        'highlights' => ['Halong Cruise', 'Golden Bridge', 'Ba Na Hills'],
    ]
];

// Dynamically fetch active International Packages added via Admin Panel
if (function_exists('getDBConnection')) {
    $pdoIntl = getDBConnection();
} else {
    require_once __DIR__ . '/../config/db.php';
    $pdoIntl = getDBConnection();
}

$dynamicIntlTabs = [];

if ($pdoIntl) {
    try {
        $dbIntlPackages = $pdoIntl->query("SELECT * FROM `packages` WHERE `category` = 'international' AND `status` = 'active' ORDER BY `id` DESC")->fetchAll();
        if (!empty($dbIntlPackages)) {
            $dynamicIntlPackages = [];
            foreach ($dbIntlPackages as $dbh) {
                // Highlights resolution
                $highlights = [];
                if (!empty($dbh['highlights'])) {
                    $decHl = json_decode($dbh['highlights'], true);
                    if (is_array($decHl)) {
                        foreach ($decHl as $hl) {
                            if (is_array($hl) && !empty($hl['title'])) {
                                $highlights[] = $hl['title'];
                            } elseif (is_string($hl)) {
                                $highlights[] = $hl;
                            }
                            if (count($highlights) >= 3) break;
                        }
                    }
                }
                if (empty($highlights)) {
                    $highlights = ['4★/5★ Luxury Stays', 'Private Transfers', 'Sightseeing Pass'];
                }

                // Filter tab category matching
                $locSearch = strtolower($dbh['location'] . ' ' . $dbh['state_country'] . ' ' . $dbh['title']);
                $categories = ['all'];
                if (str_contains($locSearch, 'dubai') || str_contains($locSearch, 'uae') || str_contains($locSearch, 'abu dhabi')) $categories[] = 'dubai';
                if (str_contains($locSearch, 'bali') || str_contains($locSearch, 'indonesia')) $categories[] = 'bali';
                if (str_contains($locSearch, 'thai') || str_contains($locSearch, 'phuket') || str_contains($locSearch, 'bangkok') || str_contains($locSearch, 'krabi')) $categories[] = 'thailand';
                if (str_contains($locSearch, 'singapore')) $categories[] = 'singapore';
                if (str_contains($locSearch, 'maldives')) $categories[] = 'maldives';
                if (str_contains($locSearch, 'vietnam') || str_contains($locSearch, 'hanoi') || str_contains($locSearch, 'halong')) $categories[] = 'vietnam';
                if (str_contains($locSearch, 'europe') || str_contains($locSearch, 'paris') || str_contains($locSearch, 'swiss')) $categories[] = 'europe';
                
                // Also add sanitized location as a filter tag
                $cleanLoc = preg_replace('/[^a-z0-9]/', '', strtolower($dbh['location'] ?? ''));
                if (!empty($cleanLoc)) $categories[] = $cleanLoc;

                $catString = implode(' ', array_unique($categories));

                $img = !empty($dbh['featured_image']) ? $dbh['featured_image'] : 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80';

                $dynamicIntlPackages[] = [
                    'id' => $dbh['id'],
                    'slug' => $dbh['slug'] ?? '',
                    'title' => $dbh['title'],
                    'country' => $dbh['state_country'] ?: $dbh['location'],
                    'duration' => $dbh['duration_text'] ?: ($dbh['duration_nights'] . 'N / ' . $dbh['duration_days'] . 'D'),
                    'category' => $catString,
                    'visa_badge' => $dbh['badge'] ?: 'Visa Assistance',
                    'visa_class' => 'bg-emerald-600 text-white',
                    'image' => $img,
                    'alt' => $dbh['title'],
                    'rating' => number_format((float)($dbh['rating'] ?: 4.9), 1),
                    'reviews' => (string)($dbh['reviews_count'] ?: '250'),
                    'highlights' => $highlights
                ];
            }
            // Use 100% dynamic international packages from database
            $internationalPackages = $dynamicIntlPackages;
        }
    } catch (Exception $e) {
        // Fallback silently
    }
}
?>
<!-- ==========================================
     INTERNATIONAL TOUR PACKAGES (POSTER LOOKBOOK CARDS)
     - Lightweight, Exciting & Visual-Forward
     - Full-bleed Destination Visuals with Smooth Zoom
     - Floating Country & Visa Assurance Badges
     - Minimalist Bottom Glass Capsule (No Heavy Text Clutter)
     - 100% Flat, Border-First (Zero Shadows)
     - Font Awesome 6 Icons Only (Zero Emojis)
=========================================== -->
<section id="international-packages" class="py-16 md:py-20 px-4 sm:px-8 xl:px-12 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto">

        <!-- 1. Section Header (Matching Domestic Style with Inline Capsule Image) -->
        <div class="max-w-3xl mx-auto text-center space-y-3 mb-10">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-globe text-brand-600"></i>
                <span>World Class Destinations & Hassle-Free Visas</span>
            </div>

            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>Explore Holidays </span>
                <span class="inline-block align-middle mx-1.5 sm:mx-2.5">
                    <img src="assets/images/banner/heading-intl-01.jpg" 
                         alt="World Wonders Montage" 
                         class="h-8 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">Around the World</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto">
            From tropical beaches to vibrant cities, discover international trips perfect for your next getaway.
            </p>

            <!-- Sleek Segmented Capsule Tabs (Clean & Unified) -->
            <div class="flex justify-start sm:justify-center overflow-x-auto scrollbar-none px-1 sm:px-0 pt-2 pb-1">
                <div class="inline-flex items-center p-1.5 rounded-full bg-slate-100 border border-slate-200 overflow-x-auto max-w-full scrollbar-none space-x-1 shrink-0">
                    <button type="button" data-intl-filter="all" class="intl-filter-btn px-4 sm:px-5 py-2 rounded-full text-xs font-bold bg-brand-600 text-white transition-all whitespace-nowrap">
                        All Destinations
                    </button>
                    <button type="button" data-intl-filter="dubai" class="intl-filter-btn px-3.5 sm:px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:text-brand-600 hover:bg-white transition-all whitespace-nowrap">
                        <i class="fa-solid fa-building mr-1.5 text-xs text-brand-600"></i>Dubai
                    </button>
                    <button type="button" data-intl-filter="bali" class="intl-filter-btn px-3.5 sm:px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:text-brand-600 hover:bg-white transition-all whitespace-nowrap">
                        <i class="fa-solid fa-umbrella-beach mr-1.5 text-xs text-brand-600"></i>Bali
                    </button>
                    <button type="button" data-intl-filter="thailand" class="intl-filter-btn px-3.5 sm:px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:text-brand-600 hover:bg-white transition-all whitespace-nowrap">
                        <i class="fa-solid fa-ship mr-1.5 text-xs text-brand-600"></i>Thailand
                    </button>
                    <button type="button" data-intl-filter="singapore" class="intl-filter-btn px-3.5 sm:px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:text-brand-600 hover:bg-white transition-all whitespace-nowrap">
                        <i class="fa-solid fa-city mr-1.5 text-xs text-brand-600"></i>Singapore
                    </button>
                    <button type="button" data-intl-filter="maldives" class="intl-filter-btn px-3.5 sm:px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:text-brand-600 hover:bg-white transition-all whitespace-nowrap">
                        <i class="fa-solid fa-water mr-1.5 text-xs text-brand-600"></i>Maldives
                    </button>
                    <button type="button" data-intl-filter="vietnam" class="intl-filter-btn px-3.5 sm:px-4 py-2 rounded-full text-xs font-semibold text-slate-600 hover:text-brand-600 hover:bg-white transition-all whitespace-nowrap">
                        <i class="fa-solid fa-mountain mr-1.5 text-xs text-brand-600"></i>Vietnam
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. Lightweight Poster Lookbook Grid (3 Columns on Desktop) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7" id="intlGrid">
            <?php foreach ($internationalPackages as $pkg): ?>
                <div class="intl-card h-[450px] sm:h-[475px] rounded-3xl border border-slate-200 hover:border-brand-500 transition-all duration-500 flex flex-col justify-between p-4 sm:p-5 relative overflow-hidden group select-none hover:-translate-y-1" data-category="<?= htmlspecialchars($pkg['category']) ?>">
                    
                    <!-- Background Visual with Zoom on Hover -->
                    <img src="<?= htmlspecialchars($pkg['image']) ?>" 
                         alt="<?= htmlspecialchars($pkg['alt']) ?>" 
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out z-0">

                    <!-- Dark Vignette Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-slate-950/45 z-10"></div>

                    <!-- TOP FLOATING BAR (Z-20) -->
                    <div class="relative z-20 flex items-center justify-between gap-2">
                        <!-- Country Pill -->
                        <span class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full text-[11px] sm:text-xs font-bold bg-white/95 backdrop-blur-md text-slate-900 border border-white/80 flex items-center space-x-1.5">
                            <i class="fa-solid fa-location-dot text-brand-600 text-xs"></i>
                            <span><?= htmlspecialchars($pkg['country']) ?></span>
                        </span>

                        <!-- Visa Badge -->
                        <span class="px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full text-[11px] sm:text-xs font-bold <?= $pkg['visa_class'] ?> backdrop-blur-md border border-white/20 flex items-center space-x-1">
                            <i class="fa-solid fa-passport text-[10px]"></i>
                            <span><?= htmlspecialchars($pkg['visa_badge']) ?></span>
                        </span>
                    </div>

                    <!-- BOTTOM FLOATING CAPSULE (Z-20) -->
                    <div class="relative z-20 bg-white/95 backdrop-blur-md rounded-2xl p-4 sm:p-4.5 border border-white/80 transition-all duration-300 group-hover:bg-white space-y-2.5">
                        
                        <!-- Top Meta: Duration & Rating -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-brand-600 flex items-center">
                                <i class="fa-regular fa-clock mr-1.5 text-brand-600"></i>
                                <?= htmlspecialchars($pkg['duration']) ?>
                            </span>
                            <span class="font-bold text-slate-800 flex items-center space-x-1">
                                <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                <span><?= htmlspecialchars($pkg['rating']) ?></span>
                                <span class="text-slate-400 font-normal text-[11px]">(<?= htmlspecialchars($pkg['reviews']) ?>)</span>
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="text-base sm:text-lg font-black text-slate-900 group-hover:text-brand-600 transition leading-snug line-clamp-1">
                            <a href="package-details.php?<?= !empty($pkg['slug']) ? 'slug=' . urlencode($pkg['slug']) : 'id=' . urlencode($pkg['id'] ?? '') ?>">
                                <?= htmlspecialchars($pkg['title']) ?>
                            </a>
                        </h3>

                        <!-- 3 Sleek Highlight Chips -->
                        <div class="flex items-center space-x-1.5 overflow-hidden text-[11px] font-semibold text-slate-600">
                            <?php foreach ($pkg['highlights'] as $tag): ?>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200/80 truncate shrink-0 max-w-[110px]">
                                    <?= htmlspecialchars($tag) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Bottom Action Row -->
                        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-emerald-600 flex items-center">
                                <i class="fa-solid fa-shield-halved text-[10px] mr-1"></i>100% Customizable
                            </span>
                            <a href="package-details.php?<?= !empty($pkg['slug']) ? 'slug=' . urlencode($pkg['slug']) : 'id=' . urlencode($pkg['id'] ?? '') ?>" 
                               class="inline-flex items-center space-x-1.5 text-xs font-bold text-brand-600 group-hover:text-brand-700 uppercase tracking-wider group-hover:translate-x-1 transition-all">
                                <span>Explore Plan</span>
                                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                            </a>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
