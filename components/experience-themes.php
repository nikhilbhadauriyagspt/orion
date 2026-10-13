<?php
/**
 * Curated Holiday Themes / Travel By Experience Component - Orion Advent
 * - Distinct Experiential Cards (Completely different from previous sections)
 * - Multi-Layered Depth: Decorative Offset Backdrop Elements Behind Images
 * - Subtle Watermark Accent Icons & Themed Ambient Colors
 * - Interactive Hotspot Destination Pills (Maldives, Bali, Ladakh, Rishikesh, etc.)
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */

$holidayThemes = [
    // 1. HONEYMOON & ROMANCE
    [
        'id' => 'theme-honeymoon',
        'title' => 'Romantic Honeymoon Escapes',
        'subtitle' => 'Love & Celebrations',
        'tagline' => 'Secluded private pool sanctuaries, candlelight beach dinners, and bespoke anniversary celebrations.',
        'theme_color' => 'rose',
        'badge_icon' => 'fa-solid fa-champagne-glasses',
        'badge_label' => 'For Couples',
        'watermark_icon' => 'fa-solid fa-heart',
        'count' => '48+ Packages',
        'rating' => '4.95',
        'reviews' => '1.2k happy couples',
        'image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Luxury Romantic Pool Villa Overwater',
        'highlights' => [
            ['name' => 'Private Plunge Pool', 'icon' => 'fa-solid fa-water-ladder'],
            ['name' => 'Candlelight Dinner', 'icon' => 'fa-solid fa-utensils'],
            ['name' => 'Sunset Yacht Cruise', 'icon' => 'fa-solid fa-sailboat']
        ],
        'spots' => [
            ['name' => 'Maldives', 'type' => 'intl'],
            ['name' => 'Bali', 'type' => 'intl'],
            ['name' => 'Goa', 'type' => 'dom'],
            ['name' => 'Kashmir', 'type' => 'dom']
        ],
        'price' => 18999,
        'hero_theme' => 'Honeymoon Special',
        'card_bg' => 'bg-gradient-to-br from-rose-50/70 via-white to-pink-50/40 border-rose-200/80 hover:border-rose-400',
        'accent_badge' => 'bg-rose-50 text-rose-700 border-rose-200',
        'tag_color' => 'text-rose-600',
        'btn_class' => 'bg-rose-600 hover:bg-rose-700 text-white border-rose-600'
    ],

    // 2. SNOW TREKS & MOUNTAINS
    [
        'id' => 'theme-mountains',
        'title' => 'Snow Peaks & Alpine Treks',
        'subtitle' => 'High Altitude Thrill',
        'tagline' => 'Conquer Himalayan passes, sleep under starry milky way skies, and wake up to frost-covered chalets.',
        'theme_color' => 'sky',
        'badge_icon' => 'fa-solid fa-person-hiking',
        'badge_label' => 'Thrill & Peaks',
        'watermark_icon' => 'fa-solid fa-mountain-sun',
        'count' => '36+ Alpine Tours',
        'rating' => '4.92',
        'reviews' => '890 trekkers',
        'image' => 'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Himalayan Snow Peaks Trekking',
        'highlights' => [
            ['name' => 'Snow Pass Trekking', 'icon' => 'fa-solid fa-mountain'],
            ['name' => 'Bonfire Camping', 'icon' => 'fa-solid fa-fire'],
            ['name' => 'Heated Alpine Chalets', 'icon' => 'fa-solid fa-house-chimney']
        ],
        'spots' => [
            ['name' => 'Manali', 'type' => 'dom'],
            ['name' => 'Leh Ladakh', 'type' => 'dom'],
            ['name' => 'Spiti Valley', 'type' => 'dom'],
            ['name' => 'Kedarkantha', 'type' => 'dom']
        ],
        'price' => 12499,
        'hero_theme' => 'Snow Hills',
        'card_bg' => 'bg-gradient-to-br from-sky-50/70 via-white to-indigo-50/40 border-sky-200/80 hover:border-sky-400',
        'accent_badge' => 'bg-sky-50 text-sky-700 border-sky-200',
        'tag_color' => 'text-sky-600',
        'btn_class' => 'bg-sky-600 hover:bg-sky-700 text-white border-sky-600'
    ],

    // 3. TROPICAL BEACHES & ISLANDS
    [
        'id' => 'theme-beach',
        'title' => 'Island & Ocean Adventures',
        'subtitle' => 'Sun, Sand & Scuba',
        'tagline' => 'Crystal turquoise waters, colorful coral reefs, speedboats, and beach clubs with world-class sunsets.',
        'theme_color' => 'teal',
        'badge_icon' => 'fa-solid fa-umbrella-beach',
        'badge_label' => 'Coast & Islands',
        'watermark_icon' => 'fa-solid fa-water',
        'count' => '52+ Beach Stays',
        'rating' => '4.88',
        'reviews' => '1.5k travelers',
        'image' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Tropical White Sand Island Beach',
        'highlights' => [
            ['name' => 'Coral Scuba Diving', 'icon' => 'fa-solid fa-water'],
            ['name' => 'Overwater Cabanas', 'icon' => 'fa-solid fa-hotel'],
            ['name' => 'Catamaran Cruises', 'icon' => 'fa-solid fa-sailboat']
        ],
        'spots' => [
            ['name' => 'Andaman', 'type' => 'dom'],
            ['name' => 'Goa', 'type' => 'dom'],
            ['name' => 'Phuket', 'type' => 'intl'],
            ['name' => 'Mauritius', 'type' => 'intl']
        ],
        'price' => 16999,
        'hero_theme' => 'Beach Vacation',
        'card_bg' => 'bg-gradient-to-br from-teal-50/70 via-white to-cyan-50/40 border-teal-200/80 hover:border-teal-400',
        'accent_badge' => 'bg-teal-50 text-brand-700 border-teal-200',
        'tag_color' => 'text-brand-600',
        'btn_class' => 'bg-brand-600 hover:bg-brand-700 text-white border-brand-600'
    ],

    // 4. ROYAL HERITAGE & PALACES
    [
        'id' => 'theme-heritage',
        'title' => 'Royal Forts & Palace Havelis',
        'subtitle' => 'Regal Heritage & Culture',
        'tagline' => 'Live like royalty in restored 17th-century havelis, desert glamping under stars, and private puppet courtyards.',
        'theme_color' => 'amber',
        'badge_icon' => 'fa-solid fa-crown',
        'badge_label' => 'Regal Grandeur',
        'watermark_icon' => 'fa-solid fa-chess-rook',
        'count' => '32+ Royal Trails',
        'rating' => '4.94',
        'reviews' => '760 history lovers',
        'image' => 'https://images.unsplash.com/photo-1599661046289-e31897846e41?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Grand Illuminated Palace Courtyard',
        'highlights' => [
            ['name' => 'Maharaja Palace Stays', 'icon' => 'fa-solid fa-crown'],
            ['name' => 'Desert Camp & Safari', 'icon' => 'fa-solid fa-car-side'],
            ['name' => 'Folk Music Dinners', 'icon' => 'fa-solid fa-music']
        ],
        'spots' => [
            ['name' => 'Jaipur', 'type' => 'dom'],
            ['name' => 'Udaipur', 'type' => 'dom'],
            ['name' => 'Jodhpur', 'type' => 'dom'],
            ['name' => 'Jaisalmer', 'type' => 'dom']
        ],
        'price' => 14499,
        'hero_theme' => 'Royal Heritage',
        'card_bg' => 'bg-gradient-to-br from-amber-50/70 via-white to-orange-50/40 border-amber-200/80 hover:border-amber-400',
        'accent_badge' => 'bg-amber-50 text-amber-800 border-amber-200',
        'tag_color' => 'text-amber-700',
        'btn_class' => 'bg-amber-600 hover:bg-amber-700 text-white border-amber-600'
    ],

    // 5. WILDLIFE & JUNGLE LIVING
    [
        'id' => 'theme-wildlife',
        'title' => 'Wildlife Safari & Jungle Lodges',
        'subtitle' => 'Untamed Wilderness',
        'tagline' => 'Track Royal Bengal tigers in open-top 4x4 gypsies, stay in luxury treehouse suites, and wake to jungle birds.',
        'theme_color' => 'emerald',
        'badge_icon' => 'fa-solid fa-binoculars',
        'badge_label' => 'Big Cat Trails',
        'watermark_icon' => 'fa-solid fa-paw',
        'count' => '26+ Safari Lodges',
        'rating' => '4.91',
        'reviews' => '620 nature lovers',
        'image' => 'https://images.unsplash.com/photo-1549366021-9f761d450615?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Majestic Tiger in Forest Jungle',
        'highlights' => [
            ['name' => 'Open-Jeep Tiger Safaris', 'icon' => 'fa-solid fa-van-shuttle'],
            ['name' => 'Luxury Treehouse Stays', 'icon' => 'fa-solid fa-tree'],
            ['name' => 'Certified Naturalists', 'icon' => 'fa-solid fa-compass']
        ],
        'spots' => [
            ['name' => 'Jim Corbett', 'type' => 'dom'],
            ['name' => 'Ranthambore', 'type' => 'dom'],
            ['name' => 'Kabini Forest', 'type' => 'dom'],
            ['name' => 'Kaziranga', 'type' => 'dom']
        ],
        'price' => 15800,
        'hero_theme' => 'Wildlife & Safari',
        'card_bg' => 'bg-gradient-to-br from-emerald-50/70 via-white to-lime-50/40 border-emerald-200/80 hover:border-emerald-400',
        'accent_badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'tag_color' => 'text-emerald-700',
        'btn_class' => 'bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600'
    ],

    // 6. SPIRITUAL & WELLNESS
    [
        'id' => 'theme-spiritual',
        'title' => 'Spiritual Solitude & Wellness',
        'subtitle' => 'Mind, Body & Soul',
        'tagline' => 'Witness holy river evening aartis, experience authentic Himalayan yoga ashrams, and rejuvenating Ayurvedic cures.',
        'theme_color' => 'orange',
        'badge_icon' => 'fa-solid fa-om',
        'badge_label' => 'Peace & Healing',
        'watermark_icon' => 'fa-solid fa-spa',
        'count' => '30+ Soul Retreats',
        'rating' => '4.89',
        'reviews' => '940 pilgrims',
        'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=800&q=80',
        'alt' => 'Serene Holy River Ghat at Sunset',
        'highlights' => [
            ['name' => 'Ganga Evening Aarti', 'icon' => 'fa-solid fa-sun'],
            ['name' => 'Ayurvedic Detox', 'icon' => 'fa-solid fa-spa'],
            ['name' => 'Riverside Ashrams', 'icon' => 'fa-solid fa-water']
        ],
        'spots' => [
            ['name' => 'Rishikesh', 'type' => 'dom'],
            ['name' => 'Varanasi', 'type' => 'dom'],
            ['name' => 'Vrindavan', 'type' => 'dom'],
            ['name' => 'Haridwar', 'type' => 'dom']
        ],
        'price' => 9999,
        'hero_theme' => 'Spiritual Retreat',
        'card_bg' => 'bg-gradient-to-br from-orange-50/70 via-white to-amber-50/40 border-orange-200/80 hover:border-orange-400',
        'accent_badge' => 'bg-orange-50 text-orange-800 border-orange-200',
        'tag_color' => 'text-orange-700',
        'btn_class' => 'bg-orange-600 hover:bg-orange-700 text-white border-orange-600',
        'search_url' => 'search.php?theme=spiritual'
    ]
];

// Dynamically enhance experience themes from live database packages
if (function_exists('getDBConnection')) {
    $pdoThemes = getDBConnection();
} else {
    require_once __DIR__ . '/../config/db.php';
    $pdoThemes = getDBConnection();
}

if ($pdoThemes) {
    try {
        $allActivePkgs = $pdoThemes->query("SELECT * FROM `packages` WHERE `status` = 'active' ORDER BY `id` DESC")->fetchAll();
        if (!empty($allActivePkgs)) {
            $themeRules = [
                'theme-honeymoon' => [
                    'keywords' => ['honeymoon', 'romantic', 'couple', 'pool villa', 'shikara', 'houseboat', 'sunset cruise', 'candle'],
                    'search_filter' => 'honeymoon'
                ],
                'theme-mountains' => [
                    'keywords' => ['mountain', 'snow', 'hill', 'trek', 'peak', 'kashmir', 'manali', 'shimla', 'solang', 'ladakh', 'alpine', 'spiti'],
                    'search_filter' => 'adventure'
                ],
                'theme-beach' => [
                    'keywords' => ['beach', 'island', 'coastal', 'goa', 'andaman', 'bali', 'phuket', 'maldives', 'ocean', 'scuba', 'water sports'],
                    'search_filter' => 'beach'
                ],
                'theme-heritage' => [
                    'keywords' => ['heritage', 'royal', 'palace', 'fort', 'rajasthan', 'jaipur', 'udaipur', 'jodhpur', 'jaisalmer', 'haveli', 'culture', 'monument'],
                    'search_filter' => 'luxury'
                ],
                'theme-wildlife' => [
                    'keywords' => ['wildlife', 'jungle', 'safari', 'forest', 'national park', 'tiger', 'corbett', 'ranthambore', 'thekkady', 'munnar', 'nature'],
                    'search_filter' => 'adventure'
                ],
                'theme-spiritual' => [
                    'keywords' => ['spiritual', 'temple', 'wellness', 'yoga', 'rishikesh', 'varanasi', 'haridwar', 'ashram', 'pilgrim', 'ghat', 'aarti'],
                    'search_filter' => 'spiritual'
                ],
            ];

            foreach ($holidayThemes as &$theme) {
                $tId = $theme['id'];
                if (isset($themeRules[$tId])) {
                    $rule = $themeRules[$tId];
                    $matchingPkgs = [];
                    foreach ($allActivePkgs as $p) {
                        $pSearchText = strtolower($p['title'] . ' ' . $p['subtitle'] . ' ' . $p['circuit_type'] . ' ' . $p['location'] . ' ' . $p['badge'] . ' ' . $p['state_country']);
                        foreach ($rule['keywords'] as $kw) {
                            if (str_contains($pSearchText, $kw)) {
                                $matchingPkgs[] = $p;
                                break;
                            }
                        }
                    }

                    if (!empty($matchingPkgs)) {
                        // Dynamic price
                        $prices = array_column($matchingPkgs, 'price');
                        $validPrices = array_filter($prices, function($pr) { return (float)$pr > 0; });
                        if (!empty($validPrices)) {
                            $theme['price'] = (float)min($validPrices);
                        }

                        // Dynamic Count
                        $theme['count'] = count($matchingPkgs) . '+ Active Packages';

                        // Dynamic Spots extracted from matching DB packages
                        $dynamicSpots = [];
                        foreach ($matchingPkgs as $mp) {
                            $locParts = explode(',', $mp['location']);
                            $spotName = trim($locParts[0] ?? $mp['location']);
                            if (!empty($spotName) && !in_array($spotName, array_column($dynamicSpots, 'name'))) {
                                $dynamicSpots[] = [
                                    'name' => $spotName,
                                    'type' => ($mp['category'] === 'international') ? 'intl' : 'dom',
                                    'slug' => $mp['slug'] ?? ''
                                ];
                            }
                            if (count($dynamicSpots) >= 4) break;
                        }
                        if (!empty($dynamicSpots)) {
                            // Merge dynamic spots with defaults
                            $existingNames = array_column($dynamicSpots, 'name');
                            foreach ($theme['spots'] as $oldSpot) {
                                if (!in_array($oldSpot['name'], $existingNames) && count($dynamicSpots) < 4) {
                                    $dynamicSpots[] = $oldSpot;
                                }
                            }
                            $theme['spots'] = $dynamicSpots;
                        }

                        // Dynamic Image
                        if (!empty($matchingPkgs[0]['featured_image'])) {
                            $theme['image'] = $matchingPkgs[0]['featured_image'];
                            $theme['alt'] = $matchingPkgs[0]['title'];
                        }

                        $theme['search_url'] = 'search.php?theme=' . urlencode($rule['search_filter']);
                    }
                }
            }
            unset($theme);
        }
    } catch (Exception $e) {
        // Fallback silently
    }
}
?>
<!-- ==========================================
     CURATED HOLIDAY THEMES / TRAVEL BY EXPERIENCE
     - Distinct Visual Aesthetic: Multi-Layered Depth with Background Elements
     - Offset Geometric Frames Behind Each Photo
     - Subtle Watermark Accent Icons & Vibe Colors
     - Interactive Hotspot Destination Pills (Maldives, Bali, Goa, etc.)
     - 100% Flat, Border-First (Zero Shadows)
     - Font Awesome 6 Icons Only (Zero Emojis)
=========================================== -->
<section id="experience-themes" class="py-10 sm:py-16 md:py-24 px-3 sm:px-8 xl:px-12 bg-white border-b border-slate-200 relative overflow-hidden">
    
    <!-- Ambient Background Accents (Subtle Atmospheric Depth) -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-teal-100/35 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-rose-100/35 blur-3xl pointer-events-none"></div>
    <div class="absolute inset-0 pointer-events-none opacity-[0.035] bg-[radial-gradient(#068285_1px,transparent_1px)] [background-size:28px_28px]"></div>

    <div class="max-w-7xl mx-auto relative z-10">

        <!-- 1. Section Header with Generated Inline Capsule Image -->
        <div class="max-w-3xl mx-auto text-center space-y-2.5 sm:space-y-3 mb-8 sm:mb-16">
            <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-compass text-brand-600 text-[11px]"></i>
                <span>Find Your Kind of Holiday</span>
            </div>

            <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>Travel Experiences </span>
                <span class="inline-block align-middle mx-1 sm:mx-2.5">
                    <img src="assets/images/banner/heading-experience-01.jpg" 
                         alt="Cappadocia Hot Air Balloons Sunrise" 
                         class="h-7 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">Made for You</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto px-2">
              Whether you're looking for adventure, relaxation, romance, or a fun family trip, discover holidays that match your mood and travel style
            </p>
        </div>

        <!-- 2. The Experiential Theme Cards Grid (3 Columns x 2 Rows) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-7 lg:gap-8">
            <?php foreach ($holidayThemes as $theme): ?>
                <div class="experience-card relative rounded-2xl sm:rounded-3xl p-4 sm:p-6 border transition-all duration-300 overflow-hidden group flex flex-col justify-between select-none <?= $theme['card_bg'] ?>">
                    
                    <!-- Atmospheric Background Watermark Icon -->
                    <i class="<?= htmlspecialchars($theme['watermark_icon']) ?> absolute -right-6 -bottom-6 text-9xl select-none opacity-[0.035] pointer-events-none group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 text-slate-900"></i>

                    <div>
                        <!-- Card Top Bar: Vibe Tag & Rating -->
                        <div class="flex items-center justify-between mb-3.5 sm:mb-4">
                            <span class="inline-flex items-center space-x-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[11px] sm:text-xs font-bold border <?= $theme['accent_badge'] ?>">
                                <i class="<?= htmlspecialchars($theme['badge_icon']) ?> text-[10px] sm:text-xs"></i>
                                <span><?= htmlspecialchars($theme['badge_label']) ?></span>
                            </span>

                            <div class="flex items-center space-x-1 text-xs font-bold text-slate-700 bg-white/90 px-2 sm:px-2.5 py-0.5 rounded-full border border-slate-200/80">
                                <i class="fa-solid fa-star text-amber-500 text-[11px]"></i>
                                <span><?= htmlspecialchars($theme['rating']) ?></span>
                                <span class="text-[10px] text-slate-400 font-normal hidden sm:inline">(<?= htmlspecialchars($theme['reviews']) ?>)</span>
                            </div>
                        </div>

                        <!-- Center Stage: Multi-Layered Photo Frame with Offset Decorative Geometry Behind Image -->
                        <div class="relative mb-4 sm:mb-5 pt-1">
                            <!-- Background Offset Element 1: Angled Colored Backdrop Layer -->
                            <div class="absolute inset-0 rounded-xl sm:rounded-2xl bg-slate-900/5 translate-x-1 translate-y-1 sm:translate-x-2 sm:translate-y-2 group-hover:translate-x-2 sm:group-hover:translate-x-3 group-hover:translate-y-2 sm:group-hover:translate-y-3 transition-transform duration-300 -z-10"></div>
                            
                            <!-- Background Offset Element 2: Decorative Offset Border Frame -->
                            <div class="hidden sm:block absolute -inset-1 rounded-2xl border-2 border-dashed border-slate-300/60 -z-10 group-hover:border-brand-500/50 transition-colors duration-300"></div>

                            <!-- Main Photo Frame -->
                            <div class="h-44 sm:h-52 rounded-xl sm:rounded-2xl overflow-hidden relative border border-slate-200/90 bg-slate-100">
                                <img src="<?= htmlspecialchars($theme['image']) ?>" 
                                     alt="<?= htmlspecialchars($theme['alt']) ?>" 
                                     loading="lazy" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                <!-- Floating Count Badge Top-Right -->
                                <span class="absolute top-2.5 right-2.5 px-2 sm:px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-900/80 text-white backdrop-blur-xs border border-white/20">
                                    <?= htmlspecialchars($theme['count']) ?>
                                </span>

                                <!-- Floating Experience Highlight Pills Over Bottom of Image -->
                                <div class="absolute bottom-2 left-2 right-2 sm:bottom-2.5 sm:left-2.5 sm:right-2.5 flex items-center space-x-1.5 overflow-x-auto no-scrollbar py-0.5">
                                    <?php foreach ($theme['highlights'] as $highlight): ?>
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-white/95 text-slate-800 backdrop-blur-sm border border-slate-200/90 shrink-0 max-w-[125px]">
                                            <i class="<?= htmlspecialchars($highlight['icon']) ?> text-[10px] text-brand-600 shrink-0"></i>
                                            <span class="truncate"><?= htmlspecialchars($highlight['name']) ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Titles & Description -->
                        <div class="space-y-1">
                            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider block <?= $theme['tag_color'] ?>">
                                <?= htmlspecialchars($theme['subtitle']) ?>
                            </span>

                            <h3 class="text-base sm:text-xl font-extrabold text-slate-900 group-hover:text-brand-600 transition leading-snug">
                                <?= htmlspecialchars($theme['title']) ?>
                            </h3>

                            <p class="text-xs text-slate-500 font-normal leading-relaxed line-clamp-2">
                                <?= htmlspecialchars($theme['tagline']) ?>
                            </p>
                        </div>

                        <!-- Interactive Hotspot Destination Pills -->
                        <div class="mt-3.5 sm:mt-4 pt-3 border-t border-slate-200/60">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider shrink-0">Top Spots:</span>
                                <?php foreach ($theme['spots'] as $spot): ?>
                                    <a href="search.php?query=<?= urlencode($spot['name']) ?>&type=<?= urlencode($spot['type'] === 'intl' ? 'international' : 'domestic') ?>" 
                                       class="theme-spot-btn px-2 sm:px-2.5 py-0.5 rounded-md sm:rounded-lg text-[10px] sm:text-[11px] font-bold bg-white text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-600 transition transform hover:-translate-y-0.5 active:scale-95" 
                                       data-spot="<?= htmlspecialchars($spot['name']) ?>" 
                                       data-type="<?= htmlspecialchars($spot['type']) ?>"
                                       data-theme="<?= htmlspecialchars($theme['hero_theme']) ?>">
                                        <?= htmlspecialchars($spot['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Pricing & CTA Action -->
                    <div class="mt-4 sm:mt-5 pt-3 sm:pt-3.5 border-t border-slate-200/60 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400 block leading-tight">Starting From</span>
                            <div class="flex items-baseline space-x-1">
                                <span class="text-sm sm:text-lg font-black text-slate-900">₹<?= number_format($theme['price']) ?></span>
                                <span class="text-[9px] sm:text-[10px] text-slate-500 font-medium">/ person</span>
                            </div>
                        </div>

                        <a href="<?= !empty($theme['search_url']) ? htmlspecialchars($theme['search_url']) : 'search.php?theme=' . urlencode($theme['hero_theme']) ?>" 
                           class="theme-cta-btn inline-flex items-center space-x-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full font-bold text-[11px] sm:text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 group/btn shrink-0 <?= $theme['btn_class'] ?>"
                           data-theme="<?= htmlspecialchars($theme['hero_theme']) ?>"
                           data-title="<?= htmlspecialchars($theme['title']) ?>">
                            <span>Explore Tours</span>
                            <i class="fa-solid fa-arrow-right text-[10px] sm:text-[11px] group-hover/btn:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
