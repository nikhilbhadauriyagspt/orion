<?php
/**
 * Ocean Cruises Directory & Listing Portal - Orion Advent
 * Dedicated search & discovery for Cordelia Cruises, Royal Caribbean, and luxury ocean liners.
 * 100% Flat, Border-First (Zero Shadows), Font Awesome 6 icons only.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';
$siteName = getSetting('site_name', 'Orion Advent');
$pageTitle = 'Luxury Ocean Cruises & High Seas Sailings | ' . htmlspecialchars($siteName);

$pdo = getDBConnection();
$cruises = [];

// Fetch active cruises
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `cruises` WHERE `status` = 'active' ORDER BY `id` DESC");
        $cruises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $cruises = [];
    }
}

// Fallback sample cruises if database has no cruises yet
if (empty($cruises)) {
    $cruises = [
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
            'featured_image' => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=900&q=80',
            'sailing_dates' => 'Every Friday & Monday',
            'highlights' => json_encode(['All-Inclusive Dining', 'Infinity Deck Pools', 'Broadway Theatre', 'Casino & Lounges'])
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
            'featured_image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80',
            'sailing_dates' => 'Weekly Sailing • Sunday',
            'highlights' => json_encode(['Sri Lanka Shore Excursions', 'Bay of Bengal High Seas', 'Gourmet Indian & Jain Dining', 'Nightly Entertainment'])
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
            'featured_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
            'sailing_dates' => 'Monthly Departures',
            'highlights' => json_encode(['RipCord Skydiving', 'North Star Observation Capsule', '18 Dining Venues', 'AquaTheater'])
        ]
    ];
}

// Get unique departure ports for filter buttons
$allPorts = [];
foreach ($cruises as $c) {
    if (!empty($c['departure_port'])) {
        $allPorts[] = trim($c['departure_port']);
    }
}
$allPorts = array_values(array_unique($allPorts));

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ==========================================
     HERO BANNER FOR CRUISES (Dedicated Portal)
=========================================== -->
<section class="bg-slate-900 text-white border-b border-slate-800 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(13,148,136,0.18),transparent_70%)] pointer-events-none"></div>
    <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-8 xl:px-12 py-12 md:py-16 relative z-10">
        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-brand-200 border border-white/20">
                <i class="fa-solid fa-ship text-brand-300"></i>
                <span>Official Partner: Cordelia Cruises &bull; Royal Caribbean</span>
            </div>

            <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight text-white">
                Luxury Ocean Cruises &amp; High Seas Sailings
            </h1>

            <p class="text-sm sm:text-base text-slate-300 font-normal leading-relaxed max-w-2xl">
                Wake up to endless ocean horizons. Choose from weekend high seas getaways to exotic international voyages with all-inclusive gourmet dining, pools, casino lounges, and Broadway entertainment.
            </p>

            <!-- Quick Port Pills in Banner -->
            <div class="pt-2 flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-400 font-medium mr-1">Popular Departure Ports:</span>
                <?php foreach ($allPorts as $p): ?>
                    <button type="button" 
                            onclick="filterCruisesByPort('<?= htmlspecialchars(strtolower($p)) ?>')" 
                            class="px-3 py-1 rounded-full text-xs font-semibold bg-white/10 hover:bg-white text-white hover:text-slate-900 border border-white/20 transition">
                        <i class="fa-solid fa-anchor text-[10px] mr-1 text-brand-300"></i><?= htmlspecialchars($p) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     INTERACTIVE FILTER BAR & LISTINGS STREAM
=========================================== -->
<main class="w-full py-8 md:py-12 px-4 sm:px-8 xl:px-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <!-- Top Controls Strip -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Search Input -->
            <div class="relative flex-1 max-w-md">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       id="cruiseSearchInput" 
                       placeholder="Search by ship, port, destination (e.g. Goa, Mumbai, Sri Lanka)..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-full text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition font-medium">
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                <button type="button" data-filter="all" class="c-tab px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-600 text-white transition shrink-0">
                    All Sailings (<span id="countAll"><?= count($cruises) ?></span>)
                </button>
                <button type="button" data-filter="domestic" class="c-tab px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:border-brand-500 transition shrink-0">
                    <i class="fa-solid fa-map-pin mr-1 text-brand-600"></i>Domestic India
                </button>
                <button type="button" data-filter="international" class="c-tab px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:border-brand-500 transition shrink-0">
                    <i class="fa-solid fa-globe mr-1 text-sky-600"></i>International
                </button>
                <button type="button" data-filter="weekend" class="c-tab px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:border-brand-500 transition shrink-0">
                    <i class="fa-solid fa-calendar-check mr-1 text-amber-600"></i>Weekend Trips (2-3N)
                </button>
            </div>

            <!-- Sort By Selector -->
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs text-slate-400 font-medium">Sort:</span>
                <select id="cruiseSortSelect" class="py-1.5 px-3 rounded-full text-xs font-semibold bg-slate-50 border border-slate-200 text-slate-700 focus:border-brand-500 outline-none cursor-pointer">
                    <option value="featured">Best Recommended</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="duration">Duration: Nights</option>
                </select>
            </div>
        </div>

        <!-- Cruises Grid -->
        <div id="cruisesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($cruises as $c): 
                $img = !empty($c['featured_image']) ? $c['featured_image'] : 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=900&q=80';
                $nights = (int)($c['duration_nights'] ?: 3);
                $isWeekend = ($nights <= 3) ? 'weekend' : 'extended';
                $cat = strtolower($c['category'] ?? 'domestic');
                $port = strtolower($c['departure_port'] ?? '');
                $tags = strtolower($c['title'] . ' ' . $c['ship_name'] . ' ' . $c['cruise_line'] . ' ' . $c['departure_port'] . ' ' . $c['destination_ports']);
                $slug = $c['slug'] ?: $c['id'];
                $detailUrl = 'cruise-details.php?slug=' . urlencode($slug);
            ?>
                <!-- Cruise Listing Card -->
                <article class="cruise-item-card bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition-all flex flex-col overflow-hidden group"
                         data-category="<?= htmlspecialchars($cat) ?>"
                         data-port="<?= htmlspecialchars($port) ?>"
                         data-weekend="<?= htmlspecialchars($isWeekend) ?>"
                         data-price="<?= (float)$c['starting_price'] ?>"
                         data-nights="<?= $nights ?>"
                         data-tags="<?= htmlspecialchars($tags) ?>">
                    
                    <!-- Top Media -->
                    <div class="h-56 relative overflow-hidden bg-slate-100">
                        <img src="<?= htmlspecialchars($img) ?>" 
                             alt="<?= htmlspecialchars($c['title']) ?>" 
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                        <!-- Badges -->
                        <div class="absolute top-3 left-3 flex flex-wrap items-center gap-1.5 z-10">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-brand-600 text-white">
                                <?= htmlspecialchars($c['badge'] ?: 'Premier Cruise') ?>
                            </span>
                            <?php if ($cat === 'international'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-500 text-white">
                                    <i class="fa-solid fa-passport mr-1"></i>International
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Sailing Duration Bottom-Right -->
                        <div class="absolute bottom-3 right-3 bg-slate-900/80 backdrop-blur-xs text-white text-[11px] font-bold px-2.5 py-1 rounded-full flex items-center space-x-1.5 border border-white/20">
                            <i class="fa-regular fa-clock text-[10px]"></i>
                            <span><?= htmlspecialchars($c['duration_text'] ?: ($c['duration_nights'] . 'N / ' . $c['duration_days'] . 'D')) ?></span>
                        </div>

                        <!-- Departure Port Bottom-Left -->
                        <div class="absolute bottom-3 left-3 bg-white/95 text-slate-800 text-[11px] font-bold px-2.5 py-1 rounded-full flex items-center space-x-1.5 border border-slate-200">
                            <i class="fa-solid fa-anchor text-brand-600 text-[10px]"></i>
                            <span>Ex-<?= htmlspecialchars($c['departure_port']) ?></span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex flex-col flex-1 justify-between space-y-4">
                        <div class="space-y-2.5">
                            <!-- Cruise Line & Ship Name & Rating -->
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-brand-700 bg-brand-50 border border-brand-200/60 px-2 py-0.5 rounded-md flex items-center gap-1 text-[11px]">
                                    <i class="fa-solid fa-ship text-[10px]"></i>
                                    <?= htmlspecialchars($c['cruise_line'] . ' • ' . $c['ship_name']) ?>
                                </span>
                                <div class="flex items-center space-x-1 text-slate-600 font-semibold text-[11px]">
                                    <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                    <span><?= number_format((float)($c['rating'] ?: 4.9), 1) ?></span>
                                    <span class="text-slate-400 font-normal">(<?= (int)($c['reviews_count'] ?: 200) ?>)</span>
                                </div>
                            </div>

                            <!-- Title -->
                            <h3 class="text-base font-bold text-slate-900 line-clamp-2 leading-snug group-hover:text-brand-600 transition">
                                <a href="<?= $detailUrl ?>">
                                    <?= htmlspecialchars($c['title']) ?>
                                </a>
                            </h3>

                            <!-- Route -->
                            <div class="text-xs text-slate-500 flex items-center gap-1.5 font-medium line-clamp-1">
                                <i class="fa-solid fa-route text-brand-600 shrink-0 text-xs"></i>
                                <span><?= htmlspecialchars($c['destination_ports'] ?: 'High Seas Cruising') ?></span>
                            </div>

                            <!-- Sailing Schedule -->
                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 font-medium">
                                <i class="fa-regular fa-calendar-days text-brand-600 text-[10px]"></i>
                                <span><?= htmlspecialchars($c['sailing_dates'] ?: 'Weekly departures available') ?></span>
                            </div>

                            <!-- Inclusions Grid -->
                            <div class="grid grid-cols-2 gap-1.5 pt-1 border-t border-slate-100">
                                <div class="flex items-center space-x-1.5 text-[11px] text-slate-600">
                                    <i class="fa-solid fa-utensils text-brand-600 text-[10px]"></i>
                                    <span>All Buffet Meals</span>
                                </div>
                                <div class="flex items-center space-x-1.5 text-[11px] text-slate-600">
                                    <i class="fa-solid fa-masks-theater text-brand-600 text-[10px]"></i>
                                    <span>Broadway Shows</span>
                                </div>
                                <div class="flex items-center space-x-1.5 text-[11px] text-slate-600">
                                    <i class="fa-solid fa-water-ladder text-brand-600 text-[10px]"></i>
                                    <span>Infinity Pools</span>
                                </div>
                                <div class="flex items-center space-x-1.5 text-[11px] text-slate-600">
                                    <i class="fa-solid fa-dice text-brand-600 text-[10px]"></i>
                                    <span>Casino &amp; Lounges</span>
                                </div>
                            </div>
                        </div>

                        <!-- Price & Action Strip -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="flex items-baseline space-x-1.5">
                                    <span class="text-xl font-black text-slate-900 font-space">₹<?= number_format((float)$c['starting_price']) ?></span>
                                    <?php if (!empty($c['original_price']) && $c['original_price'] > $c['starting_price']): ?>
                                        <span class="text-xs text-slate-400 line-through">₹<?= number_format((float)$c['original_price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[10px] text-slate-400 block font-medium">per person • twin sharing</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        onclick="openCruiseInquiryModal('<?= htmlspecialchars(addslashes($c['title'])) ?>', '<?= htmlspecialchars(addslashes($c['ship_name'])) ?>')"
                                        class="px-3 py-2 rounded-full bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 font-bold text-xs transition" 
                                        title="Quick Inquiry">
                                    <i class="fa-regular fa-message text-xs"></i>
                                </button>
                                <a href="<?= $detailUrl ?>" 
                                   class="px-4 py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center space-x-1.5">
                                    <span>View Sailing</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Empty Results Message -->
        <div id="noCruisesFound" class="hidden bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-lg mx-auto space-y-4 my-8">
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 border border-brand-200 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-ship"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">No Ocean Sailings Found</h3>
            <p class="text-xs text-slate-500">We couldn't find any cruises matching your current filters. Try resetting the filters or searching for another port.</p>
            <button type="button" onclick="resetCruiseFilters()" class="px-5 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition">
                Reset All Filters
            </button>
        </div>

    </div>
</main>

<!-- ==========================================
     QUICK CRUISE INQUIRY MODAL
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
                <input type="text" id="modalCruiseTitle" readonly class="w-full px-3 py-2 rounded-xl text-xs bg-slate-100 border border-slate-200 text-slate-800 font-semibold">
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

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Preferred Sailing Month</label>
                    <input type="text" id="inqMonth" placeholder="e.g. Next Month / Nov" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Total Guests</label>
                    <select id="inqGuests" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 outline-none">
                        <option value="2 Adults">2 Adults (1 Cabin)</option>
                        <option value="2 Adults + 1 Child">2 Adults + 1 Child</option>
                        <option value="4 Adults">4 Adults (2 Cabins)</option>
                        <option value="Group / Corporate">Group / Corporate (6+)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Preferred Cabin Type</label>
                <select id="inqCabin" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 outline-none">
                    <option value="Any Cabin">Best Value Cabin</option>
                    <option value="Ocean View">Ocean View (Window)</option>
                    <option value="Balcony Suite">Private Balcony Suite</option>
                    <option value="Presidential Suite">Chairman / Presidential Suite</option>
                </select>
            </div>

            <button type="submit" id="inqSubmitBtn" class="w-full py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95">
                Send Request to Cruise Specialist
            </button>
        </form>
    </div>
</div>

<script>
let activeFilter = 'all';

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('cruiseSearchInput');
    const tabs = document.querySelectorAll('.c-tab');
    const sortSelect = document.getElementById('cruiseSortSelect');

    searchInput.addEventListener('input', applyFilters);
    sortSelect.addEventListener('change', applyFilters);

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => {
                t.classList.remove('bg-brand-600', 'text-white');
                t.classList.add('bg-slate-100', 'text-slate-700', 'border', 'border-slate-200');
            });
            this.classList.remove('bg-slate-100', 'text-slate-700', 'border', 'border-slate-200');
            this.classList.add('bg-brand-600', 'text-white');

            activeFilter = this.getAttribute('data-filter');
            applyFilters();
        });
    });
});

function filterCruisesByPort(port) {
    const searchInput = document.getElementById('cruiseSearchInput');
    searchInput.value = port;
    applyFilters();
    searchInput.scrollIntoView({ behavior: 'smooth' });
}

function applyFilters() {
    const searchVal = (document.getElementById('cruiseSearchInput').value || '').toLowerCase().trim();
    const sortVal = document.getElementById('cruiseSortSelect').value;
    const cards = Array.from(document.querySelectorAll('.cruise-item-card'));
    let visibleCount = 0;

    cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        const port = card.getAttribute('data-port') || '';
        const weekend = card.getAttribute('data-weekend') || '';
        const tags = card.getAttribute('data-tags') || '';

        let matchFilter = false;
        if (activeFilter === 'all') {
            matchFilter = true;
        } else if (activeFilter === 'domestic' && cat === 'domestic') {
            matchFilter = true;
        } else if (activeFilter === 'international' && cat === 'international') {
            matchFilter = true;
        } else if (activeFilter === 'weekend' && weekend === 'weekend') {
            matchFilter = true;
        }

        let matchSearch = true;
        if (searchVal) {
            matchSearch = tags.includes(searchVal);
        }

        if (matchFilter && matchSearch) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const noFound = document.getElementById('noCruisesFound');
    if (noFound) {
        noFound.classList.toggle('hidden', visibleCount > 0);
    }

    // Sort visible cards
    const grid = document.getElementById('cruisesGrid');
    cards.sort((a, b) => {
        const pA = parseFloat(a.getAttribute('data-price')) || 0;
        const pB = parseFloat(b.getAttribute('data-price')) || 0;
        const nA = parseInt(a.getAttribute('data-nights')) || 0;
        const nB = parseInt(b.getAttribute('data-nights')) || 0;

        if (sortVal === 'price_low') return pA - pB;
        if (sortVal === 'price_high') return pB - pA;
        if (sortVal === 'duration') return nA - nB;
        return 0;
    });

    cards.forEach(c => grid.appendChild(c));
}

function resetCruiseFilters() {
    document.getElementById('cruiseSearchInput').value = '';
    const firstTab = document.querySelector('.c-tab[data-filter="all"]');
    if (firstTab) firstTab.click();
}

// Modal handling
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
        alert('Thank you! Your inquiry has been sent to our Cruise Concierge. We will contact you within 2 hours with exclusive pricing & stateroom options.');
        closeCruiseInquiryModal();
        btn.disabled = false;
        btn.innerText = 'Send Request to Cruise Specialist';
        document.getElementById('cruiseInquiryForm').reset();
    }, 800);
}
</script>

<?php require_once 'components/footer.php'; ?>
