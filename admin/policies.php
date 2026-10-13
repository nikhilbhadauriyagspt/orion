<?php
/**
 * Admin Legal & Policies Content Manager - Orion Advent
 * Features:
 * - Edit Privacy Policy, Terms & Conditions, Cancellation & Refund Policy, Booking Policy
 * - Live formatted preview
 * - Update Last Revised Date
 * - 100% Flat, Sage & Cream Admin Palette
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$alertMessage = '';
$alertType = 'success';
$activePolicy = isset($_GET['policy']) ? trim($_GET['policy']) : 'privacy';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_policies') {
        $postData = [
            'policy_last_updated' => trim($_POST['policy_last_updated'] ?? date('F Y')),
            'policy_privacy' => trim($_POST['policy_privacy'] ?? ''),
            'policy_terms' => trim($_POST['policy_terms'] ?? ''),
            'policy_cancellation' => trim($_POST['policy_cancellation'] ?? ''),
            'policy_booking' => trim($_POST['policy_booking'] ?? ''),
        ];

        if (updateSettings($postData)) {
            $alertMessage = "All legal policies & compliance terms updated successfully!";
            $alertType = "success";
        } else {
            $alertMessage = "Failed to update policies. Please check database connection.";
            $alertType = "error";
        }
    }
}

$settings = getGlobalSettings(true);
$pageTitle = "Legal & Policies Manager";
include 'components/head.php';
?>

<div class="min-h-screen flex bg-cream-100/70 antialiased selection:bg-sage-600 selection:text-white">
    
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 xl:pl-68 flex flex-col min-w-0 transition-all">
        
        <!-- Header -->
        <?php include 'components/header.php'; ?>

        <!-- Content Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full space-y-5">

            <!-- Top Header Action Strip -->
            <div class="bg-white border border-[#e5e4dc] p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 bg-sage-600"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5">Legal &amp; Compliance Desk</span>
                        <span class="text-xs text-slate-400">• Last Revised: <?php echo htmlspecialchars($settings['policy_last_updated'] ?? 'October 2026'); ?></span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        Terms, Privacy &amp; Cancellation Policies
                    </h1>
                    <p class="text-xs text-slate-500">
                        Customize legal terms, traveler privacy guidelines, token advance guarantees, and refund slabs shown on public policy pages.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="../policies.php" target="_blank" class="px-3.5 py-2 bg-white hover:bg-cream-100 text-slate-700 text-xs font-bold transition border border-[#e5e4dc] flex items-center gap-2">
                        <i class="fa-solid fa-arrow-up-right-from-square text-sage-700 text-xs"></i>
                        <span>View Public Policies</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($alertMessage)): ?>
                <div class="p-3.5 border text-xs font-semibold flex items-center justify-between <?php echo $alertType === 'success' ? 'bg-sage-50 text-sage-900 border-sage-200' : 'bg-rose-50 text-rose-800 border-rose-200'; ?>">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check text-sage-700' : 'fa-circle-exclamation text-rose-600'; ?> text-sm"></i>
                        <span><?php echo htmlspecialchars($alertMessage); ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 text-xs">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            <?php endif; ?>

            <form action="policies.php?policy=<?php echo urlencode($activePolicy); ?>" method="POST" class="space-y-5">
                <input type="hidden" name="action" value="save_policies">

                <!-- Meta Strip -->
                <div class="bg-white border border-[#e5e4dc] p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider shrink-0">Last Revised Text:</label>
                        <input type="text" name="policy_last_updated" value="<?php echo htmlspecialchars($settings['policy_last_updated'] ?? 'October 2026'); ?>" placeholder="e.g. October 2026" class="px-3 py-1.5 border border-[#e5e4dc] bg-cream-50 text-xs font-bold text-slate-900 focus:outline-none focus:border-sage-600">
                    </div>

                    <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-sage-700 hover:bg-sage-800 text-white font-bold text-xs uppercase tracking-wider border border-sage-800 transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Save All Policies
                    </button>
                </div>

                <!-- Policy Selector Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none border-b border-[#e5e4dc]">
                    <button type="button" onclick="switchPolicyTab('privacy')" id="pTab-privacy" class="policy-tab-btn px-4 py-2.5 text-xs font-bold transition flex items-center gap-2 whitespace-nowrap border <?php echo $activePolicy === 'privacy' ? 'bg-sage-700 text-white border-sage-800' : 'bg-white text-slate-700 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                        <span>1. Privacy Policy</span>
                    </button>

                    <button type="button" onclick="switchPolicyTab('terms')" id="pTab-terms" class="policy-tab-btn px-4 py-2.5 text-xs font-bold transition flex items-center gap-2 whitespace-nowrap border <?php echo $activePolicy === 'terms' ? 'bg-sage-700 text-white border-sage-800' : 'bg-white text-slate-700 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-file-contract text-xs"></i>
                        <span>2. Terms &amp; Conditions</span>
                    </button>

                    <button type="button" onclick="switchPolicyTab('cancellation')" id="pTab-cancellation" class="policy-tab-btn px-4 py-2.5 text-xs font-bold transition flex items-center gap-2 whitespace-nowrap border <?php echo $activePolicy === 'cancellation' ? 'bg-sage-700 text-white border-sage-800' : 'bg-white text-slate-700 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        <span>3. Cancellation &amp; Refund</span>
                    </button>

                    <button type="button" onclick="switchPolicyTab('booking')" id="pTab-booking" class="policy-tab-btn px-4 py-2.5 text-xs font-bold transition flex items-center gap-2 whitespace-nowrap border <?php echo $activePolicy === 'booking' ? 'bg-sage-700 text-white border-sage-800' : 'bg-white text-slate-700 hover:bg-cream-100 border-[#e5e4dc]'; ?>">
                        <i class="fa-solid fa-credit-card text-xs"></i>
                        <span>4. Booking &amp; Token Advance</span>
                    </button>
                </div>

                <!-- TAB 1: PRIVACY POLICY -->
                <div id="pPanel-privacy" class="policy-panel <?php echo $activePolicy === 'privacy' ? '' : 'hidden'; ?>">
                    <div class="bg-white border border-[#e5e4dc] p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Privacy Policy Document</h3>
                                <p class="text-[11px] text-slate-400">Displayed at <a href="../privacy-policy.php" target="_blank" class="text-sage-700 underline font-semibold">/privacy-policy.php</a></p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-sage-50 text-sage-800 border border-sage-200 uppercase">HTML Supported</span>
                        </div>

                        <textarea name="policy_privacy" rows="14" class="w-full p-4 text-xs font-mono text-slate-800 bg-cream-50/50 border border-[#e5e4dc] focus:outline-none focus:border-sage-600 leading-relaxed"><?php echo htmlspecialchars($settings['policy_privacy'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- TAB 2: TERMS & CONDITIONS -->
                <div id="pPanel-terms" class="policy-panel <?php echo $activePolicy === 'terms' ? '' : 'hidden'; ?>">
                    <div class="bg-white border border-[#e5e4dc] p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Terms &amp; Conditions Document</h3>
                                <p class="text-[11px] text-slate-400">Displayed at <a href="../terms.php" target="_blank" class="text-sage-700 underline font-semibold">/terms.php</a></p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-sage-50 text-sage-800 border border-sage-200 uppercase">HTML Supported</span>
                        </div>

                        <textarea name="policy_terms" rows="14" class="w-full p-4 text-xs font-mono text-slate-800 bg-cream-50/50 border border-[#e5e4dc] focus:outline-none focus:border-sage-600 leading-relaxed"><?php echo htmlspecialchars($settings['policy_terms'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- TAB 3: CANCELLATION & REFUND -->
                <div id="pPanel-cancellation" class="policy-panel <?php echo $activePolicy === 'cancellation' ? '' : 'hidden'; ?>">
                    <div class="bg-white border border-[#e5e4dc] p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Cancellation &amp; Refund Policy Document</h3>
                                <p class="text-[11px] text-slate-400">Displayed at <a href="../cancellation-policy.php" target="_blank" class="text-sage-700 underline font-semibold">/cancellation-policy.php</a></p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-sage-50 text-sage-800 border border-sage-200 uppercase">HTML Supported</span>
                        </div>

                        <textarea name="policy_cancellation" rows="14" class="w-full p-4 text-xs font-mono text-slate-800 bg-cream-50/50 border border-[#e5e4dc] focus:outline-none focus:border-sage-600 leading-relaxed"><?php echo htmlspecialchars($settings['policy_cancellation'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- TAB 4: BOOKING & TOKEN ADVANCE -->
                <div id="pPanel-booking" class="policy-panel <?php echo $activePolicy === 'booking' ? '' : 'hidden'; ?>">
                    <div class="bg-white border border-[#e5e4dc] p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Booking &amp; Token Advance Policy</h3>
                                <p class="text-[11px] text-slate-400">Displayed during checkout and in policy hub</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-sage-50 text-sage-800 border border-sage-200 uppercase">HTML Supported</span>
                        </div>

                        <textarea name="policy_booking" rows="14" class="w-full p-4 text-xs font-mono text-slate-800 bg-cream-50/50 border border-[#e5e4dc] focus:outline-none focus:border-sage-600 leading-relaxed"><?php echo htmlspecialchars($settings['policy_booking'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="px-8 py-3 bg-sage-700 hover:bg-sage-800 text-white font-bold text-xs uppercase tracking-wider border border-sage-800 transition cursor-pointer">
                        Save All Policies &amp; Terms
                    </button>
                </div>

            </form>

        </main>
    </div>
</div>

<script>
function switchPolicyTab(policyKey) {
    document.querySelectorAll('.policy-panel').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.policy-tab-btn').forEach(btn => {
        btn.classList.remove('bg-sage-700', 'text-white', 'border-sage-800');
        btn.classList.add('bg-white', 'text-slate-700', 'border-[#e5e4dc]');
    });

    const panel = document.getElementById('pPanel-' + policyKey);
    const tabBtn = document.getElementById('pTab-' + policyKey);
    if (panel) panel.classList.remove('hidden');
    if (tabBtn) {
        tabBtn.classList.remove('bg-white', 'text-slate-700', 'border-[#e5e4dc]');
        tabBtn.classList.add('bg-sage-700', 'text-white', 'border-sage-800');
    }
}
</script>

<?php include 'components/footer.php'; ?>
