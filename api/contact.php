<?php
/**
 * Contact & Support Inquiry API Controller
 * Handles traveler inquiries, sends email notification to admin & confirmation to traveler.
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'General Inquiry';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($fullname) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields (Name, Email, and Message).']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit();
}

$siteName = getSetting('site_name', 'Orion Advent');
$adminEmail = getSetting('site_email', 'support@orionadvent.com');
$ticketId = 'OA-TKT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// 1. Send Confirmation Email to Traveler
$userEmailHtml = '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background-color:#f6f5ef;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f5ef;padding:30px 15px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border:1px solid #e5e4dc;max-width:600px;width:100%;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#068285;padding:24px 30px;color:#ffffff;">
                            <h1 style="margin:0;font-size:22px;font-weight:800;">' . htmlspecialchars($siteName) . ' Concierge Desk</h1>
                            <p style="margin:4px 0 0 0;font-size:13px;color:#d0f2f3;">We received your support inquiry • Reference ' . $ticketId . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;">
                            <p style="font-size:15px;color:#1e293b;margin:0 0 16px 0;">Dear <strong>' . htmlspecialchars($fullname) . '</strong>,</p>
                            <p style="font-size:13px;color:#475569;line-height:1.6;margin:0 0 20px 0;">
                                Thank you for contacting ' . htmlspecialchars($siteName) . '. Our dedicated destination concierge team has received your message and will review your request shortly (typically within 1 to 2 business hours).
                            </p>
                            <div style="background-color:#fbfbf8;border:1px solid #e5e4dc;padding:16px;border-radius:8px;margin-bottom:20px;">
                                <p style="margin:0 0 8px 0;font-size:12px;color:#64748b;text-transform:uppercase;font-weight:700;">Inquiry Topic: <strong style="color:#0f172a;">' . htmlspecialchars($subject) . '</strong></p>
                                <p style="margin:0;font-size:13px;color:#334155;line-height:1.5;">"' . nl2br(htmlspecialchars($message)) . '"</p>
                            </div>
                            <p style="font-size:13px;color:#475569;line-height:1.6;margin:0;">
                                For immediate assistance or urgent trip adjustments, you can also connect with us on WhatsApp or call our 24x7 helpdesk.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f1f5f9;padding:16px 30px;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;text-align:center;">
                            &copy; ' . date('Y') . ' ' . htmlspecialchars($siteName) . ' Concierge Services. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

@sendPortalMail($email, $fullname, "We received your inquiry [{$ticketId}] - {$siteName}", $userEmailHtml);

// 2. Send Notification Email to Admin
$adminEmailHtml = '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:sans-serif;padding:20px;background:#f8fafc;color:#1e293b;">
    <div style="max-width:600px;margin:0 auto;background:#fff;padding:24px;border:1px solid #e2e8f0;border-radius:8px;">
        <h2 style="color:#068285;margin-top:0;">New Help & Contact Message Received</h2>
        <p><strong>Ticket ID:</strong> ' . $ticketId . '</p>
        <p><strong>Name:</strong> ' . htmlspecialchars($fullname) . '</p>
        <p><strong>Email:</strong> <a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>
        <p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>
        <p><strong>Message:</strong></p>
        <div style="background:#f1f5f9;padding:12px;border-left:4px solid #068285;">' . nl2br(htmlspecialchars($message)) . '</div>
        <p style="margin-top:20px;font-size:12px;color:#64748b;">Received on ' . date('d M Y, h:i A') . '</p>
    </div>
</body>
</html>';

@sendPortalMail($adminEmail, "Admin", "New Inquiry [{$ticketId}] from {$fullname} - {$subject}", $adminEmailHtml);

// Trigger Real-Time Admin Notification
require_once __DIR__ . '/../includes/notifications.php';
createAdminNotification(
    'contact_inquiry',
    'New Support / Contact Inquiry',
    $fullname . ' (' . $email . ') sent an inquiry: "' . $subject . '" [Ticket #' . $ticketId . ']',
    'contact.php',
    ['ticket_id' => $ticketId, 'name' => $fullname, 'email' => $email, 'subject' => $subject]
);

echo json_encode([
    'success' => true,
    'ticket_id' => $ticketId,
    'message' => "Thank you, {$fullname}! Your request #{$ticketId} has been submitted. Our concierge will contact you shortly."
]);
