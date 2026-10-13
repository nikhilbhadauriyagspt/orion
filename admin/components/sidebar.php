<?php
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/../../config/settings.php';
$adminSiteName = getSetting('site_name', 'Orion Advent');
?>
<!-- Mobile Sidebar Backdrop Overlay -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-200"></div>

<!-- Admin Sidebar (Clean, Box-Type, Sage & Cream Palette, Left Border Accent on Active) -->
<aside id="admin-sidebar" class="fixed top-0 left-0 bottom-0 z-50 w-64 xl:w-68 bg-[#fdfdfb] border-r border-[#e5e4dc] flex flex-col transition-transform duration-200 -translate-x-full lg:translate-x-0 select-none">
    
    <!-- Sidebar Header / Logo -->
    <div class="h-16 flex items-center justify-between px-5 border-b border-[#e5e4dc] bg-[#fdfdfb] shrink-0">
        <a href="dashboard.php" class="flex items-center gap-3 group min-w-0">
            <div class="w-8 h-8 rounded-none bg-sage-700 text-white flex items-center justify-center text-sm font-bold border border-sage-800 shrink-0">
                <i class="fa-solid fa-compass"></i>
            </div>
            <div class="flex flex-col min-w-0">
                <span class="font-space font-bold text-base tracking-tight text-slate-900 leading-tight truncate" title="<?php echo htmlspecialchars($adminSiteName); ?>">
                    <?php echo htmlspecialchars($adminSiteName); ?>
                </span>
                <span class="text-[9px] uppercase font-bold tracking-wider text-slate-400">Control Panel</span>
            </div>
        </a>
        
        <!-- Mobile Close Button -->
        <button type="button" onclick="toggleSidebar()" aria-label="Close Sidebar" class="lg:hidden w-8 h-8 rounded-none flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-cream-200 border border-transparent hover:border-[#e5e4dc] transition-colors">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>

    <!-- Navigation Scrollable Area (Box-style clean navigation) -->
    <div class="flex-1 overflow-y-auto py-4 space-y-5">
        
        <!-- Section 1: Core Operations -->
        <div>
            <div class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Core Overview</div>
            <ul class="space-y-0.5">
                <li>
                    <a href="dashboard.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'dashboard.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chart-pie w-4 text-center text-xs <?php echo $currentPage == 'dashboard.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Dashboard</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border <?php echo $currentPage == 'dashboard.php' ? 'border-sage-600 text-sage-800 bg-sage-50' : 'border-[#e5e4dc] text-slate-400 bg-white'; ?>">Live</span>
                    </a>
                </li>
                <li>
                    <a href="bookings.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'bookings.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-calendar-check w-4 text-center text-xs <?php echo $currentPage == 'bookings.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Bookings</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-[#e5e4dc] text-slate-600 bg-white">Orders</span>
                    </a>
                </li>
                <li>
                    <a href="payments.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'payments.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-credit-card w-4 text-center text-xs <?php echo $currentPage == 'payments.php' ? 'text-emerald-700' : 'text-slate-400'; ?>"></i>
                            <span>Payments &amp; Ledger</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-emerald-200 text-emerald-800 bg-emerald-50">₹ Money</span>
                    </a>
                </li>
                <li>
                    <a href="packages.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'packages.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-map-location-dot w-4 text-center text-xs <?php echo $currentPage == 'packages.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Tour Packages</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-[#e5e4dc] text-slate-600 bg-white">Tours</span>
                    </a>
                </li>
                <li>
                    <a href="hotels.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'hotels.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-hotel w-4 text-center text-xs <?php echo $currentPage == 'hotels.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Hotels &amp; Resorts</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-[#e5e4dc] text-slate-600 bg-white">Stays</span>
                    </a>
                </li>
                <li>
                    <a href="cruises.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo ($currentPage == 'cruises.php' || $currentPage == 'cruise-edit.php') ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-ship w-4 text-center text-xs <?php echo ($currentPage == 'cruises.php' || $currentPage == 'cruise-edit.php') ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Ocean Cruises</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-[#e5e4dc] text-slate-600 bg-white">Sail</span>
                    </a>
                </li>
                <li>
                    <a href="careers.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo ($currentPage == 'careers.php' || $currentPage == 'career-edit.php') ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-briefcase w-4 text-center text-xs <?php echo ($currentPage == 'careers.php' || $currentPage == 'career-edit.php') ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Careers &amp; Jobs</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-brand-200 text-brand-700 bg-brand-50">Jobs</span>
                    </a>
                </li>
                <li>
                    <a href="job-applications.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'job-applications.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-user-tie w-4 text-center text-xs <?php echo $currentPage == 'job-applications.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Job Applicants</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-amber-200 text-amber-800 bg-amber-50">CVs</span>
                    </a>
                </li>
                <li>
                    <a href="reviews.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'reviews.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-star w-4 text-center text-xs <?php echo $currentPage == 'reviews.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Reviews &amp; Ratings</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-[#e5e4dc] text-slate-600 bg-white">Social</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Section 3: System & Administration -->
        <div>
            <div class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Settings &amp; Access</div>
            <ul class="space-y-0.5">
                <li>
                    <a href="users.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'users.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users w-4 text-center text-xs <?php echo $currentPage == 'users.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Travelers &amp; Users</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-[#e5e4dc] text-slate-600 bg-white">Users</span>
                    </a>
                </li>
                <li>
                    <a href="policies.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'policies.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-contract w-4 text-center text-xs <?php echo $currentPage == 'policies.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Policies &amp; Legal</span>
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 border border-[#e5e4dc] text-slate-600 bg-white">Terms</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center justify-between px-5 py-2.5 text-xs font-semibold transition-all <?php echo $currentPage == 'settings.php' ? 'bg-cream-200/90 text-sage-900 border-l-4 border-sage-700 font-bold pl-[16px]' : 'text-slate-600 hover:bg-cream-100 hover:text-slate-900 border-l-4 border-transparent'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-sliders w-4 text-center text-xs <?php echo $currentPage == 'settings.php' ? 'text-sage-700' : 'text-slate-400'; ?>"></i>
                            <span>Settings &amp; API Keys</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Sidebar Footer Profile & Quick Website Link (Boxed & Clean) -->
    <div class="p-3 border-t border-[#e5e4dc] bg-[#fbfbf8] shrink-0 space-y-2">
        <div class="flex items-center justify-between p-2.5 bg-white border border-[#e5e4dc]">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 bg-sage-100 text-sage-800 font-bold text-xs flex items-center justify-center border border-sage-300 shrink-0">
                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="truncate">
                    <div class="text-xs font-bold text-slate-800 truncate"><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin Manager'; ?></div>
                    <div class="text-[10px] text-slate-400 truncate"><?php echo isset($_SESSION['admin_email']) ? htmlspecialchars($_SESSION['admin_email']) : htmlspecialchars(getSetting('site_email', 'admin@orionadvent.com')); ?></div>
                </div>
            </div>
            <a href="logout.php" title="Sign Out" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors shrink-0">
                <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
            </a>
        </div>
        
        <a href="../index.php" target="_blank" class="w-full inline-flex items-center justify-between py-2 px-3 text-xs font-medium text-slate-700 bg-white hover:bg-cream-100 border border-[#e5e4dc] transition-colors">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-globe text-sage-700 text-xs"></i>
                <span>Open Live Website</span>
            </span>
            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400"></i>
        </a>
    </div>
</aside>

