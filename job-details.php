<?php
/**
 * Job Details & Application Portal - Orion Advent
 * Features:
 * - Rich Job Description & Specifications
 * - Responsive Clean Flat 2-Column Layout
 * - Seamless AJAX Application Modal with Resume File Upload
 * - Instant Feedback & Notification Trigger
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/settings.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : (isset($_GET['id']) ? trim($_GET['id']) : '');

$pdo = getDBConnection();
$job = null;

if ($pdo && !empty($slug)) {
    try {
        if (is_numeric($slug)) {
            $stmt = $pdo->prepare("SELECT * FROM `jobs` WHERE `id` = ? AND `status` = 'active' LIMIT 1");
            $stmt->execute([(int)$slug]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `jobs` WHERE `slug` = ? AND `status` = 'active' LIMIT 1");
            $stmt->execute([$slug]);
        }
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $job = null;
    }
}

// Redirect to careers page if job not found
if (!$job) {
    header("Location: careers.php");
    exit();
}

$siteName = getSetting('site_name', 'Orion Advent');
$sitePhone = getSetting('site_phone', '+91 98765 43210');
$siteEmail = getSetting('site_email', 'careers@orionadvent.com');
$pageTitle = htmlspecialchars($job['title']) . ' | Careers at ' . htmlspecialchars($siteName);

require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<!-- ==========================================
     1. BREADCRUMB HEADER
=========================================== -->
<section class="bg-white border-b border-slate-200 py-3 px-4 sm:px-8 xl:px-12">
    <div class="max-w-7xl mx-auto flex items-center justify-between text-xs text-slate-500">
        <nav class="flex items-center space-x-2 overflow-x-auto no-scrollbar whitespace-nowrap">
            <a href="index.php" class="hover:text-brand-700 transition flex items-center space-x-1">
                <i class="fa-solid fa-house text-[11px]"></i>
                <span>Home</span>
            </a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
            <a href="careers.php" class="hover:text-brand-700 transition">Careers</a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
            <span class="text-slate-900 font-bold truncate max-w-xs sm:max-w-md"><?= htmlspecialchars($job['title']) ?></span>
        </nav>
        <a href="careers.php" class="hidden sm:inline-flex items-center space-x-1 font-bold text-brand-600 hover:text-brand-700">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>All Openings</span>
        </a>
    </div>
</section>

<!-- ==========================================
     2. JOB HERO BANNER
=========================================== -->
<section class="bg-slate-900 text-white py-12 sm:py-16 px-4 sm:px-8 xl:px-12 border-b border-slate-800 relative overflow-hidden">
    <!-- Ambient Texture -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.03] bg-[radial-gradient(#068285_1px,transparent_1px)] [background-size:24px_24px]"></div>
    
    <div class="max-w-7xl mx-auto relative z-10">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <div class="space-y-3 max-w-3xl">
                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-brand-600 text-white">
                        <?= htmlspecialchars($job['department']) ?>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-slate-200 border border-white/15">
                        <?= htmlspecialchars($job['job_type']) ?>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span><?= (int)$job['openings'] ?> <?= (int)$job['openings'] > 1 ? 'Openings' : 'Opening' ?></span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black font-space tracking-tight leading-tight">
                    <?= htmlspecialchars($job['title']) ?>
                </h1>

                <!-- Specs Row -->
                <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs sm:text-sm text-slate-300 pt-1">
                    <div class="flex items-center space-x-1.5">
                        <i class="fa-solid fa-location-dot text-brand-400"></i>
                        <span><?= htmlspecialchars($job['location']) ?></span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <i class="fa-solid fa-briefcase text-teal-400"></i>
                        <span><?= htmlspecialchars($job['experience_level']) ?> Experience</span>
                    </div>
                    <?php if (!empty($job['salary_range'])): ?>
                        <div class="flex items-center space-x-1.5">
                            <i class="fa-solid fa-wallet text-amber-400"></i>
                            <span class="font-bold text-amber-200"><?= htmlspecialchars($job['salary_range']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Header Action Button -->
            <div class="shrink-0 pt-2 lg:pt-0">
                <button type="button" 
                        id="openApplyModalHeroBtn"
                        class="w-full sm:w-auto px-8 py-4 rounded-full bg-brand-600 hover:bg-brand-500 text-white font-black text-xs uppercase tracking-widest transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center space-x-2 border border-brand-500 shadow-none">
                    <span>Apply For This Position</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>

        </div>
    </div>
</section>

<!-- ==========================================
     3. JOB DETAILS MAIN CONTENT & SIDEBAR
=========================================== -->
<section class="py-12 sm:py-16 px-4 sm:px-8 xl:px-12 bg-[#f8fafc] border-b border-slate-200">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- Left: Rich Job Description (8 cols) -->
        <div class="lg:col-span-8 space-y-8">
            
            <!-- Description Container -->
            <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 space-y-6 text-slate-700 leading-relaxed text-sm">
                
                <!-- Short Overview -->
                <?php if (!empty($job['short_description'])): ?>
                    <div class="p-4 sm:p-5 rounded-2xl bg-brand-50/60 border border-brand-200/80 text-brand-900 font-medium text-xs sm:text-sm">
                        <i class="fa-solid fa-quote-left text-brand-600 mr-2"></i>
                        <?= htmlspecialchars($job['short_description']) ?>
                    </div>
                <?php endif; ?>

                <!-- Full HTML Description -->
                <div class="prose prose-slate max-w-none prose-headings:font-space prose-headings:font-black prose-headings:text-slate-900 prose-h3:text-lg prose-h3:mt-6 prose-h3:mb-3 prose-p:text-slate-600 prose-p:text-xs sm:prose-p:text-sm prose-ul:text-xs sm:prose-ul:text-sm prose-ul:space-y-1.5 prose-li:text-slate-600">
                    <?= $job['description'] ?>
                </div>

                <!-- Requirements -->
                <?php if (!empty($job['requirements'])): ?>
                    <div class="pt-6 border-t border-slate-200/80 prose prose-slate max-w-none prose-headings:font-space prose-headings:font-black prose-headings:text-slate-900 prose-h3:text-lg prose-h3:mb-3 prose-ul:text-xs sm:prose-ul:text-sm prose-ul:space-y-1.5 prose-li:text-slate-600">
                        <?= $job['requirements'] ?>
                    </div>
                <?php endif; ?>

                <!-- Benefits -->
                <?php if (!empty($job['benefits'])): ?>
                    <div class="pt-6 border-t border-slate-200/80 prose prose-slate max-w-none prose-headings:font-space prose-headings:font-black prose-headings:text-slate-900 prose-h3:text-lg prose-h3:mb-3 prose-ul:text-xs sm:prose-ul:text-sm prose-ul:space-y-1.5 prose-li:text-slate-600">
                        <?= $job['benefits'] ?>
                    </div>
                <?php endif; ?>

                <!-- Apply Button Inside Body -->
                <div class="pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Interested in shaping experiential travel?</span>
                        <span class="text-[11px] text-slate-500 font-normal">Applications are reviewed on a rolling basis.</span>
                    </div>

                    <button type="button" 
                            id="openApplyModalBodyBtn"
                            class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center space-x-2 border border-brand-500">
                        <span>Apply For This Role</span>
                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                    </button>
                </div>

            </div>

        </div>

        <!-- Right: Sticky Role Summary Sidebar (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 space-y-6 sticky top-24 select-none">
                
                <h3 class="text-base font-black text-slate-900 font-space border-b border-slate-100 pb-3 flex items-center space-x-2">
                    <i class="fa-solid fa-clipboard-list text-brand-600"></i>
                    <span>Position Summary</span>
                </h3>

                <div class="space-y-4 text-xs">
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i class="fa-solid fa-building text-xs"></i>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block text-[10px] uppercase">Department</span>
                            <span class="font-bold text-slate-900"><?= htmlspecialchars($job['department']) ?></span>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i class="fa-solid fa-location-dot text-xs"></i>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block text-[10px] uppercase">Location &amp; Work Model</span>
                            <span class="font-bold text-slate-900"><?= htmlspecialchars($job['location']) ?></span>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i class="fa-solid fa-business-time text-xs"></i>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block text-[10px] uppercase">Job Type</span>
                            <span class="font-bold text-slate-900"><?= htmlspecialchars($job['job_type']) ?></span>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
                            <i class="fa-solid fa-user-graduate text-xs"></i>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block text-[10px] uppercase">Experience Requirement</span>
                            <span class="font-bold text-slate-900"><?= htmlspecialchars($job['experience_level']) ?></span>
                        </div>
                    </div>

                    <?php if (!empty($job['salary_range'])): ?>
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200">
                                <i class="fa-solid fa-wallet text-xs"></i>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium block text-[10px] uppercase">Offered Compensation</span>
                                <span class="font-black text-slate-900"><?= htmlspecialchars($job['salary_range']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Action Button -->
                <button type="button" 
                        id="openApplyModalSidebarBtn"
                        class="w-full py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 flex items-center justify-center space-x-2 border border-brand-500">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Apply Now</span>
                </button>

                <!-- Help Concierge Card -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-[11px] text-slate-500">
                    <span class="font-bold text-slate-800 block">Questions about this role?</span>
                    <p>Reach out to our Talent Acquisition desk at <strong class="text-brand-600"><?= htmlspecialchars($siteEmail) ?></strong></p>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- ==========================================
     4. INTERACTIVE JOB APPLICATION MODAL
=========================================== -->
<div id="applyJobModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-3xl border border-slate-200 max-w-2xl w-full p-6 sm:p-8 relative my-8 animate-in zoom-in-95 duration-200">
        
        <!-- Close Button -->
        <button type="button" 
                id="closeApplyModalBtn"
                class="absolute top-5 right-5 w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm transition">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Modal Header -->
        <div class="mb-6 space-y-1 pr-8">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                Application Form
            </span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 font-space leading-tight">
                Apply for <?= htmlspecialchars($job['title']) ?>
            </h2>
            <p class="text-xs text-slate-500 font-normal">
                Please complete the details below and upload your latest resume.
            </p>
        </div>

        <!-- Alert Notification Box -->
        <div id="applyAlertBox" class="hidden mb-4 p-3.5 rounded-xl text-xs font-semibold flex items-center gap-2"></div>

        <!-- Form -->
        <form id="jobApplicationForm" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Full Name -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="candidate_name" 
                           required 
                           placeholder="e.g. Rahul Sharma" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Email Address <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" 
                           name="candidate_email" 
                           required 
                           placeholder="name@example.com" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                <!-- Phone Number -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Mobile Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" 
                           name="candidate_phone" 
                           required 
                           placeholder="+91 98765 43210" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                <!-- Experience in Years -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Total Experience <span class="text-rose-500">*</span>
                    </label>
                    <select name="experience_years" 
                            required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                        <option value="">Select Experience</option>
                        <option value="Fresher / 0-1 Year">Fresher / 0-1 Year</option>
                        <option value="1-3 Years">1-3 Years</option>
                        <option value="3-5 Years">3-5 Years</option>
                        <option value="5-8 Years">5-8 Years</option>
                        <option value="8+ Years">8+ Years</option>
                    </select>
                </div>

                <!-- Current Location -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Current City / Location
                    </label>
                    <input type="text" 
                           name="current_location" 
                           placeholder="e.g. Delhi NCR, Bangalore" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                </div>

                <!-- Notice Period -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Notice Period
                    </label>
                    <select name="notice_period" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-brand-500 focus:bg-white transition">
                        <option value="Immediate / Available">Immediate / 0-15 Days</option>
                        <option value="1 Month">1 Month</option>
                        <option value="2 Months">2 Months</option>
                        <option value="3 Months">3 Months</option>
                    </select>
                </div>

            </div>

            <!-- Portfolio / LinkedIn URL -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    LinkedIn Profile or Portfolio Link
                </label>
                <input type="url" 
                       name="portfolio_url" 
                       placeholder="https://linkedin.com/in/username" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition">
            </div>

            <!-- Resume Upload (PDF / DOCX) -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Upload Resume (PDF, DOCX • Max 5MB) <span class="text-rose-500">*</span>
                </label>
                <div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 hover:bg-white hover:border-brand-500 transition-colors">
                    <input type="file" 
                           name="resume_file" 
                           id="resumeFileInput"
                           required 
                           accept=".pdf,.doc,.docx" 
                           class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white hover:file:bg-brand-700 cursor-pointer">
                </div>
            </div>

            <!-- Short Cover Note -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Why are you a great fit? (Optional Note)
                </label>
                <textarea name="cover_letter" 
                          rows="3" 
                          placeholder="Briefly tell us about your key achievements or why you want to join Orion Advent..." 
                          class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-normal text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white transition resize-none"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" 
                        id="submitAppBtn"
                        class="w-full py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center justify-center space-x-2 border border-brand-500 shadow-none">
                    <span>Submit Job Application</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>

        </form>

    </div>
</div>

<!-- Application Modal Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('applyJobModal');
    const heroBtn = document.getElementById('openApplyModalHeroBtn');
    const bodyBtn = document.getElementById('openApplyModalBodyBtn');
    const sidebarBtn = document.getElementById('openApplyModalSidebarBtn');
    const closeBtn = document.getElementById('closeApplyModalBtn');
    const form = document.getElementById('jobApplicationForm');
    const alertBox = document.getElementById('applyAlertBox');
    const submitBtn = document.getElementById('submitAppBtn');

    const openModal = () => {
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    const closeModal = () => {
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    if (heroBtn) heroBtn.addEventListener('click', openModal);
    if (bodyBtn) bodyBtn.addEventListener('click', openModal);
    if (sidebarBtn) sidebarBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    // Close when clicking modal backdrop
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }

    // AJAX Form Submit
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                <span>Submitting Application...</span>
            `;

            alertBox.className = 'hidden mb-4 p-3.5 rounded-xl text-xs font-semibold flex items-center gap-2';

            try {
                const response = await fetch('api/apply-job.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    alertBox.className = 'mb-4 p-4 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-2';
                    alertBox.innerHTML = `
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                        <div>
                            <span>${data.message}</span>
                        </div>
                    `;
                    form.reset();
                    submitBtn.innerHTML = `
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Application Submitted!</span>
                    `;
                    setTimeout(() => {
                        closeModal();
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = `
                            <span>Submit Job Application</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        `;
                    }, 3500);
                } else {
                    alertBox.className = 'mb-4 p-3.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 flex items-center gap-2';
                    alertBox.innerHTML = `
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm shrink-0"></i>
                        <span>${data.message || 'Failed to submit application. Please try again.'}</span>
                    `;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `
                        <span>Submit Job Application</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    `;
                }
            } catch (err) {
                alertBox.className = 'mb-4 p-3.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 flex items-center gap-2';
                alertBox.innerHTML = `
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm shrink-0"></i>
                    <span>Network error. Please check your connection and try again.</span>
                `;
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    <span>Submit Job Application</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                `;
            }
        });
    }
});
</script>

<?php require_once 'components/footer.php'; ?>
