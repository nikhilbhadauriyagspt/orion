<?php
/**
 * Admin Job Applications Management Controller
 * Standard Admin Layout with Shared Head, Sidebar, Header, Plus Jakarta Sans & Space Grotesk Fonts
 * Manage job applicants, view resumes, update status, and dispatch candidate email notifications
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/mailer.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();
$siteName = getSetting('site_name', 'Orion Advent');
$alertMessage = '';
$alertType = 'success';

// Handle Application Status Update & Candidate Email Dispatch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $appId = (int)($_POST['application_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? 'pending');
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    $notifyCandidate = isset($_POST['notify_candidate']) && $_POST['notify_candidate'] == '1';

    $allowedStatuses = ['pending', 'reviewed', 'shortlisted', 'selected', 'rejected'];
    if ($appId > 0 && in_array($newStatus, $allowedStatuses) && $pdo) {
        try {
            // Fetch current application + job details
            $stmt = $pdo->prepare("SELECT a.*, j.title AS job_title FROM job_applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ?");
            $stmt->execute([$appId]);
            $applicant = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($applicant) {
                $emailSent = $applicant['email_sent'];

                // Send email notification if checked
                if ($notifyCandidate && !empty($applicant['candidate_email'])) {
                    $subject = "Application Update: " . $applicant['job_title'] . " at " . $siteName;
                    $htmlBody = generateJobStatusEmailHtml(
                        $applicant['candidate_name'],
                        $applicant['job_title'],
                        $newStatus,
                        $adminNotes
                    );
                    $mailRes = sendPortalMail($applicant['candidate_email'], $applicant['candidate_name'], $subject, $htmlBody);
                    if ($mailRes['success']) {
                        $emailSent = 1;
                    }
                }

                $upStmt = $pdo->prepare("UPDATE job_applications SET status = ?, admin_notes = ?, email_sent = ?, updated_at = NOW() WHERE id = ?");
                $upStmt->execute([$newStatus, $adminNotes, $emailSent, $appId]);

                $alertMessage = "Application #{$appId} ({$applicant['candidate_name']}) updated to " . ucfirst($newStatus) . ($notifyCandidate ? " and candidate notified via email." : ".");
            }
        } catch (Exception $e) {
            $alertMessage = "Error updating application: " . $e->getMessage();
            $alertType = 'error';
        }
    }
}

// Handle Delete Application
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0 && $pdo) {
    $delId = (int)$_GET['delete'];
    try {
        $fStmt = $pdo->prepare("SELECT resume_file FROM job_applications WHERE id = ?");
        $fStmt->execute([$delId]);
        $resFile = $fStmt->fetchColumn();
        if ($resFile && file_exists(__DIR__ . '/../' . $resFile)) {
            @unlink(__DIR__ . '/../' . $resFile);
        }

        $stmt = $pdo->prepare("DELETE FROM job_applications WHERE id = ?");
        $stmt->execute([$delId]);
        $alertMessage = "Application #{$delId} permanently removed.";
    } catch (Exception $e) {
        $alertMessage = "Error deleting application: " . $e->getMessage();
        $alertType = 'error';
    }
}

// Filters & Query
$filterJobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

$jobsList = [];
$applications = [];
$totalApps = 0;
$pendingCount = 0;
$reviewedCount = 0;
$shortlistedCount = 0;
$selectedCount = 0;
$rejectedCount = 0;

if ($pdo) {
    try {
        $jobsList = $pdo->query("SELECT id, title, department FROM jobs ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);

        $totalApps = (int)$pdo->query("SELECT COUNT(*) FROM job_applications")->fetchColumn();
        $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM job_applications WHERE status = 'pending'")->fetchColumn();
        $reviewedCount = (int)$pdo->query("SELECT COUNT(*) FROM job_applications WHERE status = 'reviewed'")->fetchColumn();
        $shortlistedCount = (int)$pdo->query("SELECT COUNT(*) FROM job_applications WHERE status = 'shortlisted'")->fetchColumn();
        $selectedCount = (int)$pdo->query("SELECT COUNT(*) FROM job_applications WHERE status = 'selected'")->fetchColumn();
        $rejectedCount = (int)$pdo->query("SELECT COUNT(*) FROM job_applications WHERE status = 'rejected'")->fetchColumn();

        $where = ["1=1"];
        $params = [];

        if ($filterJobId > 0) {
            $where[] = "a.job_id = ?";
            $params[] = $filterJobId;
        }
        if (!empty($filterStatus)) {
            $where[] = "a.status = ?";
            $params[] = $filterStatus;
        }
        if (!empty($searchQuery)) {
            $where[] = "(a.candidate_name LIKE ? OR a.candidate_email LIKE ? OR a.candidate_phone LIKE ? OR j.title LIKE ?)";
            $wild = "%{$searchQuery}%";
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
        }

        $sql = "SELECT a.*, j.title AS job_title, j.department AS job_department, j.location AS job_location 
                FROM job_applications a 
                JOIN jobs j ON a.job_id = j.id 
                WHERE " . implode(" AND ", $where) . " 
                ORDER BY a.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $applications = [];
    }
}

$pageTitle = "Job Applicants & Resumes";
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
                        <a href="careers.php" class="hover:text-sage-700">Careers</a>
                        <span>/</span>
                        <span class="text-sage-700 font-semibold">Candidates &amp; Resumes</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">Job Applications &amp; Talent Pool</h1>
                    <p class="text-xs text-slate-500">Review candidate CVs, inspect detailed profile submissions, and manage shortlisting workflow.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="careers.php" class="px-3.5 py-2 text-xs font-bold bg-white hover:bg-cream-100 text-slate-700 border border-[#e5e4dc] transition-colors flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-briefcase text-sage-600"></i>
                        <span>Manage Vacancies (<?= count($jobsList) ?>)</span>
                    </a>
                    <a href="../careers.php" target="_blank" class="px-4 py-2 text-xs font-bold bg-sage-700 hover:bg-sage-800 text-white border border-sage-800 transition-colors flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-external-link text-[11px]"></i>
                        <span>Live Careers Portal</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 text-xs font-semibold flex items-center justify-between <?= $alertType === 'success' ? 'bg-sage-50 text-sage-900 border border-sage-300' : 'bg-rose-50 text-rose-900 border border-rose-200'; ?>">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid <?= $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?>"></i>
                        <span><?= htmlspecialchars($alertMessage); ?></span>
                    </div>
                    <a href="job-applications.php" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></a>
                </div>
            <?php endif; ?>

            <!-- Metric KPI Stat Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <a href="job-applications.php" class="p-3.5 bg-white border border-[#e5e4dc] hover:border-sage-600 transition block <?= empty($filterStatus) ? 'ring-1 ring-sage-700' : '' ?>">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Applicants</div>
                    <div class="text-xl font-extrabold font-space text-slate-900 mt-1"><?= $totalApps ?></div>
                </a>

                <a href="job-applications.php?status=pending<?= $filterJobId ? '&job_id='.$filterJobId : '' ?>" class="p-3.5 bg-white border border-[#e5e4dc] hover:border-amber-500 transition block <?= $filterStatus === 'pending' ? 'ring-1 ring-amber-600' : '' ?>">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Pending Review</div>
                    <div class="text-xl font-extrabold font-space text-amber-800 mt-1"><?= $pendingCount ?></div>
                </a>

                <a href="job-applications.php?status=reviewed<?= $filterJobId ? '&job_id='.$filterJobId : '' ?>" class="p-3.5 bg-white border border-[#e5e4dc] hover:border-sky-500 transition block <?= $filterStatus === 'reviewed' ? 'ring-1 ring-sky-600' : '' ?>">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-sky-700">Reviewed</div>
                    <div class="text-xl font-extrabold font-space text-sky-800 mt-1"><?= $reviewedCount ?></div>
                </a>

                <a href="job-applications.php?status=shortlisted<?= $filterJobId ? '&job_id='.$filterJobId : '' ?>" class="p-3.5 bg-white border border-[#e5e4dc] hover:border-sage-600 transition block <?= $filterStatus === 'shortlisted' ? 'ring-1 ring-sage-700' : '' ?>">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-sage-700">Shortlisted</div>
                    <div class="text-xl font-extrabold font-space text-sage-800 mt-1"><?= $shortlistedCount ?></div>
                </a>

                <a href="job-applications.php?status=selected<?= $filterJobId ? '&job_id='.$filterJobId : '' ?>" class="p-3.5 bg-white border border-[#e5e4dc] hover:border-emerald-500 transition block <?= $filterStatus === 'selected' ? 'ring-1 ring-emerald-600' : '' ?>">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Selected / Hired</div>
                    <div class="text-xl font-extrabold font-space text-emerald-800 mt-1"><?= $selectedCount ?></div>
                </a>

                <a href="job-applications.php?status=rejected<?= $filterJobId ? '&job_id='.$filterJobId : '' ?>" class="p-3.5 bg-white border border-[#e5e4dc] hover:border-rose-500 transition block <?= $filterStatus === 'rejected' ? 'ring-1 ring-rose-600' : '' ?>">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Rejected</div>
                    <div class="text-xl font-extrabold font-space text-rose-700 mt-1"><?= $rejectedCount ?></div>
                </a>
            </div>

            <!-- Filters & Search Bar -->
            <div class="bg-white border border-[#e5e4dc] p-3">
                <form method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                    <?php if (!empty($filterStatus)): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
                    <?php endif; ?>

                    <div class="w-full sm:w-64">
                        <select name="job_id" onchange="this.form.submit()" class="w-full py-1.5 px-3 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-medium text-slate-700">
                            <option value="">All Job Openings (<?= count($jobsList) ?>)</option>
                            <?php foreach ($jobsList as $j): ?>
                                <option value="<?= $j['id'] ?>" <?= $filterJobId == $j['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j['title']) ?> (<?= htmlspecialchars($j['department']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="relative flex-1 w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search candidate name, email, phone or position..." class="w-full pl-8 pr-4 py-1.5 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" class="px-4 py-1.5 bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">Search</button>
                        <?php if ($filterJobId || !empty($filterStatus) || !empty($searchQuery)): ?>
                            <a href="job-applications.php" class="px-3 py-1.5 border border-[#e5e4dc] text-slate-600 text-xs font-semibold hover:bg-cream-100 transition whitespace-nowrap">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Applicants Table -->
            <div class="bg-white border border-[#e5e4dc] overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-[#e5e4dc] flex items-center justify-between bg-cream-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-sage-700"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 font-space">Candidate Submissions</h2>
                        <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-cream-200 text-slate-700"><?= count($applications) ?></span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-mono">Click "Review" to inspect full CV &amp; update decision</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-cream-100/60 text-slate-500 uppercase tracking-wider border-b border-[#e5e4dc] text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4">Candidate Contact</th>
                                <th class="py-3 px-4">Applied Position</th>
                                <th class="py-3 px-4">Experience &amp; Location</th>
                                <th class="py-3 px-4">Resume</th>
                                <th class="py-3 px-4">Applied On</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e4dc]">
                            <?php if (empty($applications)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300 block"></i>
                                        <div class="font-bold text-sm text-slate-700 font-space">No applications found</div>
                                        <div class="text-xs mt-1">Applications received from the Careers page will appear here automatically.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): 
                                    $st = $app['status'] ?? 'pending';
                                    $badgeStyles = [
                                        'pending' => 'bg-amber-50 text-amber-800 border-amber-300',
                                        'reviewed' => 'bg-sky-50 text-sky-800 border-sky-300',
                                        'shortlisted' => 'bg-sage-50 text-sage-900 border-sage-400',
                                        'selected' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                                        'rejected' => 'bg-rose-50 text-rose-800 border-rose-300'
                                    ];
                                    $bCls = $badgeStyles[$st] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                                ?>
                                    <tr class="hover:bg-cream-50/70 transition-colors">
                                        <!-- Candidate Contact -->
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($app['candidate_name']) ?></div>
                                            <div class="flex items-center gap-2.5 text-slate-500 mt-0.5 text-[11px]">
                                                <a href="mailto:<?= htmlspecialchars($app['candidate_email']) ?>" class="hover:text-sage-700 flex items-center gap-1">
                                                    <i class="fa-regular fa-envelope text-slate-400 text-[10px]"></i> <?= htmlspecialchars($app['candidate_email']) ?>
                                                </a>
                                                <a href="tel:<?= htmlspecialchars($app['candidate_phone']) ?>" class="hover:text-sage-700 flex items-center gap-1 font-mono">
                                                    <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i> <?= htmlspecialchars($app['candidate_phone']) ?>
                                                </a>
                                            </div>
                                        </td>

                                        <!-- Position -->
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-800"><?= htmlspecialchars($app['job_title']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($app['job_department']) ?> &bull; <?= htmlspecialchars($app['job_location']) ?></div>
                                        </td>

                                        <!-- Experience & Loc -->
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-slate-700"><?= htmlspecialchars($app['experience_years']) ?> Yrs Exp</div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($app['current_location'] ?: 'Not Specified') ?> &bull; <?= htmlspecialchars($app['notice_period'] ?: 'Immediate') ?></div>
                                        </td>

                                        <!-- Resume -->
                                        <td class="py-3.5 px-4">
                                            <?php if (!empty($app['resume_file'])): ?>
                                                <a href="../<?= htmlspecialchars($app['resume_file']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-white border border-[#e5e4dc] hover:border-sage-600 hover:text-sage-800 text-slate-700 font-bold text-[10px] transition">
                                                    <i class="fa-solid fa-file-pdf text-rose-500 text-[11px]"></i> CV Document
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic text-[11px]">No CV</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Date -->
                                        <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">
                                            <div><?= date('d M Y', strtotime($app['created_at'])) ?></div>
                                            <div class="text-[10px] text-slate-400"><?= date('h:i A', strtotime($app['created_at'])) ?></div>
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border <?= $bCls ?>">
                                                <?= strtoupper($st) ?>
                                            </span>
                                            <?php if ($app['email_sent']): ?>
                                                <div class="text-[9px] text-sage-700 font-bold mt-0.5 flex items-center gap-0.5"><i class="fa-solid fa-check text-[8px]"></i> Email Sent</div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <button type="button" onclick="openReviewModal(<?= htmlspecialchars(json_encode($app)) ?>)" class="px-2.5 py-1 bg-sage-700 hover:bg-sage-800 text-white font-bold text-xs border border-sage-800 transition inline-flex items-center gap-1 mr-1">
                                                <i class="fa-regular fa-eye text-[11px]"></i> Review
                                            </button>
                                            <a href="job-applications.php?delete=<?= $app['id'] ?>" onclick="return confirm('Permanently delete candidate application #<?= $app['id'] ?>?')" class="p-1 text-slate-400 hover:text-rose-600 transition inline-block" title="Delete">
                                                <i class="fa-regular fa-trash-can text-xs"></i>
                                            </a>
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

<!-- Candidate Review & Status Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white border border-[#e5e4dc] max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
        <!-- Modal Header -->
        <div class="px-5 py-3.5 border-b border-[#e5e4dc] bg-cream-50 flex items-center justify-between">
            <div>
                <div class="text-[10px] font-bold text-sage-700 uppercase tracking-wider font-space">Candidate Dossier</div>
                <h3 class="text-base font-bold text-slate-900 font-space" id="modalCandidateName">Candidate Details</h3>
            </div>
            <button type="button" onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-700 p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1 text-xs">
            
            <!-- Quick Summary Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 bg-cream-50 p-3.5 border border-[#e5e4dc]">
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Position</div>
                    <div class="font-bold text-slate-900 mt-0.5" id="modalJobTitle">-</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Experience</div>
                    <div class="font-bold text-slate-900 mt-0.5" id="modalExperience">-</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Notice Period</div>
                    <div class="font-bold text-slate-900 mt-0.5" id="modalNotice">-</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Location</div>
                    <div class="font-bold text-slate-900 mt-0.5" id="modalLocation">-</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Email</div>
                    <div class="font-bold text-slate-900 mt-0.5 truncate" id="modalEmail">-</div>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Phone</div>
                    <div class="font-bold text-slate-900 mt-0.5 font-mono" id="modalPhone">-</div>
                </div>
            </div>

            <!-- Portfolio Link -->
            <div id="modalPortfolioContainer" class="flex items-center justify-between p-3 bg-white border border-[#e5e4dc]">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-link text-slate-400 text-xs"></i>
                    <span class="font-semibold text-slate-700">Portfolio / LinkedIn Profile</span>
                </div>
                <a id="modalPortfolioLink" href="#" target="_blank" class="font-bold text-sage-700 hover:underline text-xs">
                    Open Link <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[10px]"></i>
                </a>
            </div>

            <!-- Cover Note -->
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Candidate Cover Note / Pitch</label>
                <div class="p-3.5 bg-cream-50 border border-[#e5e4dc] text-slate-700 leading-relaxed whitespace-pre-line text-xs" id="modalCoverLetter">
                    No cover note submitted.
                </div>
            </div>

            <!-- Direct CV Download -->
            <div class="flex items-center justify-between p-3.5 bg-sage-50 border border-sage-300">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-sage-700 text-white flex items-center justify-center font-bold text-xs">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-xs">Official Candidate Resume</div>
                        <div class="text-[10px] text-slate-500">Uploaded during application submission</div>
                    </div>
                </div>
                <a id="modalResumeDownloadBtn" href="#" target="_blank" class="px-3 py-1.5 bg-sage-700 hover:bg-sage-800 text-white font-bold text-xs border border-sage-800 transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-download text-[10px]"></i> View CV
                </a>
            </div>

            <!-- Status Decision Form -->
            <form method="POST" action="job-applications.php" class="border-t border-[#e5e4dc] pt-4 space-y-3">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="application_id" id="modalFormAppId" value="">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Application Decision</label>
                        <select name="status" id="modalFormStatus" class="w-full py-1.5 px-3 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none font-bold text-slate-800">
                            <option value="pending">Pending Review</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="shortlisted">Shortlisted</option>
                            <option value="selected">Selected / Hired</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Candidate Notification</label>
                        <label class="flex items-center gap-2 mt-1.5 cursor-pointer">
                            <input type="checkbox" name="notify_candidate" value="1" checked class="w-4 h-4 text-sage-700 border-[#e5e4dc]">
                            <span class="text-xs font-semibold text-slate-700">Send status update email to candidate</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Internal Feedback / Candidate Note</label>
                    <textarea name="admin_notes" id="modalFormNotes" rows="2" placeholder="e.g. Interview scheduled for Monday 3 PM..." class="w-full p-2.5 text-xs bg-cream-100/50 border border-[#e5e4dc] focus:border-sage-700 focus:bg-white outline-none placeholder:text-slate-400"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeReviewModal()" class="px-3 py-1.5 border border-[#e5e4dc] text-slate-700 font-bold hover:bg-cream-100 text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-1.5 bg-sage-700 hover:bg-sage-800 text-white font-bold text-xs border border-sage-800 transition">
                        Save Decision
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    function openReviewModal(app) {
        document.getElementById('modalCandidateName').innerText = app.candidate_name;
        document.getElementById('modalJobTitle').innerText = app.job_title;
        document.getElementById('modalExperience').innerText = (app.experience_years || 0) + ' Years';
        document.getElementById('modalNotice').innerText = app.notice_period || 'Immediate';
        document.getElementById('modalLocation').innerText = app.current_location || 'Not Specified';
        document.getElementById('modalEmail').innerText = app.candidate_email;
        document.getElementById('modalPhone').innerText = app.candidate_phone;
        document.getElementById('modalCoverLetter').innerText = app.cover_letter || 'No cover note submitted.';
        
        const resumeBtn = document.getElementById('modalResumeDownloadBtn');
        if (app.resume_file) {
            resumeBtn.href = '../' + app.resume_file;
            resumeBtn.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            resumeBtn.href = '#';
            resumeBtn.classList.add('opacity-50', 'pointer-events-none');
        }

        const portContainer = document.getElementById('modalPortfolioContainer');
        const portLink = document.getElementById('modalPortfolioLink');
        if (app.portfolio_url && app.portfolio_url.trim() !== '') {
            portContainer.style.display = 'flex';
            portLink.href = app.portfolio_url.startsWith('http') ? app.portfolio_url : 'https://' + app.portfolio_url;
        } else {
            portContainer.style.display = 'none';
        }

        document.getElementById('modalFormAppId').value = app.id;
        document.getElementById('modalFormStatus').value = app.status || 'pending';
        document.getElementById('modalFormNotes').value = app.admin_notes || '';

        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

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
