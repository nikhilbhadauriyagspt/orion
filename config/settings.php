<?php
/**
 * Orion Advent Settings & Global Configuration Helper
 * Loads website branding, contact info, SMTP details, and OTP mode dynamically from database
 */
require_once __DIR__ . '/db.php';

function getGlobalSettings($reload = false) {
    static $settings = null;
    if ($settings !== null && !$reload) {
        return $settings;
    }

    $defaultSettings = [
        'site_name' => 'Orion Advent',
        'site_tagline' => 'Your Passport to Adventure',
        'site_phone' => '+91 98765 43210',
        'site_email' => 'support@orionadvent.com',
        'site_whatsapp' => '919876543210',
        'site_address' => 'DLF Cyber City, Tower B, Gurugram, Haryana - 122002',
        'site_logo' => 'assets/images/logo/logo.webp',
        
        // SMTP / Mail Configuration
        'mail_mode' => 'testing', // 'testing' or 'live'
        'testing_otp' => '123456',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '465',
        'smtp_user' => 'support@orionadvent.com',
        'smtp_pass' => '',
        'smtp_encryption' => 'ssl', // 'ssl' or 'tls'
        'smtp_from_name' => 'Orion Advent Travel',
        'smtp_from_email' => 'support@orionadvent.com',

        // Google & Facebook Social Login OAuth Configuration
        'google_login_active' => '0', // '1' or '0'
        'google_client_id' => '',
        'google_client_secret' => '',
        'facebook_login_active' => '0', // '1' or '0'
        'facebook_app_id' => '',
        'facebook_app_secret' => '',
        
        // Flight Search API & Commission / Markup Configuration
        'flight_api_provider' => 'ignav', // 'ignav' (primary live GDS API)
        'ignav_api_key' => 'ignav_fO-UFojh4eaCGFqHMzg-hbHj_FSKEtBo',
        'usd_to_inr_rate' => '86.5',
        'flight_markup_type' => 'fixed', // 'fixed' (₹) or 'percentage' (%)
        'flight_commission_domestic' => '350', // e.g. ₹350 or 5%
        'flight_commission_international' => '850', // e.g. ₹850 or 8%
        'flight_convenience_fee' => '249', // e.g. ₹249 per ticket

        // Location & Places Search API Configuration
        'location_provider' => 'osm', // 'osm' (free OpenStreetMap) or 'google' (Google Places) or 'mapbox'
        'google_places_api_key' => '',
        'mapbox_api_key' => '',
        'locationiq_api_key' => '',

        // Payment Gateway API Configuration (Token Lock / Advance)
        'payment_gateway_provider' => 'razorpay', // 'razorpay', 'cashfree', 'stripe', 'offline'
        'payment_mode' => 'test', // 'test' or 'live'
        'booking_advance_type' => 'percentage', // 'percentage' or 'fixed'
        'booking_advance_percent' => '30', // e.g. 30% advance
        'booking_advance_fixed' => '2500', // e.g. ₹2,500 token
        'razorpay_key_id' => 'rzp_test_placeholder',
        'razorpay_key_secret' => 'rzp_secret_placeholder',
        'stripe_publishable_key' => '',
        'stripe_secret_key' => '',

        // Social links
        'social_facebook' => 'https://facebook.com',
        'social_instagram' => 'https://instagram.com',
        'social_twitter' => 'https://twitter.com',
        'social_youtube' => 'https://youtube.com',
        'social_linkedin' => 'https://linkedin.com',

        // Legal & Policy Documents
        'policy_last_updated' => 'October 2026',
        'policy_privacy' => '<h3>1. Information We Collect</h3><p>At Orion Advent, we collect personal information necessary to deliver seamless travel experiences. This includes your name, email address, phone number, government ID or passport numbers (for international packages and flight bookings), and payment verification details.</p><h3>2. How We Use Your Information</h3><p>Your information is used strictly to process reservations, issue verified airline tickets, book 4-star and 5-star hotel rooms, arrange private sanitized cab transfers, and provide 24x7 WhatsApp concierge support during your trips.</p><h3>3. Data Security & SSL Encryption</h3><p>We implement enterprise-grade 256-bit SSL encryption and strict data protection protocols. We never sell, rent, or lease your personal data to third-party marketing companies.</p><h3>4. Third-Party Travel Service Providers</h3><p>To fulfill your travel itinerary, relevant details are securely shared with airlines (GDS inventory), certified resort partners, and authorized payment gateways (e.g. Razorpay).</p><h3>5. Contact Our Privacy Officer</h3><p>For inquiries regarding your personal data or privacy preferences, contact our support desk at privacy@orionadvent.com or call +91 98765 43210.</p>',
        'policy_terms' => '<h3>1. Acceptance of Agreement</h3><p>By accessing or booking holiday packages, flights, or hotel reservations through Orion Advent, you agree to be bound by these Terms & Conditions. Please read them thoroughly prior to confirming your reservation.</p><h3>2. Booking & Advance Token Confirmation</h3><p>A booking is deemed confirmed once the initial token advance (e.g. 30% or fixed token) is successfully received. The remaining balance is payable upon hotel check-in or airport arrival as specified on your booking voucher.</p><h3>3. Flight & Airline Regulations</h3><p>All flight schedules, baggage allowances (e.g. 15kg check-in, 7kg cabin), and web check-in requirements are governed by respective airline carriers (IndiGo, Air India, Emirates, etc.).</p><h3>4. Hotel Check-In & Government ID</h3><p>All adult guests must carry valid government-issued photo identification (Aadhaar, Passport, Driving License) at check-in. Standard check-in time is 02:00 PM and check-out is 11:00 AM.</p><h3>5. International Travel Passports & Visas</h3><p>For international itineraries, travelers must hold a passport valid for at least 6 months from the date of travel, along with necessary visa approvals or visa-on-arrival eligibility.</p>',
        'policy_cancellation' => '<h3>1. Token Advance Guarantee & Cancellation Slabs</h3><p>We understand travel plans can change. Our tour cancellation policy is designed with maximum fairness and flexibility:</p><ul><li><strong>30+ Days Prior to Travel Date:</strong> 100% refund of the advance token amount minus a nominal processing fee (₹500).</li><li><strong>15 to 29 Days Prior:</strong> 70% refund of the advance payment.</li><li><strong>7 to 14 Days Prior:</strong> 50% refund of the advance payment.</li><li><strong>Less than 7 Days / No-Show:</strong> Non-refundable due to pre-committed hotel, cab, and permit reservations.</li></ul><h3>2. Flight Ticket Cancellations</h3><p>Flight cancellations follow airline-specific fare rules. Refundable tickets will be processed instantly via GDS refund channels. Convenience fees and airline cancellation charges apply.</p><h3>3. Hotel Booking Cancellations</h3><p>Most curated hotel stays offer free cancellation up to 24 to 48 hours prior to check-in, as indicated explicitly on your booking confirmation voucher.</p><h3>4. Refund Processing Timeframe</h3><p>Approved refunds are processed back to the original payment source (UPI, Credit/Debit Card, Net Banking) within 5 to 7 working business days.</p>',
        'policy_booking' => '<h3>1. Transparent Wholesale Fares</h3><p>Orion Advent operates with complete price transparency. The total vacation amount displayed includes all base accommodations, breakfasts, transfers, and sightseeing passes. Zero surprise resort cuts or hidden checkout taxes.</p><h3>2. Token Lock Policy</h3><p>Lock your preferred travel dates and holiday prices with an advance token payment. The pending balance is payable seamlessly via UPI, card, or cash upon arrival at your destination hotel.</p><h3>3. Payment Security</h3><p>All digital payments are processed through RBI-authorized, PCI-DSS compliant payment gateways with multi-factor authentication (OTP verification).</p>',
    ];

    $pdo = getDBConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM `settings`");
            while ($row = $stmt->fetch()) {
                $defaultSettings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // fallback to defaults if table not yet created
        }
    }

    $settings = $defaultSettings;
    return $settings;
}

/**
 * Shorthand helper for getting a single setting value
 */
function getSetting($key, $default = '') {
    $settings = getGlobalSettings();
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Helper to update multiple settings key-values
 */
function updateSettings(array $data) {
    $pdo = getDBConnection();
    if (!$pdo) return false;

    try {
        $stmt = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
        foreach ($data as $k => $v) {
            $stmt->execute([$k, is_array($v) ? json_encode($v) : trim((string)$v)]);
        }
        // Reload cache
        getGlobalSettings(true);
        return true;
    } catch (Exception $e) {
        return false;
    }
}
