<?php
/**
 * Ocean Cruises Section - Orion Advent Homepage Component
 * Displays featured & trending luxury ocean sailings (Cordelia, Royal Caribbean, etc.)
 * Strictly 100% Flat, Border-First (Zero shadows), Font Awesome 6 icons only.
 */

if (function_exists('getDBConnection')) {
    $pdoCruises = getDBConnection();
} else {
    require_once __DIR__ . '/../config/db.php';
    $pdoCruises = getDBConnection();
}

$featuredCruises = [];

if ($pdoCruises) {
    try {
        $stmtC = $pdoCruises->query("SELECT * FROM `cruises` WHERE `status` = 'active' ORDER BY `id` DESC LIMIT 8");
        $featuredCruises = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $featuredCruises = [];
    }
}

// Fallback items if database has no entries yet
if (empty($featuredCruises)) {
    $featuredCruises = [
        [
            'id' => 1,
            'title' => 'Mumbai to Goa & Arabian High Seas Luxury Sailing',
            'slug' => 'mumbai-goa-arabian-sea-cordelia',
            'subtitle' => 'Experience the magic of sailing into the sunset with 5-star hospitality, infinity pools, and live Broadway shows.',
            'cruise_line' => 'Cordelia Cruises',
            'ship_name' => 'The Empress',
            'departure_port' => 'Mumbai',
            'destination_ports' => 'Mumbai • High Seas • Mormugao (Goa)',
            'duration_nights' => 3,
            'duration_days' => 4,
            'duration_text' => '3N / 4D',
            'starting_price' => 19999,
            'original_price' => 26999,
            'token_advance' => 3000,
            'badge' => 'Bestseller',
            'rating' => 4.9,
            'reviews_count' => 380,
            'category' => 'domestic',
            'featured_image' => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=800&q=80',
            'sailing_dates' => 'Every Friday & Monday'
        ],
        [
            'id' => 2,
            'title' => 'Chennai to Sri Lanka International Ocean Voyage',
            'slug' => 'chennai-sri-lanka-international-cruise',
            'subtitle' => 'Sail across the Bay of Bengal to explore the vibrant ports of Hambantota, Trincomalee & Jaffna.',
            'cruise_line' => 'Cordelia Cruises',
            'ship_name' => 'The Empress',
            'departure_port' => 'Chennai',
            'destination_ports' => 'Chennai • Hambantota • Trincomalee • Jaffna',
            'duration_nights' => 4,
            'duration_days' => 5,
            'duration_text' => '4N / 5D',
            'starting_price' => 28999,
            'original_price' => 36999,
            'token_advance' => 5000,
            'badge' => 'International Fav',
            'rating' => 4.8,
            'reviews_count' => 240,
            'category' => 'international',
            'featured_image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=800&q=80',
            'sailing_dates' => 'Weekly Sailing • Sunday'
        ],
        [
            'id' => 3,
            'title' => 'Singapore to Malaysia & Thailand Mega Cruise',
            'slug' => 'singapore-penang-phuket-royal-caribbean',
            'subtitle' => 'Sail on the iconic Spectrum of the Seas featuring skydiving simulators, robotic bar & world-class entertainment.',
            'cruise_line' => 'Royal Caribbean',
            'ship_name' => 'Spectrum of the Seas',
            'departure_port' => 'Singapore',
            'destination_ports' => 'Singapore • Penang • Phuket • Singapore',
            'duration_nights' => 4,
            'duration_days' => 5,
            'duration_text' => '4N / 5D',
            'starting_price' => 38999,
            'original_price' => 49999,
            'token_advance' => 6000,
            'badge' => 'Super Luxury',
            'rating' => 4.9,
            'reviews_count' => 520,
            'category' => 'international',
            'featured_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
            'sailing_dates' => 'Monthly Departures'
        ]
    ];
}
?>

<!-- ==========================================
     OCEAN CRUISES SECTION (HOMEPAGE COMPONENT)
     - Clean Pure White Background (bg-white)
     - Sharp Border-First Aesthetics (Zero Shadows)
     - Interactive Port & Category Filter Tabs
     - Font Awesome 6 Icons Only
=========================================== -->
<section id="ocean-cruises" class="py-10 sm:py-16 md:py-20 px-3 sm:px-8 xl:px-12 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto">

        <!-- 1. Section Header -->
        <div class="max-w-3xl mx-auto text-center space-y-2.5 sm:space-y-3 mb-8 sm:mb-10">
            <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-ship text-brand-600 text-[11px]"></i>
                <span>Luxury Ocean Sailings</span>
            </div>

            <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>Explore Luxury Ocean </span>
                <span class="inline-block align-middle mx-1 sm:mx-2.5">
                    <img src="assets/images/banner/heading-cruise-01.jpg" 
                         alt="Ocean Cruise Liner" 
                         class="h-7 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">Cruises & Unforgettable Sea Journeys</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto px-2">
               Discover exciting cruise destinations with ocean views, onboard dining, live entertainment, and relaxing days at sea.
            </p>

            <!-- Filter Category Pills -->
            <div class="flex items-center justify-start sm:justify-center space-x-2 overflow-x-auto pt-2 sm:pt-3 pb-1 px-1 sm:px-0 whitespace-nowrap no-scrollbar">
                <button type="button" data-cruise-filter="all" class="cruise-filter-btn px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs font-bold bg-brand-600 text-white transition shrink-0">
                    All Sailings
                </button>
                <button type="button" data-cruise-filter="domestic" class="cruise-filter-btn px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-700 hover:bg-brand-50/50 transition shrink-0">
                    <i class="fa-solid fa-map-pin mr-1.5 text-brand-600 text-[10px]"></i>Domestic India
                </button>
                <button type="button" data-cruise-filter="international" class="cruise-filter-btn px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-700 hover:bg-brand-50/50 transition shrink-0">
                    <i class="fa-solid fa-globe mr-1.5 text-sky-500 text-[10px]"></i>International
                </button>
                <button type="button" data-cruise-filter="mumbai" class="cruise-filter-btn px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-700 hover:bg-brand-50/50 transition shrink-0">
                    <i class="fa-solid fa-anchor mr-1.5 text-amber-500 text-[10px]"></i>Ex-Mumbai
                </button>
                <button type="button" data-cruise-filter="chennai" class="cruise-filter-btn px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-700 hover:bg-brand-50/50 transition shrink-0">
                    <i class="fa-solid fa-anchor mr-1.5 text-rose-500 text-[10px]"></i>Ex-Chennai
                </button>
            </div>
        </div>

        <!-- 2. Cruises Grid / Slider Container -->
        <div class="relative group/slider cruise-slider-container">
            <!-- Prev Navigation Button -->
            <button type="button" 
                    id="cruiseSlidePrev" 
                    aria-label="Previous Cruises"
                    class="hidden sm:flex absolute -left-2 sm:-left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white border border-slate-300 text-slate-700 hover:text-brand-600 hover:border-brand-500 hover:bg-slate-50 items-center justify-center transition active:scale-95 disabled:opacity-0 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>

            <!-- Next Navigation Button -->
            <button type="button" 
                    id="cruiseSlideNext" 
                    aria-label="Next Cruises"
                    class="hidden sm:flex absolute -right-2 sm:-right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white border border-slate-300 text-slate-700 hover:text-brand-600 hover:border-brand-500 hover:bg-slate-50 items-center justify-center transition active:scale-95 disabled:opacity-0 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </button>

            <!-- Slider Wrapper -->
            <div id="cruiseSlider" 
                 class="flex items-stretch space-x-4 sm:space-x-5 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 pt-1 px-1 no-scrollbar"
                 style="scrollbar-width: none; -ms-overflow-style: none;">

                <?php foreach ($featuredCruises as $cr): 
                    $imgUrl = !empty($cr['featured_image']) ? $cr['featured_image'] : 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=800&q=80';
                    $portLower = strtolower($cr['departure_port'] ?? '');
                    $catLower = strtolower($cr['category'] ?? 'domestic');
                    $filterCategories = 'all ' . $catLower . ' ' . $portLower;
                    $detailUrl = 'cruise-details.php?slug=' . urlencode($cr['slug'] ?: $cr['id']);
                ?>
                    <!-- Cruise Card -->
                    <div class="cruise-card w-[285px] sm:w-[335px] md:w-[360px] flex-shrink-0 snap-start bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition-all flex flex-col overflow-hidden group"
                         data-category="<?= htmlspecialchars($filterCategories) ?>">
                        
                        <!-- Image Container -->
                        <div class="h-44 sm:h-52 overflow-hidden relative bg-slate-100">
                            <img src="<?= htmlspecialchars($imgUrl) ?>" 
                                 alt="<?= htmlspecialchars($cr['title']) ?>" 
                                 loading="lazy" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                            <!-- Top Badges -->
                            <div class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3 flex items-center gap-1.5 z-10">
                                <span class="px-2.5 py-0.5 sm:py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-brand-600 text-white">
                                    <?= htmlspecialchars($cr['badge'] ?: 'Premier Cruise') ?>
                                </span>
                                <?php if ($catLower === 'international'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-500 text-white">
                                        <i class="fa-solid fa-passport mr-1"></i>International
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Duration Pill Bottom-Right -->
                            <div class="absolute bottom-2.5 right-2.5 sm:bottom-3 sm:right-3 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] sm:text-[11px] font-bold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full flex items-center space-x-1 sm:space-x-1.5 border border-white/20">
                                <i class="fa-regular fa-clock text-[9px] sm:text-[10px]"></i>
                                <span><?= htmlspecialchars($cr['duration_text'] ?: ($cr['duration_nights'] . 'N / ' . $cr['duration_days'] . 'D')) ?></span>
                            </div>

                            <!-- Departure Port Bottom-Left -->
                            <div class="absolute bottom-2.5 left-2.5 sm:bottom-3 sm:left-3 bg-white/95 text-slate-800 text-[10px] sm:text-[11px] font-bold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full flex items-center space-x-1 sm:space-x-1.5 border border-slate-200">
                                <i class="fa-solid fa-anchor text-brand-600 text-[9px] sm:text-[10px]"></i>
                                <span>Ex-<?= htmlspecialchars($cr['departure_port']) ?></span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 sm:p-5 flex flex-col flex-1 justify-between space-y-3 sm:space-y-4">
                            <div class="space-y-2">
                                <!-- Ship Name & Rating -->
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-brand-700 bg-brand-50 border border-brand-200/60 px-2 py-0.5 rounded-md flex items-center gap-1 text-[10px] sm:text-[11px]">
                                        <i class="fa-solid fa-ship text-[9px] sm:text-[10px]"></i>
                                        <?= htmlspecialchars($cr['cruise_line'] . ' • ' . $cr['ship_name']) ?>
                                    </span>
                                    <div class="flex items-center space-x-1 text-slate-600 font-semibold text-[10px] sm:text-[11px]">
                                        <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                        <span><?= number_format((float)($cr['rating'] ?: 4.9), 1) ?></span>
                                        <span class="text-slate-400 font-normal hidden sm:inline">(<?= (int)($cr['reviews_count'] ?: 200) ?>)</span>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 line-clamp-2 leading-snug group-hover:text-brand-600 transition">
                                    <a href="<?= $detailUrl ?>">
                                        <?= htmlspecialchars($cr['title']) ?>
                                    </a>
                                </h3>

                                <!-- Ports of Call Route -->
                                <div class="text-[11px] sm:text-xs text-slate-500 flex items-center gap-1.5 font-medium line-clamp-1">
                                    <i class="fa-solid fa-route text-brand-600 shrink-0 text-[10px] sm:text-xs"></i>
                                    <span class="truncate"><?= htmlspecialchars($cr['destination_ports'] ?: 'High Seas Cruising') ?></span>
                                </div>

                                <!-- Key Cruise Inclusions -->
                                <div class="grid grid-cols-2 gap-1.5 pt-1.5 sm:pt-2">
                                    <div class="flex items-center space-x-1.5 text-[10px] sm:text-[11px] text-slate-600 truncate">
                                        <i class="fa-solid fa-utensils text-brand-600 text-[10px] shrink-0"></i>
                                        <span class="truncate">All-Day Buffet Meals</span>
                                    </div>
                                    <div class="flex items-center space-x-1.5 text-[10px] sm:text-[11px] text-slate-600 truncate">
                                        <i class="fa-solid fa-masks-theater text-brand-600 text-[10px] shrink-0"></i>
                                        <span class="truncate">Broadway Shows</span>
                                    </div>
                                    <div class="flex items-center space-x-1.5 text-[10px] sm:text-[11px] text-slate-600 truncate">
                                        <i class="fa-solid fa-water-ladder text-brand-600 text-[10px] shrink-0"></i>
                                        <span class="truncate">Infinity Deck Pools</span>
                                    </div>
                                    <div class="flex items-center space-x-1.5 text-[10px] sm:text-[11px] text-slate-600 truncate">
                                        <i class="fa-solid fa-dice text-brand-600 text-[10px] shrink-0"></i>
                                        <span class="truncate">Casino &amp; Lounges</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing & CTA -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <div>
                                    <div class="flex items-baseline space-x-1">
                                        <span class="text-base sm:text-lg font-black text-slate-900">₹<?= number_format((float)$cr['starting_price']) ?></span>
                                        <?php if (!empty($cr['original_price']) && $cr['original_price'] > $cr['starting_price']): ?>
                                            <span class="text-[10px] sm:text-xs text-slate-400 line-through">₹<?= number_format((float)$cr['original_price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] text-slate-400 block font-medium">per person</span>
                                </div>

                                <a href="<?= $detailUrl ?>" 
                                   class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider transition active:scale-95 flex items-center space-x-1 sm:space-x-1.5 shrink-0">
                                    <span>View Sailing</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- Bottom View All Sailings Pill -->
        <div class="text-center mt-8 sm:mt-10">
            <a href="cruises.php" 
               class="inline-flex items-center space-x-2 px-4 sm:px-6 py-2.5 sm:py-3 rounded-full bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-700 border border-slate-200 hover:border-brand-300 font-bold text-[11px] sm:text-xs uppercase tracking-wider transition text-center">
                <i class="fa-solid fa-ship text-brand-600 text-xs"></i>
                <span>Explore All Ocean Sailings</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

    </div>
</section>

<!-- Client-Side Cruise Slider & Filtering Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cruiseSlider = document.getElementById('cruiseSlider');
    const prevBtn = document.getElementById('cruiseSlidePrev');
    const nextBtn = document.getElementById('cruiseSlideNext');
    const filterBtns = document.querySelectorAll('.cruise-filter-btn');
    const cruiseCards = document.querySelectorAll('.cruise-card');

    if (cruiseSlider && prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => {
            cruiseSlider.scrollBy({ left: -360, behavior: 'smooth' });
        });
        nextBtn.addEventListener('click', () => {
            cruiseSlider.scrollBy({ left: 360, behavior: 'smooth' });
        });
    }

    // Filter Buttons logic
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            filterBtns.forEach(b => {
                b.classList.remove('bg-brand-600', 'text-white');
                b.classList.add('bg-slate-100', 'text-slate-700', 'border', 'border-slate-200');
            });
            this.classList.remove('bg-slate-100', 'text-slate-700', 'border', 'border-slate-200');
            this.classList.add('bg-brand-600', 'text-white');

            const filter = this.getAttribute('data-cruise-filter');

            cruiseCards.forEach(card => {
                const cats = (card.getAttribute('data-category') || '').split(' ');
                if (filter === 'all' || cats.includes(filter)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
