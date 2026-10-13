<?php
/**
 * Orion Advent Email Sender Engine using Official PHPMailer Library
 * Compatible with cPanel Webmail, Hostinger, Gmail & Custom SMTP servers
 */
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send Email via PHPMailer
 */
function sendPortalMail($toEmail, $toName, $subject, $htmlContent, $altBody = '') {
    $settings = getGlobalSettings();
    $mailMode = isset($settings['mail_mode']) ? $settings['mail_mode'] : 'testing';

    // In Testing Mode, return instant simulated success
    if ($mailMode === 'testing') {
        return [
            'success' => true,
            'mode' => 'testing',
            'testing_otp' => isset($settings['testing_otp']) ? $settings['testing_otp'] : '123456',
            'message' => 'Testing mode is ACTIVE. Use OTP: ' . (isset($settings['testing_otp']) ? $settings['testing_otp'] : '123456')
        ];
    }

    // LIVE PHPMailer Mode
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $settings['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['smtp_user'];
        $mail->Password   = $settings['smtp_pass'];
        
        $port = (int)$settings['smtp_port'];
        $mail->Port = $port;

        $encryption = strtolower($settings['smtp_encryption']);
        if ($encryption === 'ssl' || $port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls' || $port === 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        // SSL Certificate options (prevents self-signed or shared cPanel cert rejection)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->Timeout = 15;
        $mail->CharSet = 'UTF-8';

        // Sender & Recipient
        $fromEmail = !empty($settings['smtp_from_email']) ? $settings['smtp_from_email'] : $settings['smtp_user'];
        $fromName = !empty($settings['smtp_from_name']) ? $settings['smtp_from_name'] : $settings['site_name'];

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo($fromEmail, $fromName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlContent;
        $mail->AltBody = !empty($altBody) ? $altBody : strip_tags($htmlContent);

        $mail->send();

        return [
            'success' => true,
            'mode' => 'live',
            'message' => 'Email sent successfully via PHPMailer!'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'mode' => 'live',
            'message' => 'PHPMailer Error: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Generate a luxury styled HTML email template for OTP
 */
function getOtpEmailTemplate($userName, $otpCode, $purpose = 'signup') {
    $siteName = getSetting('site_name', 'Orion Advent');
    $siteEmail = getSetting('site_email', 'support@orionadvent.com');
    $sitePhone = getSetting('site_phone', '+91 98765 43210');
    
    $title = $purpose === 'forgot' ? 'Password Reset Verification' : 'Verify Your Email Address';
    $message = $purpose === 'forgot' 
        ? 'We received a request to reset your ' . htmlspecialchars($siteName) . ' travel account password. Use the verification code below to set a new password.'
        : 'Welcome to ' . htmlspecialchars($siteName) . '! Please confirm your email address to activate your travel account and claim your ₹1,500 welcome discount.';

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #1e293b; }
            .card { max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
            .header { background: linear-gradient(135deg, #068285 0%, #071f25 100%); padding: 32px 24px; text-align: center; color: #ffffff; }
            .content { padding: 32px 28px; text-align: center; }
            .otp-box { background: #f0fdfa; border: 2px dashed #0d9488; border-radius: 16px; padding: 18px 24px; display: inline-block; margin: 24px 0; }
            .otp-code { font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #068285; font-family: monospace; }
            .footer { background: #f8fafc; padding: 20px 24px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #f1f5f9; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="header">
                <h1 style="margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">' . htmlspecialchars($siteName) . '</h1>
                <p style="margin: 6px 0 0 0; font-size: 13px; opacity: 0.85;">' . $title . '</p>
            </div>
            <div class="content">
                <h2 style="font-size: 18px; margin: 0 0 10px 0; color: #0f172a;">Hi ' . htmlspecialchars($userName) . ',</h2>
                <p style="font-size: 13px; color: #475569; line-height: 1.6; margin: 0;">' . $message . '</p>
                <div class="otp-box">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #0f766e; margin-bottom: 4px;">One-Time Verification Code</div>
                    <div class="otp-code">' . $otpCode . '</div>
                </div>
                <p style="font-size: 12px; color: #94a3b8; margin: 0;">This OTP is valid for <strong>10 minutes</strong>. Do not share it with anyone.</p>
            </div>
            <div class="footer">
                <p style="margin: 0 0 6px 0;">Need help? Contact our 24x7 Travel Concierge at <a href="mailto:' . htmlspecialchars($siteEmail) . '" style="color: #068285; text-decoration: none; font-weight: bold;">' . htmlspecialchars($siteEmail) . '</a> or call ' . htmlspecialchars($sitePhone) . '</p>
                <p style="margin: 0;">&copy; ' . date('Y') . ' ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Generate a luxury HTML email confirmation ticket for Tour & Hotel Bookings
 */
function generateBookingConfirmationEmailHtml(array $b) {
    $siteName = getSetting('site_name', 'Orion Advent');
    $siteEmail = getSetting('site_email', 'support@orionadvent.com');
    $sitePhone = getSetting('site_phone', '+91 98765 43210');
    $siteWhatsapp = getSetting('site_whatsapp', '919876543210');

    $bookingCode = htmlspecialchars($b['booking_code'] ?? 'BK-10001');
    $customerName = htmlspecialchars($b['customer_name'] ?? 'Traveler');
    $itemTitle = htmlspecialchars($b['package_title'] ?? 'Holiday Tour');
    $travelDate = !empty($b['travel_date']) ? date('l, d F Y', strtotime($b['travel_date'])) : 'Flexible Date';
    $totalAmount = number_format((float)($b['amount'] ?? 0));
    $advanceAmount = number_format((float)($b['advance_amount'] ?? 0));
    $balanceAmount = number_format((float)($b['balance_amount'] ?? 0));
    $paymentId = htmlspecialchars($b['payment_id'] ?? 'ONLINE_VERIFIED');
    $typeLabel = ($b['booking_type'] ?? 'package') === 'hotel' ? 'Hotel & Resort Stay' : 'Holiday Tour Package';

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Confirmation - ' . $bookingCode . '</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f6f5ef; margin: 0; padding: 24px 12px; color: #1e293b; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e4dc; border-radius: 12px; overflow: hidden; }
            .header-strip { background: #3a5c3d; color: #ffffff; padding: 28px 24px; text-align: center; }
            .badge-code { display: inline-block; background: #ffffff; color: #283d2a; padding: 6px 14px; font-family: monospace; font-size: 14px; font-weight: bold; border-radius: 4px; margin-top: 12px; letter-spacing: 1px; }
            .body-content { padding: 28px 24px; }
            .trip-card { background: #fbfbf8; border: 1px solid #e5e4dc; padding: 18px; border-radius: 8px; margin: 20px 0; }
            .data-grid { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
            .data-grid td { padding: 8px 0; border-bottom: 1px solid #f1f0e9; vertical-align: top; }
            .label { color: #64748b; font-weight: 500; }
            .val { color: #0f172a; font-weight: 700; text-align: right; }
            .price-table { width: 100%; background: #f4f7f4; border: 1px solid #cfe0cf; border-radius: 8px; padding: 16px; margin: 20px 0; }
            .btn-cta { display: inline-block; background: #3a5c3d; color: #ffffff !important; padding: 12px 24px; font-weight: bold; font-size: 13px; text-decoration: none; border-radius: 6px; margin-top: 10px; }
            .footer { background: #fbfbf8; border-top: 1px solid #e5e4dc; padding: 20px; text-align: center; font-size: 11px; color: #64748b; line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header-strip">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; opacity: 0.9;">Verified Travel Voucher</div>
                <h1 style="margin: 6px 0 0 0; font-size: 24px; font-weight: 800;">' . htmlspecialchars($siteName) . '</h1>
                <div class="badge-code">BOOKING ID: ' . $bookingCode . '</div>
            </div>

            <div class="body-content">
                <p style="font-size: 15px; margin: 0 0 8px 0; color: #0f172a;">Dear <strong>' . $customerName . '</strong>,</p>
                <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.6;">
                    Thank you for booking with <strong>' . htmlspecialchars($siteName) . '</strong>! Your ' . strtolower($typeLabel) . ' reservation is successfully confirmed. Below are your travel voucher and payment details.
                </p>

                <div class="trip-card">
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #3a5c3d; letter-spacing: 0.5px;">' . $typeLabel . '</div>
                    <h2 style="font-size: 16px; margin: 4px 0 12px 0; color: #0f172a; font-weight: 700;">' . $itemTitle . '</h2>
                    
                    <table class="data-grid">
                        <tr>
                            <td class="label">Primary Traveler:</td>
                            <td class="val">' . $customerName . '</td>
                        </tr>
                        <tr>
                            <td class="label">Registered Email:</td>
                            <td class="val">' . htmlspecialchars($b['customer_email'] ?? '') . '</td>
                        </tr>
                        <tr>
                            <td class="label">Contact Phone:</td>
                            <td class="val">' . htmlspecialchars($b['customer_phone'] ?? '') . '</td>
                        </tr>
                        <tr>
                            <td class="label">Departure / Check-in:</td>
                            <td class="val" style="color: #3a5c3d;">' . $travelDate . '</td>
                        </tr>
                        <tr>
                            <td class="label">Payment Reference:</td>
                            <td class="val" style="font-family: monospace;">' . $paymentId . '</td>
                        </tr>
                    </table>
                </div>

                <div class="price-table">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #283d2a; margin-bottom: 8px;">Payment &amp; Fare Summary</div>
                    <table style="width: 100%; font-size: 13px;">
                        <tr>
                            <td style="color: #475569; padding: 4px 0;">Total Itinerary Cost:</td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">₹' . $totalAmount . '</td>
                        </tr>
                        <tr>
                            <td style="color: #3a5c3d; font-weight: 700; padding: 4px 0;">Advance Token Paid (Online):</td>
                            <td style="text-align: right; font-weight: 800; color: #3a5c3d;">₹' . $advanceAmount . '</td>
                        </tr>
                        <tr style="border-top: 1px dashed #cfe0cf;">
                            <td style="color: #b45309; font-weight: 700; padding: 8px 0 0 0;">Balance Payable on Arrival:</td>
                            <td style="text-align: right; font-weight: 800; color: #b45309; padding: 8px 0 0 0;">₹' . $balanceAmount . '</td>
                        </tr>
                    </table>
                </div>

                <div style="text-align: center; margin: 24px 0;">
                    <a href="https://wa.me/' . htmlspecialchars($siteWhatsapp) . '?text=' . urlencode("Hi " . $siteName . ", I booked " . $itemTitle . " (ID: " . $bookingCode . ") and need support.") . '" class="btn-cta" target="_blank">
                        Chat with Tour Concierge on WhatsApp
                    </a>
                </div>

                <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin: 0;">
                    <strong>Next Steps:</strong> Our destination team and chauffeur details will be shared on WhatsApp and email 24 hours prior to your journey date. Please carry a valid government photo ID card.
                </p>
            </div>

            <div class="footer">
                <p style="margin: 0 0 4px 0;">24x7 Customer Support: <strong>' . htmlspecialchars($sitePhone) . '</strong> | <a href="mailto:' . htmlspecialchars($siteEmail) . '" style="color: #3a5c3d; font-weight: bold; text-decoration: none;">' . htmlspecialchars($siteEmail) . '</a></p>
                <p style="margin: 0;">&copy; ' . date('Y') . ' ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Generate a luxury HTML email for Flight Booking Requests / Inquiries
 */
function generateFlightInquiryEmailHtml(array $f) {
    $siteName = getSetting('site_name', 'Orion Advent');
    $siteEmail = getSetting('site_email', 'support@orionadvent.com');
    $sitePhone = getSetting('site_phone', '+91 98765 43210');
    $siteWhatsapp = getSetting('site_whatsapp', '919876543210');

    $bookingCode = htmlspecialchars($f['booking_code'] ?? 'FLT-10001');
    $customerName = htmlspecialchars($f['customer_name'] ?? 'Traveler');
    $flightTitle = htmlspecialchars($f['package_title'] ?? 'Flight Booking');
    $travelDate = !empty($f['travel_date']) ? date('l, d F Y', strtotime($f['travel_date'])) : 'Departure Date';
    $amount = number_format((float)($f['amount'] ?? 0));

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Flight Inquiry Confirmation - ' . $bookingCode . '</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f6f5ef; margin: 0; padding: 24px 12px; color: #1e293b; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e4dc; border-radius: 12px; overflow: hidden; }
            .header-strip { background: #1e293b; color: #ffffff; padding: 28px 24px; text-align: center; }
            .badge-code { display: inline-block; background: #ffffff; color: #0f172a; padding: 6px 14px; font-family: monospace; font-size: 14px; font-weight: bold; border-radius: 4px; margin-top: 12px; letter-spacing: 1px; }
            .body-content { padding: 28px 24px; }
            .trip-card { background: #fbfbf8; border: 1px solid #e5e4dc; padding: 18px; border-radius: 8px; margin: 20px 0; }
            .data-grid { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
            .data-grid td { padding: 8px 0; border-bottom: 1px solid #f1f0e9; }
            .label { color: #64748b; font-weight: 500; }
            .val { color: #0f172a; font-weight: 700; text-align: right; }
            .footer { background: #fbfbf8; border-top: 1px solid #e5e4dc; padding: 20px; text-align: center; font-size: 11px; color: #64748b; line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header-strip">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; opacity: 0.9;">Flight Reservation Request</div>
                <h1 style="margin: 6px 0 0 0; font-size: 24px; font-weight: 800;">' . htmlspecialchars($siteName) . ' Flights</h1>
                <div class="badge-code">QUERY ID: ' . $bookingCode . '</div>
            </div>

            <div class="body-content">
                <p style="font-size: 15px; margin: 0 0 8px 0; color: #0f172a;">Dear <strong>' . $customerName . '</strong>,</p>
                <p style="font-size: 13px; color: #475569; margin: 0; line-height: 1.6;">
                    We have received your flight reservation request. Our airline ticketing desk is verifying the latest seat availability and best group fare quote.
                </p>

                <div class="trip-card">
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #0284c7; letter-spacing: 0.5px;">Selected Airline Schedule</div>
                    <h2 style="font-size: 16px; margin: 4px 0 12px 0; color: #0f172a; font-weight: 700;">' . $flightTitle . '</h2>
                    
                    <table class="data-grid">
                        <tr>
                            <td class="label">Lead Passenger:</td>
                            <td class="val">' . $customerName . '</td>
                        </tr>
                        <tr>
                            <td class="label">Registered Email:</td>
                            <td class="val">' . htmlspecialchars($f['customer_email'] ?? '') . '</td>
                        </tr>
                        <tr>
                            <td class="label">Contact Phone:</td>
                            <td class="val">' . htmlspecialchars($f['customer_phone'] ?? '') . '</td>
                        </tr>
                        <tr>
                            <td class="label">Departure Date:</td>
                            <td class="val" style="color: #0284c7;">' . $travelDate . '</td>
                        </tr>
                        <tr>
                            <td class="label">Estimated Total Fare:</td>
                            <td class="val" style="color: #0f172a; font-size: 15px;">₹' . $amount . '</td>
                        </tr>
                    </table>
                </div>

                <div style="text-align: center; margin: 24px 0;">
                    <a href="https://wa.me/' . htmlspecialchars($siteWhatsapp) . '?text=' . urlencode("Hi " . $siteName . " Flight Desk, I submitted flight query " . $flightTitle . " (ID: " . $bookingCode . ").") . '" style="display: inline-block; background: #0284c7; color: #ffffff; padding: 12px 24px; font-weight: bold; font-size: 13px; text-decoration: none; border-radius: 6px;" target="_blank">
                        Connect with Flight Ticketing Desk on WhatsApp
                    </a>
                </div>

                <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin: 0;">
                    Our ticketing executive will reach out to you within 15 minutes to confirm passenger names as per Passport/Aadhar and issue official PNR e-tickets.
                </p>
            </div>

            <div class="footer">
                <p style="margin: 0 0 4px 0;">24x7 Flight Helpline: <strong>' . htmlspecialchars($sitePhone) . '</strong> | <a href="mailto:' . htmlspecialchars($siteEmail) . '" style="color: #0284c7; font-weight: bold; text-decoration: none;">' . htmlspecialchars($siteEmail) . '</a></p>
                <p style="margin: 0;">&copy; ' . date('Y') . ' ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Generate a clean HTML email notification for Job Applicants (Shortlisted / Selected / Update)
 */
function generateJobStatusEmailHtml($candidateName, $jobTitle, $status, $customNotes = '') {
    $siteName = getSetting('site_name', 'Orion Advent');
    $siteEmail = getSetting('site_email', 'careers@orionadvent.com');
    $sitePhone = getSetting('site_phone', '+91 98765 43210');

    $isShortlisted = in_array(strtolower($status), ['shortlisted', 'selected', 'interview']);
    $statusHeading = $isShortlisted ? 'Application Status Update: Congratulations!' : 'Application Status Update';
    $statusBadge = strtoupper(htmlspecialchars($status));

    $defaultMsg = $isShortlisted 
        ? "We are delighted to inform you that your application for the <strong>" . htmlspecialchars($jobTitle) . "</strong> role at <strong>" . htmlspecialchars($siteName) . "</strong> has been shortlisted! Our talent acquisition team was impressed by your profile and would like to proceed with the next round of discussions."
        : "Thank you for applying for the <strong>" . htmlspecialchars($jobTitle) . "</strong> position at <strong>" . htmlspecialchars($siteName) . "</strong>. Our recruitment team has reviewed your application.";

    $notesSection = !empty($customNotes) ? '
        <div style="background: #f8fafc; border-left: 4px solid #068285; padding: 14px 18px; margin: 18px 0; border-radius: 4px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #068285; margin-bottom: 4px;">Message from Talent Acquisition Team</div>
            <div style="font-size: 13px; color: #334155; line-height: 1.6;">' . nl2br(htmlspecialchars($customNotes)) . '</div>
        </div>
    ' : '';

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $statusHeading . '</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px 12px; color: #1e293b; }
            .container { max-width: 580px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
            .header { background: #068285; color: #ffffff; padding: 28px 24px; text-align: center; }
            .badge { display: inline-block; background: #ffffff; color: #068285; padding: 6px 14px; font-size: 12px; font-weight: 800; border-radius: 9999px; margin-top: 10px; letter-spacing: 1px; }
            .content { padding: 28px 24px; font-size: 14px; line-height: 1.6; color: #334155; }
            .job-card { background: #f0fdfa; border: 1px solid #ccfbf1; padding: 14px 18px; border-radius: 8px; margin: 16px 0; }
            .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center; font-size: 11px; color: #64748b; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; opacity: 0.9;">' . htmlspecialchars($siteName) . ' Careers</div>
                <h1 style="margin: 6px 0 0 0; font-size: 22px; font-weight: 800;">' . $statusHeading . '</h1>
                <div class="badge">' . $statusBadge . '</div>
            </div>

            <div class="content">
                <p style="font-size: 15px; margin: 0 0 12px 0; color: #0f172a;">Dear <strong>' . htmlspecialchars($candidateName) . '</strong>,</p>
                <p style="margin: 0 0 14px 0;">' . $defaultMsg . '</p>
                
                <div class="job-card">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0f766e; margin-bottom: 2px;">Applied Position</div>
                    <div style="font-size: 16px; font-weight: 800; color: #0f172a;">' . htmlspecialchars($jobTitle) . '</div>
                </div>

                ' . $notesSection . '

                <p style="margin: 16px 0 0 0; font-size: 13px; color: #64748b;">
                    If you have any questions regarding your application or the recruitment process, feel free to reply directly to this email or reach our talent team at <a href="mailto:' . htmlspecialchars($siteEmail) . '" style="color: #068285; font-weight: 700; text-decoration: none;">' . htmlspecialchars($siteEmail) . '</a>.
                </p>
            </div>

            <div class="footer">
                <p style="margin: 0 0 4px 0;">Talent Acquisition &bull; <strong>' . htmlspecialchars($siteName) . '</strong> &bull; ' . htmlspecialchars($sitePhone) . '</p>
                <p style="margin: 0;">&copy; ' . date('Y') . ' ' . htmlspecialchars($siteName) . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
}


