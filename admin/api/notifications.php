<?php
/**
 * Admin Real-Time Notifications Polling & Management API
 * Orion Advent Travel Portal
 */
header('Content-Type: application/json; charset=UTF-8');
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/notifications.php';

// Ensure Admin Auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : 'poll');

if ($action === 'poll' || $action === 'list') {
    $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
    $limit = isset($_GET['limit']) ? min(30, max(1, (int)$_GET['limit'])) : 10;

    $data = getAdminNotifications($limit, $sinceId);
    
    // Also fetch the maximum notification ID currently in database
    $pdo = getDBConnection();
    $maxId = 0;
    if ($pdo) {
        $maxId = (int)$pdo->query("SELECT COALESCE(MAX(id), 0) FROM `notifications`")->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'unread_count' => $data['unread_count'],
        'items' => $data['items'],
        'latest_id' => $maxId,
        'server_time' => date('Y-m-d H:i:s')
    ]);
    exit();

} elseif ($action === 'mark_read') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);
    $res = markNotificationsRead($id);
    echo json_encode(['success' => (bool)$res]);
    exit();

} elseif ($action === 'mark_all_read') {
    $res = markNotificationsRead(null);
    echo json_encode(['success' => (bool)$res]);
    exit();

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit();
}
