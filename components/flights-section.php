<?php
/**
 * Flight Deals Component - Orion Advent
 * API-Ready Architecture with Destination Photography & Interactive Slider:
 * - Data structured identically to standard Flight Aggregator APIs (Amadeus, TBO, TripJack, Skyscanner)
 * - Rich Destination Imagery for each route
 * - Smooth Interactive Carousel with Auto-slide & Hover-only Navigation
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */

$flightDeals = [
    [
        'id' => 'FL-IND-101',
        'airline' => 'IndiGo',
        'airline_code' => '6E',
        'flight_number' => '6E-2134',
        'airline_badge' => 'bg-indigo-600 text-white',
        'category' => 'all domestic delhi',
        'badge' => 'Lowest Fare',
        'badge_class' => 'bg-emerald-600 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'Mumbai, Maharashtra',
        'from_city' => 'New Delhi',
        'from_code' => 'DEL',
        'from_time' => '06:15 AM',
        'from_airport' => 'IGI Airport, T3',
        'to_city' => 'Mumbai',
        'to_code' => 'BOM',
        'to_time' => '08:30 AM',
        'to_airport' => 'CSMIA, T2',
        'duration' => '2h 15m',
        'stops' => 'Non-Stop',
        'baggage' => '15 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'Snacks on Sale',
        'refundable' => 'Free Reschedule',
        'fare' => 3899,
        'original_fare' => 4999,
    ],
    [
        'id' => 'FL-AI-102',
        'airline' => 'Air India',
        'airline_code' => 'AI',
        'flight_number' => 'AI-804',
        'airline_badge' => 'bg-rose-600 text-white',
        'category' => 'all domestic bangalore',
        'badge' => 'Complimentary Meal',
        'badge_class' => 'bg-amber-500 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1587474260584-136574528ed5?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'New Delhi, NCR',
        'from_city' => 'Bengaluru',
        'from_code' => 'BLR',
        'from_time' => '09:40 AM',
        'from_airport' => 'Kempegowda Intl, T2',
        'to_city' => 'New Delhi',
        'to_code' => 'DEL',
        'to_time' => '12:25 PM',
        'to_airport' => 'IGI Airport, T3',
        'duration' => '2h 45m',
        'stops' => 'Non-Stop',
        'baggage' => '15 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'Free Hot Meal',
        'refundable' => 'Partially Refundable',
        'fare' => 4499,
        'original_fare' => 5600,
    ],
    [
        'id' => 'FL-AK-103',
        'airline' => 'Akasa Air',
        'airline_code' => 'QP',
        'flight_number' => 'QP-1312',
        'airline_badge' => 'bg-orange-500 text-white',
        'category' => 'all domestic mumbai',
        'badge' => 'Trending Beach Route',
        'badge_class' => 'bg-brand-600 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'Goa Beach Coast',
        'from_city' => 'Mumbai',
        'from_code' => 'BOM',
        'from_time' => '11:10 AM',
        'from_airport' => 'CSMIA, T1',
        'to_city' => 'Goa Mopa',
        'to_code' => 'GOX',
        'to_time' => '12:25 PM',
        'to_airport' => 'Manohar Intl Airport',
        'duration' => '1h 15m',
        'stops' => 'Non-Stop',
        'baggage' => '15 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'Café Akasa Available',
        'refundable' => 'Instant Refundable',
        'fare' => 2799,
        'original_fare' => 3650,
    ],
    [
        'id' => 'FL-UK-104',
        'airline' => 'Vistara',
        'airline_code' => 'UK',
        'flight_number' => 'UK-611',
        'airline_badge' => 'bg-purple-600 text-white',
        'category' => 'all domestic delhi',
        'badge' => 'Valley Favorite',
        'badge_class' => 'bg-sky-600 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'Srinagar, Kashmir',
        'from_city' => 'New Delhi',
        'from_code' => 'DEL',
        'from_time' => '10:00 AM',
        'from_airport' => 'IGI Airport, T3',
        'to_city' => 'Srinagar',
        'to_code' => 'SXR',
        'to_time' => '11:35 AM',
        'to_airport' => 'Sheikh ul-Alam Intl',
        'duration' => '1h 35m',
        'stops' => 'Non-Stop',
        'baggage' => '15 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'Gourmet Meals Included',
        'refundable' => 'Free Reschedule',
        'fare' => 4999,
        'original_fare' => 6200,
    ],
    [
        'id' => 'FL-IND-105',
        'airline' => 'IndiGo',
        'airline_code' => '6E',
        'flight_number' => '6E-542',
        'airline_badge' => 'bg-indigo-600 text-white',
        'category' => 'all domestic bangalore',
        'badge' => 'IT Express',
        'badge_class' => 'bg-teal-600 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'Bengaluru, Karnataka',
        'from_city' => 'Kolkata',
        'from_code' => 'CCU',
        'from_time' => '07:20 AM',
        'from_airport' => 'Netaji Subhash Intl',
        'to_city' => 'Bengaluru',
        'to_code' => 'BLR',
        'to_time' => '09:50 AM',
        'to_airport' => 'Kempegowda Intl, T1',
        'duration' => '2h 30m',
        'stops' => 'Non-Stop',
        'baggage' => '15 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'Snacks Available',
        'refundable' => 'Refundable with Fee',
        'fare' => 4199,
        'original_fare' => 5299,
    ],
    [
        'id' => 'FL-EK-106',
        'airline' => 'Emirates',
        'airline_code' => 'EK',
        'flight_number' => 'EK-501',
        'airline_badge' => 'bg-red-600 text-white',
        'category' => 'all international mumbai',
        'badge' => 'Global Favorite',
        'badge_class' => 'bg-emerald-600 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'Dubai, UAE',
        'from_city' => 'Mumbai',
        'from_code' => 'BOM',
        'from_time' => '04:30 AM',
        'from_airport' => 'CSMIA, T2',
        'to_city' => 'Dubai',
        'to_code' => 'DXB',
        'to_time' => '06:25 AM',
        'to_airport' => 'Dubai Intl, T3',
        'duration' => '3h 25m',
        'stops' => 'Non-Stop',
        'baggage' => '30 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'Multi-Course Dining',
        'refundable' => 'Free Cancellation',
        'fare' => 12899,
        'original_fare' => 15500,
    ],
    [
        'id' => 'FL-TG-107',
        'airline' => 'Thai Airways',
        'airline_code' => 'TG',
        'flight_number' => 'TG-316',
        'airline_badge' => 'bg-purple-600 text-white',
        'category' => 'all international delhi',
        'badge' => 'Visa-Free Entry',
        'badge_class' => 'bg-emerald-600 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'Bangkok, Thailand',
        'from_city' => 'New Delhi',
        'from_code' => 'DEL',
        'from_time' => '11:55 PM',
        'from_airport' => 'IGI Airport, T3',
        'to_city' => 'Bangkok',
        'to_code' => 'BKK',
        'to_time' => '05:40 AM',
        'to_airport' => 'Suvarnabhumi Intl',
        'duration' => '4h 15m',
        'stops' => 'Non-Stop',
        'baggage' => '25 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'Royal Orchid Meals',
        'refundable' => 'Partially Refundable',
        'fare' => 14499,
        'original_fare' => 18200,
    ],
    [
        'id' => 'FL-SQ-108',
        'airline' => 'Singapore Airlines',
        'airline_code' => 'SQ',
        'flight_number' => 'SQ-503',
        'airline_badge' => 'bg-amber-600 text-white',
        'category' => 'all international bangalore',
        'badge' => 'World 5★ Airline',
        'badge_class' => 'bg-brand-600 text-white',
        'destination_image' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=800&q=80',
        'destination_title' => 'Singapore City',
        'from_city' => 'Bengaluru',
        'from_code' => 'BLR',
        'from_time' => '11:10 PM',
        'from_airport' => 'Kempegowda Intl, T2',
        'to_city' => 'Singapore',
        'to_code' => 'SIN',
        'to_time' => '06:05 AM',
        'to_airport' => 'Changi Airport, T3',
        'duration' => '4h 25m',
        'stops' => 'Non-Stop',
        'baggage' => '25 Kg Check-in',
        'cabin' => '7 Kg Cabin',
        'meal' => 'World Class Dining',
        'refundable' => 'Flexible Date Change',
        'fare' => 16999,
        'original_fare' => 20500,
    ]
];
?>
<!-- ==========================================
     POPULAR FLIGHT DEALS (IMAGE SLIDER COMPONENT)
     - Interactive Horizontal Slider with Auto-slide
     - Navigation Arrows Visible on Row Hover
     - Destination Imagery with Airline Badge & Route Overlay
     - Clean Flight Schedule Trajectory, Baggage Info & Direct Booking CTA
     - 100% Flat, Border-First (Zero Shadows)
     - Font Awesome 6 Icons Only (Zero Emojis)
=========================================== -->
<section id="flight-deals" class="py-16 md:py-20 px-4 sm:px-8 xl:px-12 bg-slate-50/60 border-b border-slate-200">
    <div class="max-w-7xl mx-auto">

        <!-- 1. Section Header (Domestic Style with Inline Capsule Image) -->
        <div class="max-w-3xl mx-auto text-center space-y-3 mb-10">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <i class="fa-solid fa-plane-departure text-brand-600"></i>
                <span>Direct & Connecting Flight Deals</span>
            </div>

            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <span>Trending Flight </span>
                <span class="inline-block align-middle mx-1.5 sm:mx-2.5">
                    <img src="assets/images/banner/heading-flight-01.jpg" 
                         alt="Airplane in Clouds" 
                         class="h-8 sm:h-11 md:h-12 w-auto rounded-full object-cover border border-slate-200 hover:scale-105 transition-transform duration-300 select-none">
                </span>
                <span class="text-brand-600">Routes</span>
            </h2>

            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto">
                Handpicked non-stop flight routes with verified airline fares, guaranteed baggage allowance, and instant web check-in confirmation.
            </p>
        </div>

        <!-- 2. Flight Cards Carousel Slider with Nav Visible ONLY on Row Hover -->
        <div class="relative group/flight-slider flight-slider-container">

            <!-- Prev Navigation Button (Appears only on row hover) -->
            <button type="button" 
                    id="flightSlidePrev" 
                    aria-label="Previous Flights"
                    class="absolute -left-2 sm:-left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white border border-slate-300 text-slate-700 hover:text-brand-600 hover:border-brand-500 hover:bg-slate-50 flex items-center justify-center transition active:scale-95 disabled:opacity-0 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>

            <!-- Next Navigation Button (Appears only on row hover) -->
            <button type="button" 
                    id="flightSlideNext" 
                    aria-label="Next Flights"
                    class="absolute -right-2 sm:-right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white border border-slate-300 text-slate-700 hover:text-brand-600 hover:border-brand-500 hover:bg-slate-50 flex items-center justify-center transition active:scale-95 disabled:opacity-0 disabled:pointer-events-none">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </button>

            <!-- Horizontal Scrollable Slider (Snap-X) -->
            <div id="flightSlider" 
                 class="flex items-stretch space-x-5 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 pt-1 px-1"
                 style="scrollbar-width: none; -ms-overflow-style: none;">

                <?php foreach ($flightDeals as $flight): ?>
                    <!-- Flight Card with Destination Imagery (Exactly 3 cards on desktop) -->
                    <div class="flight-card flight-card-3col snap-start bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition-all flex flex-col overflow-hidden group" data-category="<?= htmlspecialchars($flight['category']) ?>">
                        
                        <!-- Destination Image with Overlay Badges -->
                        <div class="h-44 sm:h-48 overflow-hidden relative border-b border-slate-100">
                            <img src="<?= htmlspecialchars($flight['destination_image']) ?>" 
                                 alt="<?= htmlspecialchars($flight['destination_title']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <!-- Gradient overlay for text contrast -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/15 to-transparent"></div>

                            <!-- Airline Badge Top-Left -->
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[11px] font-bold <?= $flight['airline_badge'] ?> flex items-center space-x-1.5 border border-white/20">
                                <i class="fa-solid fa-plane text-[10px]"></i>
                                <span><?= htmlspecialchars($flight['airline']) ?></span>
                                <span class="opacity-80 font-normal">&bull; <?= htmlspecialchars($flight['flight_number']) ?></span>
                            </span>

                            <!-- Route Highlight Badge Top-Right -->
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[11px] font-bold <?= $flight['badge_class'] ?>">
                                <?= htmlspecialchars($flight['badge']) ?>
                            </span>

                            <!-- Destination Name & Airport Route Bottom-Left -->
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white">
                                <div>
                                    <span class="text-xs font-bold text-white block"><?= htmlspecialchars($flight['destination_title']) ?></span>
                                    <span class="text-[10px] text-white/80 font-medium"><?= htmlspecialchars($flight['to_airport']) ?></span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md bg-white/20 backdrop-blur-sm text-[10px] font-bold text-white border border-white/30">
                                    <?= htmlspecialchars($flight['stops']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body: Flight Schedule & Trajectory -->
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                            
                            <!-- Schedule Trajectory Bar -->
                            <div class="py-2.5 px-3 bg-slate-50/70 rounded-xl border border-slate-100 grid grid-cols-3 items-center text-center">
                                <!-- Origin -->
                                <div class="text-left">
                                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight block"><?= htmlspecialchars($flight['from_code']) ?></span>
                                    <span class="text-[11px] font-bold text-slate-700 block"><?= htmlspecialchars($flight['from_time']) ?></span>
                                    <span class="text-[10px] text-slate-400 truncate block"><?= htmlspecialchars($flight['from_city']) ?></span>
                                </div>

                                <!-- Plane Trajectory -->
                                <div class="flex flex-col items-center justify-center px-1">
                                    <span class="text-[10px] font-bold text-slate-500 mb-0.5"><?= htmlspecialchars($flight['duration']) ?></span>
                                    <div class="w-full relative flex items-center justify-center my-0.5">
                                        <div class="w-full h-0.5 bg-slate-200"></div>
                                        <div class="absolute w-5 h-5 rounded-full bg-white border border-slate-300 text-brand-600 flex items-center justify-center text-[9px]">
                                            <i class="fa-solid fa-plane"></i>
                                        </div>
                                    </div>
                                    <span class="text-[9px] text-emerald-600 font-bold"><?= htmlspecialchars($flight['stops']) ?></span>
                                </div>

                                <!-- Destination -->
                                <div class="text-right">
                                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight block"><?= htmlspecialchars($flight['to_code']) ?></span>
                                    <span class="text-[11px] font-bold text-slate-700 block"><?= htmlspecialchars($flight['to_time']) ?></span>
                                    <span class="text-[10px] text-slate-400 truncate block"><?= htmlspecialchars($flight['to_city']) ?></span>
                                </div>
                            </div>

                            <!-- Amenities Row -->
                            <div class="py-1.5 border-y border-slate-100 flex items-center justify-between text-[10px] text-slate-600 font-medium gap-1 overflow-hidden">
                                <span class="flex items-center space-x-1 truncate">
                                    <i class="fa-solid fa-suitcase-rolling text-brand-600 text-[11px] shrink-0"></i>
                                    <span class="truncate"><?= htmlspecialchars($flight['baggage']) ?></span>
                                </span>
                                <span class="flex items-center space-x-1 truncate">
                                    <i class="fa-solid fa-utensils text-brand-600 text-[11px] shrink-0"></i>
                                    <span class="truncate"><?= htmlspecialchars($flight['meal']) ?></span>
                                </span>
                                <span class="flex items-center space-x-1 truncate">
                                    <i class="fa-solid fa-rotate-left text-brand-600 text-[11px] shrink-0"></i>
                                    <span class="truncate"><?= htmlspecialchars($flight['refundable']) ?></span>
                                </span>
                            </div>

                            <!-- Pricing & Action Row -->
                            <div class="pt-1 flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Starting From</span>
                                    <div class="flex items-baseline space-x-1.5">
                                        <span class="text-lg font-black text-slate-900">₹<?= number_format($flight['fare']) ?></span>
                                        <span class="text-xs text-slate-400 line-through">₹<?= number_format($flight['original_fare']) ?></span>
                                    </div>
                                </div>

                                <a href="flights.php?from=<?= urlencode($flight['from_code']) ?>&to=<?= urlencode($flight['to_code']) ?>&type=oneway" 
                                   class="book-flight-btn px-4 py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 inline-flex items-center space-x-1.5"
                                   data-flight-id="<?= htmlspecialchars($flight['id']) ?>"
                                   data-from="<?= htmlspecialchars($flight['from_code']) ?>"
                                   data-to="<?= htmlspecialchars($flight['to_code']) ?>">
                                    <span>Book Flight</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

    </div>
</section>
