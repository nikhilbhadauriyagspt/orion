<?php
/**
 * About Us Component
 * - Clean, Lightweight Brand Story & Impact Showcase
 * - Human-First Travel Philosophy & 3 Core Promises
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */
if (function_exists('getSetting')) {
    $aboutSiteName = getSetting('site_name', 'Orion Advent');
} else {
    require_once __DIR__ . '/../config/settings.php';
    $aboutSiteName = getSetting('site_name', 'Orion Advent');
}
?>
<!-- ==========================================
     ABOUT US & WHY CHOOSE US COMPONENT
     - Elegant, lightweight, airy brand story
     - 100% Flat, Border-First (Zero Shadows)
     - Font Awesome 6 Icons Only (Zero Emojis)
=========================================== -->
<section id="about-section" class="py-12 sm:py-16 md:py-24 px-4 sm:px-8 xl:px-12 bg-slate-50/60 border-b border-slate-200 relative overflow-hidden">
    
    <!-- Ambient Background Accents -->
    <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-brand-100/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-teal-100/30 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10">

        <!-- 1. Centered Section Header with Generated Inline Capsule Image -->
        <div class="max-w-3xl mx-auto text-center space-y-3 mb-12 sm:mb-16">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-compass text-brand-600"></i>
                <span>About <?= htmlspecialchars($aboutSiteName) ?></span>
            </div>

            <h2 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>Crafting Holidays That</span>
                <span class="inline-block align-middle mx-1.5 sm:mx-2.5">
                    <img src="assets/images/banner/heading-about-01.jpg" 
                         alt="<?= htmlspecialchars($aboutSiteName) ?> Travel Concierge Team" 
                         class="h-7 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">Become Lifelong Memories</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto">
                We started <?= htmlspecialchars($aboutSiteName) ?> with a simple belief: holiday planning shouldn't feel like stressful work. It should feel like the exciting first chapter of your adventure.
            </p>
        </div>

        <!-- 2. Two-Column Story Architecture (Spacious & Clean, Not Heavy) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
            
            <!-- Left Column: The Brand Manifesto & Live Impact (6 Cols) -->
            <div class="lg:col-span-6 space-y-6">
                
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 block flex items-center">
                    <i class="fa-solid fa-seedling mr-2 text-sm"></i>
                    <span>Know About <strong class="text-slate-900 font-bold"><?= htmlspecialchars($aboutSiteName) ?></strong></span>
                </span>

                <blockquote class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 leading-snug tracking-tight">
                    "Your Trusted Travel Partner for Discovering New Destinations and Creating Unforgettable Memories Around the World"
                </blockquote>

                <p class="text-xs sm:text-sm text-slate-600 font-normal leading-relaxed">
                    Most online travel booking platforms leave you completely on your own the moment payment clears—leaving you to navigate confusing hotel check-ins, delayed taxis, and hidden resort fees.
                </p>

                <p class="text-xs sm:text-sm text-slate-600 font-normal leading-relaxed">
                    At <strong class="text-slate-900 font-bold"><?= htmlspecialchars($aboutSiteName) ?></strong>, our relationship begins when you book. We combine direct wholesale hotel partnerships with a dedicated on-trip human concierge on WhatsApp. We take care of all the logistics so you can simply live in the moment.
                </p>

                <!-- Clean Inline Impact Metrics (Not Chunky Boxes) -->
                <div class="pt-4 border-t border-slate-200/80 grid grid-cols-2 sm:grid-cols-4 gap-4 select-none">
                    <div>
                        <span class="text-xl sm:text-2xl font-black text-slate-900 block leading-tight">50,00+</span>
                        <span class="text-[11px] font-semibold text-slate-500 block mt-0.5">Happy Travelers</span>
                    </div>
                    <div>
                        <span class="text-xl sm:text-2xl font-black text-slate-900 block leading-tight">700+</span>
                        <span class="text-[11px] font-semibold text-slate-500 block mt-0.5">Verified Stays</span>
                    </div>
                    <div>
                        <span class="text-xl sm:text-2xl font-black text-slate-900 block leading-tight">45+</span>
                        <span class="text-[11px] font-semibold text-slate-500 block mt-0.5">Curated Circuits</span>
                    </div>
                    <div>
                        <span class="text-xl sm:text-2xl font-black text-brand-600 block leading-tight">4.5 ★</span>
                        <span class="text-[11px] font-semibold text-slate-500 block mt-0.5">Guest Rating</span>
                    </div>
                </div>

            </div>

            <!-- Right Column: 3 Core Promises (Lightweight, Clean Rows) (6 Cols) -->
            <div class="lg:col-span-6 space-y-4">
                
                <!-- Promise Card 1 -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 hover:border-brand-500 transition-all flex items-start space-x-4 group">
                    <span class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 border border-brand-200 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-route text-base"></i>
                    </span>
                    <div>
                        <h4 class="text-sm sm:text-base font-extrabold text-slate-900 group-hover:text-brand-600 transition">
                            Human Pacing, Zero Rush
                        </h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Itineraries designed with generous breathing room. No 5 AM wake-up whistles or rushed photo stops—you truly savor the sights and local cuisine.
                        </p>
                    </div>
                </div>

                <!-- Promise Card 2 -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 hover:border-brand-500 transition-all flex items-start space-x-4 group">
                    <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-receipt text-base"></i>
                    </span>
                    <div>
                        <h4 class="text-sm sm:text-base font-extrabold text-slate-900 group-hover:text-brand-600 transition">
                            Direct Wholesale Fares, Zero Hidden Fees
                        </h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Direct hotel contracts and transparent per-person pricing. What you see is what you pay—no surprise checkout taxes, service cuts, or resort fees.
                        </p>
                    </div>
                </div>

                <!-- Promise Card 3 -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 hover:border-brand-500 transition-all flex items-start space-x-4 group">
                    <span class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-200 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-comments text-base"></i>
                    </span>
                    <div>
                        <h4 class="text-sm sm:text-base font-extrabold text-slate-900 group-hover:text-brand-600 transition">
                            24x7 Real-Human WhatsApp Concierge
                        </h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            A personal trip manager is in your pocket from departure to return. Need a table reservation, room change, or cab update? Just send a quick text.
                        </p>
                    </div>
                </div>

                <!-- Action CTA -->
                <div class="pt-2">
                    <a href="#hero" 
                       class="inline-flex items-center space-x-2 px-5 py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 group">
                        <span>Plan Your Dream Vacation</span>
                        <i class="fa-solid fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

            </div>

        </div>

    </div>
</section>
