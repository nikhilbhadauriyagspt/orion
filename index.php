<?php
require_once __DIR__ . '/config/settings.php';
$siteName = getSetting('site_name', 'Orion Advent');
$siteTagline = getSetting('site_tagline', 'Your Passport to Adventure');
$pageTitle = htmlspecialchars($siteName) . ' - ' . htmlspecialchars($siteTagline) . ' | Tour Packages, Flights & Hotels';
require_once 'components/header.php';
require_once 'components/navbar.php';
require_once 'components/hero.php';
require_once 'components/domestic-packages.php';
require_once 'components/flights-section.php';
require_once 'components/international-packages.php';
require_once 'components/hotels-section.php';
require_once 'components/cruises-section.php';
require_once 'components/experience-themes.php';
require_once 'components/about-section.php';
require_once 'components/reviews-section.php';
require_once 'components/how-it-works.php';
?>

<?php
require_once 'components/footer.php';
?>
