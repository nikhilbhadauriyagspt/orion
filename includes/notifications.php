<?php
/**
 * Orion Advent Admin Notifications Helper
 * Handles recording and fetching live notifications for:
 * - New Bookings & Token Advance Payments
 * - New Flight Inquiries
 * - New User Registrations & Logins
 * - New Contact Queries
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Ensures notifications table exists
 */
function ensureNotificationsTable($pdo = null) {
    if (!$pdo) {
        $pdo = getDBConnection();
    }
    if (!$pdo) return false;

    try {
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
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Record a new admin notification
 *
 * @param string $type ('booking', 'payment', 'flight_inquiry', 'user_login', 'user_register', 'contact_inquiry')
 * @param string $title Short title for toast and dropdown
 * @param string $message Detailed descriptive message
 * @param string $link Direct target URL in admin panel
 * @param array $meta Optional metadata array
 * @return bool
 */
function createAdminNotification($type, $title, $message, $link = '', $meta = []) {
    $pdo = getDBConnection();
    if (!$pdo) return false;

    ensureNotificationsTable($pdo);

    try {
        $metaJson = !empty($meta) ? json_encode($meta) : null;
        $stmt = $pdo->prepare("
            INSERT INTO `notifications` (`type`, `title`, `message`, `link`, `meta_data`, `is_read`, `created_at`)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        return $stmt->execute([$type, $title, $message, $link, $metaJson]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get recent unread / latest notifications
 *
 * @param int $limit Max items to return
 * @param int $sinceId Optional ID threshold to only return newer items
 * @return array
 */
function getAdminNotifications($limit = 15, $sinceId = 0) {
    $pdo = getDBConnection();
    if (!$pdo) return ['unread_count' => 0, 'items' => []];

    ensureNotificationsTable($pdo);

    try {
        $unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM `notifications` WHERE `is_read` = 0")->fetchColumn();

        if ($sinceId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM `notifications` WHERE `id` > ? ORDER BY `id` DESC LIMIT ?");
            $stmt->bindValue(1, (int)$sinceId, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `notifications` ORDER BY `id` DESC LIMIT ?");
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();
        }

        return [
            'unread_count' => $unreadCount,
            'items' => $items
        ];
    } catch (Exception $e) {
        return ['unread_count' => 0, 'items' => []];
    }
}

/**
 * Mark notifications as read
 */
function markNotificationsRead($id = null) {
    $pdo = getDBConnection();
    if (!$pdo) return false;

    ensureNotificationsTable($pdo);

    try {
        if ($id && is_numeric($id)) {
            $stmt = $pdo->prepare("UPDATE `notifications` SET `is_read` = 1 WHERE `id` = ?");
            return $stmt->execute([(int)$id]);
        } else {
            return $pdo->exec("UPDATE `notifications` SET `is_read` = 1") !== false;
        }
    } catch (Exception $e) {
        return false;
    }
}
