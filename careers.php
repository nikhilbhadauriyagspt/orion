<?php
/**
 * Careers & Job Openings Portal - Orion Advent
 * 100% Flat, Border-First, Zero-Shadow UI with Hero Showcase & Live Job Filter
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';

$siteName = getSetting('site_name', 'Orion Advent');
$pageTitle = 'Careers & Life at ' . htmlspecialchars($siteName) . ' | Join Our Team';

$pdo = getDBConnection();
$jobs = [];
$departments = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `jobs` WHERE `status` = 'active' ORDER BY `id` DESC");
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch unique departments
        foreach ($jobs as $j) {
            if (!empty($j['department']) && !in_array($j['department'], $departments)) {
                $departments[] = $j['department'];
            }
        }
    } catch (Exception $e) {
        $jobs = [];
    }
}

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ==========================================
     1. HERO SECTION: LIFE AT ORION ADVENT
=========================================== -->
<section class="relative bg-slate-900 text-white py-16 sm:py-24 md:py-28 px-4 sm:px-8 xl:px-12 overflow-hidden border-b border-slate-800">
    <!-- Ambient Background Travel Collage / Image -->
    <div class="absolute inset-0 z-0 opacity-25 mix-blend-luminosity">
        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1920&q=80" 
             alt="Team Collaboration" 
             class="w-full h-full object-cover">
    </div>

    <!-- Gradient Vignette -->
    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/80 to-slate-900/60 z-0"></div>

    <!-- Dot Matrix Ambient Texture -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.04] bg-[radial-gradient(#068285_1px,transparent_1px)] [background-size:24px_24px] z-0"></div>

    <div class="max-w-5xl mx-auto text-center space-y-5 relative z-10">
        
        <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>We're Actively Hiring Across Teams</span>
        </div>

        <h1 class="text-3xl sm:text-5xl md:text-6xl font-black font-space tracking-tight leading-tight">
            Build the Future of <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-teal-200 to-amber-200">
                Experiential Travel &amp; Holidays
            </span>
        </h1>

        <p class="text-xs sm:text-base text-slate-300 font-normal max-w-2xl mx-auto leading-relaxed">
            At <?= htmlspecialchars($siteName) ?>, we are on a mission to make holiday exploration deeply personal, stress-free, and memorable. Join a passionate team of explorers, engineers, designers, and hospitality concierges.
        </p>

        <!-- Quick Jump Button -->
        <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
            <a href="#open-roles" 
               class="px-6 py-3 rounded-full bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center space-x-2 border border-brand-500">
                <span>View Open Positions (<?= count($jobs) ?>)</span>
                <i class="fa-solid fa-arrow-down text-xs"></i>
            </a>
            <a href="#why-us" 
               class="px-6 py-3 rounded-full bg-white/10 hover:bg-white/20 text-slate-200 hover:text-white font-bold text-xs uppercase tracking-wider transition border border-white/20">
                <span>Why Join <?= htmlspecialchars($siteName) ?>?</span>
            </a>
        </div>

    </div>
</section>

<!-- ==========================================
     2. KEY CULTURE & IMPACT PILLARS (WHY JOIN US)
=========================================== -->
<section id="why-us" class="py-14 sm:py-20 px-4 sm:px-8 xl:px-12 bg-white border-b border-slate-200 relative">
    <div class="max-w-7xl mx-auto">
        
        <div class="max-w-3xl mx-auto text-center space-y-2.5 mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 block">Culture &amp; Life</span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 font-space tracking-tight">
                Work Where Your Passion For Travel Thrives
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 font-normal max-w-xl mx-auto">
                We believe great work happens when people feel trusted, inspired, and empowered to innovate without fear.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Pillar 1 -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-brand-500 transition-all flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 flex items-center justify-center text-xl group-hover:bg-brand-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-umbrella-beach"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 group-hover:text-brand-700 transition">
                        Annual Travel Allowance
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                        Every team member receives sponsored annual travel credits to explore any <?= htmlspecialchars($siteName) ?> partner stay across Kashmir, Goa, Kerala, or Bali.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-slate-200/80 text-[11px] font-bold text-brand-600">
                    <span>₹50,000+ Annual Credits</span>
                </div>
            </div>

            <!-- Pillar 2 -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-brand-500 transition-all flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-700 border border-teal-200 flex items-center justify-center text-xl group-hover:bg-brand-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-laptop-house"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 group-hover:text-brand-700 transition">
                        Hybrid &amp; Flexible Culture
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                        We value results over face time. Enjoy flexible hours, modern hybrid schedules, and work-from-anywhere seasonal opportunities.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-slate-200/80 text-[11px] font-bold text-teal-700">
                    <span>Autonomy First</span>
                </div>
            </div>

            <!-- Pillar 3 -->
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-brand-500 transition-all flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center text-xl group-hover:bg-brand-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 group-hover:text-brand-700 transition">
                        Fast-Track Career Growth
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-normal">
                        As a fast-growing travel brand, you won't be a tiny cog in a giant machine. You'll lead high-impact initiatives from day one.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-slate-200/80 text-[11px] font-bold text-amber-700">
                    <span>Performance Rewards</span>
                </div>
            </div>

            <!-- Pillar 4 -->
            <!-- Benefit 4 -->
    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 hover:border-brand-500 transition-all flex flex-col justify-between group">
        <div class="space-y-3">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center text-xl group-hover:bg-brand-600 group-hover:text-white transition-colors">
                <i class="fa-solid fa-people-group"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 group-hover:text-brand-700 transition">
                Team Retreats &amp; Offsites
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed font-normal">
                Connect beyond the workplace through team outings, company retreats, and collaborative experiences that inspire fresh ideas and stronger relationships.
            </p>
        </div>
        <div class="pt-4 mt-4 border-t border-slate-200/80 text-[11px] font-bold text-emerald-700">
            <span>Better Together</span>
        </div>
    </div>

        </div>

    </div>
</section>

<!-- ==========================================
     3. OPEN POSITIONS & INTERACTIVE JOB FILTER
=========================================== -->
<section id="open-roles" class="py-14 sm:py-20 px-4 sm:px-8 xl:px-12 bg-slate-50/70 border-b border-slate-200 relative">
    <div class="max-w-7xl mx-auto">
        
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div class="space-y-2">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                    <i class="fa-solid fa-briefcase text-brand-600"></i>
                    <span>Opportunities Directory</span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-black text-slate-900 font-space tracking-tight">
                    Explore Open Positions
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-normal">
                    Find the role that matches your skills and start your next career adventure.
                </p>
            </div>

            <!-- Department Filter Pills -->
            <div class="flex items-center space-x-2 overflow-x-auto no-scrollbar pb-1 max-w-full">
                <button type="button" 
                        data-dept="all" 
                        class="job-dept-btn px-4 py-2 rounded-full text-xs font-bold bg-brand-600 text-white transition shrink-0">
                    All Roles (<?= count($jobs) ?>)
                </button>
                <?php foreach ($departments as $dept): ?>
                    <button type="button" 
                            data-dept="<?= htmlspecialchars(strtolower($dept)) ?>" 
                            class="job-dept-btn px-4 py-2 rounded-full text-xs font-semibold bg-white text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-600 transition shrink-0">
                        <?= htmlspecialchars($dept) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Jobs Listing Grid -->
        <?php if (!empty($jobs)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="jobsGrid">
                <?php foreach ($jobs as $job): 
                    $deptLower = strtolower($job['department']);
                    $detailUrl = 'job-details.php?slug=' . urlencode($job['slug']);
                ?>
                    <div class="job-card bg-white rounded-2xl border border-slate-200 hover:border-brand-500 transition-all p-6 flex flex-col justify-between group relative select-none" 
                         data-dept="<?= htmlspecialchars($deptLower) ?>">
                        
                        <div class="space-y-3.5">
                            
                            <!-- Top Badge Bar: Department & Job Type -->
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-brand-50 text-brand-700 border border-brand-200/80 uppercase tracking-wider">
                                    <?= htmlspecialchars($job['department']) ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    <?= htmlspecialchars($job['job_type']) ?>
                                </span>
                            </div>

                            <!-- Job Title -->
                            <h3 class="text-lg font-black text-slate-900 group-hover:text-brand-600 transition leading-snug">
                                <a href="<?= $detailUrl ?>">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h3>

                            <!-- Short Description -->
                            <p class="text-xs text-slate-600 leading-relaxed font-normal line-clamp-3">
                                <?= htmlspecialchars($job['short_description'] ?: strip_tags($job['description'])) ?>
                            </p>

                            <!-- Key Specs Pills -->
                            <div class="space-y-2 pt-2 border-t border-slate-100 text-xs text-slate-500">
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-location-dot text-brand-600 w-4 text-center"></i>
                                    <span class="font-medium text-slate-700 truncate"><?= htmlspecialchars($job['location']) ?></span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-clock text-teal-600 w-4 text-center"></i>
                                    <span class="font-medium text-slate-700"><?= htmlspecialchars($job['experience_level']) ?> Experience</span>
                                </div>
                                <?php if (!empty($job['salary_range'])): ?>
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-wallet text-amber-600 w-4 text-center"></i>
                                        <span class="font-medium text-slate-700 truncate"><?= htmlspecialchars($job['salary_range']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <!-- Card Footer Action -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-slate-400">
                                <i class="fa-regular fa-calendar-check mr-1 text-slate-400"></i>Active Opening
                            </span>

                            <a href="<?= $detailUrl ?>" 
                               class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-full bg-slate-900 group-hover:bg-brand-600 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95">
                                <span>View &amp; Apply</span>
                                <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty Filter State -->
            <div id="emptyFilterBox" class="hidden p-12 text-center bg-white rounded-3xl border border-slate-200 space-y-3">
                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-search"></i>
                </div>
                <h4 class="text-base font-bold text-slate-800">No Openings Found in This Department</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    We don't have an active role listed under this category right now, but we are always on the lookout for great talent.
                </p>
                <button type="button" onclick="document.querySelector('[data-dept=all]').click()" class="px-5 py-2 rounded-full bg-brand-600 text-white font-bold text-xs uppercase tracking-wider">
                    Show All Openings
                </button>
            </div>

        <?php else: ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 space-y-3">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto text-2xl">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900 font-space">No Current Openings</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    We are currently not hiring for active roles, but feel free to drop your resume at <strong class="text-slate-800"><?= htmlspecialchars(getSetting('site_email', 'careers@orionadvent.com')) ?></strong>.
                </p>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- ==========================================
     4. SPONTANEOUS APPLICATION CALLOUT BANNER
=========================================== -->
<section class="py-12 sm:py-16 px-4 sm:px-8 xl:px-12 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto">
        <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-slate-900 to-slate-800 text-white flex flex-col md:flex-row items-center justify-between gap-8 border border-slate-700 relative overflow-hidden">
            
            <div class="space-y-2 max-w-xl text-center md:text-left">
                <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-brand-300 border border-white/15">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Spontaneous Applications</span>
                </span>
                <h3 class="text-2xl sm:text-3xl font-black font-space tracking-tight">
                    Don't See Your Exact Role?
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 font-normal leading-relaxed">
                    If you are exceptionally skilled in Travel Tech, Product Design, Growth Marketing, or Luxury Concierge Operations, write directly to our founders.
                </p>
            </div>

            <div class="shrink-0 text-center md:text-right">
                <a href="mailto:<?= htmlspecialchars(getSetting('site_email', 'careers@orionadvent.com')) ?>?subject=<?= urlencode('Spontaneous Application - Talent Network') ?>" 
                   class="inline-flex items-center space-x-2 px-6 py-3 rounded-full bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 border border-brand-500 shadow-none">
                    <i class="fa-regular fa-envelope text-sm"></i>
                    <span>Send Open Application</span>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- Client Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const deptBtns = document.querySelectorAll('.job-dept-btn');
    const jobCards = document.querySelectorAll('.job-card');
    const emptyFilterBox = document.getElementById('emptyFilterBox');

    deptBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            deptBtns.forEach(b => {
                b.classList.remove('bg-brand-600', 'text-white');
                b.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
            });
            btn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
            btn.classList.add('bg-brand-600', 'text-white');

            const filter = btn.getAttribute('data-dept');
            let visibleCount = 0;

            jobCards.forEach(card => {
                const cardDept = card.getAttribute('data-dept');
                if (filter === 'all' || cardDept === filter) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (emptyFilterBox) {
                if (visibleCount === 0) {
                    emptyFilterBox.classList.remove('hidden');
                } else {
                    emptyFilterBox.classList.add('hidden');
                }
            }
        });
    });
});
</script>

<?php require_once 'components/footer.php'; ?>
