<?php
/**
 * How It Works Component - Orion Advent
 * - 3-Step Hassle-Free Booking Process
 * - Step 01: Discover & Customize
 * - Step 02: Lock Rates With Token Advance
 * - Step 03: Confirmed Vouchers & WhatsApp Concierge
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */

$hiwWhatsapp = function_exists('getSetting') ? getSetting('site_whatsapp', '919876543210') : '919876543210';
$hiwSiteName = function_exists('getSetting') ? getSetting('site_name', 'Orion Advent') : 'Orion Advent';
$hiwWaMsg = urlencode("Hi {$hiwSiteName}, I want to plan a vacation");

$bookingSteps = [
    [
        'step_num' => '01',
        'step_label' => 'Step 01 / Discovery',
        'icon' => 'fa-solid fa-compass',
        'icon_color' => 'bg-brand-50 text-brand-600 border-brand-200',
        'title' => 'Pick or Customize Your Vibe',
        'description' => 'Explore handpicked domestic and international tour packages, or tell our holiday specialist your exact dates, budget, and travel preferences to create a bespoke itinerary.',
        'features' => [
            ['name' => '100% Flexible Itineraries', 'icon' => 'fa-solid fa-sliders'],
          
            ['name' => 'Custom Hotel & Room Views', 'icon' => 'fa-solid fa-hotel']
        ]
    ],
    [
        'step_num' => '02',
        'step_label' => 'Step 02 / Confirmation',
        'icon' => 'fa-solid fa-shield-halved',
        'icon_color' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'title' => 'Lock Rates With Token Advance',
        'description' => 'Confirm wholesale hotel rooms and flight seats with a nominal refundable token deposit. Pay the remaining balance close to departure. Free cancellation up to 48 hours.',
        'features' => [
            ['name' => 'Price-Lock Guarantee', 'icon' => 'fa-solid fa-lock'],
            
            ['name' => 'Free 48-Hour Cancellation', 'icon' => 'fa-solid fa-circle-check']
        ]
    ],
    [
        'step_num' => '03',
        'step_label' => 'Step 03 / Seamless Travel',
        'icon' => 'fa-solid fa-paper-plane',
        'icon_color' => 'bg-sky-50 text-sky-600 border-sky-200',
        'title' => 'Get Vouchers & WhatsApp Concierge',
        'description' => 'Receive confirmed hotel vouchers, flight e-tickets, and GST invoices instantly on email. A dedicated trip manager connects on WhatsApp to coordinate airport pickups and 24x7 support.',
        'features' => [
            ['name' => 'Instant Digital Vouchers', 'icon' => 'fa-solid fa-file-invoice'],
            ['name' => '24/7 WhatsApp Concierge', 'icon' => 'fa-solid fa-comments'],
          
        ]
    ]
];
?>
<!-- ==========================================
     HOW IT WORKS / 3-STEP HASSLE-FREE BOOKING
     - Crystal-clear booking transparency roadmap
     - Step 01 -> Step 02 -> Step 03
     - 100% Flat, Border-First (Zero Shadows)
     - Font Awesome 6 Icons Only (Zero Emojis)
=========================================== -->
<section id="how-it-works" class="py-16 md:py-24 px-4 sm:px-8 xl:px-12 bg-slate-50/70 border-b border-slate-200 relative overflow-hidden">
    
    <!-- Ambient Background Accents -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-brand-100/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-emerald-100/30 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10">

        <!-- 1. Section Header with Generated Inline Capsule Image -->
        <div class="max-w-3xl mx-auto text-center space-y-3 mb-12 sm:mb-16">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-route text-brand-600"></i>
                <span>Seamless 3-Step Journey</span>
            </div>

            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>Booking Your Holiday In</span>
                <span class="inline-block align-middle mx-1.5 sm:mx-2.5">
                    <img src="assets/images/banner/heading-process-01.jpg" 
                         alt="Traveler with Passport and Itinerary" 
                         class="h-8 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">3 Easy Steps</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto">
                No confusing paperwork, no hidden checkout traps. Here is how your dream vacation goes from exciting idea to boarding your flight.
            </p>
        </div>

        <!-- 2. Three Step Cards in Responsive Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 relative mb-12">
            
            <?php foreach ($bookingSteps as $step): ?>
                <div class="bg-white rounded-3xl border border-slate-200 hover:border-brand-500 transition-all p-6 sm:p-7 flex flex-col justify-between group relative select-none">
                    
                    <div>
                        <!-- Step Top Bar: Number & Icon -->
                        <div class="flex items-center justify-between mb-5">
                            <span class="w-12 h-12 rounded-2xl flex items-center justify-center border font-black text-lg transition-transform group-hover:scale-105 <?= $step['icon_color'] ?>">
                                <i class="<?= htmlspecialchars($step['icon']) ?>"></i>
                            </span>

                            <span class="text-3xl sm:text-4xl font-black text-slate-200 group-hover:text-brand-200 transition-colors">
                                <?= htmlspecialchars($step['step_num']) ?>
                            </span>
                        </div>

                        <!-- Step Subtitle & Title -->
                        <span class="text-[11px] font-bold uppercase tracking-wider text-brand-600 block mb-1">
                            <?= htmlspecialchars($step['step_label']) ?>
                        </span>

                        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 group-hover:text-brand-600 transition leading-snug mb-3">
                            <?= htmlspecialchars($step['title']) ?>
                        </h3>

                        <!-- Step Description -->
                        <p class="text-xs sm:text-[13px] text-slate-500 font-normal leading-relaxed mb-6">
                            <?= htmlspecialchars($step['description']) ?>
                        </p>
                    </div>

                    <!-- Step Feature Chips -->
                    <div class="pt-4 border-t border-slate-100 space-y-2">
                        <?php foreach ($step['features'] as $feat): ?>
                            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-700">
                                <span class="w-5 h-5 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0">
                                    <i class="<?= htmlspecialchars($feat['icon']) ?> text-[10px] text-brand-600"></i>
                                </span>
                                <span><?= htmlspecialchars($feat['name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

        <!-- 3. Bottom Action Callout Bar -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-6">
            
            <div class="flex items-center space-x-4 text-left">
                <span class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 border border-brand-200 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-headset text-lg"></i>
                </span>
                <div>
                    <h4 class="text-base sm:text-lg font-black text-slate-900 leading-tight">
                        Need a custom trip plan or group discount?
                    </h4>
                    <span class="text-xs text-slate-500 font-normal block mt-0.5">
                        Our destination specialists respond within 15 minutes with customized itineraries & live availability.
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-3 shrink-0 w-full sm:w-auto">
                <a href="#hero" 
                   class="flex-1 sm:flex-none inline-flex items-center justify-center space-x-2 px-5 py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 group">
                    <span>Browse Packages</span>
                    <i class="fa-solid fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                </a>

                <a href="https://wa.me/<?= htmlspecialchars($hiwWhatsapp) ?>?text=<?= $hiwWaMsg ?>" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="flex-1 sm:flex-none inline-flex items-center justify-center space-x-2 px-5 py-3 rounded-full bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 hover:border-emerald-600 font-bold text-xs uppercase tracking-wider transition-all">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>WhatsApp Us</span>
                </a>
            </div>

        </div>

    </div>
</section>
