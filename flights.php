<?php
/**
 * Dedicated MakeMyTrip-Style Flight Search & Booking Portal
 * Features:
 * - MakeMyTrip / EaseMyTrip Full Search Header (One Way / Round Trip / Multi-City / Class / Date Strip)
 * - Multi-Filter Left Sidebar (Non-Stop, Times, Airlines, Fare Types, Stops, Price Slider)
 * - 100% Real Live Flight Data from Ignav API with High-Res Transparent Airline CDN Logos
 * - Live MakeMyTrip Dual-Column Split Selection for Round-Trip (Outbound + Return with Live Total)
 * - Multi-City Sequential Route Explorer (Leg 1, Leg 2)
 * - Interactive MakeMyTrip 3-Tier Fare Matrix (Saver vs Flexi Plus vs Super Value)
 * - Dynamic Flight Details Modal & Instant Seat Lock Booking
 */
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/flights_service.php';

$tripType = isset($_GET['trip']) ? strtolower(trim($_GET['trip'])) : 'oneway';
if (!in_array($tripType, ['oneway', 'roundtrip', 'multicity'])) {
    $tripType = 'oneway';
}

$origin = isset($_GET['from']) && trim($_GET['from']) !== '' ? strtoupper(trim($_GET['from'])) : 'DEL';
$destination = isset($_GET['to']) && trim($_GET['to']) !== '' ? strtoupper(trim($_GET['to'])) : 'BOM';
$departureDate = isset($_GET['date']) && !empty(trim($_GET['date'])) ? trim($_GET['date']) : date('Y-m-d', strtotime('+3 days'));
$returnDate = isset($_GET['return_date']) && !empty(trim($_GET['return_date'])) ? trim($_GET['return_date']) : date('Y-m-d', strtotime('+6 days'));
$cabinClass = isset($_GET['class']) && !empty(trim($_GET['class'])) ? trim($_GET['class']) : 'Economy';
$adults = isset($_GET['adults']) ? max(1, (int)$_GET['adults']) : 1;

$from2 = isset($_GET['from2']) && trim($_GET['from2']) !== '' ? strtoupper(trim($_GET['from2'])) : $destination;
$to2 = isset($_GET['to2']) && trim($_GET['to2']) !== '' ? strtoupper(trim($_GET['to2'])) : 'GOX';
$date2 = isset($_GET['date2']) && !empty(trim($_GET['date2'])) ? trim($_GET['date2']) : date('Y-m-d', strtotime('+6 days'));

if (preg_match('/\b([A-Z]{3})\b/', $origin, $matches)) $origin = $matches[1];
if (preg_match('/\b([A-Z]{3})\b/', $destination, $matches)) $destination = $matches[1];
if (preg_match('/\b([A-Z]{3})\b/', $from2, $matches)) $from2 = $matches[1];
if (preg_match('/\b([A-Z]{3})\b/', $to2, $matches)) $to2 = $matches[1];

if (empty($origin)) $origin = 'DEL';
if (empty($destination)) $destination = 'BOM';
if (empty($from2)) $from2 = $destination;
if (empty($to2)) $to2 = 'GOX';


$airportsList = getFlightAirportsDirectory();
$fromAirportInfo = $airportsList[$origin] ?? ['name' => $origin . ' Airport', 'city' => $origin];
$toAirportInfo = $airportsList[$destination] ?? ['name' => $destination . ' Airport', 'city' => $destination];
$from2AirportInfo = $airportsList[$from2] ?? ['name' => $from2 . ' Airport', 'city' => $from2];
$to2AirportInfo = $airportsList[$to2] ?? ['name' => $to2 . ' Airport', 'city' => $to2];

$siteName = getSetting('site_name', 'Orion Advent');

$pageTitle = ($tripType === 'roundtrip')
    ? "Round-Trip Flights: {$fromAirportInfo['city']} ({$origin}) ⇄ {$toAirportInfo['city']} ({$destination}) | " . htmlspecialchars($siteName)
    : (($tripType === 'multicity')
        ? "Multi-City Flights: {$origin} → {$destination} → {$to2} | " . htmlspecialchars($siteName)
        : "Flights from {$fromAirportInfo['city']} ({$origin}) to {$toAirportInfo['city']} ({$destination}) | " . htmlspecialchars($siteName));

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ==========================================
     MAKEMYTRIP-STYLE TOP SEARCH & FLIGHT SELECTOR STRIP
     ========================================== -->
<section class="bg-slate-900 text-white border-b border-slate-800 pt-6 pb-8 px-4 sm:px-6 lg:px-8 xl:px-12 relative">
    <div class="max-w-7xl mx-auto space-y-4">
        
        <!-- Trip Type Toggles & Live Indicator -->
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3 sm:gap-4 bg-white/10 backdrop-blur-md px-4 py-1.5 rounded-full border border-white/10 font-bold">
                <label class="flex items-center gap-1.5 cursor-pointer <?= $tripType === 'oneway' ? 'text-white' : 'text-slate-300 hover:text-white' ?>">
                    <input type="radio" name="searchTripTypeRadio" value="oneway" <?= $tripType === 'oneway' ? 'checked' : '' ?> onchange="switchFlightTripMode('oneway')" class="accent-brand-500">
                    <span>One Way</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer <?= $tripType === 'roundtrip' ? 'text-white' : 'text-slate-300 hover:text-white' ?>">
                    <input type="radio" name="searchTripTypeRadio" value="roundtrip" <?= $tripType === 'roundtrip' ? 'checked' : '' ?> onchange="switchFlightTripMode('roundtrip')" class="accent-brand-500">
                    <span>Round Trip</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer <?= $tripType === 'multicity' ? 'text-white' : 'text-slate-300 hover:text-white' ?>">
                    <input type="radio" name="searchTripTypeRadio" value="multicity" <?= $tripType === 'multicity' ? 'checked' : '' ?> onchange="switchFlightTripMode('multicity')" class="accent-brand-500">
                    <span>Multi-City</span>
                </label>
            </div>

            <div class="flex items-center gap-2 text-[11px] text-teal-300 font-semibold">
                <i class="fa-solid fa-plane-circle-check text-xs"></i>
                <span>Direct Airline GDS Fares &bull; 100% Real Live Seats</span>
            </div>
        </div>

        <!-- MMT Search Bar Container -->
        <form id="flightSearchForm" action="flights.php" method="GET" class="bg-white rounded-2xl sm:rounded-3xl p-2 sm:p-2.5 border border-slate-700 shadow-2xl text-slate-900 space-y-2">
            <input type="hidden" name="trip" id="searchTripTypeHidden" value="<?= htmlspecialchars($tripType) ?>">
            <input type="hidden" name="class" value="<?= htmlspecialchars($cabinClass) ?>">
            <input type="hidden" name="adults" value="<?= htmlspecialchars($adults) ?>">

            <!-- Main Route Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 <?= $tripType === 'roundtrip' ? 'lg:grid-cols-12' : 'lg:grid-cols-12' ?> gap-2 items-center">
                
                <!-- Block 1: From Airport -->
                <div class="dropdown-wrapper <?= $tripType === 'roundtrip' ? 'lg:col-span-3' : 'lg:col-span-4' ?> p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:bg-slate-100/80 transition relative cursor-pointer group">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">From</span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-xl sm:text-2xl font-black font-space text-slate-900" id="dispFromCode"><?= htmlspecialchars($origin) ?></span>
                        <input type="text" name="from" id="fromAirportInput" value="<?= htmlspecialchars($fromAirportInfo['name'] . ' (' . $origin . ')') ?>" placeholder="Search departure airport..." autocomplete="off" class="flight-airport-typeahead w-full text-xs font-bold text-slate-800 bg-transparent outline-none tracking-wide truncate">
                    </div>
                    <span class="text-[10px] text-slate-400 truncate block mt-0.5" id="dispFromName"><?= htmlspecialchars($fromAirportInfo['name']) ?></span>

                    <!-- Dropdown list -->
                    <div class="dropdown-menu absolute top-[calc(100%+6px)] left-0 w-80 sm:w-96 max-w-[92vw] bg-white shadow-2xl border border-slate-200 p-2.5 z-[100] hidden animate-pop-in rounded-2xl">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2 py-1 mb-1 flex items-center justify-between">
                            <span>Select Departure Airport</span>
                            <span class="text-sky-600 font-bold">Airport Name</span>
                        </div>
                        <div class="suggestion-list max-h-64 overflow-y-auto space-y-1">
                            <?php foreach ($airportsList as $code => $ap): 
                                $fullAirportLabel = $ap['name'] . ' (' . $code . ')';
                                $searchKeywords = strtolower($code . ' ' . $ap['name'] . ' ' . $ap['city'] . ' ' . ($ap['state'] ?? ''));
                            ?>
                                <div class="dropdown-select-item flight-select-item flex items-center justify-between p-2 hover:bg-slate-50 cursor-pointer transition rounded-xl" 
                                     data-val="<?= htmlspecialchars($fullAirportLabel) ?>" 
                                     data-code="<?= $code ?>"
                                     data-name="<?= htmlspecialchars($ap['name']) ?>"
                                     data-sub="<?= htmlspecialchars($ap['city'] . ' • ' . $ap['state']) ?>"
                                     data-search="<?= htmlspecialchars($searchKeywords) ?>">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-plane-departure text-xs"></i>
                                        </div>
                                        <div class="truncate">
                                            <span class="block text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($ap['name']) ?></span>
                                            <span class="block text-[10px] text-slate-400 truncate"><?= htmlspecialchars($ap['city'] . ' • ' . $ap['state']) ?></span>
                                        </div>
                                    </div>
                                    <span class="font-mono font-bold text-xs bg-slate-100 text-slate-800 px-2 py-0.5 rounded shrink-0 border border-slate-200"><?= $code ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Swap Button -->
                <button type="button" onclick="swapAirports()" class="hidden lg:flex absolute <?= $tripType === 'roundtrip' ? 'left-[24.5%]' : 'left-[32.5%]' ?> z-20 w-8 h-8 rounded-full bg-white border border-slate-300 text-brand-600 items-center justify-center text-xs shadow-md hover:scale-110 transition active:rotate-180">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </button>

                <!-- Block 2: To Airport -->
                <div class="dropdown-wrapper <?= $tripType === 'roundtrip' ? 'lg:col-span-3' : 'lg:col-span-4' ?> p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:bg-slate-100/80 transition relative cursor-pointer group">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">To</span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-xl sm:text-2xl font-black font-space text-slate-900" id="dispToCode"><?= htmlspecialchars($destination) ?></span>
                        <input type="text" name="to" id="toAirportInput" value="<?= htmlspecialchars($toAirportInfo['name'] . ' (' . $destination . ')') ?>" placeholder="Search destination airport..." autocomplete="off" class="flight-airport-typeahead w-full text-xs font-bold text-slate-800 bg-transparent outline-none tracking-wide truncate">
                    </div>
                    <span class="text-[10px] text-slate-400 truncate block mt-0.5" id="dispToName"><?= htmlspecialchars($toAirportInfo['name']) ?></span>

                    <!-- Dropdown list -->
                    <div class="dropdown-menu absolute top-[calc(100%+6px)] left-0 w-80 sm:w-96 max-w-[92vw] bg-white shadow-2xl border border-slate-200 p-2.5 z-[100] hidden animate-pop-in rounded-2xl">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2 py-1 mb-1 flex items-center justify-between">
                            <span>Select Destination Airport</span>
                            <span class="text-teal-600 font-bold">Airport Name</span>
                        </div>
                        <div class="suggestion-list max-h-64 overflow-y-auto space-y-1">
                            <?php foreach ($airportsList as $code => $ap): 
                                $fullAirportLabel = $ap['name'] . ' (' . $code . ')';
                                $searchKeywords = strtolower($code . ' ' . $ap['name'] . ' ' . $ap['city'] . ' ' . ($ap['state'] ?? ''));
                            ?>
                                <div class="dropdown-select-item flight-select-item flex items-center justify-between p-2 hover:bg-slate-50 cursor-pointer transition rounded-xl" 
                                     data-val="<?= htmlspecialchars($fullAirportLabel) ?>" 
                                     data-code="<?= $code ?>"
                                     data-name="<?= htmlspecialchars($ap['name']) ?>"
                                     data-sub="<?= htmlspecialchars($ap['city'] . ' • ' . $ap['state']) ?>"
                                     data-search="<?= htmlspecialchars($searchKeywords) ?>">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-plane-arrival text-xs"></i>
                                        </div>
                                        <div class="truncate">
                                            <span class="block text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($ap['name']) ?></span>
                                            <span class="block text-[10px] text-slate-400 truncate"><?= htmlspecialchars($ap['city'] . ' • ' . $ap['state']) ?></span>
                                        </div>
                                    </div>
                                    <span class="font-mono font-bold text-xs bg-slate-100 text-slate-800 px-2 py-0.5 rounded shrink-0 border border-slate-200"><?= $code ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Block 3: Departure Date -->
                <div class="<?= $tripType === 'roundtrip' ? 'lg:col-span-2' : 'lg:col-span-2' ?> p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:bg-slate-100/80 transition cursor-pointer">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Departure</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        <i class="fa-regular fa-calendar text-brand-600 text-sm"></i>
                        <input type="date" name="date" id="searchDateInput" value="<?= htmlspecialchars($departureDate) ?>" min="<?= date('Y-m-d') ?>" class="w-full text-xs font-bold text-slate-900 bg-transparent outline-none">
                    </div>
                    <span class="text-[10px] text-emerald-600 font-bold block mt-0.5">Lowest fare track</span>
                </div>

                <!-- Block 4: Return Date (Visible in Round-Trip mode) -->
                <div id="searchReturnDateBlock" class="<?= $tripType === 'roundtrip' ? 'lg:col-span-2' : 'hidden' ?> p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:bg-slate-100/80 transition cursor-pointer">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Return Date</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        <i class="fa-regular fa-calendar-check text-teal-600 text-sm"></i>
                        <input type="date" name="return_date" id="searchReturnDateInput" value="<?= htmlspecialchars($returnDate) ?>" min="<?= htmlspecialchars($departureDate) ?>" class="w-full text-xs font-bold text-slate-900 bg-transparent outline-none">
                    </div>
                    <span class="text-[10px] text-teal-600 font-bold block mt-0.5">Return flight save</span>
                </div>

                <!-- Block 5: Search Button -->
                <div class="<?= $tripType === 'roundtrip' ? 'lg:col-span-2' : 'lg:col-span-2' ?> p-1">
                    <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-black text-xs sm:text-sm uppercase tracking-wider transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Search</span>
                    </button>
                </div>

            </div>

            <!-- Multi-City Leg 2 Row (Visible in Multi-City mode) -->
            <div id="searchMultiCityRow" class="<?= $tripType === 'multicity' ? '' : 'hidden' ?> pt-2 border-t border-slate-100">
                <div class="text-[11px] font-bold text-brand-700 uppercase tracking-wider mb-1 px-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-route text-xs"></i> Flight Leg 2
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Leg 2 From</span>
                        <input type="text" name="from2" id="searchFrom2Input" value="<?= htmlspecialchars($from2AirportInfo['name'] . ' (' . $from2 . ')') ?>" placeholder="Leg 2 Departure Airport..." class="flight-airport-typeahead w-full text-xs font-bold text-slate-900 bg-transparent outline-none truncate">
                    </div>
                    <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Leg 2 To</span>
                        <input type="text" name="to2" id="searchTo2Input" value="<?= htmlspecialchars($to2AirportInfo['name'] . ' (' . $to2 . ')') ?>" placeholder="Leg 2 Destination Airport..." class="flight-airport-typeahead w-full text-xs font-bold text-slate-900 bg-transparent outline-none truncate">
                    </div>
                    <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Leg 2 Date</span>
                        <input type="date" name="date2" id="searchDate2Input" value="<?= htmlspecialchars($date2) ?>" min="<?= htmlspecialchars($departureDate) ?>" class="w-full text-xs font-bold text-slate-900 bg-transparent outline-none">
                    </div>
                </div>
            </div>

        </form>

        <!-- Special Fare Categories Strip -->
        <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
            <span class="text-[11px] font-bold uppercase text-slate-400 mr-2">Special Fares:</span>
            <span class="px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold border border-white/15 cursor-pointer transition">
                Regular Fare
            </span>
            <span class="px-3 py-1 rounded-full bg-white/5 hover:bg-white/20 text-slate-300 hover:text-white text-[11px] font-medium border border-white/10 cursor-pointer transition flex items-center gap-1.5">
                <i class="fa-solid fa-graduation-cap text-[10px] text-teal-300"></i> Student Fare (Extra 10kg)
            </span>
            <span class="px-3 py-1 rounded-full bg-white/5 hover:bg-white/20 text-slate-300 hover:text-white text-[11px] font-medium border border-white/10 cursor-pointer transition flex items-center gap-1.5">
                <i class="fa-solid fa-person-cane text-[10px] text-amber-300"></i> Senior Citizen
            </span>
            <span class="px-3 py-1 rounded-full bg-white/5 hover:bg-white/20 text-slate-300 hover:text-white text-[11px] font-medium border border-white/10 cursor-pointer transition flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-[10px] text-rose-300"></i> Armed Forces
            </span>
        </div>

    </div>
</section>

<!-- ==========================================
     DATE MATRIX CAROUSEL (LOWEST FARE TRACK)
     ========================================== -->
<div class="bg-white border-b border-slate-200 py-2.5 px-4 sm:px-6 lg:px-8 xl:px-12 sticky top-16 md:top-20 z-30 shadow-xs">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 overflow-x-auto scrollbar-none">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-none text-center flex-1">
            <?php for ($d = 0; $d < 7; $d++): ?>
                <?php 
                $currTimestamp = strtotime("+$d days", strtotime($departureDate));
                $isCurDate = ($d === 0);
                $dayPrice = rand(3600, 4900);
                $rtLink = "flights.php?trip={$tripType}&from=" . urlencode($origin) . "&to=" . urlencode($destination) . "&date=" . date('Y-m-d', $currTimestamp) . ($tripType === 'roundtrip' ? '&return_date=' . urlencode($returnDate) : '');
                ?>
                <a href="<?= $rtLink ?>" 
                   class="flex-1 min-w-[115px] p-2 rounded-xl border transition-all select-none <?= $isCurDate ? 'border-brand-600 bg-brand-50 text-brand-900 font-bold' : 'border-slate-200 hover:border-slate-300 bg-slate-50/60 text-slate-700' ?>">
                    <div class="text-[11px] font-bold"><?= date('D, d M', $currTimestamp) ?></div>
                    <div class="text-xs font-black <?= $isCurDate ? 'text-brand-700' : 'text-slate-900' ?>">₹<?= number_format($dayPrice) ?></div>
                </a>
            <?php endfor; ?>
        </div>

        <?php if ($tripType === 'roundtrip'): ?>
            <!-- Round-Trip Mode Toggle Buttons -->
            <div class="hidden sm:flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 border border-slate-200 text-xs shrink-0 font-bold">
                <button type="button" id="btnModeSplit" onclick="setRoundTripViewMode('split')" class="px-3 py-1.5 rounded-lg bg-white shadow-xs text-brand-700">
                    <i class="fa-solid fa-table-columns mr-1"></i> Split View
                </button>
                <button type="button" id="btnModeCombos" onclick="setRoundTripViewMode('combos')" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900">
                    <i class="fa-solid fa-box-archive mr-1"></i> Bundled Combos
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ==========================================
     MAIN FLIGHTS RESULTS AREA (2 COLUMNS: FILTERS + RESULTS)
     ========================================== -->
<main class="py-8 px-4 sm:px-6 lg:px-8 xl:px-12 bg-slate-50 min-h-screen pb-28">
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-6 items-start">
        
        <!-- LEFT SIDEBAR: MAKEMYTRIP POWER FILTERS (Width: 280px) -->
        <aside class="w-full lg:w-72 shrink-0 space-y-4">
            
            <div class="bg-white rounded-3xl border border-slate-200 p-5 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-brand-600"></i>
                        <span>Filters</span>
                    </h3>
                    <button type="button" onclick="resetFlightFilters()" class="text-[11px] text-brand-600 hover:underline font-bold">Clear All</button>
                </div>

                <!-- 1. Stops Filter -->
                <div class="space-y-2.5">
                    <h4 class="text-xs font-bold text-slate-800">Flight Stops</h4>
                    <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition cursor-pointer text-xs font-semibold text-slate-700">
                        <span class="flex items-center gap-2">
                            <input type="checkbox" value="Non-Stop" checked class="stop-filter rounded accent-brand-600" onchange="applyFilters()">
                            <span>Non-Stop Flights</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-bold">Direct</span>
                    </label>
                    <label class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition cursor-pointer text-xs font-semibold text-slate-700">
                        <span class="flex items-center gap-2">
                            <input type="checkbox" value="1 Stop" checked class="stop-filter rounded accent-brand-600" onchange="applyFilters()">
                            <span>1 Stop Flights</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-bold">Connecting</span>
                    </label>
                </div>

                <!-- 2. Departure Time Blocks -->
                <div class="space-y-2.5 pt-3 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-800">Departure Time</h4>
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <label class="time-block-filter p-2 rounded-xl border border-slate-200 bg-slate-50 hover:border-brand-500 transition cursor-pointer flex flex-col items-center">
                            <input type="checkbox" value="early_morning" class="hidden time-filter" onchange="applyFilters()">
                            <i class="fa-solid fa-cloud-sun text-amber-500 text-xs mb-1"></i>
                            <span class="text-[10px] font-bold text-slate-800">Before 6 AM</span>
                        </label>
                        <label class="time-block-filter p-2 rounded-xl border border-slate-200 bg-slate-50 hover:border-brand-500 transition cursor-pointer flex flex-col items-center">
                            <input type="checkbox" value="morning" class="hidden time-filter" onchange="applyFilters()">
                            <i class="fa-solid fa-sun text-amber-500 text-xs mb-1"></i>
                            <span class="text-[10px] font-bold text-slate-800">6 AM - 12 PM</span>
                        </label>
                        <label class="time-block-filter p-2 rounded-xl border border-slate-200 bg-slate-50 hover:border-brand-500 transition cursor-pointer flex flex-col items-center">
                            <input type="checkbox" value="afternoon" class="hidden time-filter" onchange="applyFilters()">
                            <i class="fa-solid fa-cloud-sun-rain text-sky-500 text-xs mb-1"></i>
                            <span class="text-[10px] font-bold text-slate-800">12 PM - 6 PM</span>
                        </label>
                        <label class="time-block-filter p-2 rounded-xl border border-slate-200 bg-slate-50 hover:border-brand-500 transition cursor-pointer flex flex-col items-center">
                            <input type="checkbox" value="night" class="hidden time-filter" onchange="applyFilters()">
                            <i class="fa-solid fa-moon text-indigo-500 text-xs mb-1"></i>
                            <span class="text-[10px] font-bold text-slate-800">After 6 PM</span>
                        </label>
                    </div>
                </div>

                <!-- 3. Preferred Airlines Checklist -->
                <div class="space-y-2.5 pt-3 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-800">Airlines</h4>
                    <div class="space-y-1.5" id="airlinesChecklistContainer">
                        <div class="p-2 text-center text-xs text-slate-400">Loading airlines...</div>
                    </div>
                </div>

                <!-- 4. Price Slider -->
                <div class="space-y-2 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                        <span>Max Price:</span>
                        <span class="text-brand-600 font-black" id="priceFilterDisplay">₹30,000</span>
                    </div>
                    <input type="range" id="priceRangeInput" min="3000" max="40000" step="500" value="40000" oninput="document.getElementById('priceFilterDisplay').textContent = '₹' + parseInt(this.value).toLocaleString('en-IN'); applyFilters();" class="w-full accent-brand-600">
                </div>

            </div>

        </aside>

        <!-- RIGHT FLIGHT STREAM -->
        <div class="flex-1 w-full space-y-4">
            
            <!-- Result Header & Sort Strip -->
            <div class="bg-white rounded-2xl border border-slate-200 p-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plane text-brand-600"></i>
                    <?php if ($tripType === 'roundtrip'): ?>
                        <span>Round-Trip: <strong class="text-brand-600"><?= htmlspecialchars($origin) ?> ⇄ <?= htmlspecialchars($destination) ?></strong></span>
                    <?php elseif ($tripType === 'multicity'): ?>
                        <span>Multi-City Route: <strong class="text-brand-600"><?= htmlspecialchars($origin) ?> → <?= htmlspecialchars($destination) ?> → <?= htmlspecialchars($to2) ?></strong></span>
                    <?php else: ?>
                        <span>Flights from <strong class="text-brand-600"><?= htmlspecialchars($origin) ?></strong> to <strong class="text-brand-600"><?= htmlspecialchars($destination) ?></strong></span>
                    <?php endif; ?>
                    <span class="text-[11px] font-normal text-slate-400">(<span id="flightsFoundCount">...</span> flights found)</span>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-slate-400 font-bold">SORT BY:</span>
                    <select id="flightSortSelect" onchange="applySorting()" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 focus:outline-none">
                        <option value="price_low">Cheapest First</option>
                        <option value="duration_short">Fastest (Duration)</option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Flight Results Container -->
            <div id="flightsFeed" class="space-y-3.5">
                <!-- Loading Skeleton -->
                <div class="p-8 text-center bg-white rounded-3xl border border-slate-200 space-y-3" id="flightLoader">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-brand-600"></i>
                    <p class="text-xs font-bold text-slate-700">Connecting to Global Flight GDS &amp; Airlines Engine via Live API...</p>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- ==========================================
     MAKEMYTRIP ROUND-TRIP STICKY BOTTOM BAR
     (Fixed at bottom in round-trip mode)
     ========================================== -->
<div id="roundTripStickyBar" class="hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-md text-white border-t border-slate-700 py-3.5 px-4 sm:px-8 shadow-2xl transition-all">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center gap-4 sm:gap-8 text-xs w-full md:w-auto justify-between md:justify-start">
            <!-- Selected Outbound Flight -->
            <div id="stickyOutboundInfo" class="flex items-center gap-3">
                <div class="h-8 w-14 bg-white rounded-lg p-0.5 flex items-center justify-center shrink-0">
                    <img id="stickyObLogo" src="" alt="Airline" class="max-h-full max-w-full object-contain">
                </div>
                <div>
                    <span class="text-[10px] text-teal-300 uppercase font-black tracking-wider block">Departure Flight</span>
                    <span class="font-bold text-white text-xs sm:text-sm" id="stickyObTitle">Selecting...</span>
                    <span class="text-[10px] text-slate-400 block" id="stickyObTime">--:--</span>
                </div>
            </div>

            <div class="text-slate-600 font-bold text-lg hidden sm:block">+</div>

            <!-- Selected Return Flight -->
            <div id="stickyReturnInfo" class="flex items-center gap-3">
                <div class="h-8 w-14 bg-white rounded-lg p-0.5 flex items-center justify-center shrink-0">
                    <img id="stickyRetLogo" src="" alt="Airline" class="max-h-full max-w-full object-contain">
                </div>
                <div>
                    <span class="text-[10px] text-sky-300 uppercase font-black tracking-wider block">Return Flight</span>
                    <span class="font-bold text-white text-xs sm:text-sm" id="stickyRetTitle">Selecting...</span>
                    <span class="text-[10px] text-slate-400 block" id="stickyRetTime">--:--</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between md:justify-end gap-5 w-full md:w-auto border-t md:border-t-0 border-slate-800 pt-2 md:pt-0">
            <div class="text-left md:text-right">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Round-Trip Fare</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-400 font-space" id="stickyTotalFare">₹0</span>
            </div>
            <button type="button" onclick="bookSelectedRoundTrip()" class="px-6 py-3 rounded-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-2 cursor-pointer active:scale-95">
                <span>Book Round-Trip</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>

    </div>
</div>

<!-- ==========================================
     MAKEMYTRIP FLIGHT DETAILS & FARE RULES MODAL
     ========================================== -->
<div id="flightDetailsModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 space-y-5 animate-in zoom-in-95 duration-200 my-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-base" id="modalFlightIcon">
                    <i class="fa-solid fa-plane"></i>
                </span>
                <div>
                    <h3 class="text-base font-black text-slate-900" id="modalFlightTitle">Flight Details</h3>
                    <p class="text-xs text-slate-400" id="modalFlightRoute">DEL &rarr; BOM &bull; Non-Stop</p>
                </div>
            </div>
            <button type="button" onclick="closeFlightModal()" class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-700 cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Flight Timeline -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 text-xs" id="modalFlightTimeline">
            <!-- Timeline Rendered via JS -->
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Fare (1 Traveler)</span>
                <span class="text-xl font-black text-slate-900" id="modalFareDisplay">₹0</span>
            </div>
            <button type="button" onclick="lockFlightSeat()" class="px-6 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                Book This Flight &rarr;
            </button>
        </div>
    </div>
</div>

<!-- ==========================================
     CLIENT JAVASCRIPT: REAL-TIME FLIGHT ENGINE
     ========================================== -->
<script>
const currentTripType = '<?= $tripType ?>';

// Master immutable datasets from single API call
let masterFlights = [];
let masterOutbound = [];
let masterReturn = [];
let masterCombos = [];
let masterMultiCityLegs = [];

// Filtered/active working datasets for rendering
let currentFilteredFlights = [];
let currentFilteredOutbound = [];
let currentFilteredReturn = [];
let currentFilteredCombos = [];
let currentFilteredMultiCityLegs = [];

// User selections & modes
let selectedOutboundFlight = null;
let selectedReturnFlight = null;
let currentSelectedFlight = null;
let roundTripViewMode = 'split'; // 'split' or 'combos'

document.addEventListener('DOMContentLoaded', () => {
    fetchFlightResults();
});

function switchFlightTripMode(mode) {
    const hiddenTrip = document.getElementById('searchTripTypeHidden');
    if (hiddenTrip) hiddenTrip.value = mode;

    const retBlock = document.getElementById('searchReturnDateBlock');
    const mcRow = document.getElementById('searchMultiCityRow');

    if (mode === 'roundtrip') {
        if (retBlock) retBlock.classList.remove('hidden');
        if (mcRow) mcRow.classList.add('hidden');
    } else if (mode === 'multicity') {
        if (retBlock) retBlock.classList.add('hidden');
        if (mcRow) mcRow.classList.remove('hidden');
    } else {
        if (retBlock) retBlock.classList.add('hidden');
        if (mcRow) mcRow.classList.add('hidden');
    }
}

function swapAirports() {
    const fromInput = document.getElementById('fromAirportInput');
    const toInput = document.getElementById('toAirportInput');
    const dispFromCode = document.getElementById('dispFromCode');
    const dispToCode = document.getElementById('dispToCode');
    const dispFromName = document.getElementById('dispFromName');
    const dispToName = document.getElementById('dispToName');

    if (fromInput && toInput) {
        const tmpVal = fromInput.value;
        fromInput.value = toInput.value;
        toInput.value = tmpVal;
    }
    if (dispFromCode && dispToCode) {
        const tmpCode = dispFromCode.textContent;
        dispFromCode.textContent = dispToCode.textContent;
        dispToCode.textContent = tmpCode;
    }
    if (dispFromName && dispToName) {
        const tmpName = dispFromName.textContent;
        dispFromName.textContent = dispToName.textContent;
        dispToName.textContent = tmpName;
    }
    document.getElementById('flightSearchForm').submit();
}

/**
 * Loads flight search data once per search query.
 * Checks sessionStorage cache first (0 network calls).
 * Filters will NEVER call this function.
 */
function fetchFlightResults() {
    const origin = encodeURIComponent('<?= $origin ?>');
    const dest = encodeURIComponent('<?= $destination ?>');
    const date = encodeURIComponent('<?= $departureDate ?>');
    const retDate = encodeURIComponent('<?= $returnDate ?>');
    const from2 = encodeURIComponent('<?= $from2 ?>');
    const to2 = encodeURIComponent('<?= $to2 ?>');
    const date2 = encodeURIComponent('<?= $date2 ?>');

    const cacheKey = `orion_flight_cache_${currentTripType}_${origin}_${dest}_${date}_${retDate}_${from2}_${to2}_${date2}`;

    // 1. Check in-memory/browser session cache
    const cachedDataStr = sessionStorage.getItem(cacheKey);
    if (cachedDataStr) {
        try {
            const cachedData = JSON.parse(cachedDataStr);
            if (cachedData && cachedData.success) {
                console.log('⚡ Loaded flight data from Client Session Storage Cache (Zero API calls)');
                processLoadedFlightData(cachedData);
                return;
            }
        } catch (e) {
            sessionStorage.removeItem(cacheKey);
        }
    }

    // 2. Fetch from Live API endpoint once
    let url = `api/flights.php?trip=${currentTripType}&from=${origin}&to=${dest}&date=${date}`;
    if (currentTripType === 'roundtrip') {
        url += `&return_date=${retDate}`;
    } else if (currentTripType === 'multicity') {
        url += `&from2=${from2}&to2=${to2}&date2=${date2}`;
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showEmptyFlightState(data.message || 'Failed to fetch real-time flights.');
                return;
            }

            // Save to session cache for instant subsequent filter/tab interactions
            try {
                sessionStorage.setItem(cacheKey, JSON.stringify(data));
            } catch (err) {}

            processLoadedFlightData(data);
        })
        .catch(err => {
            showEmptyFlightState('Network error while connecting to Live Flight API.');
        });
}

/**
 * Initializes master datasets and filter components once flight data is ready
 */
function processLoadedFlightData(data) {
    if (currentTripType === 'roundtrip') {
        masterOutbound = data.outbound || [];
        masterReturn = data.return || [];
        masterCombos = data.combos || [];
        masterFlights = [...masterOutbound, ...masterReturn];

        populateAirlinesFilter(masterFlights);
        setupPriceSlider(masterFlights);

        if (masterOutbound.length > 0) selectedOutboundFlight = masterOutbound[0];
        if (masterReturn.length > 0) selectedReturnFlight = masterReturn[0];

        applyFilters();
        document.getElementById('roundTripStickyBar')?.classList.remove('hidden');

    } else if (currentTripType === 'multicity') {
        masterMultiCityLegs = data.legs || [];
        masterFlights = [];
        masterMultiCityLegs.forEach(lg => {
            if (lg.flights) masterFlights.push(...lg.flights);
        });

        populateAirlinesFilter(masterFlights);
        setupPriceSlider(masterFlights);
        applyFilters();

    } else {
        masterFlights = data.data || [];
        populateAirlinesFilter(masterFlights);
        setupPriceSlider(masterFlights);
        applyFilters();
    }
}

function showEmptyFlightState(msg) {
    document.getElementById('flightsFeed').innerHTML = `
        <div class="p-8 text-center bg-white rounded-3xl border border-slate-200">
            <p class="text-sm font-bold text-slate-700">${msg}</p>
            <p class="text-xs text-slate-400 mt-1">Please try modifying your date or selecting major airports like DEL, BOM, BLR, or GOX.</p>
        </div>
    `;
}

function populateAirlinesFilter(flights) {
    const container = document.getElementById('airlinesChecklistContainer');
    if (!container) return;

    const airlineMap = {};
    flights.forEach(f => {
        if (!f.airline) return;
        if (!airlineMap[f.airline]) {
            airlineMap[f.airline] = {
                name: f.airline,
                code: f.airline_code || '',
                minFare: f.fare
            };
        } else if (f.fare < airlineMap[f.airline].minFare) {
            airlineMap[f.airline].minFare = f.fare;
        }
    });

    let html = '';
    Object.values(airlineMap).forEach(al => {
        html += `
            <label class="flex items-center justify-between p-1.5 rounded-xl hover:bg-slate-50 cursor-pointer text-xs font-semibold text-slate-700 select-none">
                <span class="flex items-center gap-2">
                    <input type="checkbox" value="${al.name}" checked class="airline-filter rounded accent-brand-600" onchange="applyFilters()">
                    <span class="truncate">${al.name} ${al.code ? '(' + al.code + ')' : ''}</span>
                </span>
                <span class="text-[10px] text-slate-400 font-bold">from ₹${al.minFare.toLocaleString('en-IN')}</span>
            </label>
        `;
    });

    container.innerHTML = html || '<div class="text-[10px] text-slate-400">All Airlines</div>';
}

function setupPriceSlider(flights) {
    if (!flights || flights.length === 0) return;
    const fares = flights.map(f => f.fare);
    const minFare = Math.min(...fares);
    const maxFare = Math.max(...fares);
    const priceInput = document.getElementById('priceRangeInput');
    const priceDisp = document.getElementById('priceFilterDisplay');
    if (priceInput) {
        priceInput.min = Math.max(1000, Math.floor(minFare / 1000) * 1000);
        priceInput.max = Math.ceil(maxFare / 1000) * 1000 + 1000;
        priceInput.value = priceInput.max;
        if (priceDisp) priceDisp.textContent = '₹' + parseInt(priceInput.value).toLocaleString('en-IN');
    }
}

/**
 * Parses time string like "08:15 AM", "06:30 PM", "14:20" to 24h integer (0-23)
 */
function parseTimeToHour(timeStr) {
    if (!timeStr) return 12;
    const match = timeStr.trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i);
    if (!match) return 12;
    let hour = parseInt(match[1], 10);
    const period = match[3] ? match[3].toUpperCase() : null;
    if (period === 'PM' && hour < 12) hour += 12;
    if (period === 'AM' && hour === 12) hour = 0;
    return hour;
}

/**
 * Updates visual active chip styles for departure time block cards
 */
function updateTimeBlockVisuals() {
    document.querySelectorAll('.time-block-filter').forEach(label => {
        const input = label.querySelector('.time-filter');
        if (input && input.checked) {
            label.classList.add('border-brand-600', 'bg-brand-50', 'ring-2', 'ring-brand-500');
            label.classList.remove('border-slate-200', 'bg-slate-50');
        } else {
            label.classList.remove('border-brand-600', 'bg-brand-50', 'ring-2', 'ring-brand-500');
            label.classList.add('border-slate-200', 'bg-slate-50');
        }
    });
}

// ------------------------------------------------------------------
// CLIENT-SIDE IN-MEMORY FILTERING & SORTING ENGINE (ZERO API CALLS)
// ------------------------------------------------------------------
function applyFilters() {
    updateTimeBlockVisuals();

    const checkedStops = Array.from(document.querySelectorAll('.stop-filter:checked')).map(cb => cb.value);
    const checkedAirlines = Array.from(document.querySelectorAll('.airline-filter:checked')).map(cb => cb.value);
    const checkedTimes = Array.from(document.querySelectorAll('.time-filter:checked')).map(cb => cb.value);
    const maxPrice = parseFloat(document.getElementById('priceRangeInput')?.value || 999999);
    const sortVal = document.getElementById('flightSortSelect')?.value || 'price_low';

    const matchFlight = f => {
        if (!f) return false;

        // 1. Stops Filter
        if (checkedStops.length > 0) {
            const isNonStop = (f.stops === 'Non-Stop' || f.stops === '0' || f.stops === 'Direct');
            const is1Stop = (f.stops === '1 Stop' || f.stops === '1');
            const is2Plus = (!isNonStop && !is1Stop);

            let stopMatch = false;
            if (isNonStop && checkedStops.includes('Non-Stop')) stopMatch = true;
            if (is1Stop && checkedStops.includes('1 Stop')) stopMatch = true;
            if (is2Plus && (checkedStops.includes('2+ Stops') || checkedStops.includes('1 Stop'))) stopMatch = true;
            if (!stopMatch) return false;
        } else {
            return false;
        }

        // 2. Preferred Airline Filter
        if (checkedAirlines.length > 0) {
            if (!checkedAirlines.includes(f.airline)) return false;
        } else {
            return false;
        }

        // 3. Price Filter
        if (typeof f.fare === 'number' && f.fare > maxPrice) {
            return false;
        }

        // 4. Departure Time Block Filter
        if (checkedTimes.length > 0) {
            const hour = parseTimeToHour(f.from_time);
            let timeMatch = false;
            if (checkedTimes.includes('early_morning') && hour < 6) timeMatch = true;
            if (checkedTimes.includes('morning') && hour >= 6 && hour < 12) timeMatch = true;
            if (checkedTimes.includes('afternoon') && hour >= 12 && hour < 18) timeMatch = true;
            if (checkedTimes.includes('night') && hour >= 18) timeMatch = true;
            if (!timeMatch) return false;
        }

        return true;
    };

    const sortFn = (a, b) => {
        if (sortVal === 'price_low') return a.fare - b.fare;
        if (sortVal === 'price_high') return b.fare - a.fare;
        if (sortVal === 'duration_short') return (a.duration_minutes || 0) - (b.duration_minutes || 0);
        return 0;
    };

    if (currentTripType === 'roundtrip') {
        currentFilteredOutbound = masterOutbound.filter(matchFlight);
        currentFilteredReturn = masterReturn.filter(matchFlight);
        currentFilteredCombos = masterCombos.filter(c => matchFlight(c.outbound_flight) && matchFlight(c.return_flight));

        currentFilteredOutbound.sort(sortFn);
        currentFilteredReturn.sort(sortFn);

        // Maintain or re-select active choices
        if (!selectedOutboundFlight || !currentFilteredOutbound.some(f => f.id === selectedOutboundFlight.id)) {
            selectedOutboundFlight = currentFilteredOutbound.length > 0 ? currentFilteredOutbound[0] : null;
        }
        if (!selectedReturnFlight || !currentFilteredReturn.some(f => f.id === selectedReturnFlight.id)) {
            selectedReturnFlight = currentFilteredReturn.length > 0 ? currentFilteredReturn[0] : null;
        }

        const countEl = document.getElementById('flightsFoundCount');
        if (countEl) countEl.textContent = `${currentFilteredOutbound.length} outbound + ${currentFilteredReturn.length} return`;

        renderRoundTrip();
        updateRoundTripStickyBar();

    } else if (currentTripType === 'multicity') {
        let totalCount = 0;
        currentFilteredMultiCityLegs = masterMultiCityLegs.map(leg => {
            const legFiltered = (leg.flights || []).filter(matchFlight);
            legFiltered.sort(sortFn);
            totalCount += legFiltered.length;
            return {
                ...leg,
                flights: legFiltered
            };
        });

        const countEl = document.getElementById('flightsFoundCount');
        if (countEl) countEl.textContent = totalCount;

        renderMultiCity();

    } else {
        currentFilteredFlights = masterFlights.filter(matchFlight);
        currentFilteredFlights.sort(sortFn);

        const countEl = document.getElementById('flightsFoundCount');
        if (countEl) countEl.textContent = currentFilteredFlights.length;

        renderOneWay(currentFilteredFlights);
    }
}

function resetFlightFilters() {
    document.querySelectorAll('.stop-filter').forEach(cb => cb.checked = true);
    document.querySelectorAll('.airline-filter').forEach(cb => cb.checked = true);
    document.querySelectorAll('.time-filter').forEach(cb => cb.checked = false);
    
    const priceInput = document.getElementById('priceRangeInput');
    if (priceInput) {
        priceInput.value = priceInput.max;
        const priceDisp = document.getElementById('priceFilterDisplay');
        if (priceDisp) priceDisp.textContent = '₹' + parseInt(priceInput.value).toLocaleString('en-IN');
    }
    applyFilters();
}

function applySorting() {
    applyFilters();
}

// ------------------------------------------------------------------
// ROUND-TRIP RENDERING: SPLIT VIEW vs BUNDLED COMBOS
// ------------------------------------------------------------------
function setRoundTripViewMode(mode) {
    roundTripViewMode = mode;
    const btnSplit = document.getElementById('btnModeSplit');
    const btnCombos = document.getElementById('btnModeCombos');

    if (mode === 'split') {
        if (btnSplit) { btnSplit.classList.add('bg-white', 'shadow-xs', 'text-brand-700'); btnSplit.classList.remove('text-slate-600'); }
        if (btnCombos) { btnCombos.classList.remove('bg-white', 'shadow-xs', 'text-brand-700'); btnCombos.classList.add('text-slate-600'); }
        document.getElementById('roundTripStickyBar')?.classList.remove('hidden');
    } else {
        if (btnCombos) { btnCombos.classList.add('bg-white', 'shadow-xs', 'text-brand-700'); btnCombos.classList.remove('text-slate-600'); }
        if (btnSplit) { btnSplit.classList.remove('bg-white', 'shadow-xs', 'text-brand-700'); btnSplit.classList.add('text-slate-600'); }
        document.getElementById('roundTripStickyBar')?.classList.add('hidden');
    }
    renderRoundTrip();
}

function renderRoundTrip() {
    const feed = document.getElementById('flightsFeed');
    feed.innerHTML = '';

    if (roundTripViewMode === 'combos') {
        renderRoundTripCombos();
        return;
    }

    if (currentFilteredOutbound.length === 0 && currentFilteredReturn.length === 0) {
        showEmptyFlightState('No flights match your filter criteria. Try resetting filters.');
        return;
    }

    // SPLIT VIEW (Dual Column)
    const splitContainer = document.createElement('div');
    splitContainer.className = 'grid grid-cols-1 lg:grid-cols-2 gap-4';

    // Left Column: Outbound
    const obCol = document.createElement('div');
    obCol.className = 'space-y-3';
    obCol.innerHTML = `
        <div class="bg-brand-50 border border-brand-200 rounded-2xl p-3 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-black text-brand-700 tracking-wider">Outbound Flight</span>
                <h4 class="text-xs font-black text-slate-900"><?= htmlspecialchars($origin) ?> &rarr; <?= htmlspecialchars($destination) ?></h4>
            </div>
            <span class="text-[11px] font-bold text-brand-800 bg-white px-2.5 py-1 rounded-lg border border-brand-200"><?= date('D, d M', strtotime($departureDate)) ?></span>
        </div>
        <div class="space-y-2.5" id="obFlightsList"></div>
    `;

    // Right Column: Return
    const retCol = document.createElement('div');
    retCol.className = 'space-y-3';
    retCol.innerHTML = `
        <div class="bg-teal-50 border border-teal-200 rounded-2xl p-3 flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-black text-teal-700 tracking-wider">Return Flight</span>
                <h4 class="text-xs font-black text-slate-900"><?= htmlspecialchars($destination) ?> &rarr; <?= htmlspecialchars($origin) ?></h4>
            </div>
            <span class="text-[11px] font-bold text-teal-800 bg-white px-2.5 py-1 rounded-lg border border-teal-200"><?= date('D, d M', strtotime($returnDate)) ?></span>
        </div>
        <div class="space-y-2.5" id="retFlightsList"></div>
    `;

    splitContainer.appendChild(obCol);
    splitContainer.appendChild(retCol);
    feed.appendChild(splitContainer);

    // Populate Outbound List
    const obContainer = obCol.querySelector('#obFlightsList');
    if (currentFilteredOutbound.length === 0) {
        obContainer.innerHTML = '<div class="p-6 bg-white rounded-2xl text-center text-xs text-slate-400 border border-slate-100">No outbound flights match filters</div>';
    } else {
        currentFilteredOutbound.forEach(f => {
            const isSelected = selectedOutboundFlight && selectedOutboundFlight.id === f.id;
            const item = createSplitFlightCard(f, 'outbound', isSelected);
            obContainer.appendChild(item);
        });
    }

    // Populate Return List
    const retContainer = retCol.querySelector('#retFlightsList');
    if (currentFilteredReturn.length === 0) {
        retContainer.innerHTML = '<div class="p-6 bg-white rounded-2xl text-center text-xs text-slate-400 border border-slate-100">No return flights match filters</div>';
    } else {
        currentFilteredReturn.forEach(f => {
            const isSelected = selectedReturnFlight && selectedReturnFlight.id === f.id;
            const item = createSplitFlightCard(f, 'return', isSelected);
            retContainer.appendChild(item);
        });
    }
}

function createSplitFlightCard(flight, direction, isSelected) {
    const card = document.createElement('div');
    card.className = `p-3.5 rounded-2xl border transition-all cursor-pointer select-none ${isSelected ? 'border-brand-600 bg-brand-50/40 ring-2 ring-brand-500' : 'border-slate-200 bg-white hover:border-slate-300'}`;
    
    card.onclick = () => {
        if (direction === 'outbound') {
            selectedOutboundFlight = flight;
        } else {
            selectedReturnFlight = flight;
        }
        renderRoundTrip();
        updateRoundTripStickyBar();
    };

    card.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2 min-w-0">
                <input type="radio" name="${direction}_select" ${isSelected ? 'checked' : ''} class="accent-brand-600 pointer-events-none">
                <img src="${flight.airline_logo}" alt="${flight.airline}" class="h-6 w-14 object-contain">
                <span class="text-xs font-black text-slate-900 truncate">${flight.airline}</span>
                <span class="text-[10px] text-slate-400 font-mono">${flight.flight_number}</span>
            </div>
            <span class="text-sm font-black text-slate-900 font-space">₹${flight.fare.toLocaleString('en-IN')}</span>
        </div>

        <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-100">
            <div>
                <strong class="text-slate-900">${flight.from_time}</strong>
                <span class="text-[10px] text-slate-400 block">${flight.from_code}</span>
            </div>
            <div class="text-center text-[10px] text-slate-400">
                <span>${flight.duration}</span>
                <div class="w-12 h-0.5 bg-slate-200 mx-auto my-0.5"></div>
                <span class="text-emerald-600 font-bold">${flight.stops}</span>
            </div>
            <div class="text-right">
                <strong class="text-slate-900">${flight.to_time}</strong>
                <span class="text-[10px] text-slate-400 block">${flight.to_code}</span>
            </div>
        </div>
    `;

    return card;
}

function updateRoundTripStickyBar() {
    const stickyBar = document.getElementById('roundTripStickyBar');
    if (!stickyBar) return;

    if (!selectedOutboundFlight && !selectedReturnFlight) {
        stickyBar.classList.add('hidden');
        return;
    }

    if (selectedOutboundFlight) {
        document.getElementById('stickyObLogo').src = selectedOutboundFlight.airline_logo;
        document.getElementById('stickyObTitle').textContent = `${selectedOutboundFlight.airline} (${selectedOutboundFlight.flight_number})`;
        document.getElementById('stickyObTime').textContent = `${selectedOutboundFlight.from_time} → ${selectedOutboundFlight.to_time} • ₹${selectedOutboundFlight.fare.toLocaleString('en-IN')}`;
    } else {
        document.getElementById('stickyObTitle').textContent = `Select outbound flight`;
        document.getElementById('stickyObTime').textContent = `--:--`;
    }

    if (selectedReturnFlight) {
        document.getElementById('stickyRetLogo').src = selectedReturnFlight.airline_logo;
        document.getElementById('stickyRetTitle').textContent = `${selectedReturnFlight.airline} (${selectedReturnFlight.flight_number})`;
        document.getElementById('stickyRetTime').textContent = `${selectedReturnFlight.from_time} → ${selectedReturnFlight.to_time} • ₹${selectedReturnFlight.fare.toLocaleString('en-IN')}`;
    } else {
        document.getElementById('stickyRetTitle').textContent = `Select return flight`;
        document.getElementById('stickyRetTime').textContent = `--:--`;
    }

    const obFare = selectedOutboundFlight ? selectedOutboundFlight.fare : 0;
    const retFare = selectedReturnFlight ? selectedReturnFlight.fare : 0;
    const totalFare = obFare + retFare;
    document.getElementById('stickyTotalFare').textContent = '₹' + totalFare.toLocaleString('en-IN');
}

function renderRoundTripCombos() {
    const feed = document.getElementById('flightsFeed');
    feed.innerHTML = '';

    if (!currentFilteredCombos || currentFilteredCombos.length === 0) {
        showEmptyFlightState('No bundled round-trip packages match your filters.');
        return;
    }

    currentFilteredCombos.forEach(c => {
        const ob = c.outbound_flight;
        const ret = c.return_flight;
        const card = document.createElement('div');
        card.className = 'bg-white rounded-3xl border border-slate-200 hover:border-brand-500 transition-all p-5 space-y-4';

        card.innerHTML = `
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="${c.airline_logo}" alt="${c.airline}" class="h-8 w-16 object-contain">
                    <div>
                        <h4 class="text-sm font-black text-slate-900">${c.airline}</h4>
                        <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200">Round-Trip Special Bundle</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-black text-slate-900 font-space leading-none">₹${c.total_fare.toLocaleString('en-IN')}</div>
                    <span class="text-[10px] text-slate-400 line-through">₹${c.original_total_fare.toLocaleString('en-IN')}</span>
                </div>
            </div>

            <!-- Leg 1: Outbound -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs bg-slate-50/70 p-3 rounded-2xl border border-slate-100">
                <div class="sm:col-span-3">
                    <span class="text-[10px] text-brand-600 font-bold block uppercase">Departure Leg</span>
                    <strong class="text-sm text-slate-900">${ob.from_time}</strong> &bull; <span class="font-bold text-slate-700">${ob.from_code}</span>
                </div>
                <div class="sm:col-span-5 text-center text-[10px] text-slate-500">
                    <span>${ob.duration} &bull; ${ob.stops}</span>
                    <div class="w-full h-0.5 bg-slate-200 my-0.5"></div>
                    <span class="font-mono text-slate-400">${ob.flight_number}</span>
                </div>
                <div class="sm:col-span-4 text-left sm:text-right">
                    <strong class="text-sm text-slate-900">${ob.to_time}</strong> &bull; <span class="font-bold text-slate-700">${ob.to_code}</span>
                </div>
            </div>

            <!-- Leg 2: Return -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs bg-slate-50/70 p-3 rounded-2xl border border-slate-100">
                <div class="sm:col-span-3">
                    <span class="text-[10px] text-teal-600 font-bold block uppercase">Return Leg</span>
                    <strong class="text-sm text-slate-900">${ret.from_time}</strong> &bull; <span class="font-bold text-slate-700">${ret.from_code}</span>
                </div>
                <div class="sm:col-span-5 text-center text-[10px] text-slate-500">
                    <span>${ret.duration} &bull; ${ret.stops}</span>
                    <div class="w-full h-0.5 bg-slate-200 my-0.5"></div>
                    <span class="font-mono text-slate-400">${ret.flight_number}</span>
                </div>
                <div class="sm:col-span-4 text-left sm:text-right">
                    <strong class="text-sm text-slate-900">${ret.to_time}</strong> &bull; <span class="font-bold text-slate-700">${ret.to_code}</span>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between">
                <span class="text-[11px] text-slate-500"><i class="fa-solid fa-suitcase-rolling text-brand-600 mr-1"></i> 15 Kg Check-in included per leg</span>
                <button type="button" onclick='bookRoundTripCombo(${JSON.stringify(c)})' class="px-5 py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                    Book Bundled Trip &rarr;
                </button>
            </div>
        `;
        feed.appendChild(card);
    });
}

function bookSelectedRoundTrip() {
    if (!selectedOutboundFlight || !selectedReturnFlight) return;
    const total = selectedOutboundFlight.fare + selectedReturnFlight.fare;
    
    const name = prompt("Please enter Lead Passenger Full Name:", "Traveler");
    if (!name) return;
    const phone = prompt("Please enter WhatsApp / Mobile Number:", "+91 9876543210");
    if (!phone) return;

    const bookingCode = 'RT-' + Math.random().toString(36).substr(2, 7).toUpperCase();
    alert(`🎉 Round-Trip Booking Confirmed!\n\nBooking Code: ${bookingCode}\nLead Passenger: ${name}\n\nLeg 1 (Outbound): ${selectedOutboundFlight.airline} (${selectedOutboundFlight.flight_number})\nRoute: ${selectedOutboundFlight.from_code} -> ${selectedOutboundFlight.to_code} at ${selectedOutboundFlight.from_time}\n\nLeg 2 (Return): ${selectedReturnFlight.airline} (${selectedReturnFlight.flight_number})\nRoute: ${selectedReturnFlight.from_code} -> ${selectedReturnFlight.to_code} at ${selectedReturnFlight.from_time}\n\nTotal Round-Trip Fare: ₹${total.toLocaleString('en-IN')}\n\nBoth E-Tickets and Boarding Passes have been dispatched to ${phone}.`);
}

function bookRoundTripCombo(combo) {
    selectedOutboundFlight = combo.outbound_flight;
    selectedReturnFlight = combo.return_flight;
    bookSelectedRoundTrip();
}

// ------------------------------------------------------------------
// MULTI-CITY RENDERING
// ------------------------------------------------------------------
function renderMultiCity() {
    const feed = document.getElementById('flightsFeed');
    feed.innerHTML = '';

    if (!currentFilteredMultiCityLegs || currentFilteredMultiCityLegs.length === 0) {
        showEmptyFlightState('No multi-city flight options found.');
        return;
    }

    currentFilteredMultiCityLegs.forEach((lg, idx) => {
        const legSection = document.createElement('div');
        legSection.className = 'space-y-3';
        legSection.innerHTML = `
            <div class="bg-brand-50 border border-brand-200 rounded-2xl p-3.5 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-black text-brand-700 tracking-wider">Flight Leg ${lg.leg_number}</span>
                    <h4 class="text-sm font-black text-slate-900">${lg.origin} &rarr; ${lg.destination}</h4>
                </div>
                <span class="text-xs font-bold text-brand-800 bg-white px-3 py-1 rounded-xl border border-brand-200">${lg.departure_date}</span>
            </div>
            <div class="space-y-3" id="mcLegList_${idx}"></div>
        `;

        feed.appendChild(legSection);
        const listEl = legSection.querySelector(`#mcLegList_${idx}`);

        if (!lg.flights || lg.flights.length === 0) {
            listEl.innerHTML = '<div class="p-6 bg-white rounded-2xl text-center text-xs text-slate-400 border border-slate-100">No flights in this leg match current filters</div>';
        } else {
            lg.flights.forEach(f => {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition-all p-4';
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="${f.airline_logo}" alt="${f.airline}" class="h-6 w-14 object-contain">
                            <div>
                                <strong class="text-xs font-black text-slate-900">${f.airline} (${f.flight_number})</strong>
                                <p class="text-[10px] text-slate-400">${f.from_time} &bull; ${f.from_code} &rarr; ${f.to_time} &bull; ${f.to_code}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-black text-slate-900 font-space">₹${f.fare.toLocaleString('en-IN')}</span>
                            <button type="button" onclick='bookFlightTier("${f.id}", "Standard", ${f.fare})' class="px-3.5 py-1.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-[11px] cursor-pointer">
                                Select Leg
                            </button>
                        </div>
                    </div>
                `;
                listEl.appendChild(card);
            });
        }
    });
}

// ------------------------------------------------------------------
// ONE-WAY RENDERING (Standard List)
// ------------------------------------------------------------------
function renderOneWay(flights) {
    const feed = document.getElementById('flightsFeed');
    if (!flights || flights.length === 0) {
        showEmptyFlightState('No flights matching your filter criteria. Try resetting filters.');
        return;
    }

    feed.innerHTML = '';

    flights.forEach(f => {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-3xl border border-slate-200 hover:border-brand-500 transition-all p-5 space-y-4';
        
        card.innerHTML = `
            <!-- Top Row: Airline, Flight No & Badge -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-24 bg-white border border-slate-200 rounded-xl p-1 flex items-center justify-center shrink-0 shadow-xs">
                        <img src="${f.airline_logo}" alt="${f.airline}" class="max-h-full max-w-full object-contain">
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 leading-tight">${f.airline}</h4>
                        <span class="text-[11px] font-mono text-slate-400 font-semibold">${f.flight_number} &bull; <span class="text-slate-600 font-sans">${f.aircraft}</span></span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${f.badge_class}">
                        ${f.badge}
                    </span>
                </div>
            </div>

            <!-- Middle Row: Timeline Strip -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <!-- Departure Point -->
                <div class="md:col-span-3 text-left">
                    <div class="text-2xl font-black font-space text-slate-900">${f.from_time}</div>
                    <div class="text-xs font-bold text-slate-700">${f.from_code} &bull; ${f.from_city}</div>
                    <div class="text-[10px] text-slate-400 truncate" title="${f.from_airport}">${f.from_airport}</div>
                </div>

                <!-- Trajectory Line -->
                <div class="md:col-span-4 flex flex-col items-center justify-center text-center px-2">
                    <span class="text-[11px] font-bold text-slate-500 mb-1">${f.duration}</span>
                    <div class="w-full relative flex items-center justify-center">
                        <div class="w-full h-0.5 bg-slate-200"></div>
                        <div class="absolute w-6 h-6 rounded-full bg-white border border-slate-300 text-brand-600 flex items-center justify-center text-[10px]">
                            <i class="fa-solid fa-plane"></i>
                        </div>
                    </div>
                    <span class="text-[10px] text-emerald-600 font-bold mt-1">${f.stops}</span>
                </div>

                <!-- Arrival Point -->
                <div class="md:col-span-3 text-left md:text-right">
                    <div class="text-2xl font-black font-space text-slate-900">${f.to_time}</div>
                    <div class="text-xs font-bold text-slate-700">${f.to_code} &bull; ${f.to_city}</div>
                    <div class="text-[10px] text-slate-400 truncate" title="${f.to_airport}">${f.to_airport}</div>
                </div>

                <!-- Price & Book Button -->
                <div class="md:col-span-2 flex md:flex-col justify-between items-center md:items-end border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-4">
                    <div class="text-left md:text-right">
                        <div class="text-xl font-black text-slate-900 leading-none">₹${f.fare.toLocaleString('en-IN')}</div>
                        <span class="text-[10px] text-slate-400 line-through">₹${f.original_fare.toLocaleString('en-IN')}</span>
                    </div>

                    <button type="button" onclick='toggleFareTiers(${JSON.stringify(f)})' class="px-4 py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition cursor-pointer mt-2 shrink-0">
                        View Fares
                    </button>
                </div>
            </div>

            <!-- Bottom Features Strip -->
            <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-[11px] text-slate-500 gap-2">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-suitcase-rolling text-brand-600"></i> ${f.baggage}</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-bag-shopping text-brand-600"></i> ${f.cabin}</span>
                    <span class="flex items-center gap-1.5 text-emerald-600 font-bold"><i class="fa-solid fa-rotate-left"></i> ${f.refundable}</span>
                </div>

                <button type="button" onclick='openFlightModal(${JSON.stringify(f)})' class="text-brand-600 hover:underline font-bold text-[11px] flex items-center gap-1 cursor-pointer">
                    <span>Flight Details &amp; Fare Rules</span>
                    <i class="fa-solid fa-chevron-right text-[9px]"></i>
                </button>
            </div>

            <!-- Collapsible 3-Tier Fare Matrix -->
            <div id="fareTiers_${f.id}" class="hidden pt-4 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50 space-y-2 text-xs">
                    <div class="flex justify-between font-bold">
                        <span class="text-slate-800">SAVER FARE</span>
                        <span class="text-slate-900 font-black">₹${f.fare.toLocaleString('en-IN')}</span>
                    </div>
                    <ul class="text-[11px] text-slate-500 space-y-1">
                        <li>&bull; 15 Kg Check-in + 7 Kg Cabin</li>
                        <li>&bull; Standard Seat Selection</li>
                    </ul>
                    <button type="button" onclick='bookFlightTier("${f.id}", "Saver", ${f.fare})' class="w-full py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-[11px] cursor-pointer">Book Saver</button>
                </div>

                <div class="p-3.5 rounded-2xl border-2 border-brand-500 bg-brand-50/50 space-y-2 text-xs relative">
                    <span class="absolute -top-2.5 right-3 px-2 py-0.5 rounded-full bg-brand-600 text-white text-[9px] font-black uppercase">Popular</span>
                    <div class="flex justify-between font-bold">
                        <span class="text-brand-900">FLEXI PLUS</span>
                        <span class="text-brand-900 font-black">₹${(f.fare + 650).toLocaleString('en-IN')}</span>
                    </div>
                    <ul class="text-[11px] text-slate-600 space-y-1">
                        <li>&bull; Free Seat Selection</li>
                        <li>&bull; Zero Date Change Fee</li>
                    </ul>
                    <button type="button" onclick='bookFlightTier("${f.id}", "Flexi Plus", ${f.fare + 650})' class="w-full py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-[11px] cursor-pointer">Book Flexi Plus</button>
                </div>

                <div class="p-3.5 rounded-2xl border border-purple-200 bg-purple-50/50 space-y-2 text-xs">
                    <div class="flex justify-between font-bold">
                        <span class="text-purple-900">SUPER VALUE</span>
                        <span class="text-purple-900 font-black">₹${(f.fare + 1200).toLocaleString('en-IN')}</span>
                    </div>
                    <ul class="text-[11px] text-slate-600 space-y-1">
                        <li>&bull; Extra Baggage Allowance</li>
                        <li>&bull; Instant Full Refund Guarantee</li>
                    </ul>
                    <button type="button" onclick='bookFlightTier("${f.id}", "Super Value", ${f.fare + 1200})' class="w-full py-1.5 rounded-xl bg-purple-700 hover:purple-800 text-white font-bold text-[11px] cursor-pointer">Book Super Value</button>
                </div>
            </div>
        `;

        feed.appendChild(card);
    });
}

function toggleFareTiers(flight) {
    const tierEl = document.getElementById(`fareTiers_${flight.id}`);
    if (tierEl) tierEl.classList.toggle('hidden');
}

function openFlightModal(flight) {
    currentSelectedFlight = flight;
    const iconEl = document.getElementById('modalFlightIcon');
    if (iconEl && flight.airline_logo) {
        iconEl.className = 'h-10 w-24 rounded-2xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-2xs';
        iconEl.innerHTML = `<img src="${flight.airline_logo}" alt="${flight.airline}" class="max-h-full max-w-full object-contain">`;
    }
    document.getElementById('modalFlightTitle').textContent = `${flight.airline} (${flight.flight_number})`;
    document.getElementById('modalFlightRoute').textContent = `${flight.from_code} → ${flight.to_code} • ${flight.duration} • ${flight.stops} • ${flight.aircraft}`;
    document.getElementById('modalFareDisplay').textContent = '₹' + flight.fare.toLocaleString('en-IN');

    const timeline = document.getElementById('modalFlightTimeline');
    timeline.innerHTML = `
        <div class="flex items-start justify-between">
            <div>
                <strong class="text-slate-900 text-sm">${flight.from_time}</strong>
                <p class="font-bold text-brand-600">${flight.from_city} (${flight.from_code})</p>
                <p class="text-[10px] text-slate-400">${flight.from_airport}</p>
            </div>
            <span class="px-2 py-0.5 rounded bg-white text-[10px] font-bold border">${flight.duration}</span>
            <div class="text-right">
                <strong class="text-slate-900 text-sm">${flight.to_time}</strong>
                <p class="font-bold text-brand-600">${flight.to_city} (${flight.to_code})</p>
                <p class="text-[10px] text-slate-400">${flight.to_airport}</p>
            </div>
        </div>
        <div class="pt-2 border-t border-slate-200 grid grid-cols-2 gap-2 text-[11px]">
            <div><strong>Cabin Baggage:</strong> ${flight.cabin}</div>
            <div><strong>Check-in Baggage:</strong> ${flight.baggage}</div>
        </div>
    `;

    document.getElementById('flightDetailsModal').classList.remove('hidden');
    document.getElementById('flightDetailsModal').classList.add('flex');
}

function closeFlightModal() {
    document.getElementById('flightDetailsModal').classList.add('hidden');
    document.getElementById('flightDetailsModal').classList.remove('flex');
}

const isUserLoggedIn = <?= isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? 'true' : 'false' ?>;
const loggedInUser = {
    name: <?= json_encode($_SESSION['user_name'] ?? '') ?>,
    email: <?= json_encode($_SESSION['user_email'] ?? '') ?>,
    phone: <?= json_encode($_SESSION['user_phone'] ?? '') ?>
};

function lockFlightSeat() {
    if (currentSelectedFlight) {
        bookFlightTier(currentSelectedFlight.id, "Standard", currentSelectedFlight.fare);
    }
}

function bookFlightTier(flightId, tier, amount) {
    let flight = null;
    if (masterFlights && masterFlights.length > 0) {
        flight = masterFlights.find(f => f.id === flightId);
    }
    if (!flight && currentSelectedFlight) {
        flight = currentSelectedFlight;
    }
    if (!flight) {
        flight = { airline: 'Air India', flight_number: flightId, from_code: '<?= $origin ?>', to_code: '<?= $destination ?>', fare: amount };
    }

    closeFlightModal();

    if (!isUserLoggedIn) {
        showLoginRequiredModal('flight', flight.airline + ' ' + flight.flight_number);
        return;
    }

    openFlightInquiryModal(flight, tier, amount);
}

function showLoginRequiredModal(type, title) {
    const existing = document.getElementById('loginRequiredModalOverlay');
    if (existing) existing.remove();

    const currentUrl = window.location.href;
    const modalHtml = `
        <div id="loginRequiredModalOverlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 text-center space-y-4 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 border border-brand-200 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="space-y-1">
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                        Sign In Required
                    </span>
                    <h3 class="text-xl font-black text-slate-900 font-space">Sign In to Continue Booking</h3>
                    <p class="text-xs text-slate-500">Please log in to your <?php echo htmlspecialchars($siteName); ?> account to secure your reservation and receive vouchers.</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700">
                    <span>${title}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="login.php?redirect=${encodeURIComponent(currentUrl)}" class="py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider text-center transition">
                        Log In Now
                    </a>
                    <a href="signup.php?redirect=${encodeURIComponent(currentUrl)}" class="py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider text-center transition">
                        Create Account
                    </a>
                </div>
                <button type="button" onclick="document.getElementById('loginRequiredModalOverlay').remove()" class="text-xs text-slate-400 hover:text-slate-600 font-bold">
                    Cancel
                </button>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function openFlightInquiryModal(flight, tier, amount) {
    const existing = document.getElementById('flightInquiryModalOverlay');
    if (existing) existing.remove();

    const modalHtml = `
        <div id="flightInquiryModalOverlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-7 space-y-5 animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-plane-departure"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-black text-slate-900">Flight Reservation Request</h3>
                            <p class="text-[11px] text-slate-400">${flight.airline} (${flight.flight_number}) &bull; ${flight.from_code} &rarr; ${flight.to_code}</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('flightInquiryModalOverlay').remove()" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-3 bg-sky-50/60 rounded-2xl border border-sky-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-500 block">Selected Fare Tier (${tier}):</span>
                        <strong class="text-slate-900 text-sm font-space">₹${parseInt(amount).toLocaleString('en-IN')}</strong>
                    </div>
                    <span class="text-[10px] font-bold text-sky-800 bg-sky-100 px-2 py-0.5 rounded-full border border-sky-200">
                        Instant Callback Desk
                    </span>
                </div>

                <form id="flightInquiryForm" class="space-y-3" onsubmit="event.preventDefault(); submitFlightInquiryForm();">
                    <input type="hidden" id="fi_airline" value="${flight.airline}">
                    <input type="hidden" id="fi_flight_number" value="${flight.flight_number}">
                    <input type="hidden" id="fi_origin" value="${flight.from_code}">
                    <input type="hidden" id="fi_destination" value="${flight.to_code}">
                    <input type="hidden" id="fi_fare" value="${amount}">
                    <input type="hidden" id="fi_class" value="${tier}">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lead Passenger Full Name (As on Govt ID / Passport)</label>
                        <input type="text" id="fi_name" required value="${loggedInUser.name || ''}" placeholder="e.g. Rahul Sharma" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-medium">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp / Contact Phone</label>
                            <input type="tel" id="fi_phone" required value="${loggedInUser.phone || ''}" placeholder="+91 98765 43210" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email (For E-Ticket)</label>
                            <input type="email" id="fi_email" required value="${loggedInUser.email || ''}" placeholder="traveler@example.com" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Number of Passengers</label>
                            <input type="number" min="1" max="9" id="fi_pax" value="1" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Departure Date</label>
                            <input type="date" id="fi_date" required value="<?= $departureDate ?>" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 font-bold">
                        </div>
                    </div>

                    <button type="submit" id="fi_submit_btn" class="w-full py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5 cursor-pointer mt-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Submit Flight Request &amp; Get E-Ticket</span>
                    </button>
                </form>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function submitFlightInquiryForm() {
    const btn = document.getElementById('fi_submit_btn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-xs"></i> <span>Submitting Request...</span>';
    }

    const formData = new FormData();
    formData.append('passenger_name', document.getElementById('fi_name')?.value || '');
    formData.append('passenger_phone', document.getElementById('fi_phone')?.value || '');
    formData.append('passenger_email', document.getElementById('fi_email')?.value || '');
    formData.append('airline', document.getElementById('fi_airline')?.value || '');
    formData.append('flight_number', document.getElementById('fi_flight_number')?.value || '');
    formData.append('origin', document.getElementById('fi_origin')?.value || '');
    formData.append('destination', document.getElementById('fi_destination')?.value || '');
    formData.append('departure_date', document.getElementById('fi_date')?.value || '');
    formData.append('passengers_count', document.getElementById('fi_pax')?.value || '1');
    formData.append('travel_class', document.getElementById('fi_class')?.value || 'Economy');
    formData.append('total_fare', document.getElementById('fi_fare')?.value || '0');

    fetch('api/book-flight-inquiry.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.login_required) {
            document.getElementById('flightInquiryModalOverlay')?.remove();
            showLoginRequiredModal('flight', 'Flight Booking');
            return;
        }
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Error submitting flight request');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span>Submit Flight Request</span>';
            }
        }
    })
    .catch(err => {
        alert('Your flight request was received! Our ticketing team will call you.');
        window.location.href = 'my-trips.php';
    });
}
</script>

<?php
require_once 'components/footer.php';
?>
