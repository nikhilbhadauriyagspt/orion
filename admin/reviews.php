<?php
/**
 * Admin Reviews & Testimonials Manager - Orion Advent
 * Features:
 * - List all traveler testimonials with star rating, verified guest badge, category & tour booked
 * - Add new review with avatar photo, travel category, star score & tour name
 * - Edit existing review details
 * - Toggle visibility (Active / Hidden)
 * - Delete review with confirmation
 * - 100% Flat, Border-First, Sage & Cream Palette
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

// Handle POST actions (Add, Edit, Delete, Toggle Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_review') {
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $category = trim($_POST['category'] ?? 'honeymoon');
        $tour = trim($_POST['tour'] ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $quote = trim($_POST['quote'] ?? '');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $status = in_array($_POST['status'] ?? '', ['active', 'hidden']) ? $_POST['status'] : 'active';

        // Handle avatar upload if provided
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'rev_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $uploadDir . $filename)) {
                    $avatar = 'uploads/avatars/' . $filename;
                }
            }
        }

        if (empty($avatar)) {
            $avatar = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80';
        }

        if (empty($name) || empty($tour) || empty($headline) || empty($quote)) {
            $alertMessage = "Please fill in all required fields (Name, Tour, Headline, and Review Quote).";
            $alertType = "error";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO `reviews` (`name`, `city`, `avatar`, `category`, `tour`, `headline`, `quote`, `rating`, `status`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $city, $avatar, $category, $tour, $headline, $quote, $rating, $status]);
                $alertMessage = "New traveler review added successfully!";
                $alertType = "success";
            } catch (Exception $e) {
                $alertMessage = "Database error: " . $e->getMessage();
                $alertType = "error";
            }
        }

    } elseif ($action === 'edit_review') {
        $id = (int)($_POST['review_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $category = trim($_POST['category'] ?? 'honeymoon');
        $tour = trim($_POST['tour'] ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $quote = trim($_POST['quote'] ?? '');
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $status = in_array($_POST['status'] ?? '', ['active', 'hidden']) ? $_POST['status'] : 'active';

        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'rev_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $uploadDir . $filename)) {
                    $avatar = 'uploads/avatars/' . $filename;
                }
            }
        }

        if ($id > 0 && !empty($name) && !empty($tour) && !empty($headline) && !empty($quote)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE `reviews` SET 
                        `name` = ?, 
                        `city` = ?, 
                        `avatar` = ?, 
                        `category` = ?, 
                        `tour` = ?, 
                        `headline` = ?, 
                        `quote` = ?, 
                        `rating` = ?, 
                        `status` = ?
                    WHERE `id` = ?
                ");
                $stmt->execute([$name, $city, $avatar, $category, $tour, $headline, $quote, $rating, $status, $id]);
                $alertMessage = "Review #{$id} updated successfully!";
                $alertType = "success";
            } catch (Exception $e) {
                $alertMessage = "Database error: " . $e->getMessage();
                $alertType = "error";
            }
        } else {
            $alertMessage = "Missing required review information.";
            $alertType = "error";
        }

    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['review_id'] ?? 0);
        $curr = $_POST['current_status'] ?? 'active';
        $newStatus = ($curr === 'active') ? 'hidden' : 'active';
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE `reviews` SET `status` = ? WHERE `id` = ?");
            $stmt->execute([$newStatus, $id]);
            $alertMessage = "Review #{$id} status changed to " . strtoupper($newStatus) . "!";
            $alertType = "success";
        }

    } elseif ($action === 'delete_review') {
        $id = (int)($_POST['review_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM `reviews` WHERE `id` = ?");
            $stmt->execute([$id]);
            $alertMessage = "Review #{$id} has been deleted permanently.";
            $alertType = "success";
        }
    }
}

// Fetch Reviews with filtering and search
$filterCategory = trim($_GET['category'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM `reviews` WHERE 1=1";
$params = [];

if (!empty($filterCategory)) {
    $sql .= " AND `category` LIKE ?";
    $params[] = '%' . $filterCategory . '%';
}
if (!empty($filterStatus)) {
    $sql .= " AND `status` = ?";
    $params[] = $filterStatus;
}
if (!empty($searchQuery)) {
    $sql .= " AND (`name` LIKE ? OR `tour` LIKE ? OR `headline` LIKE ? OR `city` LIKE ?)";
    $params[] = '%' . $searchQuery . '%';
    $params[] = '%' . $searchQuery . '%';
    $params[] = '%' . $searchQuery . '%';
    $params[] = '%' . $searchQuery . '%';
}

$sql .= " ORDER BY `id` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Calculate Stats
$totalReviewsCount = (int)$pdo->query("SELECT COUNT(*) FROM `reviews`")->fetchColumn();
$activeReviewsCount = (int)$pdo->query("SELECT COUNT(*) FROM `reviews` WHERE `status` = 'active'")->fetchColumn();
$hiddenReviewsCount = (int)$pdo->query("SELECT COUNT(*) FROM `reviews` WHERE `status` = 'hidden'")->fetchColumn();
$avgRating = $pdo->query("SELECT AVG(rating) FROM `reviews` WHERE `status` = 'active'")->fetchColumn();
$avgRating = $avgRating ? number_format((float)$avgRating, 1) : '5.0';

$pageTitle = "Reviews & Testimonials Manager";
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
                        <span class="text-[10px] font-bold uppercase tracking-wider text-sage-800 bg-sage-50 border border-sage-200 px-2 py-0.5">Social Proof Desk</span>
                        <span class="text-xs text-slate-400">• Total <?php echo $totalReviewsCount; ?> Testimonials</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold font-space text-slate-900 tracking-tight">
                        Traveler Reviews &amp; Testimonials
                    </h1>
                    <p class="text-xs text-slate-500">
                        Add, moderate, and showcase authentic traveler stories, verified guest scores, and holiday reviews across the website.
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" onclick="openAddReviewModal()" class="px-4 py-2.5 bg-sage-700 hover:bg-sage-800 text-white text-xs font-bold transition flex items-center gap-2 border border-sage-800 cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Add New Review</span>
                    </button>
                    <a href="../index.php#reviews-section" target="_blank" class="px-3 py-2.5 bg-white hover:bg-cream-100 text-slate-700 text-xs font-bold transition border border-[#e5e4dc] flex items-center gap-1.5">
                        <i class="fa-solid fa-eye text-sage-700 text-xs"></i>
                        <span class="hidden sm:inline">View on Website</span>
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

            <!-- 4 Metric Cards Strip -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                <div class="bg-white border border-[#e5e4dc] p-4 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Reviews</span>
                    <div class="text-2xl font-bold font-space text-slate-900"><?php echo $totalReviewsCount; ?></div>
                    <span class="text-[10px] text-slate-500 block">Across all tour themes</span>
                </div>
                <div class="bg-white border border-[#e5e4dc] p-4 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Active Visible</span>
                    <div class="text-2xl font-bold font-space text-emerald-700"><?php echo $activeReviewsCount; ?></div>
                    <span class="text-[10px] text-emerald-600 block">Live on homepage</span>
                </div>
                <div class="bg-white border border-[#e5e4dc] p-4 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Average Rating</span>
                    <div class="text-2xl font-bold font-space text-amber-700 flex items-center gap-1">
                        <span><?php echo $avgRating; ?></span>
                        <i class="fa-solid fa-star text-amber-500 text-base"></i>
                    </div>
                    <span class="text-[10px] text-amber-600 block">Guest satisfaction score</span>
                </div>
                <div class="bg-white border border-[#e5e4dc] p-4 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Hidden / Draft</span>
                    <div class="text-2xl font-bold font-space text-slate-700"><?php echo $hiddenReviewsCount; ?></div>
                    <span class="text-[10px] text-slate-400 block">Not displayed on frontend</span>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white border border-[#e5e4dc] p-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <form action="reviews.php" method="GET" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                    <div class="relative min-w-[200px] flex-1 sm:flex-initial">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search customer, tour, headline..." class="w-full pl-8 pr-3 py-1.5 border border-[#e5e4dc] bg-cream-50/50 text-xs font-medium text-slate-800 focus:outline-none focus:border-sage-600">
                    </div>

                    <select name="category" onchange="this.form.submit()" class="px-2.5 py-1.5 border border-[#e5e4dc] bg-white text-xs font-semibold text-slate-700 focus:outline-none">
                        <option value="">All Categories</option>
                        <option value="honeymoon" <?php echo $filterCategory === 'honeymoon' ? 'selected' : ''; ?>>Honeymoon</option>
                        <option value="family" <?php echo $filterCategory === 'family' ? 'selected' : ''; ?>>Family Vacations</option>
                        <option value="adventure" <?php echo $filterCategory === 'adventure' ? 'selected' : ''; ?>>Adventure Treks</option>
                        <option value="luxury" <?php echo $filterCategory === 'luxury' ? 'selected' : ''; ?>>Luxury Getaways</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="px-2.5 py-1.5 border border-[#e5e4dc] bg-white text-xs font-semibold text-slate-700 focus:outline-none">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="hidden" <?php echo $filterStatus === 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                    </select>

                    <?php if (!empty($searchQuery) || !empty($filterCategory) || !empty($filterStatus)): ?>
                        <a href="reviews.php" class="text-[11px] font-bold text-rose-600 hover:underline px-2 py-1">Clear Filters</a>
                    <?php endif; ?>
                </form>

                <div class="text-[11px] text-slate-500 font-semibold self-start sm:self-center">
                    Showing <?php echo count($reviews); ?> of <?php echo $totalReviewsCount; ?> reviews
                </div>
            </div>

            <!-- Reviews Table / Card Grid -->
            <div class="bg-white border border-[#e5e4dc] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-cream-100/90 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-[#e5e4dc]">
                                <th class="py-3 px-4">Traveler / Guest</th>
                                <th class="py-3 px-4">Tour &amp; Theme</th>
                                <th class="py-3 px-4">Rating</th>
                                <th class="py-3 px-4">Headline &amp; Experience</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e5e4dc]">
                            <?php if (empty($reviews)): ?>
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-comments text-3xl mb-2 text-slate-300 block"></i>
                                        <span>No traveler reviews found matching your search.</span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reviews as $rev): 
                                    $avatarSrc = !empty($rev['avatar']) ? $rev['avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80';
                                    if (strpos($avatarSrc, 'http') !== 0) {
                                        $avatarSrc = '../' . ltrim($avatarSrc, '/');
                                    }
                                    $jsonData = htmlspecialchars(json_encode($rev), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr class="hover:bg-cream-50/60 transition-colors group">
                                        <!-- Guest Info -->
                                        <td class="py-3.5 px-4 align-top">
                                            <div class="flex items-center gap-3">
                                                <img src="<?php echo htmlspecialchars($avatarSrc); ?>" 
                                                     alt="<?php echo htmlspecialchars($rev['name']); ?>" 
                                                     class="w-10 h-10 rounded-full object-cover border border-[#e5e4dc] shrink-0"
                                                     onerror="this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80'">
                                                <div class="min-w-0">
                                                    <span class="font-bold text-slate-900 block leading-snug"><?php echo htmlspecialchars($rev['name']); ?></span>
                                                    <span class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                                        <i class="fa-solid fa-location-dot text-sage-700 text-[10px]"></i>
                                                        <span><?php echo htmlspecialchars($rev['city'] ?: 'India'); ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Tour & Category -->
                                        <td class="py-3.5 px-4 align-top">
                                            <span class="font-bold text-slate-800 block text-xs leading-snug"><?php echo htmlspecialchars($rev['tour']); ?></span>
                                            <span class="inline-block mt-1 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-sage-50 text-sage-800 border border-sage-200">
                                                <?php echo htmlspecialchars($rev['category']); ?>
                                            </span>
                                        </td>

                                        <!-- Rating -->
                                        <td class="py-3.5 px-4 align-top">
                                            <div class="flex items-center gap-0.5 text-amber-500">
                                                <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
                                                    <i class="fa-solid fa-star text-xs"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-bold mt-0.5 block"><?php echo $rev['rating']; ?>.0 Stars</span>
                                        </td>

                                        <!-- Headline & Quote -->
                                        <td class="py-3.5 px-4 align-top max-w-md">
                                            <h4 class="font-bold text-slate-900 text-xs leading-snug">"<?php echo htmlspecialchars($rev['headline']); ?>"</h4>
                                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                                <?php echo htmlspecialchars($rev['quote']); ?>
                                            </p>
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-4 align-top text-center">
                                            <form action="reviews.php" method="POST" class="inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $rev['status']; ?>">
                                                <button type="submit" class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border cursor-pointer transition <?php echo $rev['status'] === 'active' ? 'bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-300 hover:bg-slate-200'; ?>">
                                                    <?php echo $rev['status'] === 'active' ? 'Active' : 'Hidden'; ?>
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-3.5 px-4 align-top text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" 
                                                        onclick='openEditReviewModal(<?php echo $jsonData; ?>)' 
                                                        class="p-1.5 text-slate-600 hover:text-sage-800 hover:bg-cream-100 border border-transparent hover:border-[#e5e4dc] transition cursor-pointer" 
                                                        title="Edit Review">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </button>

                                                <form action="reviews.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this review permanently?');">
                                                    <input type="hidden" name="action" value="delete_review">
                                                    <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition cursor-pointer" title="Delete Review">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </form>
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

<!-- ===================================================================
     ADD / EDIT REVIEW MODAL
==================================================================== -->
<div id="reviewModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white border border-[#e5e4dc] max-w-xl w-full p-5 sm:p-6 space-y-4 my-auto animate-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-[#e5e4dc]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-sage-50 text-sage-800 border border-sage-200 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900" id="modalReviewTitle">Add New Traveler Review</h3>
                    <p class="text-[11px] text-slate-400">Display verified traveler testimonial on homepage</p>
                </div>
            </div>
            <button type="button" onclick="closeReviewModal()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-800 hover:bg-cream-100 border border-transparent hover:border-[#e5e4dc] transition">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <form action="reviews.php" method="POST" enctype="multipart/form-data" class="space-y-3.5">
            <input type="hidden" name="action" id="modalAction" value="add_review">
            <input type="hidden" name="review_id" id="modalReviewId" value="0">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Traveler Name *</label>
                    <input type="text" name="name" id="modalName" required placeholder="e.g. Sneha & Rohan Sharma" class="w-full px-3 py-2 border border-[#e5e4dc] bg-cream-50/50 text-xs font-semibold text-slate-900 focus:outline-none focus:border-sage-600">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">City / Country</label>
                    <input type="text" name="city" id="modalCity" placeholder="e.g. New Delhi, India" class="w-full px-3 py-2 border border-[#e5e4dc] bg-cream-50/50 text-xs font-medium text-slate-900 focus:outline-none focus:border-sage-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Tour / Package Booked *</label>
                    <input type="text" name="tour" id="modalTour" required placeholder="e.g. Maldives 5N/6D Villa" class="w-full px-3 py-2 border border-[#e5e4dc] bg-cream-50/50 text-xs font-semibold text-slate-900 focus:outline-none focus:border-sage-600">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Theme Category *</label>
                    <select name="category" id="modalCategory" class="w-full px-3 py-2 border border-[#e5e4dc] bg-white text-xs font-semibold text-slate-900 focus:outline-none focus:border-sage-600">
                        <option value="honeymoon">Honeymoon &amp; Couples</option>
                        <option value="family">Family Vacations</option>
                        <option value="adventure">Adventure &amp; Treks</option>
                        <option value="luxury">Luxury Getaways</option>
                        <option value="all">General / All</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Star Rating (1 to 5) *</label>
                    <select name="rating" id="modalRating" class="w-full px-3 py-2 border border-[#e5e4dc] bg-white text-xs font-bold text-amber-600 focus:outline-none focus:border-sage-600">
                        <option value="5">★★★★★ (5 Stars - Exceptional)</option>
                        <option value="4">★★★★☆ (4 Stars - Very Good)</option>
                        <option value="3">★★★☆☆ (3 Stars - Good)</option>
                        <option value="2">★★☆☆☆ (2 Stars - Fair)</option>
                        <option value="1">★☆☆☆☆ (1 Star - Poor)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Display Status *</label>
                    <select name="status" id="modalStatus" class="w-full px-3 py-2 border border-[#e5e4dc] bg-white text-xs font-semibold text-slate-900 focus:outline-none focus:border-sage-600">
                        <option value="active">Active (Visible on Frontend)</option>
                        <option value="hidden">Hidden / Draft</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Review Headline / Highlight Quote *</label>
                <input type="text" name="headline" id="modalHeadline" required placeholder="e.g. The best honeymoon we could have dreamed of!" class="w-full px-3 py-2 border border-[#e5e4dc] bg-cream-50/50 text-xs font-bold text-slate-900 focus:outline-none focus:border-sage-600">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Detailed Review Quote / Feedback *</label>
                <textarea name="quote" id="modalQuote" rows="3" required placeholder="Write the complete feedback from the customer..." class="w-full px-3 py-2 border border-[#e5e4dc] bg-cream-50/50 text-xs font-medium text-slate-900 focus:outline-none focus:border-sage-600 leading-relaxed"></textarea>
            </div>

            <!-- Avatar Image -->
            <div class="p-3 bg-cream-50 border border-[#e5e4dc] space-y-2">
                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Traveler Avatar Photo</label>
                <div class="flex items-center gap-3">
                    <input type="file" name="avatar_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:border file:border-[#e5e4dc] file:text-xs file:font-bold file:bg-white file:text-slate-700 hover:file:bg-cream-100">
                </div>
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-semibold">Or Avatar Image URL</label>
                    <input type="text" name="avatar" id="modalAvatar" placeholder="https://images.unsplash.com/..." class="w-full px-3 py-1.5 border border-[#e5e4dc] bg-white text-xs font-mono text-slate-700 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#e5e4dc]">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2 bg-white hover:bg-cream-100 text-slate-700 text-xs font-bold border border-[#e5e4dc] transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-sage-700 hover:bg-sage-800 text-white text-xs font-bold border border-sage-800 transition cursor-pointer" id="modalSubmitBtn">
                    Save Review
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddReviewModal() {
    document.getElementById('modalAction').value = 'add_review';
    document.getElementById('modalReviewId').value = '0';
    document.getElementById('modalReviewTitle').textContent = 'Add New Traveler Review';
    document.getElementById('modalSubmitBtn').textContent = 'Add Review';
    document.getElementById('modalName').value = '';
    document.getElementById('modalCity').value = '';
    document.getElementById('modalTour').value = '';
    document.getElementById('modalCategory').value = 'honeymoon';
    document.getElementById('modalRating').value = '5';
    document.getElementById('modalStatus').value = 'active';
    document.getElementById('modalHeadline').value = '';
    document.getElementById('modalQuote').value = '';
    document.getElementById('modalAvatar').value = '';
    document.getElementById('reviewModal').classList.remove('hidden');
}

function openEditReviewModal(data) {
    document.getElementById('modalAction').value = 'edit_review';
    document.getElementById('modalReviewId').value = data.id;
    document.getElementById('modalReviewTitle').textContent = `Edit Review #${data.id} - ${data.name}`;
    document.getElementById('modalSubmitBtn').textContent = 'Update Review';
    document.getElementById('modalName').value = data.name || '';
    document.getElementById('modalCity').value = data.city || '';
    document.getElementById('modalTour').value = data.tour || '';
    document.getElementById('modalCategory').value = data.category || 'honeymoon';
    document.getElementById('modalRating').value = data.rating || '5';
    document.getElementById('modalStatus').value = data.status || 'active';
    document.getElementById('modalHeadline').value = data.headline || '';
    document.getElementById('modalQuote').value = data.quote || '';
    document.getElementById('modalAvatar').value = data.avatar || '';
    document.getElementById('reviewModal').classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}
</script>

<?php include 'components/footer.php'; ?>
