<?php
/**
 * Orion Advent - Mobile Application Bottom Navigation Bar
 * 100% Flat, Border-First Design, Zero Shadows, Zero Emojis
 * Visible on mobile (<lg), hidden on desktop (lg:hidden)
 */

$currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$searchType = $_GET['type'] ?? '';

// Active state detection
$isHome = ($currentScript === 'index.php');
$isPackage = ($currentScript === 'package-details.php' || ($currentScript === 'search.php' && $searchType === 'package'));
$isFlight = ($currentScript === 'search.php' && $searchType === 'flight');
$isHotel = ($currentScript === 'hotel-details.php' || ($currentScript === 'search.php' && $searchType === 'hotel'));
$bnWhatsapp = function_exists('getSetting') ? getSetting('site_whatsapp', '919876543210') : '919876543210';
$bnSiteName = function_exists('getSetting') ? getSetting('site_name', 'Orion Advent') : 'Orion Advent';
$bnWaMsg = urlencode("Hello {$bnSiteName}, I would like to inquire about a trip");
?>

<!-- Mobile App Bottom Navigation (Fixed at bottom for mobile viewports) -->
<nav id="mobileBottomNav" class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 select-none" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <div class="grid grid-cols-5 h-[60px] max-w-lg mx-auto">
        
        <!-- 1. Explore / Home -->
        <a href="index.php" class="relative group flex flex-col items-center justify-center transition active:scale-95 <?= $isHome ? 'text-brand-600' : 'text-slate-500 hover:text-slate-800' ?>">
            <?php if ($isHome): ?>
                <span class="absolute top-0 inset-x-4 h-0.5 bg-brand-600 rounded-full"></span>
            <?php endif; ?>
            <div class="relative">
                <i class="fa-solid fa-compass text-lg mb-0.5 <?= $isHome ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-600' ?>"></i>
            </div>
            <span class="text-[10px] <?= $isHome ? 'font-bold text-brand-600' : 'font-medium text-slate-500 group-hover:text-slate-800' ?> tracking-tight">Explore</span>
        </a>

        <!-- 2. Holiday Packages -->
        <a href="search.php?type=package" class="relative group flex flex-col items-center justify-center transition active:scale-95 <?= $isPackage ? 'text-brand-600' : 'text-slate-500 hover:text-slate-800' ?>">
            <?php if ($isPackage): ?>
                <span class="absolute top-0 inset-x-4 h-0.5 bg-brand-600 rounded-full"></span>
            <?php endif; ?>
            <div class="relative">
                <i class="fa-solid fa-map-location-dot text-lg mb-0.5 <?= $isPackage ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-600' ?>"></i>
            </div>
            <span class="text-[10px] <?= $isPackage ? 'font-bold text-brand-600' : 'font-medium text-slate-500 group-hover:text-slate-800' ?> tracking-tight">Packages</span>
        </a>

        <!-- 3. Flights -->
        <a href="search.php?type=flight" class="relative group flex flex-col items-center justify-center transition active:scale-95 <?= $isFlight ? 'text-brand-600' : 'text-slate-500 hover:text-slate-800' ?>">
            <?php if ($isFlight): ?>
                <span class="absolute top-0 inset-x-4 h-0.5 bg-brand-600 rounded-full"></span>
            <?php endif; ?>
            <div class="relative">
                <i class="fa-solid fa-plane-departure text-lg mb-0.5 <?= $isFlight ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-600' ?>"></i>
            </div>
            <span class="text-[10px] <?= $isFlight ? 'font-bold text-brand-600' : 'font-medium text-slate-500 group-hover:text-slate-800' ?> tracking-tight">Flights</span>
        </a>

        <!-- 4. Hotels -->
        <a href="search.php?type=hotel" class="relative group flex flex-col items-center justify-center transition active:scale-95 <?= $isHotel ? 'text-brand-600' : 'text-slate-500 hover:text-slate-800' ?>">
            <?php if ($isHotel): ?>
                <span class="absolute top-0 inset-x-4 h-0.5 bg-brand-600 rounded-full"></span>
            <?php endif; ?>
            <div class="relative">
                <i class="fa-solid fa-hotel text-lg mb-0.5 <?= $isHotel ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-600' ?>"></i>
            </div>
            <span class="text-[10px] <?= $isHotel ? 'font-bold text-brand-600' : 'font-medium text-slate-500 group-hover:text-slate-800' ?> tracking-tight">Hotels</span>
        </a>

        <!-- 5. 24x7 WhatsApp Concierge -->
        <a href="https://wa.me/<?= htmlspecialchars($bnWhatsapp) ?>?text=<?= $bnWaMsg ?>" target="_blank" rel="noopener noreferrer" class="relative group flex flex-col items-center justify-center transition active:scale-95 text-slate-500 hover:text-emerald-600">
            <div class="relative">
                <i class="fa-brands fa-whatsapp text-xl text-emerald-500 group-hover:scale-110 transition-transform mb-0.5"></i>
                <span class="absolute -top-1 -right-2 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
            </div>
            <span class="text-[10px] font-semibold text-slate-600 group-hover:text-emerald-700 tracking-tight">Concierge</span>
        </a>

    </div>
</nav>
