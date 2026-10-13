<?php
$isUserLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$loggedInUserName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Traveler';
$loggedInUserEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$siteName = getSetting('site_name', 'Orion Advent');
$sitePhone = getSetting('site_phone', '+91 98765 43210');
$siteWhatsapp = getSetting('site_whatsapp', '919876543210');
$siteLogo = getSetting('site_logo', 'assets/images/logo/logo.webp');

$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$authRedirectQuery = '';
if (!in_array($currentScript, ['login.php', 'signup.php', 'forgot-password.php', 'reset-password.php'])) {
    $authRedirectQuery = '?redirect=' . urlencode($currentUri);
}

// Active navigation state resolution
$currentSearchType = $_GET['type'] ?? '';
$activeNav = '';
if ($currentScript === 'flights.php' || ($currentScript === 'search.php' && $currentSearchType === 'flight')) {
    $activeNav = 'flights';
} elseif ($currentScript === 'hotel-details.php' || ($currentScript === 'search.php' && $currentSearchType === 'hotel')) {
    $activeNav = 'hotels';
} elseif ($currentScript === 'contact.php' || $currentScript === 'help.php') {
    $activeNav = 'contact';
} elseif ($currentScript === 'about.php') {
    $activeNav = 'about';
} elseif ($currentScript === 'package-details.php' || ($currentScript === 'search.php' && $currentSearchType === 'package')) {
    $activeNav = 'packages';
}

$isPackagesActive = ($activeNav === 'packages');
$isFlightsActive  = ($activeNav === 'flights');
$isHotelsActive   = ($activeNav === 'hotels');
$isHolidaysActive = ($activeNav === 'holidays');
$isContactActive  = ($activeNav === 'contact');
$isAboutActive    = ($activeNav === 'about');
?>
<!-- Dynamic Navbar Header - Full Width, 100% Flat Border-First (No Shadows, Icon Library Only) -->
<header class="w-full sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200">
    <!-- Top Utility Bar (Logo Brand Color Background: bg-brand-600) -->
    <div class="w-full bg-brand-600 text-white text-[11px] sm:text-xs py-1 sm:py-2 px-3 sm:px-8 xl:px-12 border-b border-brand-700/50">
        <div class="w-full flex items-center justify-between gap-2 overflow-hidden">
<!-- Left: Travel Assistance Message -->
<div class="flex items-center space-x-1.5 sm:space-x-3 min-w-0 truncate">

    <span class="inline-flex items-center space-x-1 px-2 sm:px-2.5 py-0.5 rounded-full text-[9px] sm:text-[11px] font-bold bg-white/20 text-white border border-white/30 shrink-0">
        <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
            <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-white"></span>
        </span>
        <span class="hidden xs:inline">Travel Assistance</span>
        <span class="xs:hidden">Support</span>
    </span>

    <span class="text-teal-50 font-medium text-[10px] sm:text-xs truncate">
        <span class="hidden sm:inline">
            Your Journey, Our Priority
        </span>
        <span class="sm:hidden">
            We're Here to Help
        </span>
    </span>

</div>

            <!-- Right: WhatsApp, Helpline & Currency -->
            <div class="flex items-center space-x-2 sm:space-x-4 shrink-0">
                <a href="https://wa.me/<?php echo htmlspecialchars($siteWhatsapp); ?>" target="_blank" class="inline-flex items-center space-x-1 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full bg-white/15 text-white border border-white/25 hover:bg-white/25 transition text-[10px] sm:text-xs font-semibold">
                    <i class="fa-brands fa-whatsapp text-[11px] sm:text-sm text-emerald-300"></i>
                    <span class="hidden xs:inline">WhatsApp</span>
                </a>

                <a href="tel:<?php echo htmlspecialchars($sitePhone); ?>" class="flex items-center space-x-1 hover:text-teal-100 transition text-[10px] sm:text-xs text-white">
                    <i class="fa-solid fa-phone text-[9px] sm:text-xs text-teal-200"></i>
                    <span class="hidden md:inline text-teal-100">24x7:</span> <strong class="text-white font-medium text-[10px] sm:text-xs"><?php echo htmlspecialchars($sitePhone); ?></strong>
                </a>

                <span class="text-teal-400 hidden md:inline">|</span>

                <div class="hidden md:flex items-center space-x-1 px-2.5 py-0.5 rounded-full bg-white/15 border border-white/25 cursor-pointer hover:bg-white/25 transition">
                    <i class="fa-solid fa-indian-rupee-sign text-[11px]"></i>
                    <span>INR (₹)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar (Compact on Mobile, Full-Size on Desktop) -->
    <div class="w-full px-3 sm:px-8 xl:px-12">
        <div class="flex items-center justify-between h-14 sm:h-20 md:h-24">
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center py-1 sm:py-2 shrink-0 group focus:outline-none">
                <img src="<?php echo htmlspecialchars($siteLogo); ?>" 
                     alt="<?php echo htmlspecialchars($siteName); ?> - Your Passport to Adventure" 
                     class="h-9 sm:h-16 md:h-18 w-auto object-contain transition-transform duration-200 group-hover:scale-105"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="hidden items-center gap-2 font-space font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight">
                    <span class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center text-xs sm:text-base"><i class="fa-solid fa-compass"></i></span>
                    <span><?php echo htmlspecialchars($siteName); ?></span>
                </span>
            </a>

            <!-- Center Navigation: Clean Rounded-Full Capsule -->
            <nav class="hidden lg:flex items-center p-1 bg-slate-50 rounded-full border border-slate-200">
                
                <!-- Tour Packages -->
                <a href="search.php?type=package" class="flex items-center space-x-1.5 px-4 py-2 rounded-full text-sm transition-all <?= $isPackagesActive ? 'bg-white text-brand-700 border border-brand-200 font-bold shadow-xs' : 'text-slate-600 hover:text-brand-700 hover:bg-white border border-transparent font-medium' ?>">
                    <i class="fa-solid fa-map-location-dot text-xs <?= $isPackagesActive ? 'text-brand-600' : 'text-slate-400' ?>"></i>
                    <span>Tour Packages</span>
                </a>

                <!-- Flights -->
                <a href="flights.php" class="flex items-center space-x-1.5 px-4 py-2 rounded-full text-sm transition-all <?= $isFlightsActive ? 'bg-white text-brand-700 border border-brand-200 font-bold shadow-xs' : 'text-slate-600 hover:text-brand-700 hover:bg-white border border-transparent font-medium' ?>">
                    <i class="fa-solid fa-plane-departure text-xs <?= $isFlightsActive ? 'text-brand-600' : 'text-slate-400' ?>"></i>
                    <span>Flights</span>
                </a>

                <!-- Hotels -->
                <a href="search.php?type=hotel" class="flex items-center space-x-1.5 px-4 py-2 rounded-full text-sm transition-all <?= $isHotelsActive ? 'bg-white text-brand-700 border border-brand-200 font-bold shadow-xs' : 'text-slate-600 hover:text-brand-700 hover:bg-white border border-transparent font-medium' ?>">
                    <i class="fa-solid fa-hotel text-xs <?= $isHotelsActive ? 'text-brand-600' : 'text-slate-400' ?>"></i>
                    <span>Hotels</span>
                </a>

                <!-- Cruises -->
                <a href="cruises.php" class="flex items-center space-x-1.5 px-4 py-2 rounded-full text-sm transition-all <?= (isset($activeNav) && $activeNav === 'cruises') ? 'bg-white text-brand-700 border border-brand-200 font-bold shadow-xs' : 'text-slate-600 hover:text-brand-700 hover:bg-white border border-transparent font-medium' ?>">
                    <i class="fa-solid fa-ship text-xs <?= (isset($activeNav) && $activeNav === 'cruises') ? 'text-brand-600' : 'text-slate-400' ?>"></i>
                    <span>Cruises</span>
                </a>

                <!-- Holidays -->
                <a href="search.php?type=package" class="flex items-center space-x-1.5 px-4 py-2 rounded-full text-sm transition-all <?= $isHolidaysActive ? 'bg-white text-brand-700 border border-brand-200 font-bold shadow-xs' : 'text-slate-600 hover:text-brand-700 hover:bg-white border border-transparent font-medium' ?>">
                    <i class="fa-solid fa-umbrella-beach text-xs <?= $isHolidaysActive ? 'text-brand-600' : 'text-slate-400' ?>"></i>
                    <span>Holidays</span>
                </a>

                <!-- Contact & Help -->
                <a href="contact.php" class="flex items-center space-x-1.5 px-4 py-2 rounded-full text-sm transition-all <?= $isContactActive ? 'bg-white text-brand-700 border border-brand-200 font-bold shadow-xs' : 'text-slate-600 hover:text-brand-700 hover:bg-white border border-transparent font-medium' ?>">
                    <i class="fa-solid fa-headset text-xs <?= $isContactActive ? 'text-brand-600' : 'text-slate-400' ?>"></i>
                    <span>Help &amp; Contact</span>
                </a>
            </nav>

            <!-- Right Actions: Guest OR Logged-In User Profile Dropdown -->
            <div class="hidden md:flex items-center space-x-3">
                <?php if ($isUserLoggedIn): ?>
                    <!-- Logged In User Dropdown Capsule -->
                    <div class="relative group">
                        <button type="button" class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-full bg-slate-50 hover:bg-slate-100 border border-slate-200 transition focus:outline-none cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold text-xs flex items-center justify-center uppercase shadow-xs">
                                <?php echo substr($loggedInUserName, 0, 1); ?>
                            </div>
                            <div class="text-left leading-none">
                                <span class="block text-xs font-bold text-slate-800"><?php echo htmlspecialchars(explode(' ', $loggedInUserName)[0]); ?></span>
                                <span class="text-[10px] text-teal-600 font-semibold">Traveler Member</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:rotate-180 transition-transform duration-200 ml-1"></i>
                        </button>

                        <!-- User Profile Dropdown Menu -->
                        <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 z-50 animate-pop-in">
                            <div class="px-4 py-2.5 border-b border-slate-100">
                                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Signed in as</p>
                                <p class="text-xs font-bold text-slate-900 truncate"><?php echo htmlspecialchars($loggedInUserName); ?></p>
                                <p class="text-[11px] text-slate-500 truncate"><?php echo htmlspecialchars($loggedInUserEmail); ?></p>
                            </div>

                            <div class="py-1">
                                <a href="my-trips.php" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                    <i class="fa-solid fa-suitcase-rolling text-slate-400 w-4 text-center"></i>
                                    <span>My Bookings &amp; Trips</span>
                                </a>
                                <a href="search.php" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                    <i class="fa-solid fa-compass text-slate-400 w-4 text-center"></i>
                                    <span>Explore Holidays</span>
                                </a>
                            </div>

                            <div class="pt-1 border-t border-slate-100">
                                <a href="javascript:void(0)" onclick="
                                    fetch('api/auth.php?action=logout').then(() => window.location.reload());
                                " class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
                                    <span>Sign Out</span>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Guest: Log In and Sign Up Buttons -->
                    <a href="login.php<?php echo $authRedirectQuery; ?>" class="rounded-full px-5 py-2.5 text-xs font-bold text-slate-700 hover:text-brand-700 hover:bg-slate-100 border border-slate-300 transition flex items-center space-x-1.5">
                        <i class="fa-regular fa-user text-xs text-slate-500"></i>
                        <span>Log In</span>
                    </a>

                    <a href="signup.php<?php echo $authRedirectQuery; ?>" class="rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold px-6 py-2.5 transition active:scale-95 flex items-center space-x-1.5">
                        <i class="fa-solid fa-user-plus text-xs text-teal-200"></i>
                        <span>Sign Up</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex lg:hidden items-center">
                <button id="mobileMenuBtn" type="button" class="p-2.5 rounded-full text-slate-700 hover:text-brand-600 hover:bg-brand-50 transition focus:outline-none" aria-label="Toggle Menu">
                    <i id="hamburgerIcon" class="fa-solid fa-bars text-lg"></i>
                    <i id="closeIcon" class="fa-solid fa-xmark text-lg hidden"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-200 bg-white px-5 py-5 space-y-4 max-h-[calc(100vh-80px)] overflow-y-auto">
        <?php if ($isUserLoggedIn): ?>
            <!-- Mobile Logged-in profile badge -->
            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-brand-600 text-white font-bold text-sm flex items-center justify-center uppercase shrink-0">
                        <?php echo substr($loggedInUserName, 0, 1); ?>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-800 truncate"><?php echo htmlspecialchars($loggedInUserName); ?></p>
                        <span class="text-[11px] text-slate-500 truncate block"><?php echo htmlspecialchars($loggedInUserEmail); ?></span>
                    </div>
                </div>
                <a href="javascript:void(0)" onclick="fetch('api/auth.php?action=logout').then(() => window.location.reload());" class="p-2 text-rose-600 text-xs font-bold hover:bg-rose-50 rounded-lg">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        <?php endif; ?>

        <!-- Service Categories -->
        <div class="grid grid-cols-2 gap-2">
            <a href="search.php?type=package" class="flex items-center space-x-2 p-3 rounded-2xl <?= $isPackagesActive ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/80' : 'bg-slate-50 text-slate-700 font-medium border border-slate-200 hover:bg-slate-100 hover:text-brand-700' ?> text-xs justify-center transition">
                <i class="fa-solid fa-map-location-dot text-sm <?= $isPackagesActive ? 'text-brand-600' : 'text-slate-500' ?>"></i>
                <span>Tour Packages</span>
            </a>
            <a href="flights.php" class="flex items-center space-x-2 p-3 rounded-2xl <?= $isFlightsActive ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/80' : 'bg-slate-50 text-slate-700 font-medium border border-slate-200 hover:bg-slate-100 hover:text-brand-700' ?> text-xs justify-center transition">
                <i class="fa-solid fa-plane-departure text-sm <?= $isFlightsActive ? 'text-brand-600' : 'text-slate-500' ?>"></i>
                <span>Flights</span>
            </a>
            <a href="search.php?type=hotel" class="flex items-center space-x-2 p-3 rounded-2xl <?= $isHotelsActive ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/80' : 'bg-slate-50 text-slate-700 font-medium border border-slate-200 hover:bg-slate-100 hover:text-brand-700' ?> text-xs justify-center transition">
                <i class="fa-solid fa-hotel text-sm <?= $isHotelsActive ? 'text-brand-600' : 'text-slate-500' ?>"></i>
                <span>Hotels</span>
            </a>
            <a href="cruises.php" class="flex items-center space-x-2 p-3 rounded-2xl <?= (isset($activeNav) && $activeNav === 'cruises') ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/80' : 'bg-slate-50 text-slate-700 font-medium border border-slate-200 hover:bg-slate-100 hover:text-brand-700' ?> text-xs justify-center transition">
                <i class="fa-solid fa-ship text-sm <?= (isset($activeNav) && $activeNav === 'cruises') ? 'text-brand-600' : 'text-slate-500' ?>"></i>
                <span>Cruises</span>
            </a>
            <a href="search.php?type=package" class="flex items-center space-x-2 p-3 rounded-2xl <?= $isHolidaysActive ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/80' : 'bg-slate-50 text-slate-700 font-medium border border-slate-200 hover:bg-slate-100 hover:text-brand-700' ?> text-xs justify-center transition">
                <i class="fa-solid fa-umbrella-beach text-sm <?= $isHolidaysActive ? 'text-brand-600' : 'text-slate-500' ?>"></i>
                <span>Holidays</span>
            </a>
            <a href="about.php" class="flex items-center space-x-2 p-3 rounded-2xl <?= $isAboutActive ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/80' : 'bg-slate-50 text-slate-700 font-medium border border-slate-200 hover:bg-slate-100 hover:text-brand-700' ?> text-xs justify-center transition">
                <i class="fa-solid fa-circle-info text-sm <?= $isAboutActive ? 'text-brand-600' : 'text-slate-500' ?>"></i>
                <span>About Us</span>
            </a>
            <a href="contact.php" class="flex items-center space-x-2 p-3 rounded-2xl <?= $isContactActive ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/80' : 'bg-slate-50 text-slate-700 font-medium border border-slate-200 hover:bg-slate-100 hover:text-brand-700' ?> text-xs justify-center col-span-2 transition">
                <i class="fa-solid fa-headset text-sm <?= $isContactActive ? 'text-brand-600' : 'text-slate-500' ?>"></i>
                <span>24x7 Help &amp; Contact Concierge</span>
            </a>
        </div>

        <!-- Quick Destination Shortcuts (Domestic & International) -->
        <div class="pt-3 border-t border-slate-100 space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Popular Holiday Circuits</span>
            <div class="flex flex-wrap gap-1.5">
                <a href="search.php?type=package&query=Kashmir" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80">Kashmir</a>
                <a href="search.php?type=package&query=Himachal" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80">Himachal</a>
                <a href="search.php?type=package&query=Kerala" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80">Kerala</a>
                <a href="search.php?type=package&query=Goa" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold hover:bg-brand-50 hover:text-brand-700 border border-slate-200/80">Goa</a>
                <a href="search.php?type=package&query=Dubai" class="px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-[11px] font-semibold border border-brand-200">Dubai</a>
                <a href="search.php?type=package&query=Bali" class="px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-[11px] font-semibold border border-brand-200">Bali</a>
                <a href="search.php?type=package&query=Thailand" class="px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-[11px] font-semibold border border-brand-200">Thailand</a>
            </div>
        </div>

        <?php if (!$isUserLoggedIn): ?>
            <!-- Mobile Guest Login & Sign Up -->
            <div class="pt-3 border-t border-slate-100">
                <div class="grid grid-cols-2 gap-2">
                    <a href="login.php<?php echo $authRedirectQuery; ?>" class="flex items-center justify-center space-x-1.5 py-2.5 rounded-full text-slate-700 border border-slate-300 font-bold text-xs hover:bg-slate-50">
                        <i class="fa-regular fa-user text-xs text-slate-500"></i>
                        <span>Log In</span>
                    </a>
                    <a href="signup.php<?php echo $authRedirectQuery; ?>" class="flex items-center justify-center space-x-1.5 py-2.5 rounded-full bg-brand-600 text-white font-bold text-xs hover:bg-brand-700">
                        <i class="fa-solid fa-user-plus text-xs text-teal-200"></i>
                        <span>Sign Up</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
