<!-- Top Admin Header Bar (Clean, Box-Type, Sage & Cream Palette) -->
<header class="sticky top-0 z-30 h-16 bg-[#fdfdfb]/95 backdrop-blur-xs border-b border-[#e5e4dc] px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3 sm:gap-4 shrink-0">
    
    <!-- Left: Mobile Toggle & Search Bar -->
    <div class="flex items-center gap-3 sm:gap-4 flex-1 max-w-xl">
        <!-- Mobile Sidebar Toggle -->
        <button type="button" onclick="toggleSidebar()" aria-label="Toggle Sidebar" class="lg:hidden w-8 h-8 rounded-none border border-[#e5e4dc] flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-cream-100 transition-colors shrink-0">
            <i class="fa-solid fa-bars-staggered text-xs"></i>
        </button>

        <!-- Search Bar (Box-style clean input) -->
        <div class="relative w-full max-w-md hidden sm:block">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" placeholder="Search bookings, packages, customers..." 
                   class="w-full pl-8 pr-10 py-1.5 text-xs bg-white focus:bg-white border border-[#e5e4dc] focus:border-sage-700 outline-none transition-all placeholder:text-slate-400 font-medium">
            <div class="absolute right-2 top-1/2 -translate-y-1/2 hidden md:flex items-center px-1.5 py-0.5 text-[9px] font-bold border border-[#e5e4dc] bg-cream-100 text-slate-500 pointer-events-none">
                <span>/</span>
            </div>
        </div>

        <!-- Page breadcrumb label for small mobile screens -->
        <div class="sm:hidden font-space font-bold text-xs text-slate-800 uppercase tracking-wider">
            Admin
        </div>
    </div>

    <!-- Right: Quick Actions, Live Status, Notifications & User Profile -->
    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
        
        <!-- Live System Indicator -->
        <div class="hidden xl:flex items-center gap-2 px-2.5 py-1 bg-white text-sage-800 border border-[#e5e4dc] text-[11px] font-bold">
            <span class="w-1.5 h-1.5 bg-sage-600"></span>
            <span>Live Sync</span>
        </div>

        <!-- Add Package Action Button (Sharp box style) -->
        <a href="packages.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sage-700 hover:bg-sage-800 text-white text-xs font-semibold border border-sage-800 transition-colors shrink-0">
            <i class="fa-solid fa-plus text-[10px]"></i>
            <span class="hidden sm:inline">Add Package</span>
        </a>

        <!-- Notifications Dropdown (Live Real-Time) -->
        <?php
        require_once __DIR__ . '/../../includes/notifications.php';
        $headerNotifData = getAdminNotifications(10);
        $headerUnreadCount = $headerNotifData['unread_count'];
        $headerNotifItems = $headerNotifData['items'];
        ?>
        <div class="relative">
            <button id="notif-btn" onclick="toggleDropdown('notif-menu')" aria-label="Notifications" class="relative w-8 h-8 rounded-none border border-[#e5e4dc] bg-white flex items-center justify-center text-slate-600 hover:text-slate-900 hover:bg-cream-100 transition-colors">
                <i class="fa-regular fa-bell text-xs"></i>
                <span id="notif-badge-dot" class="<?php echo $headerUnreadCount > 0 ? '' : 'hidden'; ?> absolute top-1.5 right-1.5 w-2 h-2 bg-rose-600 rounded-full animate-pulse"></span>
            </button>
            
            <!-- Dropdown Menu -->
            <div id="notif-menu" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-[#e5e4dc] shadow-xl py-0 z-50">
                <div class="px-4 py-2.5 border-b border-[#e5e4dc] flex items-center justify-between bg-cream-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <span class="font-bold text-xs text-slate-900 uppercase tracking-wider font-space">Notifications</span>
                        <span id="notif-header-badge" class="<?php echo $headerUnreadCount > 0 ? '' : 'hidden'; ?> text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.2">
                            <span id="notif-unread-number"><?php echo $headerUnreadCount; ?></span> Unread
                        </span>
                    </div>
                    <button type="button" onclick="markAllNotificationsAsRead()" class="text-[10px] text-sage-800 hover:text-sage-900 font-bold hover:underline">
                        Mark all read
                    </button>
                </div>

                <div id="notif-list-container" class="max-h-80 overflow-y-auto divide-y divide-[#e5e4dc]">
                    <?php if (empty($headerNotifItems)): ?>
                        <div id="notif-empty-state" class="py-8 px-4 text-center">
                            <div class="w-8 h-8 bg-cream-100 text-slate-400 border border-[#e5e4dc] flex items-center justify-center mx-auto mb-2 text-xs">
                                <i class="fa-regular fa-bell-slash"></i>
                            </div>
                            <p class="text-xs font-semibold text-slate-600">No new notifications</p>
                            <p class="text-[11px] text-slate-400">All activity will appear here in real-time</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($headerNotifItems as $n): ?>
                            <?php
                            $nIcon = 'fa-bell';
                            $nBg = 'bg-slate-50 text-slate-700 border-slate-200';
                            if ($n['type'] === 'booking') {
                                $nIcon = 'fa-ticket';
                                $nBg = 'bg-sage-50 text-sage-800 border-sage-200';
                            } elseif ($n['type'] === 'flight_inquiry') {
                                $nIcon = 'fa-plane-departure';
                                $nBg = 'bg-sky-50 text-sky-800 border-sky-200';
                            } elseif ($n['type'] === 'payment') {
                                $nIcon = 'fa-indian-rupee-sign';
                                $nBg = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                            } elseif ($n['type'] === 'user_login' || $n['type'] === 'user_register') {
                                $nIcon = 'fa-user-check';
                                $nBg = 'bg-amber-50 text-amber-800 border-amber-200';
                            } elseif ($n['type'] === 'contact_inquiry') {
                                $nIcon = 'fa-envelope-open-text';
                                $nBg = 'bg-indigo-50 text-indigo-800 border-indigo-200';
                            }
                            ?>
                            <a href="<?php echo htmlspecialchars($n['link'] ?: '#'); ?>" class="flex items-start gap-3 p-3 hover:bg-cream-50/80 transition-colors <?php echo $n['is_read'] == 0 ? 'bg-amber-50/30' : ''; ?>">
                                <div class="w-7 h-7 <?php echo $nBg; ?> border flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                                    <i class="fa-solid <?php echo $nIcon; ?> text-[10px]"></i>
                                </div>
                                <div class="text-xs flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="font-bold text-slate-900 truncate"><?php echo htmlspecialchars($n['title']); ?></p>
                                        <?php if ($n['is_read'] == 0): ?>
                                            <span class="w-1.5 h-1.5 bg-rose-600 rounded-full shrink-0"></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-slate-600 text-[11px] line-clamp-2 mt-0.5 leading-snug"><?php echo htmlspecialchars($n['message']); ?></p>
                                    <span class="text-[10px] text-slate-400 mt-1 block font-mono"><?php echo date('h:i A, d M', strtotime($n['created_at'])); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="p-2 border-t border-[#e5e4dc] bg-cream-50/70 text-center">
                    <span class="text-[10px] font-mono text-slate-400"><i class="fa-solid fa-arrows-rotate text-[9px] animate-spin mr-1"></i> Auto-refreshes every 1 min</span>
                </div>
            </div>
        </div>

        <div class="h-4 w-px bg-[#e5e4dc] hidden sm:block"></div>

        <!-- User Profile Dropdown (Clean sharp box) -->
        <div class="relative">
            <button id="user-menu-btn" onclick="toggleDropdown('user-menu')" class="flex items-center gap-2 p-1 bg-white border border-[#e5e4dc] hover:bg-cream-100 transition-colors">
                <div class="w-7 h-7 bg-sage-100 text-sage-800 font-bold text-xs flex items-center justify-center border border-sage-300 shrink-0">
                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="text-left hidden md:block pr-1">
                    <div class="text-xs font-bold text-slate-800 leading-tight"><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></div>
                    <div class="text-[10px] text-slate-400 font-medium">Super Admin</div>
                </div>
                <i class="fa-solid fa-chevron-down text-slate-400 text-[9px] pr-1 hidden sm:block"></i>
            </button>

            <!-- User Menu Dropdown -->
            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-[#e5e4dc] shadow-md py-1 z-50">
                <div class="px-3.5 py-2 border-b border-[#e5e4dc] bg-cream-50">
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Signed in as</p>
                    <p class="text-xs font-bold text-slate-800 truncate"><?php echo isset($_SESSION['admin_email']) ? htmlspecialchars($_SESSION['admin_email']) : htmlspecialchars(getSetting('site_email', 'admin@orionadvent.com')); ?></p>
                </div>
                <div class="py-0.5">
                    <a href="dashboard.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-slate-700 hover:bg-cream-100 hover:text-sage-800 transition-colors">
                        <i class="fa-solid fa-chart-pie text-xs w-4 text-slate-400"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="settings.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-slate-700 hover:bg-cream-100 hover:text-sage-800 transition-colors">
                        <i class="fa-solid fa-sliders text-xs w-4 text-slate-400"></i>
                        <span>Settings</span>
                    </a>
                </div>
                <div class="border-t border-[#e5e4dc] pt-0.5">
                    <a href="logout.php" class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs w-4"></i>
                        <span>Sign Out</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</header>

<script>
    function toggleDropdown(id) {
        const menu = document.getElementById(id);
        if (!menu) return;
        const allMenus = ['notif-menu', 'user-menu'];
        allMenus.forEach(m => {
            if (m !== id) {
                const el = document.getElementById(m);
                if (el) el.classList.add('hidden');
            }
        });
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const notifBtn = document.getElementById('notif-btn');
        const notifMenu = document.getElementById('notif-menu');
        const userBtn = document.getElementById('user-menu-btn');
        const userMenu = document.getElementById('user-menu');

        if (notifBtn && notifMenu && !notifBtn.contains(e.target) && !notifMenu.contains(e.target)) {
            notifMenu.classList.add('hidden');
        }
        if (userBtn && userMenu && !userBtn.contains(e.target) && !userMenu.contains(e.target)) {
            userMenu.classList.add('hidden');
        }
    });

    function toggleSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    }
</script>
