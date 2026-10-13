<?php
/**
 * Public Legal & Policies Hub
 * Features:
 * - Ultra-Modern Layout: Sticky Table of Contents, Reading Mode, Print Action
 * - Dynamic Content loaded from Database Settings
 * - Smooth tab switching between: Privacy Policy, Terms & Conditions, Cancellation & Refund, Booking Policy
 * - 100% Flat, Border-First, Zero Shadows
 * - Pure Font Awesome 6 Icons Only (Zero Emojis)
 */
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';

$activeTab = isset($_GET['tab']) ? strtolower(trim($_GET['tab'])) : 'privacy';
if (!in_array($activeTab, ['privacy', 'terms', 'cancellation', 'booking'])) {
    $activeTab = 'privacy';
}

$siteName = getSetting('site_name', 'Orion Advent');
$sitePhone = getSetting('site_phone', '+91 98765 43210');
$siteEmail = getSetting('site_email', 'support@orionadvent.com');
$lastUpdated = getSetting('policy_last_updated', 'October 2026');

function renderDynamicPolicyText($html, $name, $email, $phone) {
    if (empty($html)) return '';
    $placeholders = [
        '[SITE_NAME]',
        '{site_name}',
        '[SITE_EMAIL]',
        '{site_email}',
        '[SITE_PHONE]',
        '{site_phone}',
        'privacy@orionadvent.com'
    ];
    $replacements = [
        htmlspecialchars($name),
        htmlspecialchars($name),
        htmlspecialchars($email),
        htmlspecialchars($email),
        htmlspecialchars($phone),
        htmlspecialchars($phone),
        htmlspecialchars($email)
    ];
    return str_replace($placeholders, $replacements, $html);
}

$policies = [
    'privacy' => [
        'title' => 'Privacy Policy & Data Security',
        'subtitle' => 'How ' . htmlspecialchars($siteName) . ' collects, protects, and handles your personal identification and travel preferences.',
        'icon' => 'fa-solid fa-shield-halved',
        'content' => renderDynamicPolicyText(getSetting('policy_privacy'), $siteName, $siteEmail, $sitePhone)
    ],
    'terms' => [
        'title' => 'Terms & Conditions of Service',
        'subtitle' => 'General terms governing your bookings, flight reservations, hotel vouchers, and portal usage.',
        'icon' => 'fa-solid fa-file-contract',
        'content' => renderDynamicPolicyText(getSetting('policy_terms'), $siteName, $siteEmail, $sitePhone)
    ],
    'cancellation' => [
        'title' => 'Cancellation & Refund Slabs',
        'subtitle' => 'Transparent refund timelines, token advance guarantees, and airline/hotel cancellation policies.',
        'icon' => 'fa-solid fa-rotate-left',
        'content' => renderDynamicPolicyText(getSetting('policy_cancellation'), $siteName, $siteEmail, $sitePhone)
    ],
    'booking' => [
        'title' => 'Booking & Token Advance Policy',
        'subtitle' => 'Understanding token payments, price locks, balance dues upon check-in, and zero hidden checkout fees.',
        'icon' => 'fa-solid fa-credit-card',
        'content' => renderDynamicPolicyText(getSetting('policy_booking'), $siteName, $siteEmail, $sitePhone)
    ],
];

$curPolicy = $policies[$activeTab];
$pageTitle = $curPolicy['title'] . ' | ' . $siteName;

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ==========================================
     HERO BANNER & BREADCRUMB
=========================================== -->
<section class="w-full bg-slate-900 text-white py-12 sm:py-16 px-4 sm:px-8 xl:px-12 relative overflow-hidden border-b border-slate-800">
    <!-- Ambient Glow -->
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-600/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-teal-600/20 blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10 space-y-4">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-xs text-slate-400">
            <a href="index.php" class="hover:text-white transition">Home</a>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
            <span class="text-slate-400">Legal &amp; Policies</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
            <span class="text-teal-400 font-bold"><?php echo htmlspecialchars($curPolicy['title']); ?></span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2 max-w-3xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-teal-300 border border-white/15">
                    <i class="<?php echo $curPolicy['icon']; ?>"></i>
                    <span>Official Legal Compliance Document</span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black font-space tracking-tight text-white leading-tight">
                    <?php echo htmlspecialchars($curPolicy['title']); ?>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 font-normal leading-relaxed">
                    <?php echo htmlspecialchars($curPolicy['subtitle']); ?>
                </p>
            </div>

            <!-- Last Revised & Print Pill -->
            <div class="flex items-center gap-3 shrink-0">
                <span class="px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs text-slate-300 font-medium">
                    <i class="fa-regular fa-clock mr-1 text-teal-300"></i>
                    Last Revised: <strong class="text-white"><?php echo htmlspecialchars($lastUpdated); ?></strong>
                </span>
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-full bg-white hover:bg-slate-100 text-slate-900 border border-slate-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-print text-xs text-brand-600"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     MAIN TWO-COLUMN POLICIES CONTAINER
=========================================== -->
<main class="w-full py-12 px-4 sm:px-8 xl:px-12 bg-slate-50 border-b border-slate-200 min-h-screen">
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- LEFT COLUMN: STICKY POLICY SELECTOR & SUMMARY (Width: 320px) -->
        <aside class="w-full lg:w-80 shrink-0 space-y-5 lg:sticky lg:top-28">
            
            <!-- Policy Navigation Menu -->
            <div class="bg-white rounded-3xl border border-slate-200 p-4 sm:p-5 space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 py-1">
                    Select Policy Document
                </div>

                <a href="policies.php?tab=privacy" class="flex items-center justify-between p-3 rounded-2xl transition border <?php echo $activeTab === 'privacy' ? 'bg-brand-50 border-brand-200 text-brand-900 font-bold' : 'border-transparent text-slate-700 hover:bg-slate-50 font-semibold'; ?>">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl <?php echo $activeTab === 'privacy' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500'; ?> flex items-center justify-center text-xs">
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>
                        <span class="text-xs">Privacy Policy</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-[10px] <?php echo $activeTab === 'privacy' ? 'text-brand-600' : 'text-slate-300'; ?>"></i>
                </a>

                <a href="policies.php?tab=terms" class="flex items-center justify-between p-3 rounded-2xl transition border <?php echo $activeTab === 'terms' ? 'bg-brand-50 border-brand-200 text-brand-900 font-bold' : 'border-transparent text-slate-700 hover:bg-slate-50 font-semibold'; ?>">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl <?php echo $activeTab === 'terms' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500'; ?> flex items-center justify-center text-xs">
                            <i class="fa-solid fa-file-contract"></i>
                        </span>
                        <span class="text-xs">Terms &amp; Conditions</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-[10px] <?php echo $activeTab === 'terms' ? 'text-brand-600' : 'text-slate-300'; ?>"></i>
                </a>

                <a href="policies.php?tab=cancellation" class="flex items-center justify-between p-3 rounded-2xl transition border <?php echo $activeTab === 'cancellation' ? 'bg-brand-50 border-brand-200 text-brand-900 font-bold' : 'border-transparent text-slate-700 hover:bg-slate-50 font-semibold'; ?>">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl <?php echo $activeTab === 'cancellation' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500'; ?> flex items-center justify-center text-xs">
                            <i class="fa-solid fa-rotate-left"></i>
                        </span>
                        <span class="text-xs">Cancellation &amp; Refund</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-[10px] <?php echo $activeTab === 'cancellation' ? 'text-brand-600' : 'text-slate-300'; ?>"></i>
                </a>

                <a href="policies.php?tab=booking" class="flex items-center justify-between p-3 rounded-2xl transition border <?php echo $activeTab === 'booking' ? 'bg-brand-50 border-brand-200 text-brand-900 font-bold' : 'border-transparent text-slate-700 hover:bg-slate-50 font-semibold'; ?>">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl <?php echo $activeTab === 'booking' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500'; ?> flex items-center justify-center text-xs">
                            <i class="fa-solid fa-credit-card"></i>
                        </span>
                        <span class="text-xs">Booking &amp; Token Policy</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-[10px] <?php echo $activeTab === 'booking' ? 'text-brand-600' : 'text-slate-300'; ?>"></i>
                </a>
            </div>

            <!-- Support Concierge Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid fa-headset"></i>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">Have Policy Questions?</h4>
                        <p class="text-[11px] text-slate-500">24x7 Traveler Concierge Support</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <a href="tel:<?php echo htmlspecialchars($sitePhone); ?>" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                        <i class="fa-solid fa-phone text-brand-600 text-xs w-4 text-center"></i>
                        <span class="font-bold"><?php echo htmlspecialchars($sitePhone); ?></span>
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($siteEmail); ?>" class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition truncate">
                        <i class="fa-solid fa-envelope text-brand-600 text-xs w-4 text-center"></i>
                        <span class="font-medium truncate"><?php echo htmlspecialchars($siteEmail); ?></span>
                    </a>
                    <a href="contact.php" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition">
                        <span>Open Support Ticket</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </aside>

        <!-- RIGHT COLUMN: FULL POLICY RICH CONTENT -->
        <div class="flex-1 w-full space-y-6">
            
            <!-- Policy Content Container -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 space-y-6">
                
                <!-- Header Indicator -->
                <div class="flex items-center justify-between pb-5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-brand-600"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Document:</span>
                        <span class="text-xs font-black text-brand-700"><?php echo htmlspecialchars($curPolicy['title']); ?></span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400">
                        Governing Law: Republic of India
                    </span>
                </div>

                <!-- Rich Policy HTML Body (Styled with Tailwind Typography) -->
                <div class="prose prose-slate max-w-none text-xs sm:text-sm text-slate-700 leading-relaxed space-y-4 policy-content-area">
                    <?php echo $curPolicy['content']; ?>
                </div>

                <!-- Footer Signoff Strip -->
                <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-check text-emerald-600 text-base"></i>
                        <span>100% Certified &amp; Compliant Travel Platform</span>
                    </div>
                    <span>Authorized by <?php echo htmlspecialchars($siteName); ?> Legal Governance</span>
                </div>

            </div>

            <!-- Trust Banner -->
            <div class="bg-slate-900 text-white rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-800">
                <div class="space-y-1.5 text-center md:text-left">
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-400">Zero Hidden Costs Guarantee</span>
                    <h3 class="text-lg sm:text-xl font-bold font-space">Ready to plan your next vacation?</h3>
                    <p class="text-xs text-slate-400">Explore 100% customizable domestic and international tour packages.</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="search.php?type=package" class="px-5 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition">
                        Browse Tour Packages
                    </a>
                </div>
            </div>

        </div>

    </div>
</main>

<style>
/* Policy Content Styling */
.policy-content-area h3 {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    letter-spacing: -0.01em;
}
.policy-content-area p {
    color: #334155;
    line-height: 1.7;
    margin-bottom: 1rem;
}
.policy-content-area ul {
    list-style-type: disc;
    padding-left: 1.25rem;
    margin-bottom: 1rem;
    space-y: 0.5rem;
}
.policy-content-area li {
    color: #334155;
    line-height: 1.6;
    margin-bottom: 0.35rem;
}
.policy-content-area strong {
    color: #0f172a;
}
</style>

<?php require_once 'components/footer.php'; ?>
