    <!-- Admin Bottom-Right Live Notification Toast Container -->
    <div id="admin-toast-container" class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none"></div>

    <!-- Admin Footer & Common Interactive Scripts -->
    <script>
        // Toggle Mobile Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar && overlay) {
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                if (isOpen) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                } else {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                }
            }
        }

        // Dropdown toggler
        function toggleDropdown(id) {
            const target = document.getElementById(id);
            if (!target) return;
            
            // Close other dropdowns
            ['notif-menu', 'user-menu'].forEach(menuId => {
                if (menuId !== id) {
                    const el = document.getElementById(menuId);
                    if (el) el.classList.add('hidden');
                }
            });

            target.classList.toggle('hidden');
        }

        // Click outside to close dropdowns
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#notif-btn') && !e.target.closest('#notif-menu')) {
                const notif = document.getElementById('notif-menu');
                if (notif) notif.classList.add('hidden');
            }
            if (!e.target.closest('#user-menu-btn') && !e.target.closest('#user-menu')) {
                const user = document.getElementById('user-menu');
                if (user) user.classList.add('hidden');
            }
        });

        // ============================================================
        // REAL-TIME NOTIFICATION POLLING & TOAST SYSTEM (1 MIN REFRESH)
        // ============================================================
        let lastKnownNotifId = <?php 
            $pdo = getDBConnection();
            $initMaxId = $pdo ? (int)$pdo->query("SELECT COALESCE(MAX(id), 0) FROM `notifications`")->fetchColumn() : 0;
            echo $initMaxId;
        ?>;
        let isInitialLoad = true;

        // Pleasant Soft Chime via Web Audio API (Zero external audio files required)
        function playNotificationChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                
                const now = ctx.currentTime;
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();

                osc1.type = 'sine';
                osc2.type = 'triangle';

                osc1.frequency.setValueAtTime(587.33, now); // D5
                osc1.frequency.exponentialRampToValueAtTime(880, now + 0.15); // A5

                osc2.frequency.setValueAtTime(440, now);
                osc2.frequency.exponentialRampToValueAtTime(659.25, now + 0.15);

                gain.gain.setValueAtTime(0.001, now);
                gain.gain.linearRampToValueAtTime(0.18, now + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.5);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + 0.5);
                osc2.stop(now + 0.5);
            } catch (e) {
                // Audio context may be restricted before user gesture
            }
        }

        // Show Modern Bottom-Right Toast Notification
        function showAdminToastNotification(item) {
            const container = document.getElementById('admin-toast-container');
            if (!container) return;

            let icon = 'fa-bell';
            let iconColor = 'bg-sage-100 text-sage-800 border-sage-300';
            let tag = 'New Activity';

            if (item.type === 'booking') {
                icon = 'fa-ticket';
                iconColor = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                tag = 'Tour Booking';
            } else if (item.type === 'flight_inquiry') {
                icon = 'fa-plane-departure';
                iconColor = 'bg-sky-100 text-sky-800 border-sky-300';
                tag = 'Flight Query';
            } else if (item.type === 'payment') {
                icon = 'fa-indian-rupee-sign';
                iconColor = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                tag = 'Payment Captured';
            } else if (item.type === 'user_login') {
                icon = 'fa-user-check';
                iconColor = 'bg-amber-100 text-amber-800 border-amber-300';
                tag = 'Traveler Login';
            } else if (item.type === 'user_register') {
                icon = 'fa-user-plus';
                iconColor = 'bg-purple-100 text-purple-800 border-purple-300';
                tag = 'New Registration';
            } else if (item.type === 'contact_inquiry') {
                icon = 'fa-envelope-open-text';
                iconColor = 'bg-indigo-100 text-indigo-800 border-indigo-300';
                tag = 'Support Inquiry';
            }

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto bg-white border-2 border-sage-600 shadow-2xl p-4 flex flex-col gap-2 transform translate-y-8 opacity-0 transition-all duration-300 ease-out';
            
            toast.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 ${iconColor} border flex items-center justify-center text-xs font-bold shrink-0">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold uppercase tracking-wider bg-cream-100 text-slate-700 px-1.5 py-0.5 border border-[#e5e4dc]">${tag}</span>
                            <h4 class="text-xs font-bold text-slate-900 leading-tight mt-1 font-space">${item.title}</h4>
                        </div>
                    </div>
                    <button type="button" onclick="this.closest('.pointer-events-auto').remove()" class="w-5 h-5 text-slate-400 hover:text-slate-800 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-600 pl-10 leading-snug">${item.message}</p>
                <div class="flex items-center justify-between pl-10 pt-1 text-[11px] border-t border-[#f0eee6] mt-1">
                    <span class="text-[10px] text-slate-400 font-mono">Just now</span>
                    ${item.link ? `<a href="${item.link}" class="text-sage-800 hover:text-sage-900 font-bold hover:underline flex items-center gap-1">Open Details <i class="fa-solid fa-arrow-right text-[9px]"></i></a>` : ''}
                </div>
            `;

            container.appendChild(toast);

            // Animate In
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-8', 'opacity-0');
            });

            // Play Chime
            playNotificationChime();

            // Auto Dismiss after 8 seconds
            setTimeout(() => {
                toast.classList.add('translate-y-8', 'opacity-0');
                setTimeout(() => toast.remove(), 350);
            }, 8000);
        }

        // Poll API for fresh notifications
        function pollAdminNotifications() {
            const url = 'api/notifications.php?action=poll&since_id=' + lastKnownNotifId;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        // If newer notifications arrived
                        if (data.items && data.items.length > 0 && !isInitialLoad) {
                            data.items.forEach(item => {
                                showAdminToastNotification(item);
                            });
                        }

                        if (data.latest_id > lastKnownNotifId) {
                            lastKnownNotifId = data.latest_id;
                        }

                        // Update Badge Count
                        const badgeDot = document.getElementById('notif-badge-dot');
                        const headerBadge = document.getElementById('notif-header-badge');
                        const unreadNumber = document.getElementById('notif-unread-number');

                        if (data.unread_count > 0) {
                            if (badgeDot) badgeDot.classList.remove('hidden');
                            if (headerBadge) headerBadge.classList.remove('hidden');
                            if (unreadNumber) unreadNumber.textContent = data.unread_count;
                        } else {
                            if (badgeDot) badgeDot.classList.add('hidden');
                            if (headerBadge) headerBadge.classList.add('hidden');
                        }
                    }
                    isInitialLoad = false;
                })
                .catch(err => {
                    isInitialLoad = false;
                });
        }

        // Mark all as read
        function markAllNotificationsAsRead() {
            fetch('api/notifications.php?action=mark_all_read', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        const badgeDot = document.getElementById('notif-badge-dot');
                        const headerBadge = document.getElementById('notif-header-badge');
                        if (badgeDot) badgeDot.classList.add('hidden');
                        if (headerBadge) headerBadge.classList.add('hidden');
                        
                        const listItems = document.querySelectorAll('#notif-list-container a');
                        listItems.forEach(el => el.classList.remove('bg-amber-50/30'));
                    }
                });
        }

        // Schedule notification polling every 60 seconds (1 minute)
        setInterval(pollAdminNotifications, 60000);
    </script>
</body>
</html>
