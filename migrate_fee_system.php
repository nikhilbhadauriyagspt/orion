<?php
require_once __DIR__ . '/db.php';

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Finchskills DB Migration</title><style>body{font-family:sans-serif;padding:30px;background:#f8fafc;color:#1e293b;} .card{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:25px;max-width:700px;margin:0 auto;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);} .success{color:#15803d;font-weight:bold;} .info{color:#0369a1;} .warn{color:#b45309;}</style></head><body><div class="card">';
echo '<h2 style="margin-top:0;color:#fe7c03;">Finchskills Automatic Database Migration</h2>';
echo '<p style="color:#64748b;font-size:14px;">Updating database tables and verifying columns safely...</p><hr style="border:0;border-top:1px solid #e2e8f0;margin:20px 0;">';

try {
    // 1. Check & Create student_installments Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `student_installments` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `student_db_id` INT DEFAULT NULL,
        `student_id` VARCHAR(50) NOT NULL,
        `admission_id` INT NOT NULL,
        `enrollment_no` VARCHAR(50) DEFAULT NULL,
        `course_title` VARCHAR(255) NOT NULL,
        `total_course_fee` DECIMAL(10,2) NOT NULL,
        `discount_amount` DECIMAL(10,2) DEFAULT '0.00',
        `seat_fee_adjusted` DECIMAL(10,2) DEFAULT '0.00',
        `final_payable` DECIMAL(10,2) NOT NULL,
        `payment_type` ENUM('full','installment') NOT NULL DEFAULT 'full',
        `total_parts` INT NOT NULL DEFAULT '1',
        `installment_no` INT NOT NULL DEFAULT '1',
        `installment_title` VARCHAR(100) NOT NULL DEFAULT '1st Installment (Admission Fee)',
        `installment_amount` DECIMAL(10,2) NOT NULL,
        `due_date` DATE DEFAULT NULL,
        `paid_date` DATETIME DEFAULT NULL,
        `status` ENUM('Paid','Pending','Overdue') NOT NULL DEFAULT 'Pending',
        `payment_id` VARCHAR(100) DEFAULT NULL,
        `receipt_no` VARCHAR(50) DEFAULT NULL,
        `reminder_sent` TINYINT(1) DEFAULT '0',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `student_id` (`student_id`),
        KEY `admission_id` (`admission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<p class='success'>&#10004; Table <code>student_installments</code> is ready.</p>";

    // 2. Check & Update admissions table columns
    $admCols = $pdo->query("SHOW COLUMNS FROM `admissions`")->fetchAll(PDO::FETCH_COLUMN);

    $admMigrations = [
        'enrollment_no' => "ALTER TABLE `admissions` ADD COLUMN `enrollment_no` VARCHAR(50) NULL AFTER `student_id`",
        'fee_total' => "ALTER TABLE `admissions` ADD COLUMN `fee_total` DECIMAL(10,2) DEFAULT 0.00 AFTER `status`",
        'seat_fee_adjusted' => "ALTER TABLE `admissions` ADD COLUMN `seat_fee_adjusted` DECIMAL(10,2) DEFAULT 0.00 AFTER `fee_total`",
        'fee_paid' => "ALTER TABLE `admissions` ADD COLUMN `fee_paid` DECIMAL(10,2) DEFAULT 0.00 AFTER `seat_fee_adjusted`",
        'fee_pending' => "ALTER TABLE `admissions` ADD COLUMN `fee_pending` DECIMAL(10,2) DEFAULT 0.00 AFTER `fee_paid`",
        'payment_plan' => "ALTER TABLE `admissions` ADD COLUMN `payment_plan` ENUM('full','installment') DEFAULT NULL AFTER `fee_pending`",
        'payment_status' => "ALTER TABLE `admissions` ADD COLUMN `payment_status` ENUM('Pending Payment','Partial Paid','Fully Paid') DEFAULT 'Pending Payment' AFTER `payment_plan`"
    ];

    foreach ($admMigrations as $col => $sql) {
        if (!in_array($col, $admCols)) {
            $pdo->exec($sql);
            echo "<p class='info'>+ Added column <code>admissions.{$col}</code></p>";
        } else {
            echo "<p class='success'>&#10004; Column <code>admissions.{$col}</code> already exists.</p>";
        }
    }

    // 3. Check & Update students table columns
    $stuCols = $pdo->query("SHOW COLUMNS FROM `students`")->fetchAll(PDO::FETCH_COLUMN);

    $stuMigrations = [
        'booked_course_id' => "ALTER TABLE `students` ADD COLUMN `booked_course_id` INT NULL AFTER `seat_status`",
        'booked_course' => "ALTER TABLE `students` ADD COLUMN `booked_course` VARCHAR(255) NULL AFTER `booked_course_id`",
        'seat_valid_until' => "ALTER TABLE `students` ADD COLUMN `seat_valid_until` DATETIME NULL AFTER `payment_date`",
        'seat_reminder_day2_at' => "ALTER TABLE `students` ADD COLUMN `seat_reminder_day2_at` DATETIME NULL AFTER `seat_valid_until`",
        'seat_reminder_day4_at' => "ALTER TABLE `students` ADD COLUMN `seat_reminder_day4_at` DATETIME NULL AFTER `seat_reminder_day2_at`",
        'seat_reminder_final_at' => "ALTER TABLE `students` ADD COLUMN `seat_reminder_final_at` DATETIME NULL AFTER `seat_reminder_day4_at`",
        'seat_cancelled_at' => "ALTER TABLE `students` ADD COLUMN `seat_cancelled_at` DATETIME NULL AFTER `seat_reminder_final_at`",
        'seat_reactivated_at' => "ALTER TABLE `students` ADD COLUMN `seat_reactivated_at` DATETIME NULL AFTER `seat_cancelled_at`",
        'seat_reactivated_by' => "ALTER TABLE `students` ADD COLUMN `seat_reactivated_by` VARCHAR(100) NULL AFTER `seat_reactivated_at`",
        'admission_id' => "ALTER TABLE `students` ADD COLUMN `admission_id` INT NULL AFTER `seat_reactivated_by`"
    ];

    foreach ($stuMigrations as $col => $sql) {
        if (!in_array($col, $stuCols)) {
            $pdo->exec($sql);
            echo "<p class='info'>+ Added column <code>students.{$col}</code></p>";
        } else {
            echo "<p class='success'>&#10004; Column <code>students.{$col}</code> already exists.</p>";
        }
    }

    // 4. Ensure site settings exist
    $checkSetting = $pdo->prepare("SELECT COUNT(*) FROM `site_settings` WHERE `setting_key` = ?");
    
    $checkSetting->execute(['seat_booking_fee']);
    if ($checkSetting->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('seat_booking_fee', '999')");
        echo "<p class='info'>+ Added site setting <code>seat_booking_fee</code> (default: 999)</p>";
    }

    $checkSetting->execute(['seat_validity_days']);
    if ($checkSetting->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES ('seat_validity_days', '7')");
        echo "<p class='info'>+ Added site setting <code>seat_validity_days</code> (default: 7)</p>";
    }

    // 5. Cleanup corrupted seat payment amounts if any (> 5000)
    $cleanCount = $pdo->exec("UPDATE students SET payment_amount = 999.00 WHERE payment_amount > 5000");
    if ($cleanCount > 0) {
        echo "<p class='warn'>&#9888; Fixed {$cleanCount} candidate record(s) where seat payment amount was corrupted (> 5000).</p>";
    }

    echo '<hr style="border:0;border-top:1px solid #e2e8f0;margin:20px 0;">';
    echo '<h3 style="color:#15803d;margin-bottom:5px;">&#10004; All Database Migrations Completed Successfully!</h3>';
    echo '<p style="color:#64748b;font-size:13px;">Security recommendation: You may delete or rename this file (<code>migrate_fee_system.php</code>) now.</p>';

} catch (Exception $e) {
    echo '<p style="color:#b91c1c;font-weight:bold;">Migration Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div></body></html>';
