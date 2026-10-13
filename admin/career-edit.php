<?php
/**
 * Admin Job Opening Add / Edit Form
 * Standard Admin Layout with Shared Head, Sidebar, Header, Plus Jakarta Sans & Space Grotesk Fonts
 * Complete Rich Text Editor (Description, Requirements, Benefits, Compensation & Specs)
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$job = null;
$errorMessage = '';

if ($jobId > 0 && $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM `jobs` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$jobId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $department = trim($_POST['department'] ?? 'Operations');
    $location = trim($_POST['location'] ?? 'Gurugram, Haryana / Hybrid');
    $jobType = trim($_POST['job_type'] ?? 'Full-time');
    $experienceLevel = trim($_POST['experience_level'] ?? '2-5 Years');
    $salaryRange = trim($_POST['salary_range'] ?? '');
    $openings = (int)($_POST['openings'] ?? 1);
    $shortDescription = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }

    if (empty($title) || empty($description)) {
        $errorMessage = "Job Title and Full Description are required.";
    } else {
        try {
            if ($jobId > 0) {
                // UPDATE
                $stmt = $pdo->prepare("
                    UPDATE `jobs` SET 
                        `title` = ?, `slug` = ?, `department` = ?, `location` = ?, 
                        `job_type` = ?, `experience_level` = ?, `salary_range` = ?, 
                        `openings` = ?, `short_description` = ?, `description` = ?, 
                        `requirements` = ?, `benefits` = ?, `status` = ?
                    WHERE `id` = ?
                ");
                $stmt->execute([
                    $title, $slug, $department, $location,
                    $jobType, $experienceLevel, $salaryRange,
                    $openings, $shortDescription, $description,
                    $requirements, $benefits, $status, $jobId
                ]);
            } else {
                // INSERT
                $stmt = $pdo->prepare("
                    INSERT INTO `jobs` 
                    (`title`, `slug`, `department`, `location`, `job_type`, `experience_level`, `salary_range`, `openings`, `short_description`, `description`, `requirements`, `benefits`, `status`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $slug, $department, $location,
                    $jobType, $experienceLevel, $salaryRange,
                    $openings, $shortDescription, $description,
                    $requirements, $benefits, $status
                ]);
            }

            header("Location: careers.php?saved=1");
            exit();
        } catch (Exception $e) {
            $errorMessage = "Database Error: " . $e->getMessage();
        }
    }
}

$pageTitle = ($jobId > 0 ? "Edit Job: " . htmlspecialchars($job['title'] ?? '') : "Add New Job Position");
include 'components/head.php';
?>

<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.config.versionCheck = false;
    }
</script>
<style>
    .cke_notification_warning, .cke_notification_message, .cke_notifications_area {
        display: none !important;
        visibility: hidden !important;
    }
</style>

<!-- App Wrapper -->
<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        <!-- Header -->
        <?php include 'components/header.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full max-w-5xl space-y-6">

            <!-- Page Header / Top Action Strip -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-[#e5e4dc] p-4 sm:p-5">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                        <span>Admin</span>
                        <span>/</span>
                        <a href="careers.php" class="hover:text-sage-700">Careers</a>
                        <span>/</span>
                        <span class="text-sage-700 font-semibold"><?= $jobId > 0 ? 'Edit Vacancy' : 'New Vacancy' ?></span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        <?= $jobId > 0 ? 'Edit Job Opening' : 'Post New Job Vacancy' ?>
                    </h1>
                    <p class="text-xs text-slate-500">Configure role specifications, department, compensation, and formatted job description.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="careers.php" class="px-3.5 py-2 text-xs font-bold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Back to Jobs</span>
                    </a>
                </div>
            </div>

            <!-- Error Banner -->
            <?php if (!empty($errorMessage)): ?>
                <div class="p-3.5 text-xs font-semibold bg-rose-50 text-rose-900 border border-rose-200 flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                    <span><?= htmlspecialchars($errorMessage) ?></span>
                </div>
            <?php endif; ?>

            <!-- Job Form -->
            <form method="POST" class="space-y-6">
                
                <!-- 1. General Position Details Card -->
                <div class="bg-white p-5 sm:p-6 border border-[#e5e4dc] space-y-4">
                    
                    <div class="flex items-center gap-2 pb-3 border-b border-[#e5e4dc]">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-space">Role Overview &amp; Specifications</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Title -->
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Job Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   required 
                                   value="<?= htmlspecialchars($job['title'] ?? '') ?>"
                                   placeholder="e.g. Senior Travel Operations Manager" 
                                   class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-bold text-slate-900">
                        </div>

                        <!-- Slug -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                URL Slug <span class="text-slate-400 font-normal">(Auto-generated if empty)</span>
                            </label>
                            <input type="text" 
                                   name="slug" 
                                   value="<?= htmlspecialchars($job['slug'] ?? '') ?>"
                                   placeholder="e.g. senior-travel-operations-manager" 
                                   class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-mono text-slate-800">
                        </div>

                        <!-- Department -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Department <span class="text-rose-500">*</span>
                            </label>
                            <select name="department" class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-semibold text-slate-800">
                                <?php
                                $departments = ['Travel Operations', 'Destination Expert', 'Sales & Concierge', 'Marketing & Growth', 'Engineering & Tech', 'Product & Design', 'Finance & HR'];
                                $currDept = $job['department'] ?? 'Travel Operations';
                                foreach ($departments as $d):
                                ?>
                                    <option value="<?= $d ?>" <?= $currDept === $d ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Job Location <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="location" 
                                   required 
                                   value="<?= htmlspecialchars($job['location'] ?? 'Gurugram, Haryana / Hybrid') ?>"
                                   placeholder="e.g. Gurugram, India / Remote / Goa" 
                                   class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-medium text-slate-900">
                        </div>

                        <!-- Job Type -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Employment Type <span class="text-rose-500">*</span>
                            </label>
                            <select name="job_type" class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-semibold text-slate-800">
                                <?php
                                $types = ['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'];
                                $currType = $job['job_type'] ?? 'Full-time';
                                foreach ($types as $t):
                                ?>
                                    <option value="<?= $t ?>" <?= $currType === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Experience Level -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Experience Required
                            </label>
                            <input type="text" 
                                   name="experience_level" 
                                   value="<?= htmlspecialchars($job['experience_level'] ?? '2-5 Years') ?>"
                                   placeholder="e.g. 2-5 Years / Entry Level" 
                                   class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-medium text-slate-900">
                        </div>

                        <!-- Salary Range -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Compensation / Salary Bracket
                            </label>
                            <input type="text" 
                                   name="salary_range" 
                                   value="<?= htmlspecialchars($job['salary_range'] ?? '₹6,00,000 - ₹9,50,000 / year') ?>"
                                   placeholder="e.g. ₹6,00,000 - ₹9,50,000 / yr" 
                                   class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-mono text-slate-900">
                        </div>

                        <!-- Openings Count -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Number of Vacancies
                            </label>
                            <input type="number" 
                                   name="openings" 
                                   min="1" 
                                   max="50"
                                   value="<?= (int)($job['openings'] ?? 1) ?>" 
                                   class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-bold text-slate-900">
                        </div>

                        <!-- Status -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Hiring Status <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-bold text-slate-800">
                                <option value="active" <?= ($job['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (Visible on Careers Page)</option>
                                <option value="closed" <?= ($job['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed (Hiring Paused)</option>
                                <option value="draft" <?= ($job['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Hidden)</option>
                            </select>
                        </div>

                        <!-- Short Description -->
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                Short Summary / Card Pitch <span class="text-slate-400 font-normal">(Shown on Job Listing Card)</span>
                            </label>
                            <textarea name="short_description" 
                                      rows="2" 
                                      placeholder="Brief 1-2 sentence overview of the opportunity..." 
                                      class="w-full px-3 py-2 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none text-slate-900"><?= htmlspecialchars($job['short_description'] ?? '') ?></textarea>
                        </div>

                    </div>
                </div>

                <!-- 2. Detailed Job Description (CKEditor) -->
                <div class="bg-white p-5 sm:p-6 border border-[#e5e4dc] space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-space">Role Responsibilities &amp; Job Description <span class="text-rose-500">*</span></h2>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">HTML Rich Text</span>
                    </div>

                    <textarea name="description" id="editor_description" rows="10" required class="w-full border border-[#e5e4dc]"><?= htmlspecialchars($job['description'] ?? '<h3>About the Role</h3><p>We are seeking a talented and passionate professional to join our fast-growing luxury travel and tour experience operations team.</p><h3>What You Will Do</h3><ul><li>Design, manage, and coordinate bespoke luxury tour itineraries and holiday circuits.</li><li>Maintain seamless relationships with on-ground tour operators, 5-star resort partners, and chauffeurs.</li><li>Ensure 100% guest satisfaction across all itinerary touchpoints.</li></ul>') ?></textarea>
                </div>

                <!-- 3. Candidate Requirements (CKEditor) -->
                <div class="bg-white p-5 sm:p-6 border border-[#e5e4dc] space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-space">Candidate Qualifications &amp; Key Requirements</h2>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">HTML Rich Text</span>
                    </div>

                    <textarea name="requirements" id="editor_requirements" rows="8" class="w-full border border-[#e5e4dc]"><?= htmlspecialchars($job['requirements'] ?? '<ul><li>2+ years of relevant experience in travel operations, guest relations, or related domains.</li><li>Exceptional verbal and written communication skills in English & Hindi.</li><li>Strong problem-solving ability with a calm, guest-first mindset during real-time logistics.</li><li>Proficiency in modern CRM and digital collaboration tools.</li></ul>') ?></textarea>
                </div>

                <!-- 4. Perks & Benefits (CKEditor) -->
                <div class="bg-white p-5 sm:p-6 border border-[#e5e4dc] space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-sage-700"></span>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-space">Perks, Travel Stipends &amp; Benefits</h2>
                        </div>
                        <span class="text-[10px] text-slate-400 font-mono">HTML Rich Text</span>
                    </div>

                    <textarea name="benefits" id="editor_benefits" rows="6" class="w-full border border-[#e5e4dc]"><?= htmlspecialchars($job['benefits'] ?? '<ul><li><strong>Annual Travel Credit:</strong> ₹50,000 yearly travel stipend to explore domestic & international destinations.</li><li><strong>Comprehensive Health Insurance:</strong> ₹5 Lakh cover for you and immediate family.</li><li><strong>Flexible Hybrid Work:</strong> Work from anywhere flexibility with modern equipment allowance.</li><li><strong>High-Performance Bonuses:</strong> Semi-annual incentive plans and transparent career ladder.</li></ul>') ?></textarea>
                </div>

                <!-- Action Strip -->
                <div class="flex items-center justify-end gap-3 bg-white p-4 border border-[#e5e4dc]">
                    <a href="careers.php" class="px-4 py-2 border border-[#e5e4dc] text-slate-700 text-xs font-bold hover:bg-cream-100 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-sage-700 hover:bg-sage-800 text-white font-bold text-xs border border-sage-800 transition flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span><?= $jobId > 0 ? 'Save Changes' : 'Publish Job Opening' ?></span>
                    </button>
                </div>

            </form>

        </main>
    </div>
</div>

<script>
    // Initialize CKEditor with versionCheck disabled
    CKEDITOR.config.versionCheck = false;
    CKEDITOR.replace('editor_description', { height: 220, versionCheck: false });
    CKEDITOR.replace('editor_requirements', { height: 180, versionCheck: false });
    CKEDITOR.replace('editor_benefits', { height: 160, versionCheck: false });


    function toggleSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    }
</script>
</body>
</html>
