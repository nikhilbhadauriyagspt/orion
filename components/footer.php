<?php
/**
 * Footer Component - Orion Advent
 * - Pre-Footer Newsletter & Travel Club Strip
 * - 5-Column Comprehensive Travel Portal Directory
 * - Payment Partner Badges & Official Tourism Accreditations
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */
require_once __DIR__ . '/../config/settings.php';
$fSiteName = getSetting('site_name', 'Orion Advent');
$fSitePhone = getSetting('site_phone', '+91 98765 43210');
$fSiteEmail = getSetting('site_email', 'support@orionadvent.com');
$fSiteAddress = getSetting('site_address', 'DLF Cyber City, Tower B, Gurugram, Haryana - 122002');
$fSiteLogo = getSetting('site_logo', 'assets/images/logo/logo.webp');
$fSiteWhatsapp = getSetting('site_whatsapp', '919876543210');
$fSocialFb = getSetting('social_facebook', '');
$fSocialInsta = getSetting('social_instagram', '');
$fSocialYt = getSetting('social_youtube', '');
$fSocialTw = getSetting('social_twitter', '');
$fSocialLi = getSetting('social_linkedin', '');
?>
<!-- ==========================================
     PRE-FOOTER: EXCLUSIVE TRAVEL CLUB
=========================================== -->

<!-- ==========================================
     MAIN PORTAL FOOTER (5-COLUMN DIRECTORY)
=========================================== -->
<footer class="bg-slate-900 text-slate-300 border-t border-slate-800 pt-16 pb-28 lg:pb-12 px-4 sm:px-8 xl:px-12 relative overflow-hidden select-none">
    <div class="max-w-7xl mx-auto space-y-14">

        <!-- 1. Five Navigation Columns Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">
            
            <!-- Col 1: Brand Info & 24x7 Concierge (4 Cols) -->
            <div class="lg:col-span-4 space-y-5">
                <!-- Dynamic Brand Logo (Logo only, no extra text) -->
                <a href="index.php" class="inline-block group">
                    <div class="bg-white px-3 py-2 rounded-xl  border border-white/20 inline-flex items-center">
                        <img src="<?php echo htmlspecialchars($fSiteLogo); ?>" 
                             alt="<?php echo htmlspecialchars($fSiteName); ?>" 
                             class="h-9 sm:h-16 w-auto object-contain transition-transform group-hover:scale-105"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="hidden items-center gap-1.5 font-space font-black text-slate-900 text-lg">
                            <i class="fa-solid fa-compass text-teal-600"></i>
                            <span><?php echo htmlspecialchars($fSiteName); ?></span>
                        </span>
                    </div>
                </a>

                <p class="text-xs sm:text-[13px] text-slate-400 leading-relaxed font-normal">
                    India's leading experiential holiday portal. We craft deeply personalized vacations across 45+ domestic circuits and international destinations with 100% price transparency and on-trip concierge care.
                </p>

                <!-- Direct Dynamic Contact Details -->
                <div class="space-y-2.5 text-xs text-slate-300 pt-1">
                    <div class="flex items-center space-x-3">
                        <span class="w-7 h-7 rounded-lg bg-slate-800 text-brand-400 flex items-center justify-center shrink-0 border border-slate-700">
                            <i class="fa-solid fa-phone text-[11px]"></i>
                        </span>
                        <div>
                            <a href="tel:<?php echo htmlspecialchars($fSitePhone); ?>" class="block text-white font-bold hover:text-brand-300 transition">
                                <?php echo htmlspecialchars($fSitePhone); ?>
                            </a>
                            <span class="text-[10px] text-slate-400">24x7 Dedicated Trip Helpdesk</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <span class="w-7 h-7 rounded-lg bg-slate-800 text-brand-400 flex items-center justify-center shrink-0 border border-slate-700">
                            <i class="fa-solid fa-envelope text-[11px]"></i>
                        </span>
                        <a href="mailto:<?php echo htmlspecialchars($fSiteEmail); ?>" class="text-slate-300 hover:text-white transition font-medium">
                            <?php echo htmlspecialchars($fSiteEmail); ?>
                        </a>
                    </div>

                    <div class="flex items-start space-x-3">
                        <span class="w-7 h-7 rounded-lg bg-slate-800 text-brand-400 flex items-center justify-center shrink-0 border border-slate-700 mt-0.5">
                            <i class="fa-solid fa-location-dot text-[11px]"></i>
                        </span>
                        <span class="text-slate-400 font-normal leading-relaxed text-xs">
                            <?php echo htmlspecialchars($fSiteAddress); ?>
                        </span>
                    </div>
                </div>

                <!-- Dynamic Social Handles -->
                <div class="flex items-center space-x-2 pt-2">
                    <?php if (!empty($fSocialFb)): ?>
                        <a href="<?php echo htmlspecialchars($fSocialFb); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 flex items-center justify-center transition">
                            <i class="fa-brands fa-facebook-f text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($fSocialInsta)): ?>
                        <a href="<?php echo htmlspecialchars($fSocialInsta); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 flex items-center justify-center transition">
                            <i class="fa-brands fa-instagram text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($fSocialYt)): ?>
                        <a href="<?php echo htmlspecialchars($fSocialYt); ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 flex items-center justify-center transition">
                            <i class="fa-brands fa-youtube text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($fSocialTw)): ?>
                        <a href="<?php echo htmlspecialchars($fSocialTw); ?>" target="_blank" rel="noopener noreferrer" aria-label="X Twitter" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 flex items-center justify-center transition">
                            <i class="fa-brands fa-x-twitter text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($fSocialLi)): ?>
                        <a href="<?php echo htmlspecialchars($fSocialLi); ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white border border-slate-700 flex items-center justify-center transition">
                            <i class="fa-brands fa-linkedin-in text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($fSiteWhatsapp)): ?>
                        <a href="https://wa.me/<?php echo htmlspecialchars($fSiteWhatsapp); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-emerald-600 text-slate-300 hover:text-white border border-slate-700 flex items-center justify-center transition">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

<?php
// Ensure database connection is present for dynamic footer links
if (!function_exists('getDBConnection')) {
    require_once __DIR__ . '/../config/db.php';
}
$footerDb = getDBConnection();

// 1. Fetch Dynamic Domestic Packages (up to 8)
$footerDomPackages = [];
// 2. Fetch Dynamic International Packages (up to 8)
$footerIntlPackages = [];
// 3. Fetch Distinct Themes or Categories
$footerThemes = [];

if ($footerDb) {
    try {
        // Domestic
        $domStmt = $footerDb->query("SELECT id, title, slug, location FROM `packages` WHERE `category` = 'domestic' AND `status` = 'active' ORDER BY `id` DESC LIMIT 8");
        $footerDomPackages = $domStmt->fetchAll(PDO::FETCH_ASSOC);

        // International
        $intlStmt = $footerDb->query("SELECT id, title, slug, location, state_country FROM `packages` WHERE `category` = 'international' AND `status` = 'active' ORDER BY `id` DESC LIMIT 8");
        $footerIntlPackages = $intlStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Silently catch and fallback to defaults
    }
}

// Fallback arrays if database has fewer packages
if (empty($footerDomPackages)) {
    $footerDomPackages = [
        ['title' => 'Kashmir Valley & Gulmarg', 'slug' => 'PKG-KASHMIR-01', 'id' => null],
        ['title' => 'Kerala Backwaters & Munnar', 'slug' => 'PKG-KERALA-02', 'id' => null],
        ['title' => 'Himachal Magic & Solang', 'slug' => 'PKG-MANALI-03', 'id' => null],
        ['title' => 'Goa Beachfront Resorts', 'slug' => 'PKG-GOA-04', 'id' => null],
        ['title' => 'Andaman Islands & Scuba', 'slug' => 'PKG-ANDAMAN-05', 'id' => null],
        ['title' => 'Royal Rajasthan Palaces', 'slug' => 'PKG-RAJASTHAN-06', 'id' => null],
        ['title' => 'Leh Ladakh Expedition', 'slug' => 'PKG-LADAKH-07', 'id' => null],
        ['title' => 'Uttarakhand River Rafting', 'slug' => 'PKG-RISHIKESH-08', 'id' => null],
    ];
}

if (empty($footerIntlPackages)) {
    $footerIntlPackages = [
        ['title' => 'Dubai & Burj Khalifa', 'slug' => 'INTL-DXB-01', 'id' => null],
        ['title' => 'Bali Romantic Escapes', 'slug' => 'INTL-BALI-02', 'id' => null],
        ['title' => 'Thailand Island Explorer', 'slug' => 'INTL-THAI-03', 'id' => null],
        ['title' => 'Singapore & Sentosa Island', 'slug' => 'INTL-SIN-04', 'id' => null],
        ['title' => 'Maldives Overwater Villas', 'slug' => 'INTL-MLD-05', 'id' => null],
        ['title' => 'Vietnam & Halong Cruise', 'slug' => 'INTL-VTN-06', 'id' => null],
        ['title' => 'Switzerland Alpine Express', 'slug' => '', 'id' => null, 'search' => 'Switzerland'],
        ['title' => 'Mauritius Luxury Beach', 'slug' => '', 'id' => null, 'search' => 'Mauritius'],
    ];
}
?>
            <!-- Col 2: Top Domestic Circuits (2 Cols) -->
            <div class="lg:col-span-2 space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white border-b border-slate-800 pb-2 flex items-center">
                    <i class="fa-solid fa-map-location-dot text-brand-400 mr-2"></i>
                    <span>Domestic Tours</span>
                </h4>
                <ul class="space-y-2 text-xs font-normal text-slate-400">
                    <?php foreach ($footerDomPackages as $fdp): 
                        if (!empty($fdp['slug'])) {
                            $fLink = 'package-details.php?id=' . urlencode($fdp['slug']);
                        } elseif (!empty($fdp['id'])) {
                            $fLink = 'package-details.php?id=' . urlencode($fdp['id']);
                        } elseif (!empty($fdp['search'])) {
                            $fLink = 'search.php?type=domestic&query=' . urlencode($fdp['search']);
                        } else {
                            $fLink = 'search.php?type=domestic&query=' . urlencode($fdp['title']);
                        }
                    ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($fLink); ?>" class="hover:text-white transition flex items-center group">
                                <i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i>
                                <span class="truncate"><?php echo htmlspecialchars($fdp['title']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Col 3: International Getaways (2 Cols) -->
            <div class="lg:col-span-2 space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white border-b border-slate-800 pb-2 flex items-center">
                    <i class="fa-solid fa-earth-americas text-brand-400 mr-2"></i>
                    <span>International</span>
                </h4>
                <ul class="space-y-2 text-xs font-normal text-slate-400">
                    <?php foreach ($footerIntlPackages as $fip): 
                        if (!empty($fip['slug'])) {
                            $fLink = 'package-details.php?id=' . urlencode($fip['slug']);
                        } elseif (!empty($fip['id'])) {
                            $fLink = 'package-details.php?id=' . urlencode($fip['id']);
                        } elseif (!empty($fip['search'])) {
                            $fLink = 'search.php?type=international&query=' . urlencode($fip['search']);
                        } else {
                            $fLink = 'search.php?type=international&query=' . urlencode($fip['title']);
                        }
                    ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($fLink); ?>" class="hover:text-white transition flex items-center group">
                                <i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i>
                                <span class="truncate"><?php echo htmlspecialchars($fip['title']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Col 4: Holiday Themes (2 Cols) -->
            <div class="lg:col-span-2 space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white border-b border-slate-800 pb-2 flex items-center">
                    <i class="fa-solid fa-compass text-brand-400 mr-2"></i>
                    <span>Travel By Vibe</span>
                </h4>
                <ul class="space-y-2 text-xs font-normal text-slate-400">
                    <li><a href="search.php?theme=honeymoon" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Honeymoon Specials</span></a></li>
                    <li><a href="search.php?theme=adventure" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Snow Peaks &amp; Treks</span></a></li>
                    <li><a href="search.php?theme=beach" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Tropical Beach Vacations</span></a></li>
                    <li><a href="search.php?query=Heritage" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Royal Forts &amp; Havelis</span></a></li>
                    <li><a href="search.php?query=Wildlife" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Wildlife &amp; Tiger Safaris</span></a></li>
                    <li><a href="search.php?theme=spiritual" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Spiritual &amp; Yoga Retreats</span></a></li>
                    <li><a href="search.php?max_price=9999" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Weekend Trips Under ₹9,999</span></a></li>
                    <li><a href="search.php?type=hotel" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>5★ Luxury Resorts</span></a></li>
                </ul>
            </div>

            <!-- Col 5: Trust, Safety & Company (2 Cols) -->
            <div class="lg:col-span-2 space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white border-b border-slate-800 pb-2 flex items-center">
                    <i class="fa-solid fa-shield-halved text-brand-400 mr-2"></i>
                    <span>Trust &amp; Policies</span>
                </h4>
                <ul class="space-y-2 text-xs font-normal text-slate-400">
                    <li><a href="about.php" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>About <?php echo htmlspecialchars($fSiteName); ?></span></a></li>
                    <li><a href="careers.php" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span class="font-semibold text-brand-300 group-hover:text-white">Careers &amp; Hiring</span><span class="ml-1.5 px-1.5 py-0.2 rounded text-[9px] font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30">We're Hiring</span></a></li>
                    <li><a href="privacy-policy.php" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Privacy Policy</span></a></li>
                    <li><a href="terms.php" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Terms &amp; Conditions</span></a></li>
                    <li><a href="cancellation-policy.php" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Cancellation &amp; Refund</span></a></li>
                    <li><a href="policies.php?tab=booking" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Token Advance Guarantee</span></a></li>
                    <li><a href="contact.php" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>24x7 Help Concierge</span></a></li>
                    <li><a href="contact.php#faq" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>Frequently Asked Questions</span></a></li>
                    <li><a href="my-trips.php" class="hover:text-white transition flex items-center group"><i class="fa-solid fa-chevron-right text-[9px] text-slate-600 group-hover:text-brand-400 mr-1.5 transition"></i><span>My Trips &amp; Vouchers</span></a></li>
                </ul>
            </div>

        </div>

        <!-- 2. Payment Security & Partner Accreditations Strip -->
        <div class="pt-8 border-t border-slate-800/80 flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-slate-400">
            
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Accepted Payment Modes:</span>
                <span class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-white font-bold text-[11px]">UPI</span>
                <span class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-white font-bold text-[11px]">Visa</span>
                <span class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-white font-bold text-[11px]">Mastercard</span>
                <span class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-white font-bold text-[11px]">RuPay</span>
                <span class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-white font-bold text-[11px]">NetBanking</span>
            </div>

            <div class="flex flex-wrap items-center gap-5 text-slate-400 font-semibold">
                <div class="flex items-center space-x-1.5">
                    <i class="fa-solid fa-lock text-emerald-400 text-xs"></i>
                    <span>256-Bit SSL Secured</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <i class="fa-solid fa-shield-halved text-brand-400 text-xs"></i>
                    <span>Verified Travel Partners</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                    <span>4.5 ★ Rated Stays</span>
                </div>
            </div>

        </div>

        <!-- 3. Bottom Legal Disclaimer & Copyright -->
        <div class="pt-6 border-t border-slate-800/60 flex flex-col md:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <span>&copy; <?= date('Y') ?> <strong class="text-slate-400"><?php echo htmlspecialchars($fSiteName); ?> Holidays &amp; Stays Pvt. Ltd.</strong> All rights reserved.</span>
                <span class="text-slate-700 hidden sm:inline">&bull;</span>
                <a href="privacy-policy.php" class="hover:text-slate-300 transition">Privacy</a>
                <span class="text-slate-700">&bull;</span>
                <a href="terms.php" class="hover:text-slate-300 transition">Terms</a>
                <span class="text-slate-700">&bull;</span>
                <a href="cancellation-policy.php" class="hover:text-slate-300 transition">Refunds</a>
            </div>

            <div class="text-center md:text-right text-slate-500 font-normal">
                Fares &amp; inclusions are verified directly with certified airline &amp; hospitality partners. Fares are subject to seasonal availability.
            </div>
        </div>

    </div>
</footer>

<!-- Mobile Bottom Navigation (Visible on mobile/tablet <lg, hidden on desktop) -->
<?php require_once __DIR__ . '/bottom-nav.php'; ?>

<!-- Main Scripts -->
<script src="assets/js/main.js"></script>
</body>
</html>
