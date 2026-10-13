<?php
/**
 * Testimonials & Verified Guest Reviews Component - Orion Advent
 * - Clean, Lightweight Testimonial Cards (Real Testimonial UI, Not Heavy)
 * - 5 Gold Stars, Verified Guest Badge, Quote, Author & Booked Tour Pill
 * - Interactive Filter Pills Track (All, Honeymoon, Family, Adventure, Luxury)
 * - Smooth Auto-Sliding Carousel (3 Cards Desktop, 2 Tablet, 1 Mobile)
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */

$testimonialCategories = [
    ['id' => 'all', 'label' => 'All Stories', 'icon' => 'fa-solid fa-earth-americas'],
    ['id' => 'honeymoon', 'label' => 'Honeymoon', 'icon' => 'fa-solid fa-heart'],
    ['id' => 'family', 'label' => 'Family Vacations', 'icon' => 'fa-solid fa-people-roof'],
    ['id' => 'adventure', 'label' => 'Adventure Treks', 'icon' => 'fa-solid fa-person-hiking'],
    ['id' => 'luxury', 'label' => 'Luxury Getaways', 'icon' => 'fa-solid fa-crown'],
];

// Dynamically fetch verified reviews added via Admin Panel
if (function_exists('getDBConnection')) {
    $pdoRev = getDBConnection();
} else {
    require_once __DIR__ . '/../config/db.php';
    $pdoRev = getDBConnection();
}

if (function_exists('getSetting')) {
    $revSiteName = getSetting('site_name', 'Orion Advent');
} else {
    require_once __DIR__ . '/../config/settings.php';
    $revSiteName = getSetting('site_name', 'Orion Advent');
}

if ($pdoRev) {
    try {
        $dbReviews = $pdoRev->query("SELECT * FROM `reviews` WHERE `status` = 'active' ORDER BY `id` DESC")->fetchAll();
        if (!empty($dbReviews)) {
            $dynamicReviews = [];
            foreach ($dbReviews as $dbr) {
                $avatar = !empty($dbr['avatar']) ? $dbr['avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80';
                $dynamicReviews[] = [
                    'id' => 'db-rev-' . $dbr['id'],
                    'name' => $dbr['name'],
                    'city' => $dbr['city'] ?: 'India',
                    'avatar' => $avatar,
                    'category' => strtolower($dbr['category']),
                    'tour' => $dbr['tour'],
                    'headline' => $dbr['headline'],
                    'quote' => $dbr['quote'],
                    'rating' => (int)($dbr['rating'] ?: 5)
                ];
            }
            // Use 100% dynamic reviews from database
            $testimonialsList = $dynamicReviews;
        }
    } catch (Exception $e) {
        // Fallback silently
    }
}
?>
<!-- ==========================================
     TESTIMONIALS & VERIFIED GUEST REVIEWS
     - Clean, Lightweight Testimonial Cards (Real Testimonial UI, Not Heavy)
     - Star Rating, Verified Guest Badge, Author Details & Tour Tag
     - Interactive Filter Pills & Auto-Sliding Carousel
     - 100% Flat, Border-First (Zero Shadows)
     - Font Awesome 6 Icons Only (Zero Emojis)
=========================================== -->
<section id="reviews-section" class="py-10 sm:py-16 md:py-24 px-3 sm:px-8 xl:px-12 bg-white border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto relative z-10">

        <!-- 1. Centered Section Header with Generated Inline Capsule Image -->
        <div class="max-w-3xl mx-auto text-center space-y-2.5 sm:space-y-3 mb-6 sm:mb-10">
            <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                <i class="fa-solid fa-star text-amber-500 text-[11px]"></i>
                <span>4.5 / 5.0 Average Guest Rating</span>
            </div>

            <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>What Our Travelers</span>
                <span class="inline-block align-middle mx-1 sm:mx-2.5">
                    <img src="assets/images/banner/heading-review-01.jpg" 
                         alt="Smiling Travelers on Beach" 
                         class="h-7 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">Say About Us</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto px-2">
                Real feedback from verified explorers who booked their domestic escapes and international getaways with <?= htmlspecialchars($revSiteName) ?>.
            </p>
        </div>

        <!-- 2. Sleek Unified Segmented Capsule Track for Categories -->
        <div class="flex items-center justify-start sm:justify-center space-x-2 overflow-x-auto pb-3 sm:pb-4 pt-1 px-1 no-scrollbar mb-6 sm:mb-10" id="testimonialFilterTrack">
            <?php foreach ($testimonialCategories as $index => $cat): ?>
                <button type="button" 
                        data-testimonial-filter="<?= htmlspecialchars($cat['id']) ?>" 
                        class="testimonial-filter-btn inline-flex items-center space-x-1.5 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-full border text-xs transition-all shrink-0 select-none <?= $index === 0 ? 'bg-brand-600 text-white border-brand-600 font-bold active-filter' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-brand-500 hover:bg-white font-semibold' ?>">
                    <i class="<?= htmlspecialchars($cat['icon']) ?> text-[11px] <?= $index === 0 ? 'text-white' : 'text-brand-600' ?>"></i>
                    <span><?= htmlspecialchars($cat['label']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- 3. Lightweight Testimonial Slider / Carousel Container -->
        <div class="relative testimonials-slider-container group">
            
            <!-- Prev Arrow Button (Hover-only on desktop) -->
            <button type="button" 
                    id="testimonialSlidePrev" 
                    aria-label="Previous Testimonials" 
                    class="hidden sm:flex absolute -left-3 sm:-left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white text-slate-800 border border-slate-300 items-center justify-center transition-all hover:bg-brand-600 hover:text-white hover:border-brand-600 disabled:opacity-30 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>

            <!-- Testimonials Track -->
            <div class="testimonials-slider-track py-2 px-1" id="testimonialsSlider">
                <?php foreach ($testimonialsList as $testimonial): ?>
                    <div class="testimonial-card-item bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition-all p-5 sm:p-6 flex flex-col justify-between group select-none relative" data-category="<?= htmlspecialchars($testimonial['category']) ?>">
                        
                        <div>
                            <!-- Top: Stars + Verified Badge + Quote Mark -->
                            <div class="flex items-center justify-between mb-3.5 sm:mb-4">
                                <div class="flex items-center space-x-1">
                                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                        <i class="fa-solid fa-star text-amber-400 text-[11px] sm:text-xs"></i>
                                    <?php endfor; ?>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center space-x-1">
                                        <i class="fa-solid fa-circle-check text-[9px]"></i>
                                        <span>Verified Guest</span>
                                    </span>
                                    <i class="fa-solid fa-quote-right text-slate-200 group-hover:text-brand-300 transition-colors text-base sm:text-lg"></i>
                                </div>
                            </div>

                            <!-- Headline Quote -->
                            <h4 class="text-sm sm:text-base font-extrabold text-slate-900 group-hover:text-brand-600 transition leading-snug mb-2">
                                "<?= htmlspecialchars($testimonial['headline']) ?>"
                            </h4>

                            <!-- Review Body -->
                            <p class="text-xs sm:text-[13px] text-slate-600 font-normal leading-relaxed line-clamp-4">
                                <?= htmlspecialchars($testimonial['quote']) ?>
                            </p>
                        </div>

                        <!-- Bottom: Author Info & Tour Tag -->
                        <div class="pt-3.5 sm:pt-4 mt-3.5 sm:mt-4 border-t border-slate-100 flex items-center justify-between gap-2.5">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <img src="<?= htmlspecialchars($testimonial['avatar']) ?>" 
                                     alt="<?= htmlspecialchars($testimonial['name']) ?>" 
                                     class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-slate-200 shrink-0">
                                
                                <div class="min-w-0">
                                    <h5 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight truncate">
                                        <?= htmlspecialchars($testimonial['name']) ?>
                                    </h5>
                                    <span class="text-[10px] sm:text-[11px] text-slate-400 font-medium block truncate">
                                        <i class="fa-solid fa-location-dot text-[9px] sm:text-[10px] text-brand-600 mr-1"></i><?= htmlspecialchars($testimonial['city']) ?>
                                    </span>
                                </div>
                            </div>

                            <span class="inline-flex items-center space-x-1 text-[10px] sm:text-[11px] font-semibold text-brand-700 bg-brand-50 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg border border-brand-200/80 shrink-0">
                                <i class="fa-solid fa-suitcase-rolling text-[9px] sm:text-[10px] text-brand-600"></i>
                                <span class="truncate max-w-[95px] sm:max-w-[110px]"><?= htmlspecialchars($testimonial['tour']) ?></span>
                            </span>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Next Arrow Button (Hover-only on desktop) -->
            <button type="button" 
                    id="testimonialSlideNext" 
                    aria-label="Next Testimonials" 
                    class="hidden sm:flex absolute -right-3 sm:-right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white text-slate-800 border border-slate-300 items-center justify-center transition-all hover:bg-brand-600 hover:text-white hover:border-brand-600 disabled:opacity-30 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </button>

        </div>

        <!-- 4. Clean Trust Signals Footer Strip -->
        <div class="mt-8 sm:mt-12 pt-5 sm:pt-6 border-t border-slate-100 flex flex-wrap items-center justify-center gap-4 sm:gap-12 text-xs font-bold text-slate-600 select-none">
            <div class="flex items-center space-x-1.5 sm:space-x-2">
                <span class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] sm:text-xs font-black border border-emerald-200">★</span>
                <span>Trustpilot 4.5 / 5 Rating</span>
            </div>
            <div class="flex items-center space-x-1.5 sm:space-x-2">
                <i class="fa-brands fa-google text-sky-600 text-xs sm:text-sm"></i>
                <span>Google Verified 4.5 ★ (2,400+ Reviews)</span>
            </div>
            <div class="flex items-center space-x-1.5 sm:space-x-2">
                <i class="fa-solid fa-award text-amber-500 text-xs sm:text-sm"></i>
                <span>TripAdvisor Travelers' Choice 2026</span>
            </div>
            <div class="flex items-center space-x-1.5 sm:space-x-2">
                <i class="fa-solid fa-shield-halved text-emerald-600 text-xs sm:text-sm"></i>
                <span>100% Verified Customer Feedback</span>
            </div>
        </div>

    </div>
</section>
