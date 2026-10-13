<?php
/**
 * Admin Careers & Job Openings Management Controller
 * Standard Admin Layout with Shared Head, Sidebar, Header, Plus Jakarta Sans & Space Grotesk Fonts
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();
$alertMessage = '';
$alertType = 'success';

if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $alertMessage = "Job opening saved successfully!";
    $alertType = 'success';
}

// 1. Handle Status Toggle
if (isset($_GET['toggle_status']) && (int)$_GET['toggle_status'] > 0 && $pdo) {
    $toggleId = (int)$_GET['toggle_status'];
    try {
        $stmt = $pdo->prepare("UPDATE `jobs` SET `status` = IF(`status` = 'active', 'closed', 'active') WHERE `id` = ?");
        $stmt->execute([$toggleId]);
        $alertMessage = "Job #{$toggleId} status toggled successfully.";
    } catch (Exception $e) {
        $alertMessage = "Error updating status: " . $e->getMessage();
        $alertType = 'error';
    }
}

// 2. Handle Delete
if (isset($_GET['delete_id']) && (int)$_GET['delete_id'] > 0 && $pdo) {
    $delId = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM `jobs` WHERE `id` = ?");
        $stmt->execute([$delId]);
        $alertMessage = "Job opening #{$delId} permanently deleted.";
    } catch (Exception $e) {
        $alertMessage = "Error deleting job: " . $e->getMessage();
        $alertType = 'error';
    }
}

// 3. Fetch Stats & Jobs
$jobs = [];
$totalJobs = 0;
$activeJobs = 0;
$closedJobs = 0;
$totalApplicants = 0;
$deptCounts = [];

if ($pdo) {
    try {
        $totalJobs = (int)$pdo->query("SELECT COUNT(*) FROM `jobs`")->fetchColumn();
        $activeJobs = (int)$pdo->query("SELECT COUNT(*) FROM `jobs` WHERE `status` = 'active'")->fetchColumn();
        $closedJobs = (int)$pdo->query("SELECT COUNT(*) FROM `jobs` WHERE `status` = 'closed'")->fetchColumn();
        $totalApplicants = (int)$pdo->query("SELECT COUNT(*) FROM `job_applications`")->fetchColumn();

        // Department breakdown
        $dStmt = $pdo->query("SELECT department, COUNT(*) as cnt FROM jobs GROUP BY department ORDER BY cnt DESC");
        while ($row = $dStmt->fetch()) {
            $deptCounts[$row['department']] = (int)$row['cnt'];
        }

        // Filters
        $currentDept = trim($_GET['dept'] ?? 'all');
        $search = trim($_GET['q'] ?? '');
        $filterStatus = trim($_GET['status'] ?? '');

        $query = "SELECT j.*, (SELECT COUNT(*) FROM job_applications a WHERE a.job_id = j.id) AS applicants_count FROM `jobs` j WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (j.title LIKE ? OR j.location LIKE ? OR j.short_description LIKE ?)";
            $wild = "%{$search}%";
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
        }

        if ($currentDept !== 'all' && !empty($currentDept)) {
            $query .= " AND j.department = ?";
            $params[] = $currentDept;
        }

        if (!empty($filterStatus)) {
            $query .= " AND j.status = ?";
            $params[] = $filterStatus;
        }

        $query .= " ORDER BY j.id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $jobs = [];
    }
}

$pageTitle = "Careers & Job Openings";
include 'components/head.php';
?>

<!-- App Wrapper -->
<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        <!-- Header -->
        <?php include 'components/header.php'; ?>

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full max-w-7xl space-y-6">

            <!-- Page Header / Top Action Strip -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-[#e5e4dc] p-4 sm:p-5">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                        <span>Admin</span>
                        <span>/</span>
                        <span class="text-sage-700 font-semibold">Recruitment &amp; Careers</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">Careers &amp; Job Postings</h1>
                    <p class="text-xs text-slate-500">Manage open roles, rich job descriptions, requirements, perks, and track applicant submissions.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="job-applications.php" class="px-3.5 py-2 text-xs font-bold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-users-viewfinder text-sage-600"></i>
                        <span>View Applicants (<?= $totalApplicants ?>)</span>
                    </a>
                    <a href="career-edit.php" class="px-4 py-2 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>Post New Job</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 text-xs font-semibold flex items-center justify-between <?= $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid <?= $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                        <span><?= htmlspecialchars($alertMessage); ?></span>
                    </div>
                    <a href="careers.php" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></a>
                </div>
            <?php endif; ?>

            <!-- Metric KPI Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Total Openings</span>
                        <i class="fa-solid fa-briefcase text-sage-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?= number_format($totalJobs); ?></div>
                    <div class="text-[10px] text-slate-400">All listed job positions</div>
                </div>

                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Active &amp; Hiring</span>
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?= number_format($activeJobs); ?></div>
                    <div class="text-[10px] text-slate-400">Live on careers portal</div>
                </div>

                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Closed / Draft</span>
                        <i class="fa-solid fa-ban text-amber-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?= number_format($closedJobs); ?></div>
                    <div class="text-[10px] text-slate-400">Hiring paused or closed</div>
                </div>

                <div class="p-3.5 bg-white border border-[#e5e4dc] space-y-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Total Candidates</span>
                        <i class="fa-solid fa-file-lines text-sky-600"></i>
                    </div>
                    <div class="text-xl font-extrabold font-space text-slate-900"><?= number_format($totalApplicants); ?></div>
                    <div class="text-[10px] text-slate-400">Resumes submitted</div>
                </div>
            </div>

            <!-- Dynamic Department Tabs & Filter Bar -->
            <div class="space-y-3">
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 bg-white border border-[#e5e4dc] p-1.5">
                    <a href="careers.php" class="px-3 py-1.5 text-xs font-bold shrink-0 transition-colors <?= ($currentDept === 'all') ? 'bg-sage-700 text-white' : 'text-slate-600 hover:bg-cream-100'; ?>">
                        All Departments (<?= $totalJobs; ?>)
                    </a>
                    <?php foreach ($deptCounts as $dName => $cnt): ?>
                        <a href="careers.php?dept=<?= urlencode($dName) ?>" class="px-3 py-1.5 text-xs font-bold shrink-0 transition-colors <?= ($currentDept === $dName) ? 'bg-sage-700 text-white' : 'text-slate-600 hover:bg-cream-100'; ?>">
                            <?= htmlspecialchars($dName) ?> (<?= $cnt ?>)
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Search & Status Filter -->
                <div class="bg-white border border-[#e5e4dc] p-3">
                    <form method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                        <?php if ($currentDept !== 'all'): ?>
                            <input type="hidden" name="dept" value="<?= htmlspecialchars($currentDept) ?>">
                        <?php endif; ?>

                        <div class="relative flex-1 w-full">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search job title, location or keywords..." class="w-full pl-8 pr-4 py-1.5 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none">
                        </div>

                        <select name="status" onchange="this.form.submit()" class="w-full sm:w-44 py-1.5 px-3 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-medium text-slate-700">
                            <option value="">All Statuses</option>
                            <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active Only</option>
                            <option value="closed" <?= $filterStatus === 'closed' ? 'selected' : '' ?>>Closed Only</option>
                        </select>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button type="submit" class="px-4 py-1.5 bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                            <?php if (!empty($search) || !empty($filterStatus) || $currentDept !== 'all'): ?>
                                <a href="careers.php" class="px-3 py-1.5 border border-[#e5e4dc] text-slate-600 text-xs font-semibold hover:bg-cream-100 transition">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Jobs Table -->
            <div class="bg-white border border-[#e5e4dc] overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-[#e5e4dc] flex items-center justify-between bg-cream-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-space">Listed Job Roles</h2>
                        <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-cream-200 text-slate-700"><?= count($jobs) ?></span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-mono">Real-time candidate tracking enabled</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-cream-100/60 text-slate-500 uppercase tracking-wider border-b border-[#e5e4dc] text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4">Position &amp; Role</th>
                                <th class="py-3 px-4">Department &amp; Type</th>
                                <th class="py-3 px-4">Location &amp; Openings</th>
                                <th class="py-3 px-4">Compensation</th>
                                <th class="py-3 px-4 text-center">Applicants</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e4dc]">
                            <?php if (empty($jobs)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-briefcase text-3xl mb-2 text-slate-300 block"></i>
                                        <div class="font-bold text-sm text-slate-700 font-space">No job openings found</div>
                                        <div class="text-xs mt-1">Click "Post New Job" above to create your first vacancy.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($jobs as $j): ?>
                                    <tr class="hover:bg-cream-50/70 transition-colors">
                                        <!-- Title & Slug -->
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($j['title']) ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">slug: <?= htmlspecialchars($j['slug']) ?></div>
                                        </td>

                                        <!-- Department & Job Type -->
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-slate-800"><?= htmlspecialchars($j['department']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($j['job_type']) ?> &bull; <?= htmlspecialchars($j['experience_level']) ?></div>
                                        </td>

                                        <!-- Location & Openings -->
                                        <td class="py-3.5 px-4">
                                            <div class="text-slate-800 font-medium"><?= htmlspecialchars($j['location']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= (int)$j['openings'] ?> Vacanc<?= (int)$j['openings'] == 1 ? 'y' : 'ies' ?></div>
                                        </td>

                                        <!-- Salary -->
                                        <td class="py-3.5 px-4 font-mono font-medium text-slate-700">
                                            <?= htmlspecialchars($j['salary_range'] ?: 'Best in Industry') ?>
                                        </td>

                                        <!-- Applicants Count -->
                                        <td class="py-3.5 px-4 text-center">
                                            <a href="job-applications.php?job_id=<?= $j['id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded <?= (int)$j['applicants_count'] > 0 ? 'bg-sage-100 text-sage-900 border border-sage-300 hover:bg-sage-200' : 'bg-slate-100 text-slate-500 border border-slate-200' ?> transition">
                                                <i class="fa-solid fa-user-group text-[10px]"></i>
                                                <span><?= (int)$j['applicants_count'] ?></span>
                                            </a>
                                        </td>

                                        <!-- Status Toggle -->
                                        <td class="py-3.5 px-4">
                                            <a href="careers.php?toggle_status=<?= $j['id'] ?>" title="Click to toggle status" class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border <?= $j['status'] === 'active' ? 'bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200' ?> transition">
                                                <span class="w-1.5 h-1.5 rounded-full <?= $j['status'] === 'active' ? 'bg-emerald-600' : 'bg-slate-400' ?>"></span>
                                                <span><?= $j['status'] === 'active' ? 'Active' : 'Closed' ?></span>
                                            </a>
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="../job-details.php?slug=<?= urlencode($j['slug']) ?>" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700 transition" title="Preview on Frontend">
                                                    <i class="fa-solid fa-external-link text-xs"></i>
                                                </a>
                                                <a href="career-edit.php?id=<?= $j['id'] ?>" class="p-1.5 text-sage-700 hover:text-sage-900 transition font-bold" title="Edit Opening">
                                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                                </a>
                                                <a href="careers.php?delete_id=<?= $j['id'] ?>" onclick="return confirm('Permanently delete this job opening? All applicant records for this role will also be deleted.')" class="p-1.5 text-rose-500 hover:text-rose-700 transition" title="Delete Opening">
                                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
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
