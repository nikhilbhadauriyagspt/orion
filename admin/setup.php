<?php
/**
 * Automated Database Installer & Setup
 * Kisi bhi server par sirf is file ko browser me run karein ya CLI se: php admin/setup.php
 */

require_once __DIR__ . '/../config/db.php';

$results = [];
$status = 'pending';
$errorMessage = '';

// Run migration logic
try {
    // 1. Connect to MySQL Server (without selecting DB first)
    $serverConn = getServerConnection();
    if (!$serverConn) {
        throw new Exception("MySQL Server se connect nahi ho paya. Please config/db.php me Host, User, ya Password check karein.");
    }
    $results[] = ["step" => "MySQL Server Connection", "status" => "success", "msg" => "Connected to MySQL host: " . DB_HOST];

    // 2. Create Database if not exists
    $dbName = DB_NAME;
    $serverConn->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $results[] = ["step" => "Database Creation", "status" => "success", "msg" => "Database `$dbName` ready"];

    // 3. Connect to created Database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 4. Create Admins Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('superadmin', 'admin', 'editor') DEFAULT 'admin',
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $results[] = ["step" => "Admins Table", "status" => "success", "msg" => "Table `admins` created successfully"];

    // 5. Create Tour Packages Table (Full Rich Metadata)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `packages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `subtitle` TEXT NULL,
            `location` VARCHAR(150) NOT NULL,
            `state_country` VARCHAR(150) NULL,
            `circuit_type` VARCHAR(100) DEFAULT 'Holiday Circuit',
            `category` ENUM('domestic', 'international', 'adventure', 'luxury', 'honeymoon') DEFAULT 'domestic',
            `duration_nights` INT DEFAULT 4,
            `duration_days` INT DEFAULT 5,
            `duration_text` VARCHAR(50) DEFAULT '4 Nights / 5 Days',
            `price` DECIMAL(10,2) NOT NULL,
            `original_price` DECIMAL(10,2) NULL,
            `token_advance` DECIMAL(10,2) DEFAULT 2500.00,
            `badge` VARCHAR(50) DEFAULT 'Bestseller',
            `rating` DECIMAL(2,1) DEFAULT 4.9,
            `reviews_count` INT DEFAULT 450,
            `featured_image` VARCHAR(255) NULL,
            `gallery` LONGTEXT NULL,
            `highlights` LONGTEXT NULL,
            `itinerary` LONGTEXT NULL,
            `inclusions` LONGTEXT NULL,
            `exclusions` LONGTEXT NULL,
            `hotel_ids` LONGTEXT NULL,
            `custom_stays` LONGTEXT NULL,
            `policies` LONGTEXT NULL,
            `status` ENUM('active', 'draft') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $results[] = ["step" => "Packages Table", "status" => "success", "msg" => "Table `packages` rich schema ready"];

    // 6. Create Bookings Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `bookings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_code` VARCHAR(50) NOT NULL UNIQUE,
            `customer_name` VARCHAR(100) NOT NULL,
            `customer_email` VARCHAR(150) NOT NULL,
            `customer_phone` VARCHAR(30) NOT NULL,
            `package_title` VARCHAR(255) NOT NULL,
            `travel_date` DATE NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `status` ENUM('confirmed', 'pending', 'completed', 'cancelled') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $results[] = ["step" => "Bookings Table", "status" => "success", "msg" => "Table `bookings` created successfully"];

    // 6.0 Create Hotels, Hotel Rooms & Gallery Tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `hotels` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `star_rating` TINYINT DEFAULT 4,
            `property_type` VARCHAR(100) DEFAULT 'Luxury Resort',
            `city` VARCHAR(100) NOT NULL,
            `state` VARCHAR(100) NULL,
            `country` VARCHAR(100) DEFAULT 'India',
            `address` TEXT NOT NULL,
            `latitude` DECIMAL(10,8) NULL,
            `longitude` DECIMAL(11,8) NULL,
            `featured_image` VARCHAR(255) NULL,
            `starting_price` DECIMAL(10,2) NOT NULL,
            `original_price` DECIMAL(10,2) NULL,
            `discount_percent` INT DEFAULT 0,
            `amenities` TEXT NULL,
            `description` TEXT NULL,
            `policies` TEXT NULL,
            `checkin_time` VARCHAR(20) DEFAULT '02:00 PM',
            `checkout_time` VARCHAR(20) DEFAULT '11:00 AM',
            `badge` VARCHAR(50) DEFAULT 'Popular',
            `status` ENUM('active', 'draft') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `hotel_rooms` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `hotel_id` INT NOT NULL,
            `room_name` VARCHAR(150) NOT NULL,
            `room_type` VARCHAR(100) DEFAULT 'Deluxe Room',
            `price_per_night` DECIMAL(10,2) NOT NULL,
            `original_price` DECIMAL(10,2) NULL,
            `max_adults` INT DEFAULT 2,
            `max_children` INT DEFAULT 1,
            `bed_type` VARCHAR(100) DEFAULT '1 King Bed',
            `room_size` VARCHAR(50) DEFAULT '350 sq.ft',
            `meal_plan` VARCHAR(100) DEFAULT 'Free Breakfast',
            `amenities` TEXT NULL,
            `status` ENUM('available', 'sold_out') DEFAULT 'available',
            FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `hotel_images` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `hotel_id` INT NOT NULL,
            `image_url` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(150) NULL,
            `sort_order` INT DEFAULT 0,
            FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $results[] = ["step" => "Hotels Tables", "status" => "success", "msg" => "Tables `hotels`, `hotel_rooms`, `hotel_images` created successfully"];

    // 6.1 Create Users (Travelers) Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `phone` VARCHAR(30) NULL,
            `password` VARCHAR(255) NULL,
            `avatar` VARCHAR(255) NULL,
            `oauth_provider` VARCHAR(50) DEFAULT 'email',
            `oauth_uid` VARCHAR(255) NULL,
            `is_verified` TINYINT(1) DEFAULT 0,
            `otp_code` VARCHAR(10) NULL,
            `otp_expires_at` DATETIME NULL,
            `status` ENUM('active', 'blocked') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `last_login` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Add oauth columns if existing table
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `oauth_provider` VARCHAR(50) DEFAULT 'email' AFTER `avatar`");
    } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `oauth_uid` VARCHAR(255) NULL AFTER `oauth_provider`");
    } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL");
    } catch (Exception $e) {}

    $results[] = ["step" => "Users Table", "status" => "success", "msg" => "Table `users` created / updated successfully"];

    // 6.2 Create Settings Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $results[] = ["step" => "Settings Table", "status" => "success", "msg" => "Table `settings` created successfully"];

    // Seed default settings if empty
    $checkSettings = $pdo->query("SELECT COUNT(*) as count FROM `settings`")->fetch();
    if ($checkSettings['count'] == 0) {
        $defaultConfig = [
            'site_name' => 'Orion Advent',
            'site_tagline' => 'Your Passport to Adventure',
            'site_phone' => '+91 98765 43210',
            'site_email' => 'support@orionadvent.com',
            'site_whatsapp' => '919876543210',
            'site_address' => 'DLF Cyber City, Tower B, Gurugram, Haryana - 122002',
            'site_logo' => 'assets/images/logo/logo.webp',
            'mail_mode' => 'testing',
            'testing_otp' => '123456',
            'smtp_host' => 'mail.orionadvent.com',
            'smtp_port' => '465',
            'smtp_user' => 'support@orionadvent.com',
            'smtp_pass' => '',
            'smtp_encryption' => 'ssl',
            'smtp_from_name' => 'Orion Advent Travel',
            'smtp_from_email' => 'support@orionadvent.com',
            'google_login_active' => '0',
            'google_client_id' => '',
            'google_client_secret' => '',
            'facebook_login_active' => '0',
            'facebook_app_id' => '',
            'facebook_app_secret' => '',
            'social_facebook' => 'https://facebook.com',
            'social_instagram' => 'https://instagram.com',
            'social_twitter' => 'https://twitter.com',
            'social_youtube' => 'https://youtube.com'
        ];
        $insertSetting = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");
        foreach ($defaultConfig as $key => $val) {
            $insertSetting->execute([$key, $val]);
        }
        $results[] = ["step" => "Default Settings", "status" => "success", "msg" => "Default site branding, SMTP & Social Login configuration seeded"];
    }

    // 6.3 Seed Sample Verified Traveler Users if empty
    $checkUsers = $pdo->query("SELECT COUNT(*) as count FROM `users`")->fetch();
    if ($checkUsers['count'] == 0) {
        $samplePass = password_hash('user123', PASSWORD_BCRYPT);
        $insertUser = $pdo->prepare("
            INSERT INTO `users` (`name`, `email`, `phone`, `password`, `is_verified`, `status`, `created_at`, `last_login`) VALUES
            (?, ?, ?, ?, 1, 'active', NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 2 HOUR),
            (?, ?, ?, ?, 1, 'active', NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 1 DAY),
            (?, ?, ?, ?, 0, 'active', NOW() - INTERVAL 1 HOUR, NULL)
        ");
        $insertUser->execute([
            'Rohan Verma', 'rohan.v@example.com', '+91 9876500111', $samplePass,
            'Priya Patel', 'priya.p@example.com', '+91 9811223344', $samplePass,
            'Amitabh Singh', 'amitabh.s@example.com', '+91 9988776655', $samplePass
        ]);
        $results[] = ["step" => "Sample Users", "status" => "success", "msg" => "Seeded 3 sample travelers for testing"];
    }

    // 7. Seed Default Admin User (admin@orionadvent.com / admin123)
    $checkAdmin = $pdo->prepare("SELECT id FROM `admins` WHERE `email` = ?");
    $checkAdmin->execute(['admin@orionadvent.com']);
    if ($checkAdmin->rowCount() === 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $insertAdmin = $pdo->prepare("
            INSERT INTO `admins` (`name`, `email`, `password`, `role`, `status`) 
            VALUES (?, ?, ?, 'superadmin', 'active')
        ");
        $insertAdmin->execute(['Admin Manager', 'admin@orionadvent.com', $hashedPassword]);
        $results[] = ["step" => "Default Admin Account", "status" => "success", "msg" => "Created superadmin: admin@orionadvent.com / admin123"];
    } else {
        $results[] = ["step" => "Default Admin Account", "status" => "success", "msg" => "Admin user `admin@orionadvent.com` already exists"];
    }

    // 8. Seed Sample Bookings if empty
    $checkBookings = $pdo->query("SELECT COUNT(*) as count FROM `bookings`")->fetch();
    if ($checkBookings['count'] == 0) {
        $pdo->exec("
            INSERT INTO `bookings` (`booking_code`, `customer_name`, `customer_email`, `customer_phone`, `package_title`, `travel_date`, `amount`, `status`) VALUES
            ('GF-1001', 'Arjun Mehta', 'arjun.m@example.com', '+91 9876543210', 'Dubai Desert Safari & Burj Khalifa Experience', '2026-10-18', 1450.00, 'confirmed'),
            ('GF-1002', 'Sophia Martinez', 'sophia.m@gmail.com', '+1 555-0199', 'Bali Luxury Villa 7D/6N Tropical Getaway', '2026-11-02', 2100.00, 'pending'),
            ('GF-1003', 'David Wilson', 'david.w@corp.org', '+44 20 7946 0912', 'Swiss Alps Ski & Scenic Train Tour', '2026-12-15', 3850.00, 'confirmed'),
            ('GF-1004', 'Ananya Verma', 'ananya.v@yahoo.com', '+91 9123456780', 'Goa Beachside Sunset Resort & Watersports', '2026-10-25', 780.00, 'completed');
        ");
        $results[] = ["step" => "Sample Bookings Data", "status" => "success", "msg" => "Seeded 4 initial live bookings"];
    }

    // 9. Create Reviews / Testimonials Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `reviews` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `city` VARCHAR(150) NULL,
            `avatar` VARCHAR(255) NULL,
            `category` VARCHAR(100) DEFAULT 'honeymoon',
            `tour` VARCHAR(200) NOT NULL,
            `headline` VARCHAR(255) NOT NULL,
            `quote` TEXT NOT NULL,
            `rating` TINYINT DEFAULT 5,
            `status` ENUM('active', 'hidden') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $results[] = ["step" => "Reviews Table", "status" => "success", "msg" => "Table `reviews` created successfully"];

    // Seed Sample Reviews if empty
    $checkReviews = $pdo->query("SELECT COUNT(*) as count FROM `reviews`")->fetch();
    if ($checkReviews['count'] == 0) {
        $stmtRev = $pdo->prepare("
            INSERT INTO `reviews` (`name`, `city`, `avatar`, `category`, `tour`, `headline`, `quote`, `rating`, `status`) VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, 'active'),
            (?, ?, ?, ?, ?, ?, ?, ?, 'active'),
            (?, ?, ?, ?, ?, ?, ?, ?, 'active'),
            (?, ?, ?, ?, ?, ?, ?, ?, 'active'),
            (?, ?, ?, ?, ?, ?, ?, ?, 'active'),
            (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmtRev->execute([
            'Sneha & Rohan Sharma', 'New Delhi, India', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80', 'honeymoon', 'Maldives 5N/6D Villa', 'The best honeymoon we could have dreamed of!', 'From the private pool villa upgrade to the seamless speedboat transfers in Male, everything was effortless. Our coordinator was on WhatsApp 24/7. Truly a 5-star experience from start to finish.', 5,
            'Aditya & Varun Rao', 'Bangalore, India', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80', 'adventure', 'Ladakh 7N/8D Biking', 'Flawless Enfield bikes and heated Pangong camps!', 'Crossing Khardung La at 18,000 ft was incredible. The backup vehicle with oxygen kits and dedicated mechanic support gave us complete peace of mind through rugged passes.', 5,
            'Dr. Rajesh & Sunita Mehra', 'Mumbai, India', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80', 'family', 'Dubai 4N/5D Atlantis', 'Traveling with elderly parents was 100% stress-free!', 'Our private chauffeur was punctual, polite, and very helpful. Fast-track tickets to Burj Khalifa and Aquaventure saved us from standing in long queues with the kids.', 5,
            'Vikram & Ananya Sen', 'Kolkata, India', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80', 'honeymoon', 'Kashmir 5N/6D Gulmarg', 'Waking up on a Dal Lake houseboat was pure magic.', 'Our heritage cedarwood houseboat was spotless. Orion arranged Phase 2 Gulmarg Gondola passes when they were completely sold out everywhere else. Unforgettable trip!', 5,
            'Kavita & Arvind Singhania', 'Ahmedabad, India', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=200&q=80', 'luxury', 'Rajasthan 5N/6D Palaces', 'Treated like royalty from airport pickup to checkout.', 'The private boat entry at Lake Pichola Udaipur and vintage car ride in Jaipur made our anniversary feel truly majestic. Transparent billing with zero hidden fees.', 5,
            'Pooja & Sameer Deshmukh', 'Pune, India', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=200&q=80', 'family', 'Kerala 4N/5D Alleppey', 'The private chef on our backwater houseboat was outstanding.', 'Cruising along the silent palm backwaters while the chef prepared fresh local river fish was the highlight of our vacation. The kids loved every moment!', 5
        ]);
        $results[] = ["step" => "Sample Reviews Data", "status" => "success", "msg" => "Seeded 6 initial verified traveler testimonials"];
    }

    // 10. Create Notifications Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notifications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `type` VARCHAR(50) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `link` VARCHAR(255) NULL,
            `meta_data` LONGTEXT NULL,
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $results[] = ["step" => "Notifications Table", "status" => "success", "msg" => "Table `notifications` created successfully"];

    // Seed Sample Notifications if empty
    $checkNotifs = $pdo->query("SELECT COUNT(*) as count FROM `notifications`")->fetch();
    if ($checkNotifs['count'] == 0) {
        $stmtNotif = $pdo->prepare("
            INSERT INTO `notifications` (`type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
            ('booking', 'New Tour Booking & Payment', 'Arjun Mehta booked \"Dubai Desert Safari\" - Advance token ₹2,500 received via Razorpay.', 'bookings.php?search=GF-1001', 0, NOW() - INTERVAL 15 MINUTE),
            ('flight_inquiry', 'New Flight Inquiry', 'Rohan Verma submitted inquiry for DEL → BOM (Air India AI-101) - Est. ₹4,500.', 'bookings.php?filter=flight', 0, NOW() - INTERVAL 45 MINUTE),
            ('user_login', 'Traveler Logged In', 'Priya Patel (priya.p@example.com) logged into the portal.', 'users.php', 1, NOW() - INTERVAL 2 HOUR)
        ");
        $stmtNotif->execute();
        $results[] = ["step" => "Sample Notifications", "status" => "success", "msg" => "Seeded initial live notifications"];
    }

    $status = 'success';

} catch (Exception $e) {
    $status = 'error';
    $errorMessage = $e->getMessage();
}

// If running via CLI, print text output
if (php_sapi_name() === 'cli') {
    echo "\n=== Orion Advent Database Setup ===\n";
    foreach ($results as $res) {
        echo "[✓] " . $res['step'] . ": " . $res['msg'] . "\n";
    }
    if ($status === 'error') {
        echo "[✗] ERROR: " . $errorMessage . "\n";
    } else {
        echo "\n🎉 Database Setup Completed Successfully!\nAdmin Login: admin@orionadvent.com / admin123\n\n";
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup & Migration - Orion Advent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-['Plus_Jakarta_Sans'] text-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg bg-white rounded-2xl p-6 sm:p-8 border border-slate-200">
        
        <!-- Header -->
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-base">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 font-['Space_Grotesk']">Database Installer</h1>
                <p class="text-xs text-slate-400">1-Click Auto Tables & Data Migration</p>
            </div>
        </div>

        <?php if ($status === 'success'): ?>
            <!-- Success Notification -->
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-3 text-emerald-800">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                <div class="text-xs">
                    <span class="font-bold block">Database Configured!</span>
                    <span class="text-emerald-700">All tables and admin credentials are ready to use.</span>
                </div>
            </div>

            <!-- Steps Summary -->
            <div class="space-y-2 mb-6 bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Executed Steps</div>
                <?php foreach ($results as $res): ?>
                    <div class="flex items-start gap-2 text-xs text-slate-700">
                        <i class="fa-solid fa-check text-emerald-500 mt-0.5 text-[11px]"></i>
                        <div>
                            <span class="font-semibold"><?php echo htmlspecialchars($res['step']); ?>:</span>
                            <span class="text-slate-500 ml-1"><?php echo htmlspecialchars($res['msg']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Default Credentials Card -->
            <div class="mb-6 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                <span class="font-semibold text-slate-800 block mb-1">Default Login:</span>
                <div class="flex justify-between items-center text-slate-600">
                    <span>admin@orionadvent.com / admin123</span>
                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Ready</span>
                </div>
            </div>

            <!-- Action Button -->
            <a href="login.php" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs rounded-xl transition-colors flex items-center justify-center gap-2">
                <span>Go to Admin Login</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>

        <?php else: ?>
            <!-- Error State -->
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-2.5 text-rose-800">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-base mt-0.5 shrink-0"></i>
                <div class="text-xs">
                    <span class="font-bold block">Setup Failed</span>
                    <span class="text-rose-700"><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
            </div>

            <div class="text-xs text-slate-500 mb-5 bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                <p class="font-semibold text-slate-700 mb-1">Troubleshooting:</p>
                <ol class="list-decimal pl-4 space-y-1 text-slate-600">
                    <li>Make sure your MySQL server is running.</li>
                    <li>Check <code class="bg-slate-200 px-1 py-0.5 rounded text-slate-800">config/db.php</code> settings.</li>
                </ol>
            </div>

            <a href="setup.php" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition-colors flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-rotate-right text-[11px]"></i>
                <span>Retry Setup</span>
            </a>
        <?php endif; ?>

    </div>

</body>
</html>
