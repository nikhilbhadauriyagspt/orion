<?php
/**
 * About Us Page - Orion Advent
 * Clean, Airy, Lightweight & Modern Travel Theme UI
 * Handcrafted Itineraries, Verified Stays, Company Vision, Core Team & Numbers
 */
require_once __DIR__ . '/config/settings.php';
$siteName = getSetting('site_name', 'Orion Advent');
$pageTitle = 'About Us - Your Passport to Seamless Journeys | ' . htmlspecialchars($siteName);
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<main class="min-h-screen bg-slate-50/60 pb-20">

    <!-- ========================================================================= -->
    <!-- 1. HERO BANNER: Atmospheric, Inspiring, High-Res Visual                   -->
    <!-- ========================================================================= -->
    <section class="relative bg-slate-950 text-white overflow-hidden py-24 sm:py-28 lg:py-36 px-4 sm:px-6 lg:px-8">
        <!-- High Quality Background Image -->
        <img src="https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1920&q=85" 
             alt="Scenic Roadtrip & Mountain Exploration" 
             class="absolute inset-0 w-full h-full object-cover object-center opacity-40 select-none scale-105 transform hover:scale-100 transition-transform duration-1000">
        
        <!-- Modern Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-950/50"></div>

        <div class="relative z-10 max-w-5xl mx-auto text-center space-y-6">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-white/15 text-white border border-white/25 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                <i class="fa-solid fa-compass text-teal-300"></i>
                <span>About <?php echo htmlspecialchars($siteName); ?></span>
            </div>

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black font-space tracking-tight text-white leading-tight">
                We Craft Vacations That Turn Into <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-teal-300 via-emerald-300 to-amber-300 bg-clip-text text-transparent">
                    Unforgettable Stories
                </span>
            </h1>

            <p class="text-sm sm:text-base lg:text-lg text-slate-300 max-w-3xl mx-auto font-normal leading-relaxed">
                Founded by passionate explorers, <?php echo htmlspecialchars($siteName); ?> is on a mission to simplify travel. From handpicked domestic paradises to luxury international escapes, we bring complete price transparency, verified stays, and round-the-clock on-trip concierge care.
            </p>

            <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                <a href="search.php?type=package" class="px-7 py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-2">
                    <span>Explore Tour Packages</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
                <a href="contact.php" class="px-7 py-3.5 rounded-full bg-white/15 hover:bg-white/25 text-white font-bold text-xs uppercase tracking-wider border border-white/30 backdrop-blur-md transition-all">
                    <span>Contact Concierge Desk</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 2. LIVE IMPACT METRICS: Sharp Border-First Stats Grid                      -->
    <!-- ========================================================================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 sm:-mt-12 relative z-20">
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="space-y-1">
                <div class="text-3xl sm:text-4xl font-black font-space text-slate-900">45+</div>
                <div class="text-xs font-bold text-teal-600 uppercase tracking-wider">Curated Destinations</div>
                <p class="text-[11px] text-slate-400">Domestic circuits &amp; global escapes</p>
            </div>

            <div class="space-y-1 pt-4 md:pt-0">
                <div class="text-3xl sm:text-4xl font-black font-space text-slate-900">5,000+</div>
                <div class="text-xs font-bold text-brand-600 uppercase tracking-wider">Happy Travelers</div>
                <p class="text-[11px] text-slate-400">Families, couples &amp; solo adventurers</p>
            </div>

            <div class="space-y-1 pt-4 md:pt-0">
                <div class="text-3xl sm:text-4xl font-black font-space text-slate-900">4.5 ★</div>
                <div class="text-xs font-bold text-amber-500 uppercase tracking-wider">Verified Rating</div>
                <p class="text-[11px] text-slate-400">Over 3,800+ authentic guest reviews</p>
            </div>

            <div class="space-y-1 pt-4 md:pt-0">
                <div class="text-3xl sm:text-4xl font-black font-space text-slate-900">24x7</div>
                <div class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Live Concierge</div>
                <p class="text-[11px] text-slate-400">On-trip human assistance anytime</p>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. OUR STORY & PHILOSOPHY: 2-Column Story with High-Res Visuals           -->
    <!-- ========================================================================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
            
            <!-- Left: Visual Grid of Real Traveling Moments -->
            <div class="lg:col-span-6 grid grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div class="h-64 sm:h-72 rounded-3xl overflow-hidden border border-slate-200 relative group">
                        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80" 
                             alt="Tropical Paradise Island" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-5 rounded-3xl bg-brand-50 border border-brand-200 text-brand-900 space-y-1">
                        <i class="fa-solid fa-shield-halved text-2xl text-brand-600 mb-1"></i>
                        <h4 class="text-sm font-bold">100% Rate Lock Guarantee</h4>
                        <p class="text-[11px] text-brand-700">Zero hidden fees, zero surprise charges on arrival.</p>
                    </div>
                </div>

                <div class="space-y-4 pt-6">
                    <div class="p-5 rounded-3xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1">
                        <i class="fa-solid fa-hand-holding-dollar text-2xl text-amber-600 mb-1"></i>
                        <h4 class="text-sm font-bold">Partial Advance Booking</h4>
                        <p class="text-[11px] text-amber-700">Lock your dream trip with token advance, pay balance on arrival.</p>
                    </div>
                    <div class="h-64 sm:h-72 rounded-3xl overflow-hidden border border-slate-200 relative group">
                        <img src="https://images.unsplash.com/photo-1530789253388-582c481c54b0?auto=format&fit=crop&w=800&q=80" 
                             alt="Cultural Exploration in Himalayas" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                </div>
            </div>

            <!-- Right: Our Story Content -->
            <div class="lg:col-span-6 space-y-6">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-widest text-teal-600">The <?php echo htmlspecialchars($siteName); ?> Story</span>
                    <h2 class="text-2xl sm:text-4xl font-black font-space text-slate-900 tracking-tight leading-tight">
                        Travel Isn't Just Visiting Places. <br>
                        It's Collecting Lifelong Memories.
                    </h2>
                </div>

                <p class="text-sm text-slate-600 leading-relaxed">
                    We noticed that modern travelers were overwhelmed with confusing booking portals, hidden cancellation penalties, and lack of personalized support when things go wrong on the road. 
                </p>

                <p class="text-sm text-slate-600 leading-relaxed">
                    <?php echo htmlspecialchars($siteName); ?> was built to replace rigid generic tour operators with thoughtful, bespoke journeys. Every itinerary in our collection is crafted by destination specialists who have personally explored the routes, vetted the hotels, and handpicked local experiences.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-200 text-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Verified Boutique Stays</h4>
                            <p class="text-[11px] text-slate-500">Clean, vetted rooms with authentic hospitality.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-200 text-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Dedicated Tour Manager</h4>
                            <p class="text-[11px] text-slate-500">Real-time WhatsApp support throughout your trip.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-200 text-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Flexible Customization</h4>
                            <p class="text-[11px] text-slate-500">Tailor days, vehicle type, and activities seamlessly.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-200 text-xs">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Instant PDF Voucher</h4>
                            <p class="text-[11px] text-slate-500">Instant confirmed tickets &amp; detailed day-wise plans.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 4. WHAT WE DO / OUR COMPREHENSIVE SUITE OF SERVICES                        -->
    <!-- ========================================================================= -->
    <section class="bg-white border-t border-b border-slate-200 py-16 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="max-w-2xl mx-auto text-center space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-600">What We Do</span>
                <h2 class="text-2xl sm:text-4xl font-black font-space text-slate-900 tracking-tight">
                    End-to-End Seamless Holiday Planning
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Everything you need for a frictionless vacation under one modern digital roof.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Service 1: Handcrafted Tour Packages -->
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-50 border border-slate-200 hover:border-brand-500 transition-all group space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 border border-brand-200 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 font-space">Curated Holiday Packages</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Comprehensive itineraries covering Kashmir, Himachal, Kerala, Goa, Rajasthan, Dubai, Bali, Thailand, and Maldives. Includes hotels, sightseeing, private transfers, and daily breakfasts.
                    </p>
                    <a href="search.php?type=package" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700">
                        <span>Browse Packages</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <!-- Service 2: Luxury Resorts & Verified Hotels -->
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-50 border border-slate-200 hover:border-emerald-500 transition-all group space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-hotel"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 font-space">Handpicked Stays &amp; Resorts</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        From heritage havelis to overwater villas and mountain cottages. Inspect authentic guest room photos, amenities, meal inclusions, and lock rooms with zero hassle.
                    </p>
                    <a href="search.php?type=hotel" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                        <span>Explore Hotels</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <!-- Service 3: Flights & Custom Itinerary Concierge -->
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-50 border border-slate-200 hover:border-sky-500 transition-all group space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-plane-departure"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 font-space">Flights &amp; Custom Concierge</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Search live domestic and international airfares, request personalized multi-city schedules, and get fast reservation assistance with airline partner discounts.
                    </p>
                    <a href="flights.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-700">
                        <span>Search Flights</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 5. WHY TRAVELERS CHOOSE US (4 CORE PILLARS)                                 -->
    <!-- ========================================================================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="bg-slate-900 text-white rounded-3xl border border-slate-800 p-8 sm:p-12 lg:p-14 relative overflow-hidden">
            <!-- Decorative Accent -->
            <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-10">
                <div class="max-w-2xl space-y-2">
                    <span class="text-xs font-bold uppercase tracking-widest text-teal-400">The <?php echo htmlspecialchars($siteName); ?> Advantage</span>
                    <h2 class="text-2xl sm:text-4xl font-black font-space tracking-tight">
                        Why Discerning Travelers Choose Us
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 font-normal">
                        We obsess over the micro-details so you can focus on making memories.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2.5">
                        <div class="w-9 h-9 rounded-xl bg-teal-400/20 text-teal-300 flex items-center justify-center text-base">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <h4 class="text-sm font-bold text-white font-space">Token Advance</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Book with partial deposit (e.g. 30%), pay the remaining balance directly on check-in.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center text-base">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <h4 class="text-sm font-bold text-white font-space">24x7 Trip Escort</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Dedicated destination concierge assigned to your booking from departure to arrival.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center text-base">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <h4 class="text-sm font-bold text-white font-space">Transparent Invoices</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Detailed breakdown of cabs, stays, entry passes, and taxes. Zero surprise expenses.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-400/20 text-indigo-300 flex items-center justify-center text-base">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <h4 class="text-sm font-bold text-white font-space">Handcrafted Quality</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">No rushed cookie-cutter schedules. Balanced pace with authentic local experiences.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

   

    <!-- ========================================================================= -->
    <!-- 7. CALL TO ACTION: START YOUR JOURNEY                                     -->
    <!-- ========================================================================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6">
        <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-brand-600 via-teal-700 to-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 border border-brand-500/40">
            <div class="space-y-2 text-center md:text-left max-w-xl">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30">
                    Ready For Your Next Adventure?
                </span>
                <h3 class="text-2xl sm:text-3xl font-black font-space">Let's Plan Your Dream Holiday Today</h3>
                <p class="text-xs sm:text-sm text-teal-100">Contact our concierge desk for free customized quotes or instant online booking.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="search.php?type=package" class="px-6 py-3 rounded-full bg-white text-brand-700 hover:bg-slate-100 font-bold text-xs uppercase tracking-wider transition-all active:scale-95 shadow-sm">
                    <span>Browse Packages</span>
                </a>
                <a href="contact.php" class="px-6 py-3 rounded-full bg-black/30 hover:bg-black/40 text-white font-bold text-xs uppercase tracking-wider border border-white/30 transition-all">
                    <span>Contact Us</span>
                </a>
            </div>
        </div>
    </section>

</main>

<?php require_once 'components/footer.php'; ?>
